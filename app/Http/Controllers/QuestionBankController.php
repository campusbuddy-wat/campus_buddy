<?php

namespace App\Http\Controllers;

use App\Models\QuestionBank;
use Illuminate\Http\Request;

class QuestionBankController extends Controller
{
    public function index(Request $request)
    {
        $query = QuestionBank::query()->where('status', 'approved');

        if ($request->filled('course')) {
            $query->where(function($q) use ($request) {
                $q->where('course_code', 'like', '%' . $request->course . '%')
                  ->orWhere('course_name', 'like', '%' . $request->course . '%')
                  ->orWhere('department', 'like', '%' . $request->course . '%');
            });
        }

        if ($request->filled('semester')) {
            $query->where('year_semester', 'like', '%' . $request->semester . '%');
        }

        // Prioritize user's department first, then show latest questions
        if (auth()->check() && auth()->user()->department) {
            $userDept = auth()->user()->department;
            $questions = $query->orderByRaw("CASE WHEN department = ? THEN 0 ELSE 1 END", [$userDept])
                               ->latest()
                               ->get();
        } else {
            $questions = $query->latest()->get();
        }

        return view('questionbank', compact('questions'));
    }

    public function store(Request $request)
    {
        try {
            $request->validate([
                'files.*' => 'required|file|max:15360|mimes:pdf,jpg,jpeg,png', // 15MB max per file, allowing images
                'department' => 'nullable|string',
                'course_code' => 'nullable|string',
                'course_name' => 'nullable|string',
                'title' => 'nullable|string',
                'difficulty' => 'nullable|string',
                'question_heading' => 'nullable|string',
                'sub_questions' => 'nullable|string',
                'year_semester' => 'nullable|string',
            ]);

            $data = $request->only([
                'department', 'course_code', 'course_name', 'title', 
                'difficulty', 'question_heading', 'sub_questions', 'year_semester'
            ]);
            
            $data['user_id'] = auth()->id();
            $data['status'] = 'pending'; 

            $filePaths = [];
            if ($request->hasFile('files')) {
                foreach ($request->file('files') as $file) {
                    $ext = strtolower($file->getClientOriginalExtension());
                    $resourceType = ($ext === 'pdf') ? 'raw' : 'image';
                    // Preserve the original filename WITH extension so Cloudinary URL ends in .pdf/.png/etc.
                    $originalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
                    $safeFilename  = preg_replace('/[^a-zA-Z0-9_-]/', '_', $originalName);
                    $filePaths[] = cloudinary()->uploadApi()->upload($file->getRealPath(), [
                        'folder'          => 'question_banks',
                        'resource_type'   => $resourceType,
                        'use_filename'    => true,
                        'unique_filename' => false,
                        'public_id'       => $safeFilename . '_' . time(),
                        'format'          => $ext,  // force correct extension in URL (.pdf, .png, etc.)
                    ])['secure_url'];
                }
            }
            $data['file_path'] = $filePaths;

            QuestionBank::create($data);

            return redirect()->back()->with('success', 'Question files uploaded successfully! They will appear once approved by admin.');
        } catch (\Exception $e) {
            \Log::error('[QuestionBank] Upload error: ' . $e->getMessage(), [
                'exception' => $e
            ]);
            return redirect()->back()->withInput()->with('error', '❌ Server Error during upload: ' . $e->getMessage());
        }
    }
}
