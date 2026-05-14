<?php

namespace App\Http\Controllers;

use App\Services\GroqAIService;
use App\Services\RAGService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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
                $chat = \App\Models\AiChat::create([
                    'user_id' => $user->id,
                    'type' => 'buddy',
                    'title' => $title,
                    'history' => $history,
                ]);
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

        $message = strip_tags($request->input('message'));
        $history = $request->input('history', []);
        $chatId  = $request->input('chat_id');
        $sessionId = session()->getId();

        $chat = null;
        if ($chatId) {
            $chat = \App\Models\AiChat::where('id', $chatId)->where('session_id', $sessionId)->first();
            if ($chat) {
                $history = $chat->history;
            }
        }

        try {
            if (!$this->groq->isConfigured()) {
                return response()->json([
                    'response' => "I'm currently being set up. Please visit daffodilvarsity.edu.bd for information or try again later.",
                    'error'    => true,
                ], 503);
            }

            $systemPrompt = $this->rag->buildVisitorSystemPrompt();
            $messages = $this->buildMessageArray($history, $message);
            $response = $this->groq->chat($systemPrompt, $messages);

            // Save history
            $history[] = ['role' => 'user', 'content' => $message];
            $history[] = ['role' => 'assistant', 'content' => $response];

            if (!$chat) {
                $title = substr($message, 0, 25) . (strlen($message) > 25 ? '...' : '');
                $chat = \App\Models\AiChat::create([
                    'session_id' => $sessionId,
                    'type' => 'visitor',
                    'title' => $title,
                    'history' => $history,
                ]);
            } else {
                $chat->update(['history' => $history]);
            }

            return response()->json([
                'response' => $response,
                'chat_id'  => $chat->id,
                'error'    => false,
            ]);

        } catch (\RuntimeException $e) {
            Log::error('[VisitorAI] Chat error: ' . $e->getMessage());

            return response()->json([
                'response' => "I'm having trouble connecting right now. For immediate help, please visit daffodilvarsity.edu.bd or call the admission helpline. 📞",
                'error'    => true,
            ], 500);
        }
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
