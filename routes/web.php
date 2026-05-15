<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\BuddyAIController;
use App\Http\Controllers\AIFeaturesController;
use App\Http\Controllers\ClassTaskController;
use App\Http\Controllers\AnnouncementController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ScheduleController;
use App\Http\Controllers\AlumniController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\NotesController;
use App\Http\Controllers\ClubController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\EventController;
use Illuminate\Support\Facades\Route;

// ==================== PUBLIC ROUTES ====================

// Landing Page
Route::get('/', [PageController::class, 'landing'])->name('landing');

// Buddy Visitor (no auth required)
Route::get('/buddy-visitor', [PageController::class, 'buddyVisitor'])->name('buddy-visitor');

// Documentation / Pitch Deck (public with admin-controlled access)
Route::get('/docs', [\App\Http\Controllers\DocsController::class, 'index'])->name('docs');

// ==================== AUTH ROUTES ====================

Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login'])->middleware('throttle:login');
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

Route::get('/signup', [\App\Http\Controllers\Auth\SignupController::class, 'showRegistrationForm'])->name('signup');
Route::post('/signup', [\App\Http\Controllers\Auth\SignupController::class, 'register'])->middleware('throttle:signup');

// Password Reset
Route::get('/forgot-password', [ForgotPasswordController::class, 'showLinkRequestForm'])->name('password.request');
Route::post('/forgot-password', [ForgotPasswordController::class, 'sendResetCode'])->name('password.email');
Route::get('/reset-password', [ForgotPasswordController::class, 'showResetForm'])->name('password.reset.form');
Route::post('/reset-password', [ForgotPasswordController::class, 'reset'])->name('password.reset.update');

Route::post('/login/guest', function () {
    return 'Guest Login Route';
})->name('login.guest');

// ==================== AUTHENTICATED ROUTES ====================

// Dashboard
Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard')->middleware('auth');

// CR Dashboard
Route::get('/cr-dashboard', [PageController::class, 'crDashboard'])->name('cr-dashboard')->middleware('auth');

// Buddy AI Chat
Route::get('/buddy-chat', [PageController::class, 'buddyChat'])->name('buddy-chat')->middleware(['auth', 'throttle:buddy-chat']);

// Schedule/Routine
Route::get('/routine', [ScheduleController::class, 'index'])->name('routine')->middleware('auth');
Route::post('/schedule', [ScheduleController::class, 'store'])->name('schedule.store')->middleware('auth');
Route::put('/schedule/{schedule}', [ScheduleController::class, 'update'])->name('schedule.update')->middleware('auth');
Route::delete('/schedule/{schedule}', [ScheduleController::class, 'destroy'])->name('schedule.destroy')->middleware('auth');

// Class Tasks
Route::get('/classtask', [ClassTaskController::class, 'index'])->name('classtask')->middleware('auth');
Route::post('/assignments', [ClassTaskController::class, 'store'])->name('assignments.store')->middleware('auth');
Route::put('/classtask/{task}', [ClassTaskController::class, 'update'])->name('classtask.update')->middleware('auth');
Route::delete('/classtask/{task}', [ClassTaskController::class, 'destroy'])->name('classtask.destroy')->middleware('auth');
Route::post('/classtask/{task}/complete', [ClassTaskController::class, 'complete'])->name('classtask.complete')->middleware('auth');

// Announcements
Route::post('/announcements', [AnnouncementController::class, 'store'])->name('announcements.store')->middleware('auth');

// Notes/Materials
Route::get('/notes', [NotesController::class, 'index'])->name('notes')->middleware('auth');
Route::post('/materials', [\App\Http\Controllers\MaterialController::class, 'store'])->name('materials.store')->middleware('auth');

// Clubs
Route::get('/clubs', [ClubController::class, 'index'])->name('clubs')->middleware('auth');

// Question Bank
Route::get('/question-bank', [\App\Http\Controllers\QuestionBankController::class, 'index'])->name('question-bank')->middleware('auth');
Route::post('/question-bank', [\App\Http\Controllers\QuestionBankController::class, 'store'])->name('question-bank.store')->middleware('auth');

// Community
Route::get('/community', [PostController::class, 'index'])->name('community')->middleware('auth');
Route::post('/community/post', [PostController::class, 'store'])->name('community.post.store')->middleware('auth');
Route::post('/community/post/{post}/like', [PostController::class, 'like'])->name('community.post.like')->middleware('auth');

Route::post('/community/post/{post}/comment', [CommentController::class, 'store'])->name('community.post.comment')->middleware('auth');
Route::put('/community/comment/{comment}', [CommentController::class, 'update'])->name('community.comment.update')->middleware('auth');
Route::delete('/community/comment/{comment}', [CommentController::class, 'destroy'])->name('community.comment.destroy')->middleware('auth');
Route::post('/community/comment/{comment}/like', [CommentController::class, 'like'])->name('community.comment.like')->middleware('auth');
Route::post('/community/comment/{comment}/reply', [CommentController::class, 'reply'])->name('community.comment.reply')->middleware('auth');

// Talents
Route::get('/talents', [\App\Http\Controllers\TalentController::class, 'index'])->name('talents')->middleware('auth');
Route::post('/talents', [\App\Http\Controllers\TalentController::class, 'store'])->name('talents.store')->middleware('auth');

// Alumni
Route::get('/alumni', [AlumniController::class, 'index'])->name('alumni')->middleware('auth');
Route::post('/alumni/register', [AlumniController::class, 'store'])->name('alumni.register')->middleware('auth');
Route::delete('/alumni/{alumni}', [AlumniController::class, 'destroy'])->name('alumni.destroy')->middleware('auth');

// Events
Route::post('/events', [EventController::class, 'store'])->name('events.store')->middleware('auth');

// Profile
Route::match(['post', 'patch'], '/profile/update', [ProfileController::class, 'update'])->name('profile.update')->middleware('auth');
Route::get('/profile/settings', [ProfileController::class, 'settings'])->name('profile.settings')->middleware('auth');
Route::patch('/profile/settings', [ProfileController::class, 'updateSettings'])->name('profile.settings.update')->middleware('auth');
Route::delete('/profile/image', [ProfileController::class, 'deleteProfileImage'])->name('profile.image.delete')->middleware('auth');

// ==================== AI CHAT API ROUTES ====================

// Buddy AI (authenticated students — personalized with RAG context)
Route::post('/api/buddy-chat', [BuddyAIController::class, 'chat'])
    ->middleware(['auth', 'throttle:30,1'])
    ->name('api.buddy-chat');

// Visitor AI (public — DIU admission assistant, no auth needed)
Route::post('/api/buddy-visitor', [BuddyAIController::class, 'visitorChat'])
    ->middleware('throttle:20,1')
    ->name('api.buddy-visitor');

// Get specific chat history
Route::get('/api/ai-chat/{chat}', [BuddyAIController::class, 'getChat'])
    ->middleware('throttle:30,1')
    ->name('api.ai-chat.get');

// ==================== AI FEATURE API ROUTES ====================

// Daily Dashboard Briefing (auto-loads on dashboard)
Route::get('/api/ai/daily-briefing', [AIFeaturesController::class, 'dailyBriefing'])
    ->middleware(['auth', 'throttle:10,1'])
    ->name('api.ai.daily-briefing');

// Personalized Routine Advisor
Route::post('/api/ai/routine-advisor', [AIFeaturesController::class, 'routineAdvisor'])
    ->middleware(['auth', 'throttle:20,1'])
    ->name('api.ai.routine-advisor');

// Dynamic Task Tips Generator
Route::post('/api/ai/task-tips', [AIFeaturesController::class, 'taskTips'])
    ->middleware(['auth', 'throttle:20,1'])
    ->name('api.ai.task-tips');

// PDF & Notes Summarizer
Route::post('/api/ai/summarize-notes', [AIFeaturesController::class, 'summarizeNotes'])
    ->middleware(['auth', 'throttle:15,1'])
    ->name('api.ai.summarize-notes');

// Question Bank Practice Generator
Route::post('/api/ai/practice-generator', [AIFeaturesController::class, 'practiceGenerator'])
    ->middleware(['auth', 'throttle:15,1'])
    ->name('api.ai.practice-generator');
