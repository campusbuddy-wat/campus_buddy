<?php

namespace App\Http\Controllers;

use App\Models\QuestionBank;
use Illuminate\Http\Request;

class QuestionBankController extends Controller
{
    public function index(Request $request)
    {
        $query = QuestionBank::query()->where('status', 'approved');

        if (auth()->check() && auth()->user()->department) {
            $query->where('department', auth()->user()->department);
        }

        if ($request->filled('course')) {
            $query->where(function($q) use ($request) {
                $q->where('course_code', 'like', '%' . $request->course . '%')
                  ->orWhere('course_name', 'like', '%' . $request->course . '%');
            });
        }

        if ($request->filled('semester')) {
            $query->where('year_semester', 'like', '%' . $request->semester . '%');
        }

        $questions = $query->latest()->get();

        return view('questionbank', compact('questions'));
    }

    public function store(Request $request)
    {
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
                $filePaths[] = $file->store('question_banks', 'public');
            }
        }
        $data['file_path'] = $filePaths;

        QuestionBank::create($data);

        return redirect()->back()->with('success', 'Question files uploaded successfully! They will appear once approved by admin.');
    }
}
