<?php

namespace App\Http\Controllers;

use App\Models\Schedule;
use Illuminate\Http\Request;

class ScheduleController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        if ($user && $user->role === 'admin') {
            // Admin can view all schedules
            $schedules = Schedule::orderBy('day')->get();
        } else {
            // Filter schedules by user's group and major
            $schedules = Schedule::where('department', $user->department)
                ->where('batch', $user->batch)
                ->where('section', $user->section)
                ->where(function ($query) use ($user) {
                    if ($user->major) {
                        $query->where('major', $user->major)->orWhereNull('major')->orWhere('major', '');
                    } else {
                        $query->whereNull('major')->orWhere('major', '');
                    }
                })
                ->get();
        }

        return view('routine', compact('schedules'));
    }

    public function store(Request $request)
    {
        // Only CR or Admin can add schedules
        if (!in_array(auth()->user()->role, ['cr', 'admin'])) {
            return back()->with('error', 'Only CR or Admin can manage schedules.');
        }

        $request->validate([
            'course_code' => 'required|string|max:20',
            'course_title' => 'required|string|max:255',
            'teacher_initial' => 'required|string|max:50',
            'room_no' => 'required|string|max:20',
            'type' => 'required|string|in:theory,lab',
            'lab_section' => 'nullable|string|max:50',
            'day' => 'required|string',
            'time_slot' => 'required|string',
        ]);

        $data = $request->all();
        // Auto-fill ownership from CR profile
        $user = auth()->user();
        $data['department'] = $user->department;
        $data['batch'] = $user->batch;
        $data['section'] = $user->section;
        $data['major'] = $user->major;
        $data['user_id'] = $user->id;

        Schedule::create($data);

        return back()->with('success', 'Schedule added successfully!');
    }

    public function update(Request $request, Schedule $schedule)
    {
        $user = auth()->user();
        $isOwner = $schedule->user_id === $user->id;
        $isAdmin = $user->role === 'admin';

        if (!$isAdmin && !$isOwner) {
            return back()->with('error', 'Unauthorized. You can only manage your own submissions.');
        }

        $request->validate([
            'course_code' => 'required|string|max:20',
            'course_title' => 'required|string|max:255',
            'teacher_initial' => 'required|string|max:50',
            'room_no' => 'required|string|max:20',
            'type' => 'required|string|in:theory,lab',
            'lab_section' => 'nullable|string|max:50',
            'day' => 'required|string',
            'time_slot' => 'required|string',
        ]);

        $data = $request->all();
        // Force current group info
        $data['department'] = $user->department;
        $data['batch'] = $user->batch;
        $data['section'] = $user->section;
        $data['major'] = $user->major;

        $schedule->update($data);

        return back()->with('success', 'Schedule updated successfully!');
    }

    public function destroy(Schedule $schedule)
    {
        $user = auth()->user();
        $isOwner = $schedule->user_id === $user->id;
        $isAdmin = $user->role === 'admin';

        if (!$isAdmin && !$isOwner) {
            return back()->with('error', 'Unauthorized. You can only manage your own submissions.');
        }

        $schedule->delete();

        return back()->with('success', 'Schedule deleted successfully!');
    }
}