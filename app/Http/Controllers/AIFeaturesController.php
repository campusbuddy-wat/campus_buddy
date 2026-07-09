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
     * Extract text content from uploaded file (PDF, DOCX, DOC, PPTX).
     *
     * @param  string $fullPath    Absolute filesystem path to the file
     * @param  string $extension   File extension (lowercase)
     * @return string              Extracted raw text (up to 12 000 chars)
     */
    private function extractFileText(string $fullPath, string $extension): string
    {
        $text = '';

        try {
            switch ($extension) {

                // ── PDF ───────────────────────────────────────────────────────
                case 'pdf':
                    $parser = new \Smalot\PdfParser\Parser();
                    $pdf    = $parser->parseFile($fullPath);
                    $text   = $pdf->getText();
                    break;

                // ── DOCX ──────────────────────────────────────────────────────
                // PhpWord's Word2007 reader supports .docx reading
                case 'docx':
                    $phpWord = \PhpOffice\PhpWord\IOFactory::load($fullPath, 'Word2007');
                    foreach ($phpWord->getSections() as $section) {
                        $text .= $this->extractPhpWordSectionText($section);
                    }
                    break;

                // ── DOC (legacy Word) ──────────────────────────────────────────
                case 'doc':
                    $phpWord = \PhpOffice\PhpWord\IOFactory::load($fullPath, 'MsDoc');
                    foreach ($phpWord->getSections() as $section) {
                        $text .= $this->extractPhpWordSectionText($section);
                    }
                    break;

                // ── PPTX ──────────────────────────────────────────────────────
                // PhpWord has NO PowerPoint reader — use ZipArchive to parse
                // the raw slide XML directly (PPTX is just a ZIP of XML files).
                case 'pptx':
                    $zip = new \ZipArchive();
                    if ($zip->open($fullPath) === true) {
                        // Sort slide filenames so text is ordered slide 1 → N
                        $slideFiles = [];
                        for ($i = 0; $i < $zip->numFiles; $i++) {
                            $name = $zip->getNameIndex($i);
                            if (preg_match('/^ppt\/slides\/slide(\d+)\.xml$/', $name, $m)) {
                                $slideFiles[(int)$m[1]] = $name;
                            }
                        }
                        ksort($slideFiles);

                        foreach ($slideFiles as $slideNum => $name) {
                            $xml = $zip->getFromName($name);
                            // Extract every <a:t> text run — these hold all visible slide text
                            if (preg_match_all('/<a:t[^>]*>(.*?)<\/a:t>/su', $xml, $matches)) {
                                $slideText = implode(' ', $matches[1]);
                                // Decode XML entities
                                $slideText = html_entity_decode($slideText, ENT_XML1 | ENT_QUOTES, 'UTF-8');
                                $text .= "--- Slide {$slideNum} ---\n" . trim($slideText) . "\n";
                            }
                        }
                        $zip->close();
                    }
                    break;

                default:
                    break;
            }
        } catch (\Exception $e) {
            Log::warning("[AI:Notes] Text extraction failed ({$extension}): " . $e->getMessage());
        }

        // Normalise whitespace: collapse excessive blank lines / spaces
        $text = preg_replace('/ {2,}/', ' ', $text);
        $text = preg_replace('/\n{3,}/', "\n\n", $text);
        $text = trim($text);

        // Return up to 12 000 characters — enough for a thorough summary without blowing tokens
        return mb_substr($text, 0, 12000);
    }

    /**
     * Recursively walk a PhpWord section and collect all text runs.
     */
    private function extractPhpWordSectionText($container): string
    {
        $out = '';
        if (!method_exists($container, 'getElements')) {
            return $out;
        }
        foreach ($container->getElements() as $el) {
            if (method_exists($el, 'getText')) {
                $t = $el->getText();
                if (is_string($t) && $t !== '') $out .= $t . "\n";
            }
            // Recurse into tables, list items, etc.
            $out .= $this->extractPhpWordSectionText($el);
        }
        return $out;
    }

    /**
     * Summarize a PDF / DOCX / DOC / PPTX material using AI.
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

            $extractedText   = '';
            $extractedChars  = 0;
            $supportedTypes  = ['pdf', 'docx', 'doc', 'pptx'];
            $extension       = strtolower(trim($materialData['file_type'] ?? ''));

            if (!empty($materialData['file_path']) && in_array($extension, $supportedTypes)) {
                $filePath = $materialData['file_path'];
                $tempPath = null;
                $fullPath = null;

                try {
                    if (str_starts_with($filePath, 'http://') || str_starts_with($filePath, 'https://')) {
                        // Remote file (Cloudinary)
                        $response = \Illuminate\Support\Facades\Http::timeout(30)->get($filePath);
                        if ($response->successful()) {
                            $tempPath = tempnam(sys_get_temp_dir(), 'remote_material_') . '.' . $extension;
                            file_put_contents($tempPath, $response->body());
                            $fullPath = $tempPath;
                        } else {
                            Log::warning("[AI:Notes] Failed to fetch remote file: {$filePath}");
                        }
                    } else {
                        // Local file
                        $fullPath = storage_path('app/public/' . $filePath);
                    }

                    if ($fullPath && file_exists($fullPath)) {
                        $extractedText  = $this->extractFileText($fullPath, $extension);
                        $extractedChars = mb_strlen($extractedText);
                        Log::info("[AI:Notes] Extracted {$extractedChars} chars from {$extension} for user {$user->id}");
                    }
                } catch (\Exception $e) {
                    Log::error("[AI:Notes] Error loading/parsing file: " . $e->getMessage());
                } finally {
                    if ($tempPath && file_exists($tempPath)) {
                        @unlink($tempPath);
                    }
                }
            }

            // Pass extraction stats so the prompt can calibrate its tone
            $materialData['extracted_chars'] = $extractedChars;

            $systemPrompt = $this->rag->buildNotesSummaryPrompt($user, $materialData, $extractedText);

            $response = $this->groq->chat($systemPrompt, [
                ['role' => 'user', 'content' => "Analyse this material thoroughly and produce a comprehensive academic breakdown."],
            ]);

            return response()->json([
                'response' => $response,
                'error'    => false,
                'chars_extracted' => $extractedChars,
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
            'message'          => 'required|string|max:2000',
            'history'          => 'nullable|array',
            'history.*.role'   => 'required|string|in:user,assistant',
            'history.*.content'=> 'required|string',
            'department'       => 'nullable|string|max:100',
            'course_code'      => 'nullable|string|max:50',
            'semester'         => 'nullable|string|max:50',
            'term'             => 'nullable|string|max:20',
            'selected_qb_data' => 'nullable|array',
        ]);

        $user    = Auth::user();
        $message = strip_tags($request->input('message'));
        $history = $request->input('history', []);
        $filters = $request->only(['department', 'course_code', 'semester', 'term']);

        try {
            if (!$this->groq->isConfigured()) {
                return response()->json([
                    'response' => "Practice generator is being set up. Check the question bank for past questions.",
                    'error'    => true,
                ], 503);
            }

            $selectedQbData = $request->input('selected_qb_data');

            // ── Style-aware quiz generation when QB cards are selected ──
            if ($message === 'GENERATE_QUIZ_SAMPLE' && !empty($selectedQbData)) {
                $systemPrompt = $this->rag->buildQuizGeneratorPrompt($selectedQbData, $filters['course_code'] ?? '');
                $userMessage  = $this->rag->buildQuizUserMessage($selectedQbData);
                $response = $this->groq->chat($systemPrompt, [
                    ['role' => 'user', 'content' => $userMessage]
                ]);
                return response()->json(['response' => $response, 'error' => false]);
            }

            // ── Default generic practice generation ──
            $systemPrompt = $this->rag->buildQuestionBankPrompt($user, $filters);

            $messages = [];
            $limitedHistory = array_slice($history, -10);
            foreach ($limitedHistory as $msg) {
                $messages[] = [
                    'role'    => $msg['role'],
                    'content' => strip_tags($msg['content'])
                ];
            }
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

    /**
     * Check if there are any uploaded materials for a given course code.
     */
    public function checkCourseMaterials(Request $request): JsonResponse
    {
        $request->validate([
            'course_code' => 'required|string|max:50',
        ]);

        $courseCode = $request->input('course_code');
        $normalizedCode = str_replace([' ', '-'], '', strtoupper($courseCode));

        $materials = \App\Models\Material::whereRaw("REPLACE(REPLACE(course_code, ' ', ''), '-', '') = ?", [$normalizedCode])->get();

        return response()->json([
            'has_materials' => $materials->isNotEmpty(),
            'count'         => $materials->count(),
            'materials'     => $materials->map(fn($m) => [
                'id'             => $m->id,
                'title'          => $m->title,
                'file_extension' => $m->file_extension,
            ]),
        ]);
    }

    /**
     * Generate style-matched practice Final Exam paper from course materials and/or past QB papers.
     */
    public function finalExamGenerator(Request $request): JsonResponse
    {
        $request->validate([
            'course_code'      => 'required|string|max:50',
            'selected_qb_data' => 'nullable|array',
            'use_materials'    => 'required|boolean',
        ]);

        $courseCode     = $request->input('course_code');
        $selectedQbData = $request->input('selected_qb_data', []);
        $useMaterials   = (bool) $request->input('use_materials');

        $user = Auth::user();

        try {
            if (!$this->groq->isConfigured()) {
                return response()->json([
                    'response' => "AI practice generator is currently offline. Please check past questions.",
                    'error'    => true,
                ], 503);
            }

            $extractedText = '';
            if ($useMaterials) {
                $normalizedCode = str_replace([' ', '-'], '', strtoupper($courseCode));
                $materials = \App\Models\Material::whereRaw("REPLACE(REPLACE(course_code, ' ', ''), '-', '') = ?", [$normalizedCode])->get();

                $extractedTexts = [];
                foreach ($materials as $material) {
                    $filePath = storage_path('app/public/' . $material->file_path);

                    if (str_starts_with($material->file_path, 'http')) {
                        try {
                            $tempFile = tempnam(sys_get_temp_dir(), 'qb_material_');
                            $content = @file_get_contents($material->file_path);
                            if ($content !== false) {
                                file_put_contents($tempFile, $content);
                                $extracted = $this->extractFileText($tempFile, strtolower($material->file_extension));
                                if ($extracted) {
                                    $extractedTexts[] = "--- Document: {$material->title} ---\n" . $extracted;
                                }
                            }
                            @unlink($tempFile);
                        } catch (\Exception $e) {
                            Log::warning("[AI:FinalExam] Failed downloading Cloudinary material {$material->id}: " . $e->getMessage());
                        }
                    } else if (file_exists($filePath)) {
                        $extracted = $this->extractFileText($filePath, strtolower($material->file_extension));
                        if ($extracted) {
                            $extractedTexts[] = "--- Document: {$material->title} ---\n" . $extracted;
                        }
                    }
                }

                if (!empty($extractedTexts)) {
                    $extractedText = implode("\n\n", $extractedTexts);
                }
            }

            // Build system prompt and user message via RAGService
            $systemPrompt = $this->rag->buildFinalExamPrompt($selectedQbData, $courseCode, $extractedText);

            $response = $this->groq->chat($systemPrompt, [
                ['role' => 'user', 'content' => "Generate the Final Exam sample following the instructions."]
            ]);

            return response()->json([
                'response' => $response,
                'error'    => false,
            ]);

        } catch (\Exception $e) {
            Log::error('[AI:FinalExam] Error: ' . $e->getMessage());
            return response()->json([
                'response' => "Failed to generate the final exam sample. Please try again.",
                'error'    => true,
            ], 500);
        }
    }

    /**
     * Generate style-matched practice Midterm Exam paper from course materials and/or past QB papers.
     */
    public function midExamGenerator(Request $request): JsonResponse
    {
        $request->validate([
            'course_code'      => 'required|string|max:50',
            'selected_qb_data' => 'nullable|array',
            'use_materials'    => 'required|boolean',
        ]);

        $courseCode     = $request->input('course_code');
        $selectedQbData = $request->input('selected_qb_data', []);
        $useMaterials   = (bool) $request->input('use_materials');

        $user = Auth::user();

        try {
            if (!$this->groq->isConfigured()) {
                return response()->json([
                    'response' => "AI practice generator is currently offline. Please check past questions.",
                    'error'    => true,
                ], 503);
            }

            $extractedText = '';
            if ($useMaterials) {
                $normalizedCode = str_replace([' ', '-'], '', strtoupper($courseCode));
                $materials = \App\Models\Material::whereRaw("REPLACE(REPLACE(course_code, ' ', ''), '-', '') = ?", [$normalizedCode])->get();

                $extractedTexts = [];
                foreach ($materials as $material) {
                    $filePath = storage_path('app/public/' . $material->file_path);

                    if (str_starts_with($material->file_path, 'http')) {
                        try {
                            $tempFile = tempnam(sys_get_temp_dir(), 'qb_material_');
                            $content = @file_get_contents($material->file_path);
                            if ($content !== false) {
                                file_put_contents($tempFile, $content);
                                $extracted = $this->extractFileText($tempFile, strtolower($material->file_extension));
                                if ($extracted) {
                                    $extractedTexts[] = "--- Document: {$material->title} ---\n" . $extracted;
                                }
                            }
                            @unlink($tempFile);
                        } catch (\Exception $e) {
                            Log::warning("[AI:MidExam] Failed downloading Cloudinary material {$material->id}: " . $e->getMessage());
                        }
                    } else if (file_exists($filePath)) {
                        $extracted = $this->extractFileText($filePath, strtolower($material->file_extension));
                        if ($extracted) {
                            $extractedTexts[] = "--- Document: {$material->title} ---\n" . $extracted;
                        }
                    }
                }

                if (!empty($extractedTexts)) {
                    $extractedText = implode("\n\n", $extractedTexts);
                }
            }

            // Build system prompt and user message via RAGService
            $systemPrompt = $this->rag->buildMidExamPrompt($selectedQbData, $courseCode, $extractedText);

            $response = $this->groq->chat($systemPrompt, [
                ['role' => 'user', 'content' => "Generate the Midterm Exam sample following the instructions."]
            ]);

            return response()->json([
                'response' => $response,
                'error'    => false,
            ]);

        } catch (\Exception $e) {
            Log::error('[AI:MidExam] Error: ' . $e->getMessage());
            return response()->json([
                'response' => "Failed to generate the midterm exam sample. Please try again.",
                'error'    => true,
            ], 500);
        }
    }
}
