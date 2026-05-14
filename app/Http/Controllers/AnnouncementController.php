<?php

namespace App\Http\Controllers;

use App\Models\Announcement;
use Illuminate\Http\Request;

class AnnouncementController extends Controller
{
    public function store(Request $request)
    {
        if (!in_array(auth()->user()->role, ['cr', 'admin'])) {
            return back()->with('error', 'Only CR or Admin can add announcements.');
        }

        $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
        ]);

        Announcement::create([
            'user_id' => auth()->id(),
            'title' => $request->title,
            'content' => $request->input('content'),
            'department' => auth()->user()->department,
            'batch' => auth()->user()->batch,
            'section' => auth()->user()->section,
            'major' => auth()->user()->major,
        ]);

        return back()->with('success', 'Announcement added successfully!');
    }
}