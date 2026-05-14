<?php

namespace App\Http\Controllers;

use App\Models\Announcement;
use App\Models\ClassTask;
use App\Models\Event;
use App\Models\Post;
use App\Models\Schedule;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        // 1. Announcements: Filtered by group, latest first
        $announcements = Announcement::where('department', $user->department)
            ->where('batch', $user->batch)
            ->where('section', $user->section)
            ->where(function ($query) use ($user) {
                if ($user->major) {
                    $query->where('major', $user->major)
                        ->orWhereNull('major')
                        ->orWhere('major', '');
                } else {
                    $query->whereNull('major')->orWhere('major', '');
                }
            })
            ->latest()
            ->get();

        // 2. Class Tasks: Filtered by group, sorted by URGENCY (deadline ASC)
        if ($user->role === 'admin') {
            $assignments = ClassTask::orderBy('deadline', 'asc')->get();
        } else {
            $assignments = ClassTask::where('department', $user->department)
                ->where('batch', $user->batch)
                ->where('section', $user->section)
                ->where(function ($query) use ($user) {
                    if ($user->major) {
                        $query->where('major', $user->major)
                            ->orWhereNull('major')
                            ->orWhere('major', '');
                    } else {
                        $query->whereNull('major')->orWhere('major', '');
                    }
                })
                ->orderBy('deadline', 'asc')
                ->get();
        }

        // 3. Today's Schedule
        if ($user->role === 'admin') {
            $todaySchedule = Schedule::where('day', now()->format('l'))
                ->orderBy('time_slot', 'asc')
                ->get();
        } else {
            $todaySchedule = Schedule::where('day', now()->format('l'))
                ->where('section', $user->section)
                ->where('department', $user->department)
                ->where(function ($query) use ($user) {
                    $query->where('batch', $user->batch)->orWhereNull('batch');
                })
                ->where(function ($query) use ($user) {
                    if ($user->major) {
                        $query->where('major', $user->major)->orWhereNull('major')->orWhere('major', '');
                    } else {
                        $query->whereNull('major')->orWhere('major', '');
                    }
                })
                ->orderBy('time_slot', 'asc')
                ->get();
        }

        $events = Event::latest()->get();

        // 4. Latest Community Posts
        $latestPosts = Post::with(['user', 'likes', 'comments'])->latest()->take(4)->get();

        return view('dashboard', compact('announcements', 'assignments', 'todaySchedule', 'events', 'latestPosts'));
    }
}
