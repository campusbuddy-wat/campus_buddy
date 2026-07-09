<?php

namespace App\Http\Controllers;

use App\Models\Material;
use Illuminate\Support\Facades\Auth;

class NotesController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        if (!$user->department || !$user->section || !$user->batch) {
            $classMaterials = collect();
            $handNotes = collect();
            return view('notes', compact('classMaterials', 'handNotes'))
                ->with('error', 'Please update your profile with Department, Batch, and Section to see materials.');
        }

        $query = Material::where('department', $user->department)
            ->where('batch', $user->batch)
            ->where('section', $user->section);

        if ($user->major) {
            $query->where(function ($q) use ($user) {
                $q->whereNull('major')->orWhere('major', '')->orWhere('major', $user->major);
            });
        } else {
            $query->where(function ($q) {
                $q->whereNull('major')->orWhere('major', '');
            });
        }

        $allMaterials = $query->latest()->get();
        $classMaterials = $allMaterials->where('type', 'class_material');
        $handNotes = $allMaterials->where('type', 'hand_note');

        return view('notes', compact('classMaterials', 'handNotes'));
    }

    public function viewMaterial($id)
    {
        $material = Material::findOrFail($id);
        $extension = strtolower($material->file_extension);
        $filename = \Illuminate\Support\Str::slug($material->title) . '.' . $extension;

        $mimeType = $this->getMimeType($extension);

        if (str_starts_with($material->file_path, 'http://') || str_starts_with($material->file_path, 'https://')) {
            // Remote file (Cloudinary)
            try {
                $response = \Illuminate\Support\Facades\Http::timeout(60)->get($material->file_path);
                if ($response->successful()) {
                    return response($response->body(), 200, [
                        'Content-Type' => $mimeType,
                        'Content-Disposition' => 'inline; filename="' . $filename . '"',
                    ]);
                }
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error("[View] Remote file fetch failed: " . $e->getMessage());
            }
            return redirect()->away($material->file_path); // Fallback
        }

        $filePath = storage_path('app/public/' . $material->file_path);

        if (!file_exists($filePath)) {
            abort(404, 'File not found');
        }

        return response()->file($filePath, [
            'Content-Type' => $mimeType,
            'Content-Disposition' => 'inline; filename="' . $filename . '"'
        ]);
    }

    public function downloadMaterial($id)
    {
        $material = Material::findOrFail($id);
        $extension = strtolower($material->file_extension);
        $filename = \Illuminate\Support\Str::slug($material->title) . '.' . $extension;

        $mimeType = $this->getMimeType($extension);

        if (str_starts_with($material->file_path, 'http://') || str_starts_with($material->file_path, 'https://')) {
            // Remote file (Cloudinary)
            try {
                $response = \Illuminate\Support\Facades\Http::timeout(60)->get($material->file_path);
                if ($response->successful()) {
                    return response($response->body(), 200, [
                        'Content-Type' => 'application/octet-stream',
                        'Content-Disposition' => 'attachment; filename="' . $filename . '"',
                    ]);
                }
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error("[Download] Remote file fetch failed: " . $e->getMessage());
            }
            return redirect()->away($material->file_path); // Fallback
        }

        $filePath = storage_path('app/public/' . $material->file_path);

        if (!file_exists($filePath)) {
            abort(404, 'File not found');
        }

        return response()->download($filePath, $filename);
    }

    private function getMimeType($extension)
    {
        $mimes = [
            'pdf'  => 'application/pdf',
            'doc'  => 'application/msword',
            'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'xls'  => 'application/vnd.ms-excel',
            'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'ppt'  => 'application/vnd.ms-powerpoint',
            'pptx' => 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
            'png'  => 'image/png',
            'jpg'  => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'txt'  => 'text/plain',
        ];

        return $mimes[$extension] ?? 'application/octet-stream';
    }
}
