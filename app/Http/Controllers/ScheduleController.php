<?php

namespace App\Http\Controllers;

use App\Models\Schedule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class ScheduleController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        
        if ($user->role === 'admin') {
            $cacheKey = "routine_admin";
        } else {
            $cacheKey = "routine_{$user->department}_{$user->batch}_{$user->section}";
            if ($user->major) {
                $cacheKey .= "_{$user->major}";
            }
        }

        $schedules = Cache::remember($cacheKey, 60, function () use ($user) {
            if ($user->role === 'admin') {
                return Schedule::orderBy('day')->get();
            }
            return Schedule::where('department', $user->department)
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
        });

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

        $this->clearRoutineCache($user);

        return back()->with('success', 'Schedule added successfully!');
    }

    public function update(Request $request, Schedule $schedule)
    {
        $user = auth()->user();
        $isCR = $user->role === 'cr' &&
                $schedule->department === $user->department &&
                $schedule->batch == $user->batch &&
                $schedule->section === $user->section;
        $isAdmin = $user->role === 'admin';

        if (!$isAdmin && !$isCR) {
            return back()->with('error', 'Unauthorized. You can only manage schedules for your own section.');
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

        $this->clearRoutineCache($user);

        return back()->with('success', 'Schedule updated successfully!');
    }

    public function destroy(Schedule $schedule)
    {
        $user = auth()->user();
        $isCR = $user->role === 'cr' &&
                $schedule->department === $user->department &&
                $schedule->batch == $user->batch &&
                $schedule->section === $user->section;
        $isAdmin = $user->role === 'admin';

        if (!$isAdmin && !$isCR) {
            return back()->with('error', 'Unauthorized. You can only manage schedules for your own section.');
        }

        $schedule->delete();

        $this->clearRoutineCache($user);

        return back()->with('success', 'Schedule deleted successfully!');
    }

    private function clearRoutineCache($user)
    {
        $cacheKey = "routine_{$user->department}_{$user->batch}_{$user->section}";
        if ($user->major) {
            $cacheKey .= "_{$user->major}";
        }
        Cache::forget($cacheKey);
        Cache::forget("routine_admin");
    }
}