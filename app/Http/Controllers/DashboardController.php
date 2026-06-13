<?php

namespace App\Http\Controllers;

use App\Models\Announcement;
use App\Models\ClassTask;
use App\Models\Event;
use App\Models\Post;
use App\Models\Schedule;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $uid = $user->id;
        $ttl = 60; // seconds

        // 1. Announcements — cached per user group
        $announcements = Cache::remember("dash_announcements_{$uid}", $ttl, function () use ($user) {
            return Announcement::where('department', $user->department)
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
        });

        // 2. Class Tasks — cached per user group
        $assignments = Cache::remember("dash_tasks_{$uid}", $ttl, function () use ($user) {
            if ($user->role === 'admin') {
                return ClassTask::orderBy('deadline', 'asc')->get();
            }
            return ClassTask::where('department', $user->department)
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
        });

        // 3. Today's Schedule — cached per user group
        $todaySchedule = Cache::remember("dash_schedule_{$uid}_" . now()->format('l'), $ttl, function () use ($user) {
            if ($user->role === 'admin') {
                return Schedule::where('day', now()->format('l'))
                    ->orderBy('time_slot', 'asc')
                    ->get();
            }
            return Schedule::where('day', now()->format('l'))
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
        });

        // 4. Events — cached globally (same for all users)
        $events = Cache::remember('dash_events', $ttl, function () {
            return Event::latest()->get();
        });

        // 5. Latest Community Posts — cached globally
        $latestPosts = Cache::remember('dash_posts', $ttl, function () {
            return Post::with(['user', 'likes', 'comments'])->latest()->take(4)->get();
        });

        return view('dashboard', compact('announcements', 'assignments', 'todaySchedule', 'events', 'latestPosts'));
    }
}
