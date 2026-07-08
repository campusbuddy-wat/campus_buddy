<?php

namespace App\Jobs;

use App\Models\QuestionBank;
use App\Services\GroqAIService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class GenerateQuestionBankMetadata implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $questionBank;

    /**
     * Create a new job instance.
     */
    public function __construct(QuestionBank $questionBank)
    {
        $this->questionBank = $questionBank;
    }

    /**
     * Execute the job.
     */
    public function handle(GroqAIService $groq): void
    {
        $filePaths = $this->questionBank->file_path ?? [];
        if (is_string($filePaths)) {
            $filePaths = json_decode($filePaths, true) ?? [];
        }

        $extractedText = '';
        foreach ($filePaths as $path) {
            $isUrl = str_starts_with($path, 'http://') || str_starts_with($path, 'https://');
            $tempFile = null;
            $localPath = '';

            if ($isUrl) {
                try {
                    // Download file to a temporary local path
                    $tempFile = tempnam(sys_get_temp_dir(), 'qb_');
                    $contents = file_get_contents($path);
                    if ($contents === false) {
                        Log::warning("[AI:Job] Failed to download file from URL: {$path}");
                        continue;
                    }
                    file_put_contents($tempFile, $contents);
                    $localPath = $tempFile;
                } catch (\Exception $e) {
                    Log::warning("[AI:Job] Download failed for {$path}: " . $e->getMessage());
                    if ($tempFile && file_exists($tempFile)) {
                        @unlink($tempFile);
                    }
                    continue;
                }
            } else {
                $localPath = storage_path('app/public/' . $path);
            }

            if (empty($localPath) || !file_exists($localPath)) {
                if ($tempFile && file_exists($tempFile)) {
                    @unlink($tempFile);
                }
                continue;
            }

            $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));

            if ($ext === 'pdf') {
                try {
                    $parser = new \Smalot\PdfParser\Parser();
                    $pdf = $parser->parseFile($localPath);
                    $extractedText .= $pdf->getText() . "\n";
                } catch (\Exception $e) {
                    Log::warning('[AI:Job] PDF parse failed: ' . $e->getMessage());
                }
            } elseif (in_array($ext, ['jpg', 'jpeg', 'png'])) {
                try {
                    $ocr = new \thiagoalessio\TesseractOCR\TesseractOCR($localPath);
                    if (file_exists('/usr/bin/tesseract')) {
                        $ocr->executable('/usr/bin/tesseract');
                    } elseif (file_exists('/opt/homebrew/bin/tesseract')) {
                        $ocr->executable('/opt/homebrew/bin/tesseract');
                    }
                    $extractedText .= $ocr->run() . "\n";
                } catch (\Exception $e) {
                    Log::warning('[AI:Job] OCR failed: ' . $e->getMessage());
                }
            }

            // Cleanup temp file
            if ($tempFile && file_exists($tempFile)) {
                @unlink($tempFile);
            }
        }

        if (empty(trim($extractedText))) {
            Log::info('[AI:Job] No text was extracted from files.');
            return;
        }

        $extractedText = substr($extractedText, 0, 8000); // Limit to ~8k chars

        $systemPrompt = <<<PROMPT
You are a university administrator AI. Analyze the following raw text extracted from an exam question paper.
Extract the metadata and output ONLY a JSON object with the following keys:
- department (e.g. "SWE" or "CSE", deduce from course if possible)
- course_code (e.g. "SWE441" or "SE225")
- course_name (e.g. "Software Quality Assurance")
- title (MUST be exactly "Midterm", "Final", or "Quiz")
- difficulty (MUST be "Easy", "Medium", or "Hard")
- year_semester (e.g. "Fall 2024" or "Spring 2025")
- question_heading (e.g. "Q1: Software Testing" or a very short overview of the main topic)
- sub_questions (A short bulleted summary of 2-3 main questions asked. One per line.)
- tags (3-4 relevant comma-separated tags, e.g. "testing, quality, diagrams")

If you cannot find a value, leave it as an empty string. DO NOT include markdown formatting.
PROMPT;

        try {
            $jsonResponse = $groq->chatJson($systemPrompt, [
                ['role' => 'user', 'content' => $extractedText]
            ]);

            $data = json_decode($jsonResponse, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                // Update the model with the extracted data, falling back to what's already there
                $this->questionBank->update([
                    'department' => $data['department'] ?: $this->questionBank->department,
                    'course_code' => $data['course_code'] ?: $this->questionBank->course_code,
                    'course_name' => $data['course_name'] ?: $this->questionBank->course_name,
                    'title' => $data['title'] ?: $this->questionBank->title,
                    'difficulty' => $data['difficulty'] ?: $this->questionBank->difficulty,
                    'year_semester' => $data['year_semester'] ?: $this->questionBank->year_semester,
                    'question_heading' => $data['question_heading'] ?: $this->questionBank->question_heading,
                    'sub_questions' => $data['sub_questions'] ?: $this->questionBank->sub_questions,
                    'tags' => $data['tags'] ?: $this->questionBank->tags,
                ]);
            }
        } catch (\Exception $e) {
            Log::error('[AI:Job] Metadata extraction failed: ' . $e->getMessage());
        }
    }
}
