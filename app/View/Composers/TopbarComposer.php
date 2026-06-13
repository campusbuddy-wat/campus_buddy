<?php

namespace App\View\Composers;

use App\Models\AlumniRegistration;
use App\Models\Announcement;
use App\Models\ClassTask;
use App\Models\Material;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Route;
use Illuminate\View\View;

class TopbarComposer
{
    /**
     * Bind notification data to the topbar view.
     * Queries are cached per-user for 60s to avoid hitting Neon on every page load.
     */
    public function compose(View $view): void
    {
        $currentRoute = Route::currentRouteName() ?? '';

        $isAuthPage = in_array($currentRoute, [
            'login', 'signup', 'password.request',
            'password.reset.form', 'password.reset.update'
        ]);
        $isLandingPage = ($currentRoute === 'landing');

        $notifications = collect();
        $unreadCount = 0;

        // Only fetch notifications for authenticated users on internal pages
        if (Auth::check() && !$isLandingPage && !$isAuthPage) {
            $userId = Auth::id();
            $cacheKey = "topbar_notifications_{$userId}";

            // Cache all 4 DB queries together for 60 seconds per user
            $notifications = Cache::remember($cacheKey, 60, function () use ($userId) {
                $recentAnnouncements = Announcement::latest()->take(2)->get()->map(function ($item) {
                    $item->notif_type = 'announcement';
                    $item->notif_icon = 'dashboard';
                    $item->notif_label = 'CR Announcement';
                    return $item;
                });

                $recentTasks = ClassTask::latest()->take(2)->get()->map(function ($item) {
                    $item->notif_type = 'task';
                    $item->notif_icon = 'submission';
                    $item->notif_label = 'New ClassTask';
                    return $item;
                });

                $recentMaterials = Material::latest()->take(1)->get()->map(function ($item) {
                    $item->notif_type = 'material';
                    $item->notif_icon = 'alert';
                    $item->notif_label = 'New Material';
                    return $item;
                });

                // Alumni Approval Notification
                $alumniNotif = collect();
                $userEmail = Auth::user()->email;
                $approvedAlumni = AlumniRegistration::where('email', $userEmail)
                    ->where('status', 'approved')
                    ->orderBy('updated_at', 'desc')
                    ->first();

                if ($approvedAlumni) {
                    if (!$approvedAlumni->is_notified || $approvedAlumni->updated_at->diffInHours(now()) < 24) {
                        $alumniNotif = collect([(object)[
                            'notif_type' => 'alumni',
                            'notif_icon' => 'alert',
                            'notif_label' => 'System',
                            'title' => 'Alumni registration approved!',
                            'created_at' => $approvedAlumni->updated_at,
                            'id' => $approvedAlumni->id
                        ]]);
                    }
                }

                return $recentAnnouncements->concat($recentTasks)
                    ->concat($recentMaterials)
                    ->concat($alumniNotif)
                    ->sortByDesc('created_at')
                    ->values();
            });

            $unreadCount = $notifications->count();
        }

        $view->with([
            'currentRoute' => $currentRoute,
            'isAuthPage' => $isAuthPage,
            'isLandingPage' => $isLandingPage,
            'notifications' => $notifications,
            'unreadCount' => $unreadCount,
        ]);
    }
}
