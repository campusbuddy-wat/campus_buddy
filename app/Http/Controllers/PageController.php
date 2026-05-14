<?php

namespace App\Http\Controllers;

class PageController extends Controller
{
    public function crDashboard()
    {
        return view('cr-dashboard');
    }

    public function buddyChat()
    {
        $chats = \App\Models\AiChat::where('user_id', auth()->id())
                    ->where('type', 'buddy')
                    ->latest()
                    ->get();
        return view('buddy-chat', compact('chats'));
    }

    public function buddyVisitor()
    {
        $chats = \App\Models\AiChat::where('session_id', session()->getId())
                    ->where('type', 'visitor')
                    ->latest()
                    ->get();
        return view('buddy-visitor', compact('chats'));
    }

    public function landing()
    {
        return view('landing');
    }
}
