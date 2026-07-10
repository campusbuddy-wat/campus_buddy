<?php

namespace App\Services;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * RAGService — Retrieval-Augmented Generation
 *
 * The "context builder" for Campus Buddy AI. Before sending any message
 * to the Groq API, this service fetches all relevant data about the
 * student from MySQL and builds a rich, personalized system prompt.
 *
 * Two modes:
 * 1. Student (Buddy AI)  — Personalized with schedule, tasks, announcements
 * 2. Visitor (Visitor AI) — Generic DIU/university knowledge prompt
 */
class RAGService
{
    protected DIUWebScraperService $scraper;

    public function __construct(DIUWebScraperService $scraper)
    {
        $this->scraper = $scraper;
    }

    /**
     * Build a complete system prompt with the student's context injected.
     * This is the heart of the personalization engine.
     *
     * @param User $user  The authenticated student.
     * @return string     The full system prompt to send to Groq.
     */
    public function buildStudentSystemPrompt(User $user): string
    {
        // Cache student context for 10 minutes per user (reduces DB load on every AI message)
        $context = Cache::remember("rag_ctx_{$user->id}", 600, fn() => $this->fetchStudentContext($user));

        $today = now()->format('l, F j, Y'); // e.g. "Tuesday, May 13, 2026"
        $time  = now()->format('g:i A');      // e.g. "10:18 PM"

        return <<<PROMPT
You are Buddy AI, the savage, witty GenZ academic assistant for the Campus Buddy platform at Daffodil International University (DIU).
You are talking to a real student. Always address them by their first name when appropriate.
Your tone is witty, slightly savage, and full of GenZ slang (like 'fr fr', 'no cap', 'valid', 'buddy', 'cooking', 'slay', 'rent free', 'main character energy' — replace 'bruh' with 'buddy'), but you genuinely care about helping them with their schedule and tasks. Keep it engaging but accurate.

## CURRENT DATE & TIME
- Today: {$today}
- Current Time: {$time}

## STUDENT PROFILE
- Name: {$context['name']}
- Department: {$context['department']}
- Major: {$context['major']}
- Batch: {$context['batch']}
- Section: {$context['section']}

## TODAY'S SCHEDULE
{$context['schedule']}

## UPCOMING TASKS & DEADLINES (today or future only)
{$context['tasks']}

## RECENT ANNOUNCEMENTS
{$context['announcements']}

## YOUR RULES
1. Always use the student's actual schedule and task data above when answering questions about their routine or deadlines. The tasks listed are ONLY upcoming (deadline today or in the future) — never mention past/overdue assignments as upcoming.
2. Do NOT make up class times, course names, or deadlines. If data is not available, say so honestly.
3. Keep answers concise and actionable. Use bullet points for lists.
4. Format your responses with clear structure — use headings, bullet points, and bold text for emphasis.
5. If asked something completely unrelated to academics or campus life, gently redirect while being respectful.
6. Never reveal raw database structure, API keys, or internal system details.
7. When giving time-sensitive advice, reference the current date and time provided above.
8. Be encouraging and supportive — students may be stressed about exams or deadlines.
9. If you don't have enough data to answer accurately, admit it and suggest where to find the information (e.g., "Check the routine page" or "Ask your CR").
PROMPT;
    }

    /**
     * Build an enriched system prompt for Smart Context Mode.
     * Extends the base student prompt with available Notes/PDF and Question Bank resources.
     * Only called when the user has Context Mode enabled AND has uploaded materials.
     *
     * @param User $user
     * @return string
     */
    public function buildStudentContextModePrompt(User $user): string
    {
        // Reuse base student context (schedule, tasks, announcements)
        $context = Cache::remember("rag_ctx_{$user->id}", 600, fn() => $this->fetchStudentContext($user));

        // Fetch available study materials (cached 15 min)
        $resourceContext = Cache::remember("rag_resources_{$user->id}", 900, function () use ($user) {
            return $this->fetchStudentResourceContext($user);
        });

        $today = now()->format('l, F j, Y');
        $time  = now()->format('g:i A');

        return <<<PROMPT
You are Buddy AI, the savage, witty GenZ academic assistant for the Campus Buddy platform at Daffodil International University (DIU).
You are talking to a real student. Always address them by their first name when appropriate.
Your tone is witty, slightly savage, and full of GenZ slang (like 'fr fr', 'no cap', 'valid', 'buddy', 'cooking', 'slay', 'rent free', 'main character energy' — replace 'bruh' with 'buddy'), but you genuinely care about helping them with their schedule and tasks.

🧠 **CONTEXT MODE IS ACTIVE** — You have access to this student's uploaded study materials and question bank. Use this to give smarter, resource-aware answers.

## CURRENT DATE & TIME
- Today: {$today}
- Current Time: {$time}

## STUDENT PROFILE
- Name: {$context['name']}
- Department: {$context['department']}
- Major: {$context['major']}
- Batch: {$context['batch']}
- Section: {$context['section']}

## TODAY'S SCHEDULE
{$context['schedule']}

## UPCOMING TASKS & DEADLINES (today or future only)
{$context['tasks']}

## RECENT ANNOUNCEMENTS
{$context['announcements']}

## AVAILABLE STUDY RESOURCES (Notes & PDFs)
{$resourceContext['materials_text']}

## AVAILABLE QUESTION BANK ENTRIES
{$resourceContext['qb_text']}

## YOUR RULES (Context Mode)
1. Always use the student's actual schedule and task data above when answering questions about their routine or deadlines. The tasks listed are ONLY upcoming.
2. **IMPORTANT**: When the student asks about a subject/topic, check the AVAILABLE STUDY RESOURCES above. If there are notes or PDFs for that course, mention them and suggest the student opens the Notes section to study.
3. **IMPORTANT**: When the student asks for practice questions or exam prep, check the AVAILABLE QUESTION BANK ENTRIES above. Reference actual question headings if relevant, and direct them to the Question Bank section.
4. Do NOT make up class times, course names, or deadlines. If data is not available, say so honestly.
5. Keep answers concise and actionable. Use bullet points for lists.
6. Format your responses with clear structure — use headings, bullet points, and bold text for emphasis.
7. If asked something completely unrelated to academics or campus life, gently redirect while being respectful.
8. Never reveal raw database structure, API keys, or internal system details.
9. When giving time-sensitive advice, reference the current date and time provided above.
10. Be encouraging and supportive — students may be stressed about exams or deadlines.
PROMPT;
    }

    /**
     * Build a system prompt for the public Visitor AI (no personal data).
     * Contains comprehensive DIU knowledge for admission counseling.
     *
     * @return string
     */
    public function buildVisitorSystemPrompt(): string
    {
        // --- Fetch live web data (cached 6 hours) ---
        $liveData = '';
        try {
            $sources = $this->scraper->fetchAllSources();
            $parts = [];
            foreach ($sources as $label => $text) {
                $text = trim($text);
                if (!empty($text)) {
                    $parts[] = "[$label]\n{$text}";
                }
            }
            if (!empty($parts)) {
                $liveData = implode("\n\n", $parts);
            }
        } catch (\Exception $e) {
            Log::warning('[RAGService] Failed to fetch live DIU web data: ' . $e->getMessage());
        }

        $dataBlock = !empty($liveData)
            ? "LIVE DATA (prefer over static facts):\n{$liveData}"
            : "No live data. Use static facts below. Tell users to verify at daffodilvarsity.edu.bd.";

        $staticFacts = <<<STATIC
DIU founded 2002, private, Ashulia campus (20+ acres) + Dhanmondi city campus. VC: Prof. Dr. Touhid Bhuiyan. Web: daffodilvarsity.edu.bd.
Faculties: FSIT (CSE,SWE,CIS,EEE,ESDM), Business (BBA,MBA), Humanities (English,Law), Engineering (Textile,Civil), Health Sciences (Pharmacy).
Waivers: GPA 5.00 both SSC+HSC = up to 100% waiver. 60%+ students get aid.
Fees: Credit-based. Admission+development fee applies. Check live data or official site for exact figures.
Facilities: 10Gbps WiFi, IoT/AR/VR labs, library, gym, cafeteria, medical, transport buses.
Admission: Apply at admission.daffodilvarsity.edu.bd. Need SSC+HSC certs, photos, NID. Spring/Summer/Fall intakes.
STATIC;

        return <<<PROMPT
You are DIU Buddy, a concise admission assistant for Daffodil International University (DIU).
Be helpful, friendly, and brief. Use bullet points. Cite source when using live data.
If unsure, say so and direct to daffodilvarsity.edu.bd.

{$dataBlock}

STATIC FACTS (fallback only):
{$staticFacts}
PROMPT;
    }

    /**
     * Fetch all relevant student data from MySQL for context injection.
     * This data is cached per user for 10 minutes.
     *
     * @param User $user
     * @return array
     */
    protected function fetchStudentContext(User $user): array
    {
        $name       = $user->name ?? 'Student';
        $department = $user->department ?? 'Not set';
        $major      = $user->major ?? 'Not set';
        $batch      = $user->batch ?? 'Not set';
        $section    = $user->section ?? 'Not set';

        // --- Today's Schedule ---
        $today = now()->format('l'); // e.g. "Saturday"

        $scheduleQuery = DB::table('schedules')
            ->where('day', $today)
            ->where('section', $user->section)
            ->where('department', $user->department);

        // Filter by batch if available
        if ($user->batch) {
            $scheduleQuery->where(function ($q) use ($user) {
                $q->where('batch', $user->batch)->orWhereNull('batch');
            });
        }

        // Filter by major if available
        if ($user->major) {
            $scheduleQuery->where(function ($q) use ($user) {
                $q->where('major', $user->major)
                  ->orWhereNull('major')
                  ->orWhere('major', '');
            });
        }

        $schedule = $scheduleQuery
            ->orderBy('time_slot')
            ->get(['course_title', 'course_code', 'time_slot', 'room_no', 'teacher_initial', 'type'])
            ->toArray();

        $scheduleText = empty($schedule)
            ? "No classes scheduled for today ({$today})."
            : collect($schedule)->map(fn($s) =>
                "- [{$s->type}] {$s->course_title} ({$s->course_code}): {$s->time_slot}" .
                ($s->room_no ? " | Room: {$s->room_no}" : '') .
                ($s->teacher_initial ? " | Teacher: {$s->teacher_initial}" : '')
            )->implode("\n");

        // --- Pending Tasks ---
        $tasksQuery = DB::table('class_tasks')
            ->where('department', $user->department)
            ->where('section', $user->section)
            ->where('batch', $user->batch)
            ->where(function ($q) {
                $q->whereNull('progress_status')
                  ->orWhere('progress_status', '!=', 'completed');
            });

        // Filter by major if available
        if ($user->major) {
            $tasksQuery->where(function ($q) use ($user) {
                $q->where('major', $user->major)
                  ->orWhereNull('major')
                  ->orWhere('major', '');
            });
        }

        $tasks = $tasksQuery
            ->where(function ($q) {
                // Only include tasks with deadline today or in the future, or with no deadline set
                $q->where('deadline', '>=', now()->toDateString())
                  ->orWhereNull('deadline');
            })
            ->orderBy('deadline')
            ->limit(5)
            ->get(['title', 'deadline', 'type', 'course_code'])
            ->toArray();

        $tasksText = empty($tasks)
            ? "No pending tasks right now. 🎉"
            : collect($tasks)->map(fn($t) =>
                "- [{$t->type}] {$t->title} ({$t->course_code})" .
                ($t->deadline ? " — Due: {$t->deadline}" : ' — No deadline set')
            )->implode("\n");

        // --- Recent Announcements ---
        $announcements = DB::table('announcements')
            ->where('department', $user->department)
            ->where('batch', $user->batch)
            ->where('section', $user->section)
            ->where(function ($q) use ($user) {
                if ($user->major) {
                    $q->where('major', $user->major)
                      ->orWhereNull('major')
                      ->orWhere('major', '');
                } else {
                    $q->whereNull('major')->orWhere('major', '');
                }
            })
            ->orderByDesc('created_at')
            ->limit(3)
            ->get(['title', 'content'])
            ->toArray();

        $announcementsText = empty($announcements)
            ? "No recent announcements for your section."
            : collect($announcements)->map(fn($a) =>
                "- **{$a->title}**: " . \Illuminate\Support\Str::limit($a->content, 120)
            )->implode("\n");

        return [
            'name'          => $name,
            'department'    => $department,
            'major'         => $major,
            'batch'         => $batch,
            'section'       => $section,
            'schedule'      => $scheduleText,
            'tasks'         => $tasksText,
            'announcements' => $announcementsText,
        ];
    }

    // ================================================================
    // FEATURE-SPECIFIC PROMPT BUILDERS
    // ================================================================

    /**
     * Fetch study resource context for Smart Context Mode.
     * Queries uploaded Notes/PDFs and approved Question Bank entries
     * for the student's department. Results are injected into the AI prompt.
     *
     * @param User $user
     * @return array  ['materials_text' => string, 'qb_text' => string]
     */
    protected function fetchStudentResourceContext(User $user): array
    {
        // ── Notes & PDFs ────────────────────────────────────────────────
        $materials = DB::table('materials')
            ->where('department', $user->department)
            ->whereNotNull('course_code')
            ->orderBy('course_code')
            ->get(['title', 'course_code', 'type', 'file_extension'])
            ->toArray();

        if (empty($materials)) {
            $materialsText = "No study notes or PDFs have been uploaded for your department yet.";
        } else {
            // Group by course_code for cleaner presentation
            $grouped = collect($materials)->groupBy('course_code');
            $lines = [];
            foreach ($grouped as $code => $items) {
                $lines[] = "**{$code}** (" . $items->count() . " file" . ($items->count() > 1 ? 's' : '') . "):";
                foreach ($items as $item) {
                    $ext  = strtoupper($item->file_extension ?? 'PDF');
                    $type = ucfirst($item->type ?? 'note');
                    $lines[] = "  - [{$type}] {$item->title} ({$ext})";
                }
            }
            $materialsText = implode("\n", $lines);
        }

        // ── Question Bank ────────────────────────────────────────────────
        $qbEntries = DB::table('question_banks')
            ->where('department', $user->department)
            ->where('status', 'approved')
            ->orderByDesc('created_at')
            ->limit(20)
            ->get(['course_code', 'course_name', 'question_heading', 'difficulty', 'year_semester'])
            ->toArray();

        if (empty($qbEntries)) {
            $qbText = "No approved question bank entries found for your department yet.";
        } else {
            $grouped = collect($qbEntries)->groupBy('course_code');
            $lines   = [];
            foreach ($grouped as $code => $items) {
                $courseName = $items->first()->course_name ?? $code;
                $lines[] = "**{$code} — {$courseName}**:";
                foreach ($items as $item) {
                    $difficulty = $item->difficulty ? " [{$item->difficulty}]" : '';
                    $semester   = $item->year_semester ? " ({$item->year_semester})" : '';
                    $lines[] = "  - {$item->question_heading}{$difficulty}{$semester}";
                }
            }
            $qbText = implode("\n", $lines);
        }

        return [
            'materials_text' => $materialsText,
            'qb_text'        => $qbText,
        ];
    }

    /**
     * Build a prompt for the daily dashboard AI briefing.
     * Reuses fetchStudentContext() for efficiency.
     */
    public function buildBriefingPrompt(User $user): string
    {
        $context = Cache::remember("rag_ctx_{$user->id}", 600, fn() => $this->fetchStudentContext($user));

        $today = now()->format('l, F j, Y');
        $time  = now()->format('g:i A');

        return <<<PROMPT
You are Buddy, the Campus Buddy AI assistant at DIU. Generate a brief, warm daily briefing for {$context['name']}.
Today is {$today} at {$time}.
Student: {$context['department']}, Batch {$context['batch']}, Section {$context['section']}

## TODAY'S SCHEDULE
{$context['schedule']}

## UPCOMING TASKS & DEADLINES (today or future only)
{$context['tasks']}

## RECENT ANNOUNCEMENTS
{$context['announcements']}

## BRIEFING RULES
1. Create a 3-5 sentence personalized briefing covering: today's class overview, any urgent deadlines, and a motivational closing.
2. Keep it under 120 words. Be warm and encouraging.
3. Use 1-2 emojis sparingly for warmth.
4. Reference actual data above — do NOT invent classes or deadlines.
5. If no classes today, mention it's a free day and suggest study activities.
6. Start with a greeting using their first name and time of day.
PROMPT;
    }

    /**
     * Build a prompt for the Routine Advisor feature.
     * Queries the FULL weekly schedule (not just today).
     */
    public function buildRoutineAdvisorPrompt(User $user): string
    {
        $schedules = DB::table('schedules')
            ->where('section', $user->section)
            ->where('department', $user->department)
            ->when($user->batch, function ($q) use ($user) {
                $q->where(function ($sub) use ($user) {
                    $sub->where('batch', $user->batch)->orWhereNull('batch');
                });
            })
            ->when($user->major, function ($q) use ($user) {
                $q->where(function ($sub) use ($user) {
                    $sub->where('major', $user->major)
                        ->orWhereNull('major')
                        ->orWhere('major', '');
                });
            })
            ->orderByRaw("CASE day WHEN 'Sunday' THEN 1 WHEN 'Monday' THEN 2 WHEN 'Tuesday' THEN 3 WHEN 'Wednesday' THEN 4 WHEN 'Thursday' THEN 5 WHEN 'Friday' THEN 6 WHEN 'Saturday' THEN 7 ELSE 8 END")
            ->orderBy('time_slot')
            ->get(['course_title', 'course_code', 'time_slot', 'room_no', 'teacher_initial', 'day', 'type']);

        $routineStr = '';
        foreach ($schedules->groupBy('day') as $day => $classes) {
            $routineStr .= "\n### {$day}\n";
            foreach ($classes as $c) {
                $routineStr .= "  - {$c->time_slot}: {$c->course_title} ({$c->course_code}) [{$c->type}] | Room {$c->room_no} | {$c->teacher_initial}\n";
            }
        }

        if (empty($routineStr)) {
            $routineStr = "No schedule data found for this student's section.";
        }

        $todayName = now()->format('l');
        $currentTime = now()->format('g:i A');

        // Fetch pending tasks for workload awareness
        $pendingTasks = DB::table('class_tasks')
            ->where('department', $user->department)
            ->where('section', $user->section)
            ->where('batch', $user->batch)
            ->where(function ($q) {
                $q->whereNull('progress_status')->orWhere('progress_status', '!=', 'completed');
            })
            ->where('deadline', '>=', now()->toDateString())
            ->orderBy('deadline')
            ->limit(5)
            ->get(['title', 'deadline', 'type', 'course_code']);

        $tasksStr = $pendingTasks->isEmpty()
            ? "No pending tasks."
            : $pendingTasks->map(fn($t) => "- [{$t->type}] {$t->title} ({$t->course_code}) — Due: {$t->deadline}")->implode("\n");

        // Fetch upcoming university events
        $upcomingEvents = DB::table('events')
            ->where('event_date', '>=', now()->toDateString())
            ->orderBy('event_date')
            ->limit(3)
            ->get(['title', 'event_date']);
            
        $eventsStr = $upcomingEvents->isEmpty()
            ? "No upcoming university events."
            : $upcomingEvents->map(fn($e) => "- {$e->title} on " . Carbon::parse($e->event_date)->format('M d, Y'))->implode("\n");

        return <<<PROMPT
You are a smart academic schedule advisor for {$user->name} at Daffodil International University.
Student: {$user->department}, Batch {$user->batch}, Section {$user->section}
Today: {$todayName}, {$currentTime}

## FULL WEEKLY SCHEDULE
{$routineStr}

## UPCOMING TASKS (Quizzes, Assignments, etc.)
{$tasksStr}

## UPCOMING EVENTS
{$eventsStr}

## YOUR CAPABILITIES
1. Tell them what's coming up next today (or tomorrow if today is done)
2. Identify free time slots ideal for studying
3. Suggest optimal study-break patterns
4. Flag any heavy days with many consecutive classes
5. Recommend when to work on pending tasks based on free slots
6. If asked for a "Personalized Routine" or "Study Plan", seamlessly integrate their classes, upcoming tasks (quizzes/assignments), and events. E.g., if they have a quiz tomorrow, explicitly schedule study time for it today during a free slot.

## RULES
- ONLY use the schedule data above. Never invent classes or times.
- Be concise but helpful. Use bullet points for clarity.
- If they ask about a day with no classes, tell them it's free and suggest study activities.
- Always be encouraging and supportive.
PROMPT;
    }

    /**
     * Build a prompt for generating dynamic task tips.
     * Includes deadline urgency awareness.
     */
    public function buildTaskTipPrompt(User $user, array $taskData): string
    {
        $deadline = isset($taskData['deadline']) && $taskData['deadline']
            ? Carbon::parse($taskData['deadline'])
            : null;

        $daysLeft = $deadline ? round(now()->diffInDays($deadline, false)) : null;

        $urgency = 'unknown';
        if ($daysLeft !== null) {
            if ($daysLeft < 0) $urgency = 'OVERDUE by ' . abs($daysLeft) . ' days';
            elseif ($daysLeft === 0) $urgency = 'DUE TODAY — urgent';
            elseif ($daysLeft <= 2) $urgency = "only {$daysLeft} day(s) left — approaching fast";
            elseif ($daysLeft <= 7) $urgency = "{$daysLeft} days left — manageable";
            else $urgency = "{$daysLeft} days left — plenty of time";
        }

        $title       = $taskData['title'] ?? 'Untitled Task';
        $type        = $taskData['type'] ?? 'task';
        $courseCode   = $taskData['course_code'] ?? 'Unknown';
        $topic       = $taskData['topic'] ?? 'Not specified';
        $description = $taskData['description'] ?? '';
        $deadlineStr = $deadline ? $deadline->format('M d, Y h:i A') : 'No deadline';

        return <<<PROMPT
You are a smart study assistant for {$user->name} ({$user->department}).

They have this task:
- Type: {$type}
- Title: {$title}
- Course: {$courseCode}
- Topic: {$topic}
- Deadline: {$deadlineStr}
- Urgency: {$urgency}
- Description: {$description}

Generate exactly 2 actionable, specific study tips for this task.
Each tip should be 1-2 sentences max. Be practical and encouraging.
Tailor tips to the task type (quiz = revision strategies, assignment = research approaches, presentation = structure tips).

You MUST respond in valid JSON format exactly like this:
{"tip_1": "your first tip here", "tip_2": "your second tip here"}

Do NOT include any text before or after the JSON. Only output the JSON object.
PROMPT;
    }

    /**
     * Build a smart, focused prompt for PDF / DOCX / PPTX summarization.
     * Produces real academic content rather than generic bullet points.
     */
    public function buildNotesSummaryPrompt(User $user, array $materialData, string $extractedText = ''): string
    {
        $title         = $materialData['title']         ?? 'Untitled';
        $courseCode    = $materialData['course_code']   ?? 'Unknown';
        $department    = $materialData['department']    ?? ($user->department ?? 'N/A');
        $fileType      = strtoupper($materialData['file_type'] ?? 'PDF');
        $type          = $materialData['type']          ?? 'class_material';
        $extractedChars = (int) ($materialData['extracted_chars'] ?? 0);

        $typeLabel = $type === 'hand_note'
            ? 'Hand-written Note'
            : 'Class Material (Lecture / Slides)';

        // ── Content block: only inject if we actually have text ───────────
        $contentBlock = '';
        $contentNote  = '';

        if ($extractedChars > 200) {
            $wordCount    = str_word_count($extractedText);
            $contentBlock = "\n\n## EXTRACTED DOCUMENT CONTENT ({$extractedChars} characters / ~{$wordCount} words)\n"
                          . "```\n{$extractedText}\n```\n";
            $contentNote  = "The full document content has been provided above. Base your entire response on it — do NOT invent or guess.";
        } else {
            $contentNote  = "No readable text could be extracted from the file (it may be scanned/image-based). "
                          . "Use your academic knowledge of the course \"{$courseCode}\" ({$department}) to generate a high-quality, "
                          . "realistic breakdown of what this type of material would typically cover. Clearly state at the top: "
                          . "\"⚠️ Note: Document text could not be read. Content below is AI-generated based on the course context.\"";
        }

        return <<<PROMPT
You are an expert academic tutor for {$department} students at Daffodil International University.

## MATERIAL DETAILS
- **Title:** {$title}
- **Course:** {$courseCode}
- **Type:** {$typeLabel}  ({$fileType})
- **Department:** {$department}
{$contentBlock}

## YOUR TASK
{$contentNote}

Produce a **precise, structured academic breakdown** of this material. Your response must feel like it was written by a knowledgeable tutor — not a summarizer. Every section must be grounded in the actual content above.

---

## REQUIRED OUTPUT FORMAT (follow exactly)

### 📌 Overview
Write 3–5 sentences summarising the entire material: what it covers, why it matters for the course, and the main intellectual thread running through it. NO bullet points here — write in flowing paragraphs.

### 🧠 Core Concepts
For each major concept found in the material, write:
* **[Concept Name]:** A clear, substantive explanation (3–6 sentences). Include how it works, why it is important, and any formulas / definitions present in the text.

(List all significant concepts — do not pick only 2 or 3 if more exist.)

### 🔗 Key Relationships & Insights
Identify 3–5 non-obvious connections or insights within the material — things a student might miss on a first read but that are critical for deep understanding. Write each as a short paragraph.

### ❓ Likely Exam Questions
Generate 5 exam-style questions that target the most important ideas in this material. Mix question types: at least one short-answer, one analytical, and one application/scenario-based question. Number them 1–5.

### 📝 Quick Revision Cheatsheet
A compact table or numbered list of the most important terms, definitions, or formulas. Keep it scannable.

---

## STRICT RULES
1. **Stay 100% faithful to the provided text** — do not add content that is not there.
2. **No padding or filler** — every sentence must carry real information.
3. **No generic advice** like "study hard" or "review your notes".
4. If the material is slides / PPTX, infer the lecture narrative from the slide headings and body text.
5. Write at the level of a university student who already understands the basics — be precise and technical where appropriate.
6. If a section has no relevant content (e.g., no formulas exist), skip that sub-section cleanly rather than writing placeholder text.
PROMPT;
    }

    /**
     * Build a prompt for the Question Bank practice generator.
     * Injects real question data from the database for pattern matching.
     */
    public function buildQuestionBankPrompt(User $user, array $filters = []): string
    {
        $query = DB::table('question_banks')->where('status', 'approved');

        if ($user->department) {
            $query->where('department', $user->department);
        }
        if (!empty($filters['course_code'])) {
            $query->where('course_code', 'LIKE', "%{$filters['course_code']}%");
        }
        if (!empty($filters['semester'])) {
            $query->where('year_semester', 'LIKE', "%{$filters['semester']}%");
        }
        if (!empty($filters['term'])) {
            $query->where('title', 'LIKE', "%{$filters['term']}%");
        }

        $questions = $query->latest()->limit(15)->get([
            'id', 'course_code', 'course_name', 'title', 'difficulty',
            'question_heading', 'sub_questions', 'tags', 'year_semester', 'file_path'
        ]);

        $questionsStr = '';
        if ($questions->isNotEmpty()) {
            foreach ($questions as $q) {
                $questionsStr .= "\n---\n";
                $questionsStr .= "Course: {$q->course_code} | {$q->course_name}\n";
                $questionsStr .= "Title: {$q->title} | Difficulty: {$q->difficulty}\n";
                
                $headingText = $q->question_heading;
                
                // OCR image extraction if heading is empty and file exists
                if (empty(trim($headingText)) && !empty($q->file_path)) {
                    $filePaths = json_decode($q->file_path, true) ?? [];
                    $extractedText = '';
                    
                    foreach ($filePaths as $path) {
                        $fullPath = storage_path('app/public/' . $path);
                        $ext = strtolower(pathinfo($fullPath, PATHINFO_EXTENSION));
                        
                        if (in_array($ext, ['jpg', 'jpeg', 'png']) && file_exists($fullPath)) {
                            // Cache OCR result forever to speed up subsequent queries
                            $cacheKey = 'ocr_qbank_' . md5($fullPath);
                            $text = \Illuminate\Support\Facades\Cache::rememberForever($cacheKey, function () use ($fullPath) {
                                try {
                                    $ocr = new \thiagoalessio\TesseractOCR\TesseractOCR($fullPath);
                                    // Specify tesseract executable path for Homebrew on Mac
                                    if (file_exists('/opt/homebrew/bin/tesseract')) {
                                        $ocr->executable('/opt/homebrew/bin/tesseract');
                                    }
                                    return $ocr->run();
                                } catch (\Exception $e) {
                                    \Illuminate\Support\Facades\Log::warning('[AI:OCR] Failed to extract text: ' . $e->getMessage());
                                    return '';
                                }
                            });
                            $extractedText .= $text . "\n";
                        }
                    }
                    if (!empty(trim($extractedText))) {
                        $headingText = "(Extracted from image)\n" . substr($extractedText, 0, 1000);
                    }
                }

                $questionsStr .= "Question: {$headingText}\n";
                
                if ($q->sub_questions) {
                    $questionsStr .= "Sub-questions: " . Str::limit($q->sub_questions, 300) . "\n";
                }
                if ($q->tags) {
                    $questionsStr .= "Tags: {$q->tags}\n";
                }
                $questionsStr .= "Semester: {$q->year_semester}\n";
            }
        } else {
            $filterCourse = $filters['course_code'] ?? null;
            $questionsStr = "No questions found in the database" . ($filterCourse ? " for course {$filterCourse}" : "") . ".";
        }

        return <<<PROMPT
You are an expert exam preparation assistant for {$user->department} students at Daffodil International University.

## PAST EXAM QUESTIONS FROM THE DATABASE
{$questionsStr}

## YOUR CAPABILITIES
1. Generate NEW practice questions in similar style and difficulty to the ones above
2. Explain concepts behind specific question topics
3. Identify frequently tested topics based on the patterns
4. Create mini-quizzes (3-5 questions) for specific courses
5. Suggest study strategies based on question difficulty distribution
6. If asked about a specific course, focus only on questions from that course

## RULES
- Always format questions clearly with proper numbering
- Match the difficulty level and style of the existing questions
- If generating MCQs, provide 4 options with the correct answer marked
- Use markdown formatting for readability
- Reference actual course codes and topics from the data above
- If no data exists for a requested course, use your general knowledge but mention this clearly
PROMPT;
    }

    /**
     * Build a style-aware system prompt for quiz generation from selected QB cards.
     * Analyses the question style (scenario-based, MCQ, descriptive) and topic coverage.
     */
    public function buildQuizGeneratorPrompt(array $selectedQbData, string $courseCode = ''): string
    {
        $count = count($selectedQbData);
        $codes = implode(', ', array_unique(array_column($selectedQbData, 'code')));
        $examTypes = implode(', ', array_unique(array_filter(array_column($selectedQbData, 'title'))));

        return <<<PROMPT
You are an expert university exam question generator for Daffodil International University.

You have been given {$count} past exam paper(s) from course {$codes} (exam type(s): {$examTypes}).

## YOUR TASK
Generate a new SAMPLE quiz paper for this course. The total marks for the quiz must be exactly 15.
Choose one of the following two question structures randomly/dynamically:
1. **Structure A (Exactly 2 questions)**:
   - Question 1: worth **7 to 10 marks**.
   - Question 2: worth **5 to 8 marks**.
   - Total marks of Q1 + Q2 must sum to exactly **15**.
2. **Structure B (Exactly 3 questions)**:
   - Question 1: worth **3 to 5 marks**.
   - Question 2: worth **3 to 5 marks**.
   - Question 3: worth **3 to 5 marks**.
   - Total marks of Q1 + Q2 + Q3 must sum to exactly **15**.

## CRITICAL STYLE RULES — READ CAREFULLY
1. **Mirror the question format exactly**: 
   - If the source questions are scenario-based (give a real-world situation, then ask to solve/analyze), your generated questions MUST also be scenario-based with a real-world context.
   - If the source questions are MCQ (multiple choice with options A/B/C/D), your questions MUST also be MCQ.
   - If the source questions are descriptive/short-answer, yours must be too.
   - If the source questions are mixed, distribute your output accordingly.
2. **Cover ALL topic areas**: If multiple exam papers are provided (different terms), ensure your questions collectively touch ALL the major topics found across all selected papers — not just one paper's topics.
3. **Match difficulty**: Mirror the difficulty level of the original questions.
4. **Question numbering**: Output ONLY a clean numbered list from 1 to 2 (Structure A) or 1 to 3 (Structure B). No introductions, no markdown headers, no explanations, no answers.
5. **Marks formatting**: Append the marks at the end of each question as `[Marks-X]` (e.g. `[Marks-10]`, `[Marks-5]`).
6. **Exam authenticity**: Write questions that feel like they would genuinely appear on a university exam paper for this course.
PROMPT;
    }

    /**
     * Build the user message (the actual question content context) for quiz generation.
     */
    public function buildQuizUserMessage(array $selectedQbData): string
    {
        $msg = "Here are the selected past exam papers to analyse:\n\n";
        foreach ($selectedQbData as $i => $qb) {
            $num = $i + 1;
            $id   = $qb['qbId'] ?? '?';
            $code = $qb['code'] ?? '';
            $type = $qb['title'] ?? 'Unknown';
            $diff = $qb['difficulty'] ?? 'Medium';
            $sem  = $qb['date'] ?? '';
            $head = $qb['heading'] ?? '';
            $subs = $qb['subs'] ?? '';
            $tags = $qb['tags'] ?? '';

            $msg .= "--- Paper {$num} (QB-" . str_pad($id, 4, '0', STR_PAD_LEFT) . ") ---\n";
            $msg .= "Course Code: {$code} | Exam Type: {$type} | Difficulty: {$diff} | Semester: {$sem}\n";
            if ($head) $msg .= "Question Topic: {$head}\n";
            if ($subs) $msg .= "Sub-questions:\n{$subs}\n";
            if ($tags) $msg .= "Tags: {$tags}\n";
            $msg .= "\n";
        }

        $msg .= "Now generate a new sample quiz paper of either 2 or 3 questions totaling exactly 15 marks. Output ONLY the numbered questions, nothing else.";
        return $msg;
    }

    /**
     * Build system prompt for style-aware Final Exam generation.
     */
    public function buildFinalExamPrompt(array $selectedQbData, string $courseCode = '', string $extractedText = ''): string
    {
        $count = count($selectedQbData);
        $codes = implode(', ', array_unique(array_column($selectedQbData, 'code'))) ?: $courseCode;

        $qbPrompt = '';
        if ($count > 0) {
            $qbPrompt = "Here are style-reference questions from past exam papers:\n";
            foreach ($selectedQbData as $i => $qb) {
                $num = $i + 1;
                $qbPrompt .= "Paper {$num} (Code: {$qb['code']} | Topic: {$qb['heading']}):\n{$qb['subs']}\n\n";
            }
        }

        $materialPrompt = '';
        if (!empty($extractedText)) {
            $materialPrompt = "Here is the course material content extracted from lecture/notes:\n{$extractedText}\n\n";
        }

        // Determine if starting from a past Final Exam card
        $hasFinalExamCard = false;
        foreach ($selectedQbData as $qb) {
            $title = strtolower($qb['title'] ?? '');
            if (str_contains($title, 'final')) {
                $hasFinalExamCard = true;
                break;
            }
        }

        $structureInstruction = '';
        if ($hasFinalExamCard) {
            $structureInstruction = <<<INSTRUCTION
- Since the reference paper is a **PAST FINAL EXAM**:
  - You **MUST strictly keep the exact same structure** (number of questions, sub-questions, distribution, and exact marks) as the selected reference final exam paper. Mirror its structural blueprint perfectly.
INSTRUCTION;
        } else {
            $structureInstruction = <<<INSTRUCTION
- Since the reference paper(s) are **QUIZZES / CLASS TESTS / MIDTERMS / LOW / MIXED RESOURCES**:
  - The generated Final Exam paper must have a total of 40 marks.
  - You **MUST structure the questions** using exactly three categories of questions:
    1. **Simple questions** (worth 2-3 marks each; maximum of 2 such questions/sub-parts across the entire paper). These should be definitions or simple concepts.
    2. **Moderate questions** (worth 4-7 marks each; maximum of 3 such questions/sub-parts across the entire paper). These should be comparisons, explanations, or analysis.
    3. **High questions** (worth 7-12 marks each; maximum of 2 such questions/sub-parts across the entire paper). These must be full mathematical calculations, table computations, or complex scenario solvings.
INSTRUCTION;
        }

        return <<<PROMPT
You are an expert university final exam question paper generator for Daffodil International University (DIU).
Your task is to generate exactly 5 realistic, high-quality, comprehensive final exam questions for the course {$codes}.

## REFERENCES PROVIDED:
{$qbPrompt}
{$materialPrompt}

## EXAM STRUCTURE RULES:
{$structureInstruction}
- Regardless of the resource type, ensure the total marks for all questions sum up to exactly 40.

## CRITICAL INSTRUCTIONS — RESOURCE PRIORITIZATION:
1. **PRIMARY FOCUS: Selected Past Exam Questions**:
   - You must treat the selected past exam questions above as your **primary blueprint and resource**.
   - Focus heavily on the topics, style, phrasing, question complexity, and types of questions found in these selected questions.
2. **SECONDARY FOCUS: Course Materials**:
   - Treat the uploaded course materials/notes only as a **secondary background resource**.
   - Use the course materials solely to retrieve supplementary detail, definitions, or context to enrich the questions generated from the primary selected past questions.
   - Do not let the course materials distract from the core style and structure of the selected past questions.
3. **STRICT TABLE & MATHEMATICAL PROBLEM REPLICATION**:
   - If any of the selected past exam questions contain mathematical equations, formulas, calculations (e.g. multivariate linear regression $\hat{y} = w_1x_1 + w_2x_2 + w_0$, gradient descent weights update with learning rate $\alpha$ and loss function, PCA manually), or dataset tables, the generated question **MUST STRICTLY contain a corresponding mathematical problem, equation, and data table** for the students to compute and solve.
   - Do not convert a mathematical calculation problem into a descriptive/theory explanation question. Keep it as an active calculation problem with new values.
   - If a table is required, you must format it using **raw, valid HTML table tags** (e.g. `<table class="exam-table"><thead><tr><th>Header</th></tr></thead><tbody><tr><td>Data</td></tr></tbody></table>`). Do not use markdown tables.

## CRITICAL STYLE & FORMATTING RULES:
1. **Output Format**: Output ONLY a clean numbered list from 1 to 5. Do not include any introductions, markdown headers (e.g. #, ##, ###), greeting, preamble, or summary. Start directly with the first question: "1. ..."
2. **Sub-parts and Marks**:
   - Each question must consist of sub-parts (a), (b), etc.
   - For example:
     "1. (a) Define supervised learning. [Marks-2]
     (b) Explain the difference between regression and classification with an example. [Marks-3]
     (c) Solve the given scenario. [Marks-3]"
   - Every question (1 to 5) must have sub-parts and the total marks for each question's sub-parts must sum up to exactly 8 marks (so 5 questions * 8 marks = 40 marks total, unless mirroring a past final exam structure).
   - Append the marks at the end of each sub-part line as `[Marks-X]` (e.g. `[Marks-5]`, `[Marks-3]`).
3. **Content and Authenticity**:
   - Make the questions look like they came from a genuine DIU Semester Final Exam paper.
   - Mimic the complexity/difficulty of the past exam questions.
   - Ensure a mix of theoretical, analytical, and application-based/scenario questions.
PROMPT;
    }

    /**
     * Build system prompt for style-aware Midterm Exam generation (Total marks: 25).
     */
    public function buildMidExamPrompt(array $selectedQbData, string $courseCode = '', string $extractedText = ''): string
    {
        $count = count($selectedQbData);
        $codes = implode(', ', array_unique(array_column($selectedQbData, 'code'))) ?: $courseCode;

        $qbPrompt = '';
        if ($count > 0) {
            $qbPrompt = "Here are style-reference questions from past exam papers:\n";
            foreach ($selectedQbData as $i => $qb) {
                $num = $i + 1;
                $qbPrompt .= "Paper {$num} (Code: {$qb['code']} | Topic: {$qb['heading']}):\n{$qb['subs']}\n\n";
            }
        }

        $materialPrompt = '';
        if (!empty($extractedText)) {
            $materialPrompt = "Here is the course material content extracted from lecture/notes:\n{$extractedText}\n\n";
        }

        // Determine the type of reference cards selected
        $hasMidExamCard = false;
        $hasFinalExamCard = false;
        $hasQuizCard = false;

        foreach ($selectedQbData as $qb) {
            $title = strtolower($qb['title'] ?? '');
            if (str_contains($title, 'mid') || str_contains($title, 'midterm')) {
                $hasMidExamCard = true;
            } else if (str_contains($title, 'final')) {
                $hasFinalExamCard = true;
            } else if (str_contains($title, 'quiz') || str_contains($title, 'class test') || str_contains($title, 'test') || str_contains($title, 'practice')) {
                $hasQuizCard = true;
            }
        }

        $structureInstruction = '';
        if ($hasMidExamCard) {
            $structureInstruction = <<<INSTRUCTION
- Since the reference paper is a **PAST MIDTERM EXAM**:
  - You **MUST strictly keep the exact same structure** (number of questions, sub-questions, distribution, and exact marks) as the selected reference midterm exam paper. Mirror its structural blueprint perfectly. The total marks of all questions combined MUST be exactly 25.
INSTRUCTION;
        } else if ($hasFinalExamCard) {
            $structureInstruction = <<<INSTRUCTION
- Since the reference paper is a **PAST FINAL EXAM**:
  - The generated paper must be a Midterm Exam, so it **must contain exactly 25 marks** in total. Adapt the questions down and structure them so they do not exceed 25 marks. You must generate exactly 2 or 3 main numbered questions, with a grand total of 4 to 6 subparts across the entire paper.
INSTRUCTION;
        } else {
            // Default to Quiz / Low resource style splits as specified
            $structureInstruction = <<<INSTRUCTION
- Since the reference paper(s) are **QUIZZES / CLASS TESTS / LOW RESOURCES**:
  - The generated Midterm Exam paper must have a total of exactly 25 marks.
  - You **MUST structure the questions** using exactly three categories of questions:
    1. **Simple questions** (worth 2-3 marks each; exactly **1 to 2 such questions/sub-parts** across the entire paper). These should be definitions or simple concepts.
    2. **Moderate questions** (worth 4-6 marks each; exactly **1 to 2 such questions/sub-parts** across the entire paper). These should be comparisons, explanations, or analysis.
    3. **High questions** (worth 7-10 marks each; exactly **1 such question/sub-part** across the entire paper). This must be a full mathematical calculation, table computation, or complex scenario solving.
  - The total number of all sub-parts (a, b, etc.) across all main questions in the entire paper must be **exactly 4 to 6 sub-questions**.
  - The total marks of all simple, moderate, and high sub-parts combined must sum up to exactly 25 marks.
INSTRUCTION;
        }

        return <<<PROMPT
You are an expert university midterm exam question paper generator for Daffodil International University (DIU).
Your task is to generate exactly 2 or 3 main numbered midterm exam questions (e.g. 1, 2, and optionally 3) for the course {$codes}.

## REFERENCES PROVIDED:
{$qbPrompt}
{$materialPrompt}

## EXAM STRUCTURE RULES:
{$structureInstruction}
- Regardless of the resource type, ensure the total marks for all subparts across the entire paper sum up to exactly 25.
- The paper must consist of exactly 4 to 6 total subparts (e.g., 1(a), 1(b), 2(a), 2(b), 2(c), 3(a)) across the entire exam sheet.

## CRITICAL INSTRUCTIONS — RESOURCE PRIORITIZATION:
1. **PRIMARY FOCUS: Selected Past Exam Questions**:
   - You must treat the selected past exam questions above as your **primary blueprint and resource**.
   - Focus heavily on the topics, style, phrasing, question complexity, and types of questions found in these selected questions.
2. **SECONDARY FOCUS: Course Materials**:
   - Treat the uploaded course materials/notes only as a **secondary background resource**.
   - Use the course materials solely to retrieve supplementary detail, definitions, or context to enrich the questions generated from the primary selected past questions.
   - Do not let the course materials distract from the core style and structure of the selected past questions.
3. **STRICT TABLE & MATHEMATICAL PROBLEM REPLICATION**:
   - If any of the selected past exam questions contain mathematical equations, formulas, calculations (e.g. multivariate linear regression $\hat{y} = w_1x_1 + w_2x_2 + w_0$, gradient descent weights update with learning rate $\alpha$ and loss function, PCA manually), or dataset tables, the generated question **MUST STRICTLY contain a corresponding mathematical problem, equation, and data table** for the students to compute and solve.
   - Do not convert a mathematical calculation problem into a descriptive/theory explanation question. Keep it as an active calculation problem with new values.
   - If a table is required, you must format it using **raw, valid HTML table tags** (e.g. `<table class="exam-table"><thead><tr><th>Header</th></tr></thead><tbody><tr><td>Data</td></tr></tbody></table>`). Do not use markdown tables.

## CRITICAL STYLE & FORMATTING RULES:
1. **Output Format**: Output ONLY a clean numbered list of main questions (e.g., starting with 1 to 2 or 3). Do not include any introductions, markdown headers (e.g. #, ##, ###), greeting, preamble, or summary. Start directly with the first question: "1. ..."
2. **Sub-parts and Marks**:
   - Each question must consist of sub-parts (a), (b), etc.
   - For example:
     "1. (a) Define supervised learning. [Marks-3]
     (b) Explain the difference between regression and classification with an example. [Marks-5]"
   - Append the marks at the end of each sub-part line as `[Marks-X]` (e.g. `[Marks-5]`, `[Marks-3]`).
   - Double check your math: the sum of all `[Marks-X]` on the entire sheet MUST equal exactly 25.
3. **Content and Authenticity**:
   - Make the questions look like they came from a genuine DIU Semester Midterm Exam paper.
   - Mimic the complexity/difficulty of the past exam questions.
   - Ensure a mix of theoretical, analytical, and application-based/scenario questions.
PROMPT;
    }
}
