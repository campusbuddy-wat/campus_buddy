<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Database Cleanup Scheduled Tasks
Schedule::call(function () {
    // 1. Remove Class Tasks where deadline is strictly older than 15 days ago
    DB::table('class_tasks')
        ->where('deadline', '<', Carbon::now()->subDays(15))
        ->delete();

    // 2. Remove Announcements strictly older than 7 days
    DB::table('announcements')
        ->where('created_at', '<', Carbon::now()->subDays(7))
        ->delete();
})->daily();

// Pre-warm Visitor AI cache every 6 hours so real DIU web data is always ready
Schedule::command('visitor-ai:warm-cache')->everySixHours();
