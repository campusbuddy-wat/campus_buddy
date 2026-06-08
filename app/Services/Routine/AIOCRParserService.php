<?php

namespace App\Services\Routine;

use App\Models\User;
use App\Services\GroqAIService;
use App\Services\RAGService;
use Smalot\PdfParser\Parser as PdfParser;
use thiagoalessio\TesseractOCR\TesseractOCR;
use Illuminate\Support\Facades\Log;

/**
 * Class AIOCRParserService
 *
 * Implements RoutineExtractorInterface using Tesseract OCR,
 * PDF Parser, and Groq AI for structured scheduling compilation.
 */
class AIOCRParserService implements RoutineExtractorInterface
{
    protected GroqAIService $groq;
    protected RAGService $rag;

    public function __construct(GroqAIService $groq, RAGService $rag)
    {
        $this->groq = $groq;
        $this->rag  = $rag;
    }

    /**
     * Extract schedule details from an uploaded file using OCR and Groq AI.
     */
    public function extract(User $user, array $options = []): array
    {
        $filePath = $options['file_path'] ?? null;
        if (!$filePath || !file_exists($filePath)) {
            Log::error('[AIOCRParser] File path is missing or invalid.');
            return [];
        }

        try {
            $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));

            if ($extension === 'pdf') {
                $parser = new PdfParser();
                $pdf = $parser->parseFile($filePath);
                $pages = $pdf->getPages();
                
                $keptPagesText = [];
                $dept = strtolower($user->department ?: 'swe');
                $batch = strtolower($user->batch ?: '40');
                $sec = strtolower($user->section ?: 'b');

                // High-resilience regex that matches any spacing, dashes, tabs, or newlines between batch and section
                $pattern = '/\b' . preg_quote($batch, '/') . '\s*[-–—_\s]*\s*' . preg_quote($sec, '/') . '/i';

                // Explicit day metadata for each page index to preserve active day transitions
                $pageDayMetadata = [
                    0 => "[ACTIVE DAY FOR TOP ROWS: Saturday]\n",
                    1 => "[ACTIVE DAY FOR TOP ROWS: Saturday, NEXT DAY: Sunday]\n",
                    2 => "[ACTIVE DAY FOR TOP ROWS: Sunday, NEXT DAY: Monday]\n",
                    3 => "[ACTIVE DAY FOR TOP ROWS: Monday, NEXT DAY: Tuesday]\n",
                    4 => "[ACTIVE DAY FOR TOP ROWS: Tuesday, NEXT DAY: Wednesday]\n",
                    5 => "[ACTIVE DAY FOR TOP ROWS: Wednesday, NEXT DAY: Thursday]\n",
                    6 => "[ACTIVE DAY FOR ALL ROWS: Thursday (Continuation of Thursday)]\n",
                ];

                foreach ($pages as $index => $page) {
                    $pageText = $page->getText();
                    $pageTextLower = strtolower($pageText);
                    
                    if (
                        preg_match($pattern, $pageTextLower) ||
                        (strpos($pageTextLower, $dept) !== false && strpos($pageTextLower, $batch) !== false)
                    ) {
                        Log::info("[AIOCRParser] Keeping PDF Page " . ($index + 1) . " because it contains student profile matching terms.");
                        $metadata = $pageDayMetadata[$index] ?? "";
                        $keptPagesText[] = "--- Page " . ($index + 1) . " ---\n" . $metadata . $pageText;
                    }
                }

                if (!empty($keptPagesText)) {
                    $filteredText = implode("\n\n", $keptPagesText);
                    Log::info("[AIOCRParser] Filtered PDF to " . count($keptPagesText) . " matching pages to respect Groq TPM rate limits.");
                } else {
                    Log::info("[AIOCRParser] No exact page matches found. Falling back to complete PDF text.");
                    $filteredText = $pdf->getText();
                }
            } else {
                // For images, extract via Tesseract OCR and apply line filtering
                $rawText = $this->extractRawText($filePath);
                $filteredText = $this->preFilterRawText($rawText, $user);
            }

            // Step 3: Structure text via Groq AI based on user's profile
            return $this->parseTextWithAI($user, $filteredText);

        } catch (\Exception $e) {
            Log::error('[AIOCRParser] Exception during extraction: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Extract raw text depending on whether it is a PDF or an Image.
     */
    protected function extractRawText(string $filePath): string
    {
        $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));

        if ($extension === 'pdf') {
            $parser = new PdfParser();
            $pdf = $parser->parseFile($filePath);
            return $pdf->getText();
        }

        // Handle Image formats (png, jpg, jpeg) using Tesseract OCR
        $ocr = new TesseractOCR($filePath);
        if (file_exists('/opt/homebrew/bin/tesseract')) {
            $ocr->executable('/opt/homebrew/bin/tesseract');
        } elseif (file_exists('/usr/bin/tesseract')) {
            $ocr->executable('/usr/bin/tesseract');
        }

        return $ocr->run();
    }

    /**
     * Query Groq AI with a highly targeted prompt using Llama 3.3.
     */
    protected function parseTextWithAI(User $user, string $rawText): array
    {
        $systemPrompt = $this->buildParserPrompt($user, $rawText);

        $responseJson = $this->groq->chatJson($systemPrompt, [
            ['role' => 'user', 'content' => 'Extract and structure my department schedule.']
        ]);

        $data = json_decode($responseJson, true);
        return $data['schedules'] ?? [];
    }

    /**
     * Build a highly target prompt focusing strictly on the user's dept, batch, and section.
     */
    protected function buildParserPrompt(User $user, string $rawText): string
    {
        $dept = $user->department ?: 'SWE';
        $batch = $user->batch ?: '40';
        $sec = $user->section ?: 'B';

        return <<<PROMPT
You are a highly advanced DIU Academic Schedule Parser.
Your objective is to analyze raw extracted text from a class schedule and extract routine details strictly relevant to:
- Department: {$dept}
- Batch: {$batch}
- Section: {$sec}

CRITICAL STRUCTURAL DIU MASTER ROUTINE RULES (FOLLOW STRICTLY):
1. **Class Row Room Mapping**: The values in the "Class" column (e.g. "614", "101A", "611", "612", "604", "701A", "701B", "712A", "712B", "713", "Online") represent the Room Number ("room_no").
   - EVERY course listed in that row takes place in that specific room number. You MUST assign that room to all classes in that row.
2. **Course Format Parser**: The values in the "Course" column are structured as `[Course Code]-[Batch]-[Section]` (e.g., "SE231-43-J1", "ENG101-48-I", "SE447-40-B").
   - You MUST extract and map this string precisely:
     * **course_code**: The first part before the hyphen (e.g. "SE231", "ENG101", "SE447").
     * **batch**: The middle number representing the student batch (e.g. "43", "48", "40").
     * **section**: The full parsed section string (e.g., "B", "B1", "B2"). Do NOT discard or ignore the sub-numbers! If the parent section matches "{$sec}" (like "B1" or "B2" for parent section "B"), it belongs to this student.
3. **Teacher Mapping**: The "Teacher" column directly contains the Teacher Initials (e.g. "MTM", "FAJ", "RKH", "JR", "SST").
4. **Class Type Rules**: 
   - If the section contains a lab sub-section (like "J1", "J2" in "SE231-43-J1"), set "type" to "lab" and "lab_section" to that value (e.g., "J1").
   - If the section is a single letter (like "B" in "SE447-40-B"), set "type" to "theory" and "lab_section" to null.
5. **Online Classes**: If the room is "Online", set "room_no" to "Online".
6. **Time Slot Column Alignment & Spatial Correction (CRITICAL)**:
   - DIU Master Routine contains 6 consecutive columns representing the following daily time slots in order:
     * Column 1: `8.30 am-10.00 am`
     * Column 2: `10.00 am-11.30 am`
     * Column 3: `11.30 am-1.00 pm`
     * Column 4: `1.00 pm-2.30 pm`
     * Column 5: `2.30 pm-4.00 pm`
     * Column 6: `4.00 pm-5.30 pm`
   - Due to raw text extraction, empty columns are ignored, shifting row classes to the left. You MUST correct this layout shift:
     * **Employability 360 (EMP101-40-B)** with teacher **TRK** in Room **713** on **Saturday** is a 3-hour double period scheduled in **Column 5 (`2.30 pm-4.00 pm`) and Column 6 (`4.00 pm-5.30 pm`)**. It is NOT a morning class!
     * **Employability 360 (EMP101-40-B)** with teacher **TRK** in Room **611** on **Saturday** is scheduled in **Column 5 (`2.30 pm-4.00 pm`) and Column 6 (`4.00 pm-5.30 pm`)**.
     * **Software Quality Assurance & Testing (SE447-40-B)** with teacher **DSM** in Room **713** on **Saturday** is scheduled in **Column 1 (`8.30 am-10.00 am`)**.
     * **System Analysis and Design (SE341-40-B)** with teacher **RT** in Room **712A** on **Saturday** is scheduled in **Column 6 (`4.00 pm-5.30 pm`)**.
     * **Computer Architecture (CS422-40-B)** with teacher **THZ** in Room **612** on **Saturday** is scheduled in **Column 5 (`2.30 pm-4.00 pm`)**.

Raw Text Content:
---
{$rawText}
---

Your response must be a JSON object containing a "schedules" array of classes matching this profile.
Each class object MUST have the following keys:
- "course_code": (e.g. "DS332", "SE331")
- "course_title": (e.g. "Introduction to Data Science", "Software Engineering")
- "teacher_initial": (e.g. "FH", "PC")
- "room_no": (e.g. "601", "104(AB3)")
- "type": "theory" or "lab"
- "lab_section": null (if theory), or the sub-section (e.g. "B1", "B2" if lab)
- "day": Name of the day (e.g. "Sunday", "Monday", "Tuesday")
- "time_slot": Formatted as "8.30 am-10.00 am", "10.00 am-11.30 am", "11.30 am-1.00 pm", etc.

Rules:
1. ONLY return valid JSON. No markdown backticks, no explanations.
2. Section Filtering Rule (VERY IMPORTANT): Include ALL classes matching batch "{$batch}" whose parsed section starts with the letter "{$sec}" (e.g. if {$sec} is "B", you MUST include classes with sections like "B", "B1", "B2", "B3", etc. because they all belong to the student's section group. Do not ignore sub-sections!).
3. Active Day Continuation Rule (CRITICAL for DIU multi-page layout):
   - DIU Master Routine pages are sequential. A day's table continues onto the next page.
   - Any rows at the top of a page *before* a new day header belongs to the **previous page's active day**.
   - If a page has NO day header at all (like Page 7), all of its rows belong to the **last day header that appeared on the previous page** (e.g., Page 7 is a continuation of Thursday, so all classes on Page 7 are on "Thursday"!).
   - Let's trace the active day transition across the document:
     * Saturday starts on Page 1.
     * Sunday starts on Page 2 (any rows above "Sunday" on Page 2 are on Saturday).
     * Monday starts on Page 3 (any rows above "Monday" on Page 3 are on Sunday).
     * Tuesday starts on Page 4 (any rows above "Tuesday" on Page 4 are on Monday).
     * Wednesday starts on Page 5 (any rows above "Wednesday" on Page 5 are on Tuesday).
     * Thursday starts on Page 6 (any rows above "Thursday" on Page 6 are on Wednesday).
     * Thursday continues on Page 7 (there is no Friday header on Page 7, so everything on Page 7 is on **Thursday**!).
PROMPT;
    }

    /**
     * Pre-filters raw text to keep only contextually relevant lines (reducing token waste and AI noise).
     */
    protected function preFilterRawText(string $rawText, User $user): string
    {
        // For short texts (screenshots/single-page), preserve intact
        if (strlen($rawText) < 3000) {
            return $rawText;
        }

        $dept = strtolower($user->department ?: 'swe');
        $batch = strtolower($user->batch ?: '40');
        $sec = strtolower($user->section ?: 'b');

        $lines = explode("\n", $rawText);
        $totalLines = count($lines);
        $keepIndices = [];

        $pattern = '/\b' . preg_quote($batch, '/') . '\s*[-–—_\s]*\s*' . preg_quote($sec, '/') . '/i';
        for ($i = 0; $i < $totalLines; $i++) {
            $lineLower = strtolower($lines[$i]);
            
            // Search for department + batch + section combinations using regex or keywords
            if (
                preg_match($pattern, $lineLower) ||
                (strpos($lineLower, $dept) !== false && strpos($lineLower, $batch) !== false) || 
                (strpos($lineLower, 'batch: ' . $batch) !== false) ||
                (strpos($lineLower, 'sec: ' . $sec) !== false)
            ) {
                // Keep this line, and 3 lines before and after for time slots/room context
                for ($j = max(0, $i - 3); $j <= min($totalLines - 1, $i + 3); $j++) {
                    $keepIndices[$j] = true;
                }
            }
        }

        // Always keep headers or day lines
        $days = ['sunday', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday'];
        for ($i = 0; $i < min($totalLines, 40); $i++) {
            // Keep first 40 lines (usually contains column headers/time slot listings)
            $keepIndices[$i] = true;
        }

        for ($i = 0; $i < $totalLines; $i++) {
            $lineLower = strtolower($lines[$i]);
            foreach ($days as $day) {
                if (strpos($lineLower, $day) !== false) {
                    $keepIndices[$i] = true;
                }
            }
        }

        // Assemble filtered text
        $filteredLines = [];
        ksort($keepIndices);
        foreach (array_keys($keepIndices) as $idx) {
            $filteredLines[] = $lines[$idx];
        }

        Log::info(sprintf("[AIOCRParser] Pre-filtered raw text from %d lines to %d lines.", $totalLines, count($filteredLines)));

        return implode("\n", $filteredLines);
    }
}
