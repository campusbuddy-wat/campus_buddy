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
            overflow: hidden;
            background: #ffffff !important;
        }

        /* Hide unwanted topbar elements for visitors */
        .topbar .desktop-nav, 
        .topbar .top-right-section {
            display: none !important;
        }

        .main {
            padding-bottom: 0 !important;
        }

        /* Normal Mode Layout */
        .layout {
            height: 100vh;
            overflow: hidden;
            display: flex;
            flex-direction: column;
            padding-top: 100px !important;
            transition: padding-top 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .buddy-chat-wrapper {
            height: calc(100vh - 100px) !important;
            margin: 0;
            padding: 0;
            overflow: hidden;
            display: flex;
            transition: height 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            background: #f8fafc;
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
@endsection

@push('scripts')
<script>
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
                    addMessage(aiResponse, 'bot');
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
        function renderMarkdown(text) {
            let html = text;
            html = html.replace(/```(\w*)\n?([\s\S]*?)```/g, '<pre><code>$2</code></pre>');
            html = html.replace(/`([^`]+)`/g, '<code style="background:#f1f5f9;padding:2px 6px;border-radius:4px;font-size:0.9em;">$1</code>');
            html = html.replace(/\*\*([^*]+)\*\*/g, '<strong>$1</strong>');
            html = html.replace(/(?<!\*)\*([^*]+)\*(?!\*)/g, '<em>$1</em>');
            html = html.replace(/^### (.+)$/gm, '<strong style="font-size:1.05em;display:block;margin:8px 0 4px;">$1</strong>');
            html = html.replace(/^## (.+)$/gm, '<strong style="font-size:1.1em;display:block;margin:10px 0 4px;">$1</strong>');
            html = html.replace(/^\d+\.\s+(.+)$/gm, '<div style="padding-left:16px;margin:2px 0;">• $1</div>');
            html = html.replace(/^[-*]\s+(.+)$/gm, '<div style="padding-left:16px;margin:2px 0;">• $1</div>');
            html = html.replace(/\n/g, '<br>');
            return html;
        }

        function addMessage(text, sender) {
            const row = document.createElement('div');
            row.className = `message-row ${sender}-row`;
            const time = new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
            const renderedText = sender === 'bot' ? renderMarkdown(text) : text.replace(/</g, '&lt;').replace(/>/g, '&gt;');
            
            row.innerHTML = `
                <div class="msg-avatar ${sender}-avatar">${sender === 'bot' ? `<img src="{{ asset('assets/landing/character.png') }}" style="width:100%;height:100%;border-radius:50%;object-fit:cover;">` : '👤'}</div>
                <div class="msg-content-wrap">
                    <span class="msg-sender-name">${sender === 'bot' ? 'DIU Buddy' : 'Future Student'}</span>
                    <div class="msg-bubble">${renderedText}</div>
                    <span class="msg-time">${time}</span>
                </div>
            `;
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
    });
</script>
@endpush
