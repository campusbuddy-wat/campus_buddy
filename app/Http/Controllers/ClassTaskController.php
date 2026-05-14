<?php

namespace App\Http\Controllers;

use App\Models\ClassTask;
use Illuminate\Http\Request;

class ClassTaskController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        if ($user && $user->role === 'admin') {
            $tasks = ClassTask::orderBy('deadline', 'asc')->get();
        } else {
            $tasks = ClassTask::where('department', $user->department)
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

        // Auto-remove: Mark as completed and deadline over -> remove automatically
        $tasks = $tasks->reject(function ($task) {
            return $task->progress_status === 'Completed' && \Carbon\Carbon::parse($task->deadline)->isPast();
        });

        return view('classtask', compact('tasks'));
    }

    public function store(Request $request)
    {
        if (!in_array(auth()->user()->role, ['cr', 'admin'])) {
            return back()->with('error', 'Only CR or Admin can add tasks.');
        }

        $request->validate([
            'type' => 'required|string|in:assignment,quiz,presentation',
            'course_code' => 'required|string|max:20',
            'title' => 'required|string|max:255',
            'deadline' => 'required|date',
        ]);

        ClassTask::create([
            'type' => $request->type,
            'user_id' => auth()->id(),
            'course_code' => $request->course_code,
            'title' => $request->title,
            'topic' => $request->topic,
            'description' => $request->description,
            'deadline' => $request->deadline,
            'duration_or_slot' => $request->duration_or_slot,
            'progress_status' => $request->progress_status,
            'tip_1' => $request->tip_1,
            'tip_2' => $request->tip_2,
            'department' => auth()->user()->department,
            'batch' => auth()->user()->batch,
            'section' => auth()->user()->section,
            'major' => auth()->user()->major,
        ]);

        return back()->with('success', ucfirst($request->type) . ' added successfully!');
    }

    public function update(Request $request, ClassTask $task)
    {
        if (auth()->user()->role !== 'cr' || $task->user_id !== auth()->id()) {
            return back()->with('error', 'Unauthorized action.');
        }

        $request->validate([
            'type' => 'required|string|in:assignment,quiz,presentation',
            'course_code' => 'required|string|max:20',
            'title' => 'required|string|max:255',
            'deadline' => 'required|date',
        ]);

        $task->update([
            'type' => $request->type,
            'course_code' => $request->course_code,
            'title' => $request->title,
            'topic' => $request->topic,
            'description' => $request->description,
            'deadline' => $request->deadline,
            'duration_or_slot' => $request->duration_or_slot,
            'progress_status' => $request->progress_status,
            'tip_1' => $request->tip_1,
            'tip_2' => $request->tip_2,
        ]);

        return back()->with('success', ucfirst($request->type) . ' updated successfully!');
    }

    public function destroy(ClassTask $task)
    {
        if (auth()->user()->role !== 'cr' || $task->user_id !== auth()->id()) {
            return back()->with('error', 'Unauthorized action.');
        }

        $task->delete();

        return back()->with('success', 'Task deleted successfully!');
    }
    public function complete(ClassTask $task)
    {
        // For shared tasks, usually CR/Admin marks as complete, but user asked for a button on each card.
        // We will allow the owner or CR to mark it.
        $task->update([
            'progress_status' => $task->progress_status === 'Completed' ? 'Pending' : 'Completed'
        ]);

        return back()->with('success', 'Task status updated!');
    }
}