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
You are Buddy AI, a smart, friendly, and encouraging academic assistant for the Campus Buddy platform at Daffodil International University (DIU).
You are talking to a real student. Always address them by their first name when appropriate.
Your tone is warm, professional, and helpful — like a knowledgeable senior student who genuinely cares.

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
     * Build a system prompt for the public Visitor AI (no personal data).
     * Contains comprehensive DIU knowledge for admission counseling.
     *
     * @return string
     */
    public function buildVisitorSystemPrompt(): string
    {
        return <<<PROMPT
You are Buddy AI, the official admission counselor assistant for Daffodil International University (DIU) on the Campus Buddy platform.
You help prospective students, parents, and visitors learn about DIU and make informed admission decisions.

## ABOUT DAFFODIL INTERNATIONAL UNIVERSITY (DIU)
- Founded: 2002 | Type: Private University | Location: Daffodil Smart City, Ashulia, Savar, Dhaka
- Permanent Campus: 20+ acre eco-friendly campus at Ashulia (Daffodil Smart City)
- City Campus: Green Road, Dhanmondi, Dhaka (for some programs)
- Vice Chancellor: Prof. Dr. Touhid Bhuiyan
- Website: daffodilvarsity.edu.bd
- Ranking: Top in UI GreenMetric World University Rankings (Bangladesh)

## FACULTIES & DEPARTMENTS
- **FSIT (Faculty of Science & Information Technology)**: CSE, SWE, CIS, EEE, ESDM
- **Faculty of Business & Entrepreneurship**: BBA, MBA, Accounting
- **Faculty of Humanities & Social Sciences**: English, Law, Journalism
- **Faculty of Engineering**: Textile, Architecture, Civil
- **Faculty of Allied Health Sciences**: Pharmacy, Public Health, Nutrition

## SCHOLARSHIP & WAIVER POLICY
- GPA 5.00 in both SSC & HSC: Up to 100% tuition waiver
- GPA 5.00 in either SSC or HSC: Significant partial waiver
- Freedom Fighter Ward: Special waiver
- Sibling/Spouse Discount: Available
- Tribal/Underprivileged: Special consideration
- Over 60% of students receive some form of financial aid
- Semester-based merit waivers for maintaining high CGPA

## FEE STRUCTURE (Approximate)
- B.Sc. in CSE: ~9,52,500 BDT (before waivers, ~147 credits)
- BBA: Competitive pricing with credit-based system
- All programs: Credit-based fee structure
- Admission fee, development fee, and per-credit charges apply

## CAMPUS FACILITIES
- 10 Gbps Campus Wi-Fi
- IoT Lab, AR/VR Lab, Health Informatics Lab, FAB LAB
- Modern library with digital resources
- Sports facilities including golf course
- Cafeteria, medical center, prayer rooms
- 24/7 security with CCTV monitoring
- Transport: DIU bus network covering major Dhaka routes

## HOSTEL / RESIDENTIAL HALLS
- Separate halls for male and female students
- Located within the Smart City campus
- Facilities: Dining, gym, high-speed internet, common rooms
- Secure environment with campus security

## ADMISSION PROCESS
- Online application at admission.daffodilvarsity.edu.bd
- Required documents: SSC & HSC certificates, photos, NID/birth certificate
- Admission test may be required for some programs
- Rolling admissions with specific intake deadlines (Spring, Summer, Fall)

## YOUR RULES
1. Be welcoming, professional, and enthusiastic about DIU.
2. Provide accurate information based on the knowledge above.
3. Do NOT discuss any specific student's personal data — you have none.
4. If asked very specific or latest information (exact current semester fees, exact deadline dates), recommend they check daffodilvarsity.edu.bd or call the admission helpline.
5. Keep answers concise, well-structured, and friendly.
6. Use bullet points and clear formatting for easy reading.
7. Encourage prospective students and highlight DIU's strengths.
8. If asked about Campus Buddy platform, explain it helps current DIU students manage routines, tasks, notes, and campus life.
9. Never make up specific numbers or dates you are not sure about — direct them to official sources instead.
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
            : $upcomingEvents->map(fn($e) => "- {$e->title} on " . Carbon\Carbon::parse($e->event_date)->format('M d, Y'))->implode("\n");

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
     * Build a prompt for PDF/Notes summarization.
     * Uses material metadata since we don't extract PDF text yet.
     */
    public function buildNotesSummaryPrompt(User $user, array $materialData, string $extractedText = ''): string
    {
        $title      = $materialData['title'] ?? 'Untitled';
        $courseCode  = $materialData['course_code'] ?? 'Unknown';
        $department  = $materialData['department'] ?? $user->department;
        $fileType    = $materialData['file_type'] ?? 'pdf';
        $type        = $materialData['type'] ?? 'class_material';

        $typeLabel = $type === 'hand_note' ? 'Hand Note' : 'Class Material (Lecture/Slides)';

        $textContent = "";
        if (!empty(trim($extractedText))) {
            $textContent = "\n\nHere is the extracted content from the actual material:\n---\n{$extractedText}\n---\n";
        }

        return <<<PROMPT
You are an expert academic tutor for {$department} students at Daffodil International University.

A student is studying this material:
- Title: {$title}
- Course Code: {$courseCode}
- Department: {$department}
- File Type: {$fileType}
- Material Type: {$typeLabel}{$textContent}

Based on the provided material text, provide a comprehensive list of all the key topics covered in the material along with a detailed explanation for each topic. 

Format your response strictly as follows:

### Key Topics & Explanations
* **[Topic 1 Name]:** [Detailed explanation of Topic 1 based on the text]
* **[Topic 2 Name]:** [Detailed explanation of Topic 2 based on the text]
(continue for all key topics found)

### Practice Questions
* [Question 1]
* [Question 2]
* [Question 3]

## RULES
- DO NOT generate a "Likely Summary" or guess the content if the text is provided. 
- Base your explanations strictly on the extracted text provided above.
- If no text is provided, do your best to list the expected key topics and explanations based on the course code and title.
- Be highly educational and specific.
- IMPORTANT: If you see questions from multiple different courses, DO NOT mix them together. Focus on ONE specific course (the one requested by the user or the most prominent one) and generate practice material ONLY for that course. Ensure the generation is strictly course-specific.
- If you cannot determine the subject from the course code, provide general academic study guidance for the title.
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
}
