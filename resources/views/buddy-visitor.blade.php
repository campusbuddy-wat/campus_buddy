@extends('layouts.app')

@section('title', 'Campus Buddy | DIU Admission Help')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/buddy-chat.css') }}">
    <style>
        /* Force full screen filling and hide footer by default on this page */
        footer, .footer {
            display: none !important;
        }
        
        body, html {
            overflow: hidden !important;
            background: #ffffff !important;
            height: 100vh !important;
            width: 100vw !important;
            margin: 0 !important;
            padding: 0 !important;
        }

        /* Hide unwanted topbar elements for visitors */
        .topbar .desktop-nav, 
        .topbar .top-right-section {
            display: none !important;
        }

        body .layout {
            position: absolute !important;
            top: 100px !important;
            bottom: 0 !important;
            left: 0 !important;
            right: 0 !important;
            height: auto !important;
            min-height: 0 !important;
            padding: 0 !important;
            margin: 0 !important;
            display: flex !important;
            flex-direction: column !important;
            overflow: hidden !important;
            background: #f8fafc !important;
        }

        @media (max-width: 1280px) { body .layout { top: 90px !important; } }
        @media (max-width: 960px) { body .layout { top: 65px !important; } }
        @media (max-width: 768px) { body .layout { top: 60px !important; } }

        body .main {
            flex: 1 !important;
            display: flex !important;
            flex-direction: column !important;
            height: 100% !important;
            width: 100% !important;
            overflow: hidden !important;
            padding-bottom: 0 !important;
            margin: 0 !important;
        }

        body .buddy-chat-wrapper {
            flex: 1 !important;
            height: 100% !important;
            width: 100% !important;
            margin: 0 !important;
            padding: 0 !important;
            overflow: hidden !important;
            display: flex !important;
            background: #f8fafc !important;
        }

        /* Sidebar toggle states */
        body.left-sidebar-hidden .chat-sidebar {
            display: none !important;
        }
        body.right-sidebar-hidden .options-sidebar {
            display: none !important;
        }

        /* Visitor Specific Overrides */
        .welcome-avatar {
            background: linear-gradient(135deg, #16a34a, #00aaff);
            box-shadow: 0 12px 40px rgba(22, 163, 74, 0.3);
        }
        
        .welcome-title span {
            background: linear-gradient(135deg, #16a34a, #00aaff);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            text-shadow: 0 10px 20px rgba(22, 163, 74, 0.1);
        }

        .visitor-badge {
            background: rgba(22, 163, 74, 0.1);
            color: #16a34a;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 700;
            margin-bottom: 15px;
            display: inline-block;
            text-transform: uppercase;
            letter-spacing: 1px;
            border: 1px solid rgba(22, 163, 74, 0.2);
        }

        .options-sidebar {
            background: #ffffff;
            border-left: 1px solid #e2e8f0;
        }

        .section-card h3 { 
            color: #16a34a; 
            border-bottom: 2px solid #f0fdf4;
            padding-bottom: 8px;
            margin-bottom: 15px;
        }
        
        .chat-top-header { 
            border-bottom: 1.5px solid #f0f4f8; 
            background: #fff;
        }

        /* FAQ Interactive Items */
        .faq-item {
            cursor: pointer;
            padding: 14px;
            border-radius: 12px;
            background: #fff;
            border: 1.5px solid #e2e8f0;
            margin-bottom: 12px;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            display: flex;
            flex-direction: column;
            gap: 4px;
        }
        .faq-item:hover {
            border-color: #16a34a;
            box-shadow: 0 4px 15px rgba(22, 163, 74, 0.1);
            transform: translateX(6px);
            background: #f0fdf4;
        }
        .faq-item span {
            color: #1a202c;
            font-weight: 700;
            font-size: 13.5px;
        }
        .faq-item p {
            font-size: 11.5px;
            color: #718096;
            margin: 0;
        }

        .main-send-btn {
            background: #16a34a !important;
            box-shadow: 0 4px 12px rgba(22, 163, 74, 0.3) !important;
        }
        .main-send-btn:hover {
            background: #15803d !important;
        }

        .res-link {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 10px;
            border-radius: 8px;
            color: #4a5568;
            text-decoration: none;
            font-size: 13px;
            font-weight: 500;
            transition: all 0.2s;
        }
        .res-link:hover {
            background: #f0fdf4;
            color: #16a34a;
        }

        /* Message Styling */
        .bot-row .msg-avatar {
            background: linear-gradient(135deg, #16a34a, #00aaff) !important;
        }
        .bot-row .msg-bubble {
            border-left-color: #16a34a;
        }

        /* Mobile specific sidebars logic (reused from buddy-chat) */
        @media (max-width: 768px) {
            .chat-sidebar, .options-sidebar {
                position: fixed;
                top: 60px;
                bottom: 0;
                z-index: 2100;
                width: 280px !important;
                display: none !important;
                box-shadow: 20px 0 50px rgba(0,0,0,0.1);
            }
            .chat-sidebar { left: 0; border-right: none; }
            .options-sidebar { right: 0; border-left: none; }
            body.show-left-sidebar .chat-sidebar { display: flex !important; }
            body.show-right-sidebar .options-sidebar { display: flex !important; }
        }

        /* ── Calculator Modal & Overlay Styles ─────────────────── */
        .calc-overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0,0,0,0.6);
            backdrop-filter: blur(4px);
            z-index: 3000;
            overflow-y: auto;
            padding: 20px;
        }
        .calc-overlay.open {
            display: flex;
            align-items: flex-start;
            justify-content: center;
        }

        .calc-modal {
            background: #ffffff;
            border-radius: 20px;
            width: 100%;
            max-width: 900px;
            overflow: hidden;
            box-shadow: 0 25px 60px rgba(0,0,0,0.25);
            margin: auto;
            animation: calcFadeIn 0.3s ease-out;
        }

        @keyframes calcFadeIn {
            from { opacity: 0; transform: translateY(-20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .calc-header {
            background: linear-gradient(135deg, #16a34a 0%, #0d7632 100%);
            color: #ffffff;
            padding: 20px 24px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .calc-header h2 { margin: 0; font-size: 1.15rem; font-weight: 700; color: #fff; }
        .calc-header p  { margin: 4px 0 0; font-size: 0.78rem; opacity: 0.9; color: #f0fdf4; }
        
        .calc-close {
            background: rgba(255,255,255,0.2);
            border: none;
            border-radius: 50%;
            width: 36px;
            height: 36px;
            cursor: pointer;
            color: #ffffff;
            font-size: 1.2rem;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: background 0.2s;
        }
        .calc-close:hover { background: rgba(255,255,255,0.35); }

        .calc-body {
            display: grid;
            grid-template-columns: 1fr 340px;
            gap: 0;
        }
        @media (max-width: 768px) {
            .calc-body { grid-template-columns: 1fr; }
            .calc-result-panel { border-left: none; border-top: 1px solid #e2e8f0; }
        }

        .calc-form-panel { padding: 24px; max-height: 80vh; overflow-y: auto; }
        
        .calc-section {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 14px;
            padding: 18px;
            margin-bottom: 16px;
        }
        .calc-section-title {
            font-size: 0.85rem;
            font-weight: 700;
            color: #1e293b;
            margin: 0 0 14px;
            text-transform: uppercase;
            letter-spacing: 0.04em;
        }

        .calc-row { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }
        .calc-field { display: flex; flex-direction: column; gap: 5px; margin-bottom: 10px; }
        .calc-field label {
            font-size: 0.75rem;
            font-weight: 600;
            color: #4a5568;
            display: flex;
            align-items: center;
            gap: 4px;
        }
        .calc-field label .req { color: #ef4444; }
        
        .calc-select, .calc-input {
            border: 1.5px solid #cbd5e1;
            border-radius: 10px;
            padding: 10px 14px;
            font-size: 0.85rem;
            color: #1e293b;
            background: #ffffff;
            outline: none;
            transition: border-color 0.2s, box-shadow 0.2s;
            width: 100%;
            box-sizing: border-box;
        }
        .calc-select {
            appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='none' stroke='%2394a3b8' stroke-width='2.5' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpolyline points='6 9 12 15 18 9'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 12px center;
            padding-right: 36px;
        }
        .calc-select:focus, .calc-input:focus {
            border-color: #16a34a;
            box-shadow: 0 0 0 3px rgba(22,163,74,0.15);
        }

        .waiver-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
        }
        @media (max-width: 480px) {
            .waiver-grid { grid-template-columns: 1fr; }
        }
        
        .waiver-card {
            border: 2px solid #e2e8f0;
            border-radius: 12px;
            padding: 12px 14px;
            cursor: pointer;
            transition: all 0.2s;
            display: flex;
            align-items: flex-start;
            gap: 10px;
            background: #ffffff;
        }
        .waiver-card:hover { border-color: #16a34a; background: #f0fdf4; }
        .waiver-card.selected { border-color: #16a34a; background: #f0fdf4; }
        
        .waiver-card .wc-check {
            width: 18px; height: 18px; min-width: 18px;
            border-radius: 50%;
            border: 2px solid #cbd5e1;
            margin-top: 2px;
            display: flex; align-items: center; justify-content: center;
        }
        .waiver-card.selected .wc-check {
            background: #16a34a; border-color: #16a34a;
        }
        .waiver-card.selected .wc-check::after {
            content: '✓'; color: #ffffff; font-size: 11px; font-weight: 700;
        }
        .waiver-card .wc-icon { font-size: 1.1rem; min-width: 22px; }
        .waiver-card .wc-title { font-size: 0.8rem; font-weight: 700; color: #1a202c; line-height: 1.3; }
        .waiver-card .wc-sub { font-size: 0.72rem; color: #718096; margin-top: 2px; }

        .prog-pills { display: grid; grid-template-columns: repeat(3, 1fr); gap: 10px; margin-top: 12px; }
        .prog-pill {
            background: #f1f5f9;
            border-radius: 10px;
            padding: 10px 12px;
            text-align: center;
        }
        .prog-pill .pv { font-size: 0.9rem; font-weight: 700; color: #1e293b; }
        .prog-pill .pk { font-size: 0.7rem; color: #94a3b8; margin-top: 2px; }

        .gpa-hint { font-size: 0.7rem; color: #d97706; margin-top: 4px; display: flex; align-items: center; gap: 4px; }

        .calc-result-panel {
            background: linear-gradient(160deg, #f8fafc 0%, #f1f5f9 100%);
            border-left: 1px solid #e2e8f0;
            padding: 24px;
            display: flex;
            flex-direction: column;
            gap: 16px;
            max-height: 80vh;
            overflow-y: auto;
        }
        .calc-result-panel > * {
            flex-shrink: 0 !important;
        }

        .result-empty {
            text-align: center;
            padding: 60px 16px;
            color: #94a3b8;
        }
        .result-empty .re-icon { font-size: 3rem; margin-bottom: 12px; }
        .result-empty p { font-size: 0.85rem; line-height: 1.5; color: #718096; }

        .result-congrats {
            background: linear-gradient(135deg, #16a34a 0%, #0d7632 100%);
            border-radius: 16px;
            padding: 20px;
            color: #ffffff;
            text-align: center;
        }
        .result-congrats .rc-label { font-size: 0.8rem; opacity: 0.9; margin-bottom: 8px; font-weight: 600; }
        .result-congrats .rc-percent { font-size: 3.2rem; font-weight: 800; line-height: 1; margin: 8px 0; }
        .result-congrats .rc-name { font-size: 0.82rem; opacity: 0.9; }

        .result-card {
            background: #ffffff;
            border-radius: 14px;
            border: 1px solid #e2e8f0;
            overflow: hidden;
        }
        .result-card-head {
            background: #f8fafc;
            border-bottom: 1px solid #e2e8f0;
            padding: 10px 14px;
            font-size: 0.75rem;
            font-weight: 700;
            color: #64748b;
            text-transform: uppercase;
        }
        .result-card-body { padding: 14px; }
        .result-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 6px 0;
            font-size: 0.83rem;
            color: #475569;
            border-bottom: 1px solid #f1f5f9;
        }
        .result-row:last-child { border-bottom: none; }
        .result-row .rk { color: #64748b; }
        .result-row .rv { font-weight: 600; color: #1e293b; }

        .pay-box {
            border-radius: 12px;
            padding: 14px 16px;
            text-align: center;
        }
        .pay-box.admission { background: #eff6ff; border: 1.5px solid #bfdbfe; }
        .pay-box.final     { background: #f0fdf4; border: 1.5px solid #bbf7d0; }
        .pay-box .pb-label { font-size: 0.72rem; color: #64748b; margin-bottom: 4px; }
        .pay-box .pb-amount { font-size: 1.5rem; font-weight: 800; }
        .pay-box.admission .pb-amount { color: #1a56db; }
        .pay-box.final     .pb-amount { color: #16a34a; }
        .pay-box .pb-sub   { font-size: 0.68rem; color: #94a3b8; }

        .result-error {
            background: #fef2f2;
            border: 1px solid #fecaca;
            border-radius: 12px;
            padding: 16px;
            text-align: center;
            color: #dc2626;
            font-size: 0.83rem;
        }

        .calc-btn {
            width: 100%;
            padding: 14px;
            background: linear-gradient(135deg, #16a34a 0%, #0d7632 100%);
            color: #ffffff;
            border: none;
            border-radius: 12px;
            font-size: 0.9rem;
            font-weight: 700;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            transition: all 0.2s;
            font-family: inherit;
            margin-top: 10px;
        }
        .calc-btn:hover:not(:disabled) { opacity: 0.95; transform: translateY(-1px); }
        .calc-btn:disabled { opacity: 0.5; cursor: not-allowed; }

        .reset-btn {
            width: 100%;
            padding: 10px;
            background: #f8fafc;
            border: 1.5px solid #e2e8f0;
            border-radius: 10px;
            color: #475569;
            font-size: 0.82rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
            margin-top: 8px;
        }
        .reset-btn:hover { background: #e2e8f0; }

        .loading-spin {
            display: inline-block;
            width: 16px; height: 16px;
            border: 2px solid rgba(255,255,255,0.5);
            border-top-color: #fff;
            border-radius: 50%;
            animation: spin 0.7s linear infinite;
        }
        @keyframes spin { to { transform: rotate(360deg); } }
    </style>
@endpush

@section('content')
<div class="buddy-chat-wrapper">
    <!-- Sidebar: Admission FAQs -->
    <aside class="chat-sidebar" style="width: 320px;">
        <div class="sidebar-header">
            <span class="sidebar-title">DIU Admission Guide</span>
            <div class="visitor-badge" style="margin-bottom: 0;">Visitor Mode</div>
        </div>
        
        <div class="sidebar-search" style="margin-top: 15px;">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="11" cy="11" r="8" />
                <line x1="21" y1="21" x2="16.65" y2="16.65" />
            </svg>
            <input type="text" id="searchVisitorChats" placeholder="Search DIU admission topics…">
        </div>
        
        <div class="sidebar-label">DIU Frequents Questions</div>

        <!-- Waiver Calculator Card Link -->
        <div class="faq-item" id="openWaiverCalcCard" style="border-color: #16a34a; background: #f0fdf4;">
            <span>💰 DIU Fee & Waiver Calculator</span>
            <p>Calculate your tuition fees & waiver eligibility instantly.</p>
        </div>
        
        <div class="faq-item" onclick="askFAQ('What are the scholarship requirements at DIU?')">
            <span>💰 DIU Scholarship & Waivers</span>
            <p>60%+ students get waivers! Check GPA 5.00 & special cases.</p>
        </div>

        <div class="faq-item" onclick="askFAQ('Tell me about the CSE department labs and research')">
            <span>💻 FSIT & CSE Department</span>
            <p>IoT, AR/VR, Health Informatics, and the unique FAB LAB.</p>
        </div>

        <div class="faq-item" onclick="askFAQ('How is the DIU Smart City campus at Ashulia?')">
            <span>🏫 Smart City Campus</span>
            <p>Explore the green, 20+ acre permanent campus world.</p>
        </div>

        <div class="faq-item" onclick="askFAQ('What is the total fee for B.Sc. in CSE?')">
            <span>💳 Tuition & Fee Structure</span>
            <p>See program-wise breakdown and credit-based costs.</p>
        </div>
        
        <div class="faq-item" onclick="askFAQ('Does DIU provide transport from Green Road?')">
            <span>🚌 Transport & Logistics</span>
            <p>DIU Bus network covering major parts of Dhaka city.</p>
        </div>

        @if(isset($chats) && $chats->count() > 0)
            <div class="sidebar-label" style="margin-top:20px;">Recent Chats</div>
            <div id="chatHistoryList">
                @foreach($chats as $chat)
                    <div class="faq-item chat-history-item" data-id="{{ $chat->id }}" style="cursor:pointer; display:flex; align-items:center; gap:8px;">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                          <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z" />
                        </svg>
                        <span style="font-size:13px; font-weight:500; color:#1e293b; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;">{{ $chat->title }}</span>
                    </div>
                @endforeach
            </div>
        @endif
    </aside>

    <!-- Main Chat Area -->
    <main class="chat-main" id="chatMain">
        <!-- Top Bar -->
        <div class="chat-top-header">
            <div class="chat-top-left">
                <button class="menu-toggle-btn" id="sidebarToggle" title="Toggle Sidebar">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="3" y="3" width="18" height="18" rx="2" ry="2"/>
                        <line x1="9" y1="3" x2="9" y2="21"/>
                    </svg>
                </button>
                <div class="buddy-avatar">
                    <img src="{{ asset('assets/landing/character.png') }}" alt="Buddy" style="width: 100%; height: 100%; border-radius: 50%; object-fit: cover;">
                </div>
                <div class="chat-bot-info">
                    <h2>Buddy AI <span style="font-size: 10px; background: #f0fdf4; color: #16a34a; padding: 2px 6px; border-radius: 4px; margin-left: 6px;">DIU Export Assist</span></h2>
                    <div class="chat-bot-status"><span></span> Online to help you join DIU!</div>
                </div>
            </div>
            <div class="chat-top-actions">
                <button class="chat-action-btn" id="toggleSidebarsBtn" title="Toggle Features">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="3" y="3" width="18" height="18" rx="2" ry="2"/>
                        <line x1="15" y1="3" x2="15" y2="21"/>
                    </svg>
                </button>
            </div>
        </div>

        <!-- Welcome Stage -->
        <div class="welcome-section" id="welcomeSection">
            <div class="welcome-avatar">
                <img src="{{ asset('assets/landing/character.png') }}">
                <div class="avatar-pulse-ring"></div>
            </div>
            <div class="welcome-text">
                <div class="visitor-badge">Daffodil Int. University Counselor</div>
                <h1 class="welcome-title">Welcome to <span>Daffodil Smart City!</span></h1>
                <p class="welcome-subtitle">I'm your DIU Buddy. Explore our 20+ acre eco-campus at Ashulia or ask about our scholarships, labs, and modern departments.</p>
            </div>

            <div class="quick-prompts">
                <div class="quick-prompt-chip" onclick="askFAQ('Waiver policy for GPA 5.00')">
                    <span class="chip-icon">🏆</span>
                    <div class="chip-content">
                        <span class="chip-title">Scholarships</span>
                        <span class="chip-desc">"GPA 5.00 Waiver Details"</span>
                    </div>
                </div>
                <div class="quick-prompt-chip" onclick="askFAQ('Explore FSIT Faculty')">
                    <span class="chip-icon">💻</span>
                    <div class="chip-content">
                        <span class="chip-title">FSIT Faculty</span>
                        <span class="chip-desc">"CSE, Soft. Eng, ESDM"</span>
                    </div>
                </div>
                <div class="quick-prompt-chip" onclick="askFAQ('Admission deadline for Fall 2024')">
                    <span class="chip-icon">📅</span>
                    <div class="chip-content">
                        <span class="chip-title">Apply</span>
                        <span class="chip-desc">"Current Deadlines"</span>
                    </div>
                </div>
                <div class="quick-prompt-chip" onclick="askFAQ('Hostel facilities for boys and girls')">
                    <span class="chip-icon">🏠</span>
                    <div class="chip-content">
                        <span class="chip-title">Hotels</span>
                        <span class="chip-desc">"Residential Halls info"</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Messages Window -->
        <div class="chat-messages" id="chatMessages" style="display: none;"></div>

        <!-- Input Area -->
        <div class="chat-input-section">
            <div class="input-form-container">
                <textarea id="chatInput" placeholder="Ask about DIU Admission, Waivers, or Campus..." rows="1"></textarea>
                <button class="main-send-btn" id="sendBtn">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="22" y1="2" x2="11" y2="13" /><polygon points="22 2 15 22 11 13 2 9 22 2" />
                    </svg>
                </button>
            </div>
            <p class="input-info-text">DIU Buddy AI refers to official DIU data. Verify latest dates on daffodilvarsity.edu.bd.</p>
        </div>
    </main>

    <!-- Right Sidebar: Facts and Quick Stats -->
    <aside class="options-sidebar">
        <div class="section-card">
            <h3>DIU Smart Facts</h3>
            <div style="font-size: 13px; color: #4a5568; line-height: 1.8;">
                <p><strong>🏆 Ranking:</strong> Top in UI GreenMetric</p>
                <p><strong>🌐 Connectivity:</strong> 10Gbps Campus Wi-Fi</p>
                <p><strong>👨‍🔬 Research:</strong> IoT, AR/VR, Health Labs</p>
                <p><strong>🤝 Alumni:</strong> 30,000+ Strong Network</p>
                <p><strong>📍 Location:</strong> Ashulia, Savar (Smart City)</p>
            </div>
        </div>

        <div class="section-card">
            <h3>Visitor Resources</h3>
            <div class="res-links">
                <a href="https://daffodilvarsity.edu.bd" target="_blank" class="res-link">🎓 DIU Official Portal</a>
                <a href="#" class="res-link">📂 Credit Fee Calculator</a>
                <a href="https://annisulhuq.daffodil.university/vt/" target="_blank" class="res-link">🏗️ Virtual Campus Drone Tour</a>
                <a href="#" class="res-link">📞 Admission Helpline</a>
            </div>
        </div>
        
        <div class="become-pro-card" style="background: linear-gradient(135deg, #16a34a, #0f7632);">
            <div class="pro-badge" style="background: rgba(255,255,255,0.2);">JOIN DIU</div>
            <div class="pro-content">
                <h4>Apply Now</h4>
                <p>Join the digital revolution at Daffodil Smart City!</p>
            </div>
            <a href="https://admission.daffodilvarsity.edu.bd/" target="_blank" style="display: block; text-align: center; text-decoration: none; padding: 11px; background: #fff; color: #16a34a; border-radius: 10px; font-weight: 700; margin-top: 15px; box-shadow: 0 4px 10px rgba(0,0,0,0.1);">Start Online Application</a>
        </div>
    </aside>
</div>

<!-- ════════════════════════════════════════════════
     WAIVER CALCULATOR MODAL (Laravel Integrated)
════════════════════════════════════════════════ -->
<div class="calc-overlay" id="calcOverlay">
    <div class="calc-modal">

        <!-- Modal header -->
        <div class="calc-header">
            <div>
                <h2>💰 Tuition Fee & Waiver Calculator</h2>
                <p>Official DIU fee calculator — real-time data from daffodilvarsity.edu.bd</p>
            </div>
            <button class="calc-close" id="closeCalcBtn">✕</button>
        </div>

        <div class="calc-body">
            <!-- LEFT: Form -->
            <div class="calc-form-panel">
                
                <!-- Step 1: Basic Info -->
                <div class="calc-section">
                    <p class="calc-section-title">📋 Basic Information</p>
                    <div class="calc-row">
                        <div class="calc-field">
                            <label>Tuition Category <span class="req">*</span></label>
                            <select class="calc-select" id="caTuitionCategory">
                                <option value="1">Local Tuition Fee (BDT)</option>
                                <option value="2">International Tuition Fee (USD)</option>
                            </select>
                        </div>
                        <div class="calc-field">
                            <label>Program Type <span class="req">*</span></label>
                            <select class="calc-select" id="caProgramType">
                                <option value="1">Undergraduate</option>
                                <option value="2">Postgraduate</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Step 2: Waiver Category -->
                <div class="calc-section">
                    <p class="calc-section-title">🏷️ Select Waiver Category</p>
                    <div class="waiver-grid" id="waiverGrid">
                        <!-- Populated by JS -->
                    </div>
                </div>

                <!-- Step 3: Program -->
                <div class="calc-section">
                    <p class="calc-section-title">🎓 Program & Details</p>
                    <div class="calc-field">
                        <label>Program <span class="req">*</span></label>
                        <select class="calc-select" id="caProgram">
                            <option value="">— Select a program —</option>
                        </select>
                    </div>
                    <div class="prog-pills" id="progPills" style="display:none;">
                        <div class="prog-pill">
                            <div class="pv" id="pillDuration">—</div>
                            <div class="pk">Duration</div>
                        </div>
                        <div class="prog-pill">
                            <div class="pv" id="pillCredits">—</div>
                            <div class="pk">Credits</div>
                        </div>
                        <div class="prog-pill">
                            <div class="pv" id="pillFees">—</div>
                            <div class="pk">Total Fees</div>
                        </div>
                    </div>

                    <!-- Academic Results (only for result-based waiver) -->
                    <div id="academicSection" style="margin-top:14px; display:none;">
                        <p style="font-size:0.8rem;font-weight:700;color:#475569;margin:0 0 10px;">Academic Results</p>
                        <div class="calc-row">
                            <div class="calc-field">
                                <label>SSC Result / Equivalent <span class="req">*</span></label>
                                <input class="calc-input" id="caSSC" type="text" inputmode="decimal" placeholder="e.g. 4.94" autocomplete="off" />
                                <div class="gpa-hint" id="sscHint" style="display:none; margin-top:5px;">
                                    <label style="display:flex; align-items:center; gap:6px; cursor:pointer; font-size:0.75rem; color:#475569; user-select:none;">
                                        <input type="checkbox" id="caSSCGolden" /> ⭐️ Golden GPA
                                    </label>
                                </div>
                            </div>
                            <div class="calc-field">
                                <label>HSC Result / Equivalent <span class="req">*</span></label>
                                <input class="calc-input" id="caHSC" type="text" inputmode="decimal" placeholder="e.g. 5.00" autocomplete="off" />
                                <div class="gpa-hint" id="hscHint" style="display:none; margin-top:5px;">
                                    <label style="display:flex; align-items:center; gap:6px; cursor:pointer; font-size:0.75rem; color:#475569; user-select:none;">
                                        <input type="checkbox" id="caHSCGolden" /> ⭐️ Golden GPA
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Additional Info -->
                    <div style="margin-top:14px;">
                        <p style="font-size:0.8rem;font-weight:700;color:#475569;margin:0 0 10px;">Additional Information</p>
                        <div class="calc-row">
                            <div class="calc-field">
                                <label>Education Board / University <span class="req">*</span></label>
                                <select class="calc-select" id="caBoard">
                                    <option value="1">General Education Board</option>
                                    <option value="2">Madrasah Board</option>
                                    <option value="3">Technical Education Board</option>
                                    <option value="4">University (Diploma)</option>
                                    <option value="5">Foreign Board / University</option>
                                </select>
                            </div>
                            <div class="calc-field">
                                <label>Gender <span class="req">*</span></label>
                                <select class="calc-select" id="caGender">
                                    <option value="1">Male</option>
                                    <option value="2">Female</option>
                                    <option value="3">Others</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Submit -->
                <button class="calc-btn" id="calcSubmitBtn" disabled>
                    Calculate My Fees →
                </button>

            </div>

            <!-- RIGHT: Result -->
            <div class="calc-result-panel" id="calcResultPanel">
                <div class="result-empty" id="resultEmpty">
                    <div class="re-icon">🧮</div>
                    <p>Fill in the form and click <strong>Calculate My Fees</strong> to see your personalized fee breakdown and waiver eligibility.</p>
                </div>
            </div>

        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // ── Config url from Laravel services config ──────────────────────────────────
    window.VISITOR_AI_URL = "{{ rtrim(config('services.visitor_ai.url'), '/') }}";

    document.addEventListener('DOMContentLoaded', function() {
        const chatInput = document.getElementById('chatInput');
        const sendBtn = document.getElementById('sendBtn');
        const chatMessages = document.getElementById('chatMessages');
        const welcomeSection = document.getElementById('welcomeSection');
        const toggleSidebarsBtn = document.getElementById('toggleSidebarsBtn');
        const sidebarToggle = document.getElementById('sidebarToggle');

        // CSRF token for POST requests
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

        // Conversation history for context
        let conversationHistory = [];
        let currentChatId = null;
        let isProcessing = false;
        
        chatInput.addEventListener('input', function() {
            this.style.height = 'auto';
            this.style.height = (this.scrollHeight) + 'px';
        });

        async function sendMessage(text) {
            const rawText = text || chatInput.value.trim();
            if (rawText === '' || isProcessing) return;

            isProcessing = true;
            sendBtn.disabled = true;
            sendBtn.style.opacity = '0.6';

            if (welcomeSection.style.display !== 'none') {
                welcomeSection.style.display = 'none';
                chatMessages.style.display = 'flex';
            }

            addMessage(rawText, 'user');
            if(!text) chatInput.value = '';
            chatInput.style.height = 'auto';

            // Show typing indicator
            showTyping();

            try {
                const response = await fetch('/api/buddy-visitor', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({
                        chat_id: currentChatId,
                        message: rawText,
                        history: conversationHistory.slice(-16),
                    }),
                });

                hideTyping();

                if (!response.ok) {
                    const errData = await response.json().catch(() => ({}));
                    const fallback = errData.response || "I'm having trouble connecting right now. For immediate help, visit daffodilvarsity.edu.bd 🔄";
                    addMessage(fallback, 'bot');
                    conversationHistory.push({ role: 'user', content: rawText });
                    conversationHistory.push({ role: 'assistant', content: fallback });
                } else {
                    const data = await response.json();
                    if (data.chat_id) {
                        currentChatId = data.chat_id;
                    }
                    const aiResponse = data.response || "I couldn't generate a response. Please try again.";
                    addMessage(aiResponse, 'bot', data.sources || []);
                    conversationHistory.push({ role: 'user', content: rawText });
                    conversationHistory.push({ role: 'assistant', content: aiResponse });
                }
            } catch (error) {
                hideTyping();
                console.error('Visitor AI Error:', error);
                addMessage("Something went wrong while connecting. Please check your connection and try again, or visit daffodilvarsity.edu.bd directly. 🔄", 'bot');
            }

            isProcessing = false;
            sendBtn.disabled = false;
            sendBtn.style.opacity = '1';
        }

        window.askFAQ = function(question) {
            sendMessage(question);
        };

        // Handle loading chat history
        document.querySelectorAll('.chat-history-item').forEach(item => {
            item.addEventListener('click', async (e) => {
                e.preventDefault();
                const id = item.dataset.id;
                if (!id) return;

                document.querySelectorAll('.chat-history-item').forEach(i => i.style.background = '#fff');
                item.style.background = '#f1f5f9';

                try {
                    const res = await fetch(`/api/ai-chat/${id}`);
                    const chatData = await res.json();
                    
                    currentChatId = chatData.id;
                    conversationHistory = chatData.history || [];
                    
                    // Clear UI
                    chatMessages.innerHTML = '';
                    welcomeSection.style.display = 'none';
                    chatMessages.style.display = 'flex';
                    
                    // Render history
                    conversationHistory.forEach(msg => {
                        addMessage(msg.content, msg.role === 'assistant' ? 'bot' : 'user');
                    });
                } catch (err) {
                    console.error("Failed to load chat", err);
                }
            });
        });

        // Search functionality
        const searchVisitorChats = document.getElementById('searchVisitorChats');
        if (searchVisitorChats) {
            searchVisitorChats.addEventListener('input', (e) => {
                const query = e.target.value.toLowerCase().trim();
                
                // Filter FAQs
                document.querySelectorAll('.faq-item:not(.chat-history-item)').forEach(item => {
                    const text = item.querySelector('span').textContent.toLowerCase();
                    if (text.includes(query)) {
                        item.style.display = '';
                    } else {
                        item.style.display = 'none';
                    }
                });

                // Filter History
                document.querySelectorAll('.chat-history-item').forEach(item => {
                    const text = item.querySelector('span').textContent.toLowerCase().trim();
                    if (text.startsWith(query)) {
                        item.style.display = 'flex';
                    } else {
                        item.style.display = 'none';
                    }
                });
            });
        }

        sendBtn.addEventListener('click', () => sendMessage());
        chatInput.addEventListener('keypress', (e) => {
            if (e.key === 'Enter' && !e.shiftKey) {
                e.preventDefault();
                sendMessage();
            }
        });

        /**
         * Simple Markdown-to-HTML renderer for AI responses.
         */
        /**
         * Simple Markdown-to-HTML renderer for AI responses.
         */
        function renderMarkdown(text) {
            if (!text) return "";

            // Extract bare URLs first to linkify them with clean hostnames
            const urlPlaceholders = [];
            const urlRegex = /https?:\/\/[^\s<>"')]+/g;
            const textWithPlaceholders = text.replace(urlRegex, (url) => {
                try {
                    const hostname = new URL(url).hostname.replace(/^www\./, "");
                    const idx = urlPlaceholders.length;
                    urlPlaceholders.push(
                        `<a href="${url}" target="_blank" rel="noopener noreferrer" class="inline-link">${hostname}&nbsp;↗</a>`
                    );
                    return `\x00LINK${idx}\x00`;
                } catch {
                    return url;
                }
            });

            let html = textWithPlaceholders;
            html = html.replace(/```(\w*)\n?([\s\S]*?)```/g, '<pre><code>$2</code></pre>');
            html = html.replace(/`([^`]+)`/g, '<code style="background:#f1f5f9;padding:2px 6px;border-radius:4px;font-size:0.9em;">$1</code>');
            html = html.replace(/\*\*([^*]+)\*\*/g, '<strong>$1</strong>');
            html = html.replace(/(?<!\*)\*([^*]+)\*(?!\*)/g, '<em>$1</em>');
            html = html.replace(/^### (.+)$/gm, '<strong style="font-size:1.05em;display:block;margin:8px 0 4px;">$1</strong>');
            html = html.replace(/^## (.+)$/gm, '<strong style="font-size:1.1em;display:block;margin:10px 0 4px;">$1</strong>');
            html = html.replace(/^\d+\.\s+(.+)$/gm, '<div style="padding-left:16px;margin:2px 0;">• $1</div>');
            html = html.replace(/^[-*]\s+(.+)$/gm, '<div style="padding-left:16px;margin:2px 0;">• $1</div>');
            html = html.replace(/\n/g, '<br>');

            // Restore links
            html = html.replace(/\x00LINK(\d+)\x00/g, (_, idx) => urlPlaceholders[+idx]);

            return html;
        }

        function addMessage(text, sender, sources = []) {
            const row = document.createElement('div');
            row.className = `message-row ${sender}-row`;
            const time = new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
            
            // Clean Verify line from bot message
            let cleanText = text;
            if (sender === 'bot') {
                cleanText = text.replace(/\n*🔗\s*Verify at:\s*https?:\/\/\S+/gi, "").trim();
            }
            
            const renderedText = sender === 'bot' ? renderMarkdown(cleanText) : text.replace(/</g, '&lt;').replace(/>/g, '&gt;');
            
            row.innerHTML = `
                <div class="msg-avatar ${sender}-avatar">${sender === 'bot' ? `<img src="{{ asset('assets/landing/character.png') }}" style="width:100%;height:100%;border-radius:50%;object-fit:cover;">` : '👤'}</div>
                <div class="msg-content-wrap">
                    <span class="msg-sender-name">${sender === 'bot' ? 'DIU Buddy' : 'Future Student'}</span>
                    <div class="msg-bubble">${renderedText}</div>
                    <span class="msg-time">${time}</span>
                </div>
            `;

            // Append verify section inside bubble
            if (sender === 'bot' && sources && sources.length > 0) {
                const seen = new Set();
                const uniqueSources = sources.filter(s => {
                    if (!s.url || seen.has(s.url)) return false;
                    seen.add(s.url);
                    return true;
                });

                if (uniqueSources.length > 0) {
                    const bubble = row.querySelector('.msg-bubble');
                    const srcDiv = document.createElement('div');
                    srcDiv.className = 'sources-section';
                    srcDiv.style.marginTop = '8px';
                    srcDiv.style.borderTop = '1px solid var(--border)';
                    srcDiv.style.paddingTop = '6px';
                    srcDiv.innerHTML = `
                        <div style="font-size: 11px; color: var(--text-muted); display: flex; align-items: center; gap: 4px;">
                            <span>ℹ️ Please visit the official website (https://daffodilvarsity.edu.bd/) to verify manually.</span>
                        </div>
                    `;
                    bubble.appendChild(srcDiv);
                }
            }

            chatMessages.appendChild(row);
            chatMessages.scrollTop = chatMessages.scrollHeight;
        }

        function showTyping() {
            const row = document.createElement('div');
            row.className = 'message-row bot-row typing-row';
            row.id = 'typingIndicator';
            row.innerHTML = `
                <div class="msg-avatar bot-avatar"><img src="{{ asset('assets/landing/character.png') }}" style="width:100%;height:100%;border-radius:50%;object-fit:cover;"></div>
                <div class="msg-content-wrap">
                    <div class="typing-indicator"><div class="typing-dot"></div><div class="typing-dot"></div><div class="typing-dot"></div></div>
                </div>
            `;
            chatMessages.appendChild(row);
            chatMessages.scrollTop = chatMessages.scrollHeight;
        }

        function hideTyping() {
            const indicator = document.getElementById('typingIndicator');
            if (indicator) indicator.remove();
        }

        sidebarToggle.addEventListener('click', () => {
            if (window.innerWidth <= 768) {
                document.body.classList.toggle('show-left-sidebar');
            } else {
                document.body.classList.toggle('left-sidebar-hidden');
            }
        });
        toggleSidebarsBtn.addEventListener('click', () => {
            if (window.innerWidth <= 768) {
                document.body.classList.toggle('show-right-sidebar');
            } else {
                document.body.classList.toggle('right-sidebar-hidden');
            }
        });

        // ════════════════════════════════════════════════
        // WAIVER CALCULATOR JS INTEGRATION
        // ════════════════════════════════════════════════
        const WAIVER_CATEGORIES = [
            { id: 1,  icon: '📊', title: 'Result Based Waiver (SSC & HSC)',     sub: 'Based on SSC & HSC GPA',           hasResult: true  },
            { id: 2,  icon: '👥', title: 'Sibling / Spouse',                     sub: 'For siblings or spouse of student', hasResult: false },
            { id: 7,  icon: '♿', title: 'Physically Challenged',                sub: 'For differently-abled students',   hasResult: false },
            { id: 3,  icon: '👔', title: 'Employee',                             sub: 'For DIU employees & dependents',   hasResult: false },
            { id: 10, icon: '🌿', title: 'Tribal / Ethnic Group',               sub: 'For tribal/ethnic students',       hasResult: false },
            { id: 11, icon: '💍', title: 'Spouse / 1st Blood Relative of Alumni',sub: 'For relatives of DIU alumni',      hasResult: false },
            { id: 6,  icon: '🎓', title: 'Diploma Holders',                     sub: 'For diploma certificate holders',  hasResult: false },
            { id: 13, icon: '🏫', title: 'Daffodil Polytechnic / BSDI / DTI',   sub: 'Affiliated institution students',  hasResult: false },
            { id: 14, icon: '🏢', title: 'Daffodil Int\'l / Eminence College',   sub: 'Affiliated college students',      hasResult: false },
            { id: 15, icon: '📈', title: 'DIPTI Business Management College',    sub: 'BM College students',              hasResult: false },
        ];

        let selectedWaiverCat = null;
        let selectedProgram   = null;
        let programsCache     = {};
        let isCalculating     = false;

        const overlay      = document.getElementById('calcOverlay');
        const openCardBtn  = document.getElementById('openWaiverCalcCard');
        const closeBtn     = document.getElementById('closeCalcBtn');
        const waiverGrid   = document.getElementById('waiverGrid');
        const progSelect   = document.getElementById('caProgram');
        const tuitionCat   = document.getElementById('caTuitionCategory');
        const programType  = document.getElementById('caProgramType');
        const submitBtn    = document.getElementById('calcSubmitBtn');
        const resultPanel  = document.getElementById('calcResultPanel');

        if (openCardBtn) {
            openCardBtn.addEventListener('click', () => {
                overlay.classList.add('open');
                if (!waiverGrid.children.length) initCalc();
            });
        }
        if (closeBtn) {
            closeBtn.addEventListener('click', () => overlay.classList.remove('open'));
        }
        overlay.addEventListener('click', e => {
            if (e.target === overlay) overlay.classList.remove('open');
        });

        function initCalc() {
            renderWaiverCards();
            loadPrograms();
        }

        function renderWaiverCards() {
            waiverGrid.innerHTML = '';
            WAIVER_CATEGORIES.forEach(cat => {
                const card = document.createElement('div');
                card.className = 'waiver-card';
                card.dataset.id = cat.id;
                card.dataset.hasResult = cat.hasResult;
                card.innerHTML = `
                    <div class="wc-check"></div>
                    <div class="wc-icon">${cat.icon}</div>
                    <div class="wc-text">
                        <div class="wc-title">${cat.title}</div>
                        <div class="wc-sub">${cat.sub}</div>
                    </div>`;
                card.addEventListener('click', () => selectWaiverCat(cat, card));
                waiverGrid.appendChild(card);
            });
        }

        function selectWaiverCat(cat, card) {
            document.querySelectorAll('.waiver-card').forEach(c => c.classList.remove('selected'));
            card.classList.add('selected');
            selectedWaiverCat = cat;
            
            const acSec = document.getElementById('academicSection');
            if (acSec) {
                acSec.style.display = cat.hasResult ? 'block' : 'none';
            }
            validateForm();
        }

        async function loadPrograms() {
            const tCat  = tuitionCat.value;
            const pType = programType.value;
            const key   = `${tCat}_${pType}`;
            progSelect.disabled = true;
            progSelect.innerHTML = '<option>Loading programs…</option>';

            if (programsCache[key]) {
                populateProgramDropdown(programsCache[key]);
                return;
            }
            try {
                const resp = await fetch(`${window.VISITOR_AI_URL}/api/calculator/programs?tuition_category_id=${tCat}&program_type_id=${pType}`);
                const data = await resp.json();
                programsCache[key] = data.programs || [];
                populateProgramDropdown(programsCache[key]);
            } catch(e) {
                console.error("Programs load failed:", e);
                progSelect.innerHTML = '<option>Error loading programs</option>';
            }
        }

        function populateProgramDropdown(programs) {
            progSelect.disabled = false;
            progSelect.innerHTML = '<option value="">— Select a program —</option>';
            programs.forEach(p => {
                const opt = document.createElement('option');
                opt.value = p.id;
                opt.text  = p.name;
                opt.dataset.duration = p.duration;
                opt.dataset.credit   = p.credit;
                opt.dataset.fees     = p.total_fees;
                progSelect.appendChild(opt);
            });
            selectedProgram = null;
            document.getElementById('progPills').style.display = 'none';
            validateForm();
        }

        progSelect.addEventListener('change', () => {
            const opt = progSelect.selectedOptions[0];
            const pills = document.getElementById('progPills');
            if (opt && opt.value) {
                selectedProgram = {
                    id: opt.value,
                    name: opt.text,
                    duration: opt.dataset.duration,
                    credit: opt.dataset.credit,
                    fees: opt.dataset.fees
                };
                document.getElementById('pillDuration').textContent = opt.dataset.duration || '—';
                document.getElementById('pillCredits').textContent  = opt.dataset.credit   || '—';
                document.getElementById('pillFees').textContent     = (parseInt(tuitionCat.value) === 2 ? '$ ' : '৳ ') + (opt.dataset.fees || '—');
                pills.style.display = 'grid';
            } else {
                selectedProgram = null;
                pills.style.display = 'none';
            }
            validateForm();
        });

        ['caSSC', 'caHSC'].forEach(id => {
            const el = document.getElementById(id);
            if (el) {
                el.addEventListener('input', function() {
                    // Allow only digits and a single decimal point
                    this.value = this.value.replace(/[^0-9.]/g, '');
                    const parts = this.value.split('.');
                    if (parts.length > 2) {
                        this.value = parts[0] + '.' + parts.slice(1).join('');
                    }
                    // Prevent entering a GPA value higher than 5.0
                    const parsed = parseFloat(this.value);
                    if (parsed > 5.0) {
                        this.value = '5.00';
                    }

                    const isFive = parseFloat(this.value) === 5.0;
                    const hint = document.getElementById(id === 'caSSC' ? 'sscHint' : 'hscHint');
                    if (hint) {
                        hint.style.display = isFive ? 'block' : 'none';
                        if (!isFive) {
                            const checkbox = document.getElementById(id === 'caSSC' ? 'caSSCGolden' : 'caHSCGolden');
                            if (checkbox) checkbox.checked = false;
                        }
                    }
                    validateForm();
                });
            }
        });

        tuitionCat.addEventListener('change', () => { selectedProgram = null; loadPrograms(); resetResult(); });
        programType.addEventListener('change', () => { selectedProgram = null; loadPrograms(); resetResult(); });

        function validateForm() {
            const wOk   = !!selectedWaiverCat;
            const pOk   = !!selectedProgram;
            const sscVal = document.getElementById('caSSC').value;
            const hscVal = document.getElementById('caHSC').value;
            const resOk = !selectedWaiverCat?.hasResult || (sscVal && hscVal);
            submitBtn.disabled = !(wOk && pOk && resOk);
        }

        submitBtn.addEventListener('click', async () => {
            if (isCalculating) return;
            isCalculating = true;
            submitBtn.innerHTML = '<div class="loading-spin"></div> Calculating…';
            submitBtn.disabled = true;

            const ssc = document.getElementById('caSSC').value;
            const hsc = document.getElementById('caHSC').value;

            const payload = {
                tuition_category_id: parseInt(tuitionCat.value),
                program_type_id:     parseInt(programType.value),
                waiver_category_id:  selectedWaiverCat.id,
                tuition_id:          parseInt(selectedProgram.id),
                board_id:            parseInt(document.getElementById('caBoard').value),
                gender_id:           parseInt(document.getElementById('caGender').value),
            };
            if (selectedWaiverCat.hasResult) {
                payload.ssc_result = ssc;
                payload.hsc_result = hsc;
                
                const sscGolden = document.getElementById('caSSCGolden');
                const hscGolden = document.getElementById('caHSCGolden');
                if (sscGolden && sscGolden.checked) payload.ssc_golden = 1;
                if (hscGolden && hscGolden.checked) payload.hsc_golden = 1;
            }

            try {
                const resp = await fetch(`${window.VISITOR_AI_URL}/api/calculator/calculate`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(payload),
                });
                const data = await resp.json();
                renderResult(data);
            } catch(e) {
                renderError('Network error. Please try again.');
            } finally {
                isCalculating = false;
                submitBtn.innerHTML = 'Calculate My Fees →';
                submitBtn.disabled = false;
            }
        });

        function fmt(n) {
            if (!n && n !== 0) return '—';
            return '৳ ' + Number(n).toLocaleString('en-BD', { maximumFractionDigits: 2 });
        }
        function fmtUSD(n) {
            if (!n && n !== 0) return '—';
            return '$ ' + Number(n).toLocaleString('en-US', { maximumFractionDigits: 2 });
        }

        function renderResult(data) {
            if (data.success && data.data) {
                const d   = data.data;
                const f   = d.fees;
                const isInt = parseInt(tuitionCat.value) === 2;
                const fmtFn = isInt ? fmtUSD : fmt;

                resultPanel.innerHTML = `
                    <div class="result-congrats">
                        <div class="rc-label">🎉 Congratulations!</div>
                        <div style="font-size:0.82rem;opacity:0.9;margin-bottom:8px;">
                            You are eligible for <strong>${d.waiver_rate}%</strong> tuition fee waiver under<br>
                            <em>${d.category_name}</em> quota.
                        </div>
                        <div class="rc-percent">${d.waiver_rate}%</div>
                        <div class="rc-name">${d.category_name}</div>
                    </div>

                    <div class="result-card">
                        <div class="result-card-head">🎓 Program</div>
                        <div class="result-card-body">
                            <div class="result-row"><span class="rk">${d.program.name}</span></div>
                            <div class="result-row">
                                <span class="rk">Duration</span>
                                <span class="rv">${d.program.duration}</span>
                            </div>
                            <div class="result-row">
                                <span class="rk">Total Credit Hour</span>
                                <span class="rv">${d.program.credit_hours}</span>
                            </div>
                        </div>
                    </div>

                    <div class="result-card">
                        <div class="result-card-head">💳 Fee Summary</div>
                        <div class="result-card-body">
                            <div class="result-row">
                                <span class="rk">Total Cost</span>
                                <span class="rv">${fmtFn(f.total_cost)}</span>
                            </div>
                            <div class="result-row">
                                <span class="rk">Tuition Fees</span>
                                <span class="rv">${fmtFn(f.tuition_fees)}</span>
                            </div>
                            <div class="result-row">
                                <span class="rk">Other Fees</span>
                                <span class="rv">${fmtFn(f.other_fees)}</span>
                            </div>
                            <div class="result-row">
                                <span class="rk">Waiver Amount (${d.waiver_rate}%)</span>
                                <span class="rv" style="color:#16a34a;">- ${fmtFn(f.waiver_amount)}</span>
                            </div>
                        </div>
                    </div>

                    <div class="pay-box admission">
                        <div class="pb-label">Payable During Admission</div>
                        <div class="pb-amount">${fmtFn(f.admission_fees)}</div>
                        <div class="pb-sub">(From First Semester Fee)</div>
                    </div>

                    <div class="pay-box final">
                        <div class="pb-label">After Waiver — Total Cost Will Be</div>
                        <div class="pb-amount">${fmtFn(f.final_cost_after_waiver)}</div>
                    </div>

                    ${d.additional_message ? `<div style="font-size:0.78rem;color:#64748b;text-align:center;padding:4px 8px;">${d.additional_message}</div>` : ''}

                    <button class="reset-btn" id="resetCalcBtn">🔄 Reset</button>
                `;
                document.getElementById('resetCalcBtn').addEventListener('click', resetResult);
            } else {
                const msg = data.data?.message || data.message || 'No waiver available for these inputs.';
                renderError(msg.replace(/<[^>]+>/g, ''));
            }
        }

        function renderError(msg) {
            resultPanel.innerHTML = `
                <div class="result-error">
                    <div style="font-size:2rem;margin-bottom:8px;">😕</div>
                    <strong>No Result</strong><br>${msg}
                </div>
                <button class="reset-btn" id="resetCalcBtn" style="margin-top:8px;">🔄 Try Again</button>
            `;
            document.getElementById('resetCalcBtn').addEventListener('click', resetResult);
        }

        function resetResult() {
            resultPanel.innerHTML = `
                <div class="result-empty" id="resultEmpty">
                    <div class="re-icon">🧮</div>
                    <p>Fill in the form and click <strong>Calculate My Fees</strong> to see your personalized fee breakdown and waiver eligibility.</p>
                </div>`;
        }
    });
</script>
@endpush
