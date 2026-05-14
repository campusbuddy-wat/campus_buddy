<?php

namespace App\Http\Controllers;

use App\Models\DocsSetting;
use App\Models\DocsSection;
use App\Models\DocsTeamMember;
use App\Models\User;
use App\Models\Post;
use App\Models\Material;
use App\Models\Schedule;
use App\Models\QuestionBank;
use App\Models\AlumniRegistration;
use App\Models\Event;
use App\Models\Club;
use App\Models\Talent;

class DocsController extends Controller
{
    public function index()
    {
        $settings = DocsSetting::getSettings();

        // Access control check
        if (!$settings->isCurrentlyAccessible()) {
            return response()->view('docs-unavailable', [], 403);
        }

        // Fetch visible sections ordered by sort_order
        $sections = DocsSection::visible()->get();

        // Fetch team members
        $teamMembers = DocsTeamMember::orderBy('sort_order')->get();

        // Live metrics from the database
        $metrics = [
            'total_users' => User::count(),
            'total_students' => User::where('role', 'student')->count(),
            'total_posts' => Post::count(),
            'total_materials' => Material::count(),
            'total_schedules' => Schedule::count(),
            'total_questions' => QuestionBank::count(),
            'total_alumni' => AlumniRegistration::count(),
            'total_events' => Event::count(),
            'total_clubs' => Club::count(),
            'total_talents' => Talent::count(),
        ];

        return view('docs', compact('settings', 'sections', 'teamMembers', 'metrics'));
    }
}
