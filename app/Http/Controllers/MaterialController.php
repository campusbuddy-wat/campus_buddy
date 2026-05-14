<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Material;
use Illuminate\Support\Facades\Auth;

class MaterialController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'course_code' => 'required|string|max:20',
            'file' => 'required|file|mimes:pdf,pptx,docx,doc|max:65536', // 64MB max
            'type' => 'nullable|string|in:class_material,hand_note',
            'department' => 'nullable|string|max:50',
            'batch' => 'nullable|string|max:20',
            'section' => 'nullable|string|max:10',
        ]);

        $user = Auth::user();
        $file = $request->file('file');
        $extension = $file->getClientOriginalExtension();
        $path = $file->store('materials', 'public');

        $department = $request->department ?: $user->department;
        $batch = $request->batch ?: $user->batch;
        $section = $request->section ?: $user->section;

        if (!$department || !$section || !$batch) {
            return redirect()->back()->withErrors(['file' => 'Department, Section, and Batch are required. Please fill them in or update your profile.']);
        }

        $material = Material::create([
            'user_id' => $user->id,
            'type' => $request->type ?? 'class_material',
            'department' => $department,
            'major' => $user->major,
            'section' => $section,
            'batch' => $batch,
            'course_code' => $request->course_code,
            'title' => $request->title,
            'file_path' => $path,
            'file_extension' => $extension,
        ]);

        \Illuminate\Support\Facades\Log::info("Material created: ID {$material->id}, Title: {$material->title} for {$material->department}-{$material->batch}-{$material->section}");

        return redirect()->back()->with('success', 'Material uploaded successfully!');
    }
}