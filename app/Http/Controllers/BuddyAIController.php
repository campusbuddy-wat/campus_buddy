<?php

namespace App\Http\Controllers;

use App\Services\GroqAIService;
use App\Services\RAGService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * BuddyAIController
 *
 * API controller handling chat interactions for both:
 * 1. Buddy AI (authenticated students) — personalized with RAG context
 * 2. Visitor AI (public visitors) — generic DIU admission assistant
 *
 * Both endpoints accept a message + optional chat history,
 * build appropriate system prompts, and forward to Groq API.
 */
class BuddyAIController extends Controller
{
    protected GroqAIService $groq;
    protected RAGService $rag;

    public function __construct(GroqAIService $groq, RAGService $rag)
    {
        $this->groq = $groq;
        $this->rag  = $rag;
    }

    /**
     * Handle authenticated student chat messages.
     * Route: POST /api/buddy-chat
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function chat(Request $request): JsonResponse
    {
        $request->validate([
            'chat_id' => 'nullable|integer',
            'message' => 'required|string|max:2000',
            'history' => 'nullable|array|max:20',
        ]);

        $user    = Auth::user();
        $message = strip_tags($request->input('message'));
        $history = $request->input('history', []);
        $chatId  = $request->input('chat_id');

        $chat = null;
        if ($chatId) {
            $chat = \App\Models\AiChat::where('id', $chatId)->where('user_id', $user->id)->first();
            if ($chat) {
                $history = $chat->history;
            }
        }

        try {
            if (!$this->groq->isConfigured()) {
                return response()->json([
                    'response' => "I'm currently being set up and not fully operational yet. Please try again later or contact your administrator.",
                    'error'    => true,
                ], 503);
            }

            $systemPrompt = $this->rag->buildStudentSystemPrompt($user);
            $messages = $this->buildMessageArray($history, $message);
            $response = $this->groq->chat($systemPrompt, $messages);

            // Save History
            $history[] = ['role' => 'user', 'content' => $message];
            $history[] = ['role' => 'assistant', 'content' => $response];

            if (!$chat) {
                $title = substr($message, 0, 25) . (strlen($message) > 25 ? '...' : '');
                try {
                    $chat = \App\Models\AiChat::create([
                        'user_id' => $user->id,
                        'type' => 'buddy',
                        'title' => $title,
                        'history' => $history,
                    ]);
                } catch (\Illuminate\Database\QueryException $e) {
                    // 23505 is PostgreSQL unique violation. Auto-fix sequence and retry.
                    if ($e->getCode() == '23505') {
                        \Illuminate\Support\Facades\DB::statement("SELECT setval(pg_get_serial_sequence('ai_chats', 'id'), (SELECT COALESCE(MAX(id), 1) FROM ai_chats));");
                        $chat = \App\Models\AiChat::create([
                            'user_id' => $user->id,
                            'type' => 'buddy',
                            'title' => $title,
                            'history' => $history,
                        ]);
                    } else {
                        throw $e;
                    }
                }
            } else {
                $chat->update(['history' => $history]);
            }

            return response()->json([
                'response' => $response,
                'chat_id'  => $chat->id,
                'error'    => false,
            ]);

        } catch (\RuntimeException $e) {
            Log::error('[BuddyAI] Chat error for user ' . $user->id . ': ' . $e->getMessage());

            return response()->json([
                'response' => "I'm having trouble connecting right now. Please try again in a moment. 🔄",
                'error'    => true,
            ], 500);
        }
    }

    /**
     * Handle public visitor chat messages (no auth required).
     * Route: POST /api/buddy-visitor
     *
     * Proxies the request to the Python RAG microservice (ai_service/).
     * The Python service handles retrieval from Qdrant + Groq generation.
     * If the Python service is unreachable, falls back to a helpful message.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function visitorChat(Request $request): JsonResponse
    {
        $request->validate([
            'chat_id' => 'nullable|integer',
            'message' => 'required|string|max:2000',
            'history' => 'nullable|array|max:20',
        ]);

        $message   = strip_tags($request->input('message'));
        $history   = $request->input('history', []);
        $chatId    = $request->input('chat_id');
        $sessionId = session()->getId();

        $chat = null;
        if ($chatId) {
            $chat = \App\Models\AiChat::where('id', $chatId)->where('session_id', $sessionId)->first();
            if ($chat) {
                $history = $chat->history;
            }
        }

        // ── Call the Python RAG microservice ──────────────────────────────────
        $aiServiceUrl = config('services.visitor_ai.url');
        
        if (empty($aiServiceUrl)) {
            Log::error('[VisitorAI] Python service URL is empty. Please set VISITOR_AI_URL in your Render dashboard environment variables.');
            return response()->json([
                'response' => "I'm having trouble connecting to the AI service right now. For immediate help, please visit daffodilvarsity.edu.bd or call the admission helpline. 📞",
                'error'    => true,
            ], 503);
        }

        if (!str_starts_with($aiServiceUrl, 'http://') && !str_starts_with($aiServiceUrl, 'https://')) {
            Log::error('[VisitorAI] Python service URL is invalid (missing http:// or https://): ' . $aiServiceUrl);
            return response()->json([
                'response' => "I'm having trouble connecting to the AI service right now. For immediate help, please visit daffodilvarsity.edu.bd or call the admission helpline. 📞",
                'error'    => true,
            ], 503);
        }

        $aiServiceUrl = rtrim($aiServiceUrl, '/');
        Log::info('[VisitorAI] Sending chat request to: ' . "{$aiServiceUrl}/api/chat");

        try {
            $aiResponse = Http::timeout(30)
                ->post("{$aiServiceUrl}/api/chat", [
                    'message' => $message,
                    'history' => array_slice($history, -8),   // last 4 turns
                ]);

            if ($aiResponse->failed()) {
                $errorDetail = $aiResponse->json('detail') ?? $aiResponse->body();
                throw new \RuntimeException(
                    'Python AI service returned status ' . $aiResponse->status() . ' - ' . $errorDetail
                );
            }

            $data        = $aiResponse->json();
            $rawAnswer   = $data['answer']   ?? 'Sorry, I could not generate a response.';
            $sources     = $data['sources']  ?? [];
            $found       = $data['found']    ?? false;
            $finalResponse = $rawAnswer;

        } catch (\Exception $e) {
            Log::error('[VisitorAI] Python service error: ' . $e->getMessage());
            return response()->json([
                'response' => "I'm having trouble connecting to the AI service right now. For immediate help, please visit daffodilvarsity.edu.bd or call the admission helpline. 📞",
                'error'    => true,
            ], 503);
        }

        // ── Persist RAW chat history to the database ─────────────────────────
        $history[] = ['role' => 'user',      'content' => $message];
        $history[] = ['role' => 'assistant', 'content' => $rawAnswer];

        if (!$chat) {
            $title = substr($message, 0, 25) . (strlen($message) > 25 ? '...' : '');
            try {
                $chat = \App\Models\AiChat::create([
                    'session_id' => $sessionId,
                    'type'       => 'visitor',
                    'title'      => $title,
                    'history'    => $history,
                ]);
            } catch (\Illuminate\Database\QueryException $e) {
                if ($e->getCode() == '23505') {
                    \Illuminate\Support\Facades\DB::statement("SELECT setval(pg_get_serial_sequence('ai_chats', 'id'), (SELECT COALESCE(MAX(id), 1) FROM ai_chats));");
                    $chat = \App\Models\AiChat::create([
                        'session_id' => $sessionId,
                        'type'       => 'visitor',
                        'title'      => $title,
                        'history'    => $history,
                    ]);
                } else {
                    throw $e;
                }
            }
        } else {
            $chat->update(['history' => $history]);
        }

        return response()->json([
            'response' => $finalResponse,
            'sources'  => $sources ?? [],
            'found'    => $found   ?? false,
            'chat_id'  => $chat->id,
            'error'    => false,
        ]);
    }

    /**
     * Get specific chat history.
     */
    public function getChat($id): JsonResponse
    {
        $chat = \App\Models\AiChat::find($id);
        if (!$chat) {
            return response()->json(['error' => 'Chat not found'], 404);
        }

        // Check ownership
        if ($chat->type === 'buddy' && $chat->user_id !== Auth::id()) {
            return response()->json(['error' => 'Forbidden'], 403);
        }
        if ($chat->type === 'visitor' && $chat->session_id !== session()->getId()) {
            return response()->json(['error' => 'Forbidden'], 403);
        }

        return response()->json([
            'id' => $chat->id,
            'title' => $chat->title,
            'history' => $chat->history,
        ]);
    }

    /**
     * Delete a chat session.
     */
    public function deleteChat($id): JsonResponse
    {
        $chat = \App\Models\AiChat::find($id);
        if (!$chat) {
            return response()->json(['error' => 'Chat not found', 'success' => false], 404);
        }

        // Check ownership
        if ($chat->type === 'buddy') {
            if ($chat->user_id !== Auth::id()) {
                return response()->json(['error' => 'Forbidden', 'success' => false], 403);
            }
        } else {
            if ($chat->session_id !== session()->getId()) {
                return response()->json(['error' => 'Forbidden', 'success' => false], 403);
            }
        }

        $chat->delete();
        return response()->json(['success' => true]);
    }

    /**
     * Rename a chat session.
     */
    public function renameChat(Request $request, $id): JsonResponse
    {
        $request->validate([
            'title' => 'required|string|max:100',
        ]);

        $chat = \App\Models\AiChat::find($id);
        if (!$chat) {
            return response()->json(['error' => 'Chat not found', 'success' => false], 404);
        }

        // Check ownership
        if ($chat->type === 'buddy') {
            if ($chat->user_id !== Auth::id()) {
                return response()->json(['error' => 'Forbidden', 'success' => false], 403);
            }
        } else {
            if ($chat->session_id !== session()->getId()) {
                return response()->json(['error' => 'Forbidden', 'success' => false], 403);
            }
        }

        $chat->update([
            'title' => strip_tags($request->input('title'))
        ]);

        return response()->json(['success' => true]);
    }

    /**
     * Build the messages array from history + current user message.
     * Sanitizes all content to prevent prompt injection.
     *
     * @param array  $history
     * @param string $currentMessage
     * @return array
     */
    protected function buildMessageArray(array $history, string $currentMessage): array
    {
        $messages = [];

        // Add sanitized chat history
        foreach ($history as $msg) {
            $messages[] = [
                'role'    => $msg['role'],
                'content' => strip_tags($msg['content']),
            ];
        }

        // Add the current user message
        $messages[] = [
            'role'    => 'user',
            'content' => $currentMessage,
        ];

        return $messages;
    }
}
