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
}
