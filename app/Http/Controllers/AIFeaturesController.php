<?php

namespace App\Http\Controllers;

use App\Services\GroqAIService;
use App\Services\RAGService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

/**
 * AIFeaturesController
 *
 * Handles all AI-powered feature endpoints beyond the main chat:
 * 1. Daily Dashboard Briefing
 * 2. Personalized Routine Advisor
 * 3. Class Task AI Tips
 * 4. PDF & Notes Summarizer
 * 5. Question Bank Practice Generator
 *
 * All endpoints reuse GroqAIService for API calls and
 * RAGService for context building.
 */
class AIFeaturesController extends Controller
{
    protected GroqAIService $groq;
    protected RAGService $rag;

    public function __construct(GroqAIService $groq, RAGService $rag)
    {
        $this->groq = $groq;
        $this->rag  = $rag;
    }

    /**
     * Generate a personalized daily briefing for the dashboard.
     * Route: GET /api/ai/daily-briefing
     */
    public function dailyBriefing(Request $request): JsonResponse
    {
        $user = Auth::user();

        try {
            if (!$this->groq->isConfigured()) {
                return response()->json([
                    'response' => "Good " . $this->getTimeGreeting() . ", {$user->name}! 👋 AI briefing is being set up. Check your routine and tasks pages for today's details.",
                    'error'    => true,
                ], 503);
            }

            $systemPrompt = $this->rag->buildBriefingPrompt($user);

            $response = $this->groq->chat($systemPrompt, [
                ['role' => 'user', 'content' => 'Give me my daily briefing for today.'],
            ]);

            return response()->json([
                'response' => $response,
                'error'    => false,
            ]);

        } catch (\RuntimeException $e) {
            Log::error('[AI:Briefing] Error for user ' . $user->id . ': ' . $e->getMessage());

            return response()->json([
                'response' => "Good " . $this->getTimeGreeting() . ", {$user->name}! I couldn't generate your briefing right now, but check your routine page for today's schedule. 📚",
                'error'    => true,
            ], 500);
        }
    }

    /**
     * Handle routine advisor chat messages.
     * Route: POST /api/ai/routine-advisor
     */
    public function routineAdvisor(Request $request): JsonResponse
    {
        $request->validate([
            'message' => 'required|string|max:2000',
        ]);

        $user    = Auth::user();
        $message = strip_tags($request->input('message'));

        try {
            if (!$this->groq->isConfigured()) {
                return response()->json([
                    'response' => "AI advisor is not configured yet. Please check your routine page for your full schedule.",
                    'error'    => true,
                ], 503);
            }

            $systemPrompt = $this->rag->buildRoutineAdvisorPrompt($user);

            $response = $this->groq->chat($systemPrompt, [
                ['role' => 'user', 'content' => $message],
            ]);

            return response()->json([
                'response' => $response,
                'error'    => false,
            ]);

        } catch (\RuntimeException $e) {
            Log::error('[AI:Routine] Error for user ' . $user->id . ': ' . $e->getMessage());

            return response()->json([
                'response' => "I'm having trouble analyzing your routine right now. Please try again shortly. 🔄",
                'error'    => true,
            ], 500);
        }
    }

    /**
     * Generate dynamic AI tips for a specific class task.
     * Route: POST /api/ai/task-tips
     */
    public function taskTips(Request $request): JsonResponse
    {
        $request->validate([
            'title'       => 'required|string|max:500',
            'type'        => 'required|string|max:50',
            'course_code' => 'required|string|max:50',
            'topic'       => 'nullable|string|max:500',
            'deadline'    => 'nullable|string|max:100',
            'description' => 'nullable|string|max:2000',
        ]);

        $user = Auth::user();

        try {
            if (!$this->groq->isConfigured()) {
                return response()->json([
                    'tip_1' => 'Start with the core concepts first, then move to practical applications.',
                    'tip_2' => 'Break the task into smaller sub-tasks and tackle them one by one.',
                    'error' => true,
                ], 503);
            }

            $systemPrompt = $this->rag->buildTaskTipPrompt($user, $request->only([
                'title', 'type', 'course_code', 'topic', 'deadline', 'description',
            ]));

            $response = $this->groq->chat($systemPrompt, [
                ['role' => 'user', 'content' => "Generate 2 actionable study tips for this task."],
            ]);

            // Try to parse JSON response for structured tips
            $tips = $this->parseTaskTips($response);

            return response()->json([
                'tip_1' => $tips['tip_1'],
                'tip_2' => $tips['tip_2'],
                'error' => false,
            ]);

        } catch (\RuntimeException $e) {
            Log::error('[AI:TaskTips] Error for user ' . $user->id . ': ' . $e->getMessage());

            return response()->json([
                'tip_1' => 'Focus on understanding the key concepts before diving into details.',
                'tip_2' => 'Review similar past assignments for reference and patterns.',
                'error' => true,
            ], 500);
        }
    }

    /**
     * Summarize a PDF/note material using AI.
     * Route: POST /api/ai/summarize-notes
     */
    public function summarizeNotes(Request $request): JsonResponse
    {
        $request->validate([
            'title'       => 'required|string|max:500',
            'course_code' => 'required|string|max:50',
            'department'  => 'required|string|max:100',
            'file_type'   => 'nullable|string|max:20',
            'type'        => 'nullable|string|max:50',
            'file_path'   => 'nullable|string|max:1000',
        ]);

        $user = Auth::user();

        try {
            if (!$this->groq->isConfigured()) {
                return response()->json([
                    'response' => "AI summarizer is being set up. Please review the material directly.",
                    'error'    => true,
                ], 503);
            }

            $materialData = $request->only([
                'title', 'course_code', 'department', 'file_type', 'type', 'file_path',
            ]);

            // Attempt to extract text if it's a PDF
            $extractedText = '';
            if (!empty($materialData['file_path']) && strtolower($materialData['file_type'] ?? '') === 'pdf') {
                $fullPath = storage_path('app/public/' . $materialData['file_path']);
                if (file_exists($fullPath)) {
                    try {
                        $parser = new \Smalot\PdfParser\Parser();
                        $pdf    = $parser->parseFile($fullPath);
                        $extractedText = $pdf->getText();
                        // Truncate to avoid exceeding max tokens (roughly ~3000 chars is safe for prompt context)
                        $extractedText = substr($extractedText, 0, 4000); 
                    } catch (\Exception $e) {
                        Log::warning('[AI:Notes] Could not parse PDF for user ' . $user->id . ': ' . $e->getMessage());
                    }
                }
            }

            $systemPrompt = $this->rag->buildNotesSummaryPrompt($user, $materialData, $extractedText);

            $response = $this->groq->chat($systemPrompt, [
                ['role' => 'user', 'content' => "Summarize this material and give me key study points."],
            ]);

            return response()->json([
                'response' => $response,
                'error'    => false,
            ]);

        } catch (\RuntimeException $e) {
            Log::error('[AI:Notes] Error for user ' . $user->id . ': ' . $e->getMessage());

            return response()->json([
                'response' => "I couldn't summarize this material right now. Please try again. 🔄",
                'error'    => true,
            ], 500);
        }
    }

    /**
     * Generate practice questions from the question bank.
     * Route: POST /api/ai/practice-generator
     */
    public function practiceGenerator(Request $request): JsonResponse
    {
        $request->validate([
            'message'     => 'required|string|max:2000',
            'history'     => 'nullable|array',
            'history.*.role' => 'required|string|in:user,assistant',
            'history.*.content' => 'required|string',
            'department'  => 'nullable|string|max:100',
            'course_code' => 'nullable|string|max:50',
            'semester'    => 'nullable|string|max:50',
            'term'        => 'nullable|string|max:20',
        ]);

        $user       = Auth::user();
        $message    = strip_tags($request->input('message'));
        $history    = $request->input('history', []);
        
        $filters = $request->only(['department', 'course_code', 'semester', 'term']);

        try {
            if (!$this->groq->isConfigured()) {
                return response()->json([
                    'response' => "Practice generator is being set up. Check the question bank for past questions.",
                    'error'    => true,
                ], 503);
            }

            $systemPrompt = $this->rag->buildQuestionBankPrompt($user, $filters);

            $messages = [];
            // Take the last 10 messages from history to keep context without overloading tokens
            $limitedHistory = array_slice($history, -10);
            foreach ($limitedHistory as $msg) {
                $messages[] = [
                    'role' => $msg['role'],
                    'content' => strip_tags($msg['content'])
                ];
            }
            // Append the new message
            $messages[] = ['role' => 'user', 'content' => $message];

            $response = $this->groq->chat($systemPrompt, $messages);

            return response()->json([
                'response' => $response,
                'error'    => false,
            ]);

        } catch (\RuntimeException $e) {
            Log::error('[AI:Practice] Error for user ' . $user->id . ': ' . $e->getMessage());

            return response()->json([
                'response' => "I couldn't generate practice questions right now. Please try again. 🔄",
                'error'    => true,
            ], 500);
        }
    }

    /**
     * Parse AI response to extract structured tip_1 and tip_2.
     * Falls back to splitting the text if JSON parsing fails.
     */
    protected function parseTaskTips(string $response): array
    {
        // Try JSON parse first
        $decoded = json_decode($response, true);
        if (is_array($decoded) && isset($decoded['tip_1']) && isset($decoded['tip_2'])) {
            return $decoded;
        }

        // Try to extract JSON from response that may contain surrounding text
        if (preg_match('/\{[^{}]*"tip_1"\s*:\s*"([^"]+)"[^{}]*"tip_2"\s*:\s*"([^"]+)"[^{}]*\}/', $response, $matches)) {
            return ['tip_1' => $matches[1], 'tip_2' => $matches[2]];
        }

        // Fallback: split by numbered lines or double newlines
        $lines = preg_split('/\n+/', trim($response));
        $tips  = array_filter($lines, fn($l) => strlen(trim($l)) > 10);
        $tips  = array_values($tips);

        return [
            'tip_1' => isset($tips[0]) ? trim(preg_replace('/^\d+[\.\)]\s*/', '', $tips[0])) : 'Break the task into smaller parts and start with the fundamentals.',
            'tip_2' => isset($tips[1]) ? trim(preg_replace('/^\d+[\.\)]\s*/', '', $tips[1])) : 'Review class notes and past examples related to this topic.',
        ];
    }

    /**
     * Get time-of-day greeting.
     */
    protected function getTimeGreeting(): string
    {
        $hour = (int) now()->format('G');
        if ($hour < 12) return 'morning';
        if ($hour < 17) return 'afternoon';
        return 'evening';
    }
}
