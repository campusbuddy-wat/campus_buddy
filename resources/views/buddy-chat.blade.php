@extends('layouts.app')

@section('title', 'Campus Buddy | Buddy Chat')

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

        .main {
            padding-bottom: 0 !important;
        }

        /* Normal Mode Layout */
        .layout {
            height: 100vh;
            overflow: hidden;
            display: flex;
            flex-direction: column;
            padding-top: 100px !important; /* Match topbar height */
            transition: padding-top 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .buddy-chat-wrapper {
            height: calc(100vh - 100px) !important;
            margin: 0;
            padding: 0;
            overflow: hidden;
            display: flex;
            transition: height 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        /* Topbar toggle states */
        body.topbar-hidden .topbar {
            transform: translateY(-100%);
            opacity: 0;
            pointer-events: none;
        }
        body.topbar-hidden .layout {
            padding-top: 0 !important;
        }
        body.topbar-hidden .buddy-chat-wrapper {
            height: 100vh !important;
        }

        /* Sidebar toggle states */
        body.left-sidebar-hidden .chat-sidebar {
            display: none !important;
        }
        body.right-sidebar-hidden .options-sidebar {
            display: none !important;
        }

        /* Mobile specific sidebars logic */
        @media (max-width: 768px) {
            .chat-sidebar, .options-sidebar {
                position: fixed;
                top: 60px; /* Below topbar */
                bottom: 0;
                z-index: 2100;
                width: 280px !important;
                display: none !important; /* Hide by default on mobile */
                box-shadow: 20px 0 50px rgba(0,0,0,0.1);
            }

            .chat-sidebar { left: 0; }
            .options-sidebar { right: 0; }

            /* Show sidebars when explicitly toggled on mobile */
            body.show-left-sidebar .chat-sidebar {
                display: flex !important;
                animation: slideRight 0.3s ease;
            }

            body.show-right-sidebar .options-sidebar {
                display: flex !important;
                animation: slideLeft 0.3s ease;
            }

            /* Overlay for mobile */
            .sidebar-overlay {
                position: fixed;
                top: 0;
                left: 0;
                right: 0;
                bottom: 0;
                background: rgba(0,0,0,0.5);
                backdrop-filter: blur(4px);
                z-index: 2050;
                display: none;
            }

            body.show-left-sidebar .sidebar-overlay,
            body.show-right-sidebar .sidebar-overlay {
                display: block;
            }

            @keyframes slideRight {
                from { transform: translateX(-100%); }
                to { transform: translateX(0); }
            }
            @keyframes slideLeft {
                from { transform: translateX(100%); }
                to { transform: translateX(0); }
            }

            /* Adjust Welcome prompt for mobile */
            .quick-prompts {
                display: grid !important;
                grid-template-columns: 1fr 1fr !important;
                gap: 12px !important;
            }
        }

        /* Master Topbar styles */
        .topbar {
            transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1), opacity 0.3s;
            z-index: 1500 !important;
        }

        /* Floating Controls removed - using header toggles */
        .floating-controls { display: none !important; }

        /* Sidebar and Header overrides */
        .buddy-mini-header {
            display: none !important;
        }
        
        /* Show chat header for controls */
        .chat-top-header {
            display: flex !important;
        }

        /* Responsive Topbar Height Adjustments */
        @media (max-width: 1600px) {
            .layout { padding-top: 90px !important; }
            .buddy-chat-wrapper { height: calc(100vh - 90px) !important; }
            body.topbar-hidden .layout { padding-top: 0 !important; }
            body.topbar-hidden .buddy-chat-wrapper { height: 100vh !important; }
        }
        @media (max-width: 1200px) {
            .layout { padding-top: 65px !important; }
            .buddy-chat-wrapper { height: calc(100vh - 65px) !important; }
            body.topbar-hidden .layout { padding-top: 0 !important; }
            body.topbar-hidden .buddy-chat-wrapper { height: 100vh !important; }
        }
        @media (max-width: 768px) {
            .layout { padding-top: 60px !important; }
            .buddy-chat-wrapper { height: calc(100vh - 60px) !important; }
            body.topbar-hidden .layout { padding-top: 0 !important; }
            body.topbar-hidden .buddy-chat-wrapper { height: 100vh !important; }
        }
    </style>
@endpush

@section('content')
  <div class="sidebar-overlay" id="sidebarOverlay"></div>

  <div class="buddy-chat-wrapper">
    <!-- ================= SIDEBAR: Chat History ================= -->
    <aside class="chat-sidebar">
      <div class="sidebar-header">
        <span class="sidebar-title">Chats</span>
        <button class="new-chat-btn" id="newChatBtn" title="New Chat">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"
            stroke-linecap="round" stroke-linejoin="round">
            <line x1="12" y1="5" x2="12" y2="19" />
            <line x1="5" y1="12" x2="19" y2="12" />
          </svg>
        </button>
      </div>

      <div class="sidebar-search">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
          stroke-linejoin="round">
          <circle cx="11" cy="11" r="8" />
          <line x1="21" y1="21" x2="16.65" y2="16.65" />
        </svg>
        <input type="text" id="searchChats" placeholder="Search conversations…">
      </div>

      <div id="chatHistoryList" style="overflow-y: auto; flex: 1;">
        @if(isset($chats) && $chats->count() > 0)
            @foreach($chats as $chat)
                <a href="#" class="chat-history-item" data-id="{{ $chat->id }}">
                  <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z" />
                  </svg>
                  <span class="history-text">{{ $chat->title }}</span>
                  <span class="history-time">{{ $chat->created_at->shortAbsoluteDiffForHumans() }}</span>
                </a>
            @endforeach
        @else
            <span class="sidebar-label" style="text-align:center; display:block; margin-top:20px;">No recent chats</span>
        @endif
      </div>
    </aside>

    <!-- ================= MAIN CHAT AREA ================= -->
    <main class="chat-main" id="chatMain">

      <!-- Chat Top Bar -->
      <div class="chat-top-header">
        <div class="chat-top-left">
          <button class="menu-toggle-btn" id="sidebarToggle" title="Toggle Chats">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
              <rect x="3" y="3" width="18" height="18" rx="2" ry="2"/>
              <line x1="9" y1="3" x2="9" y2="21"/>
            </svg>
          </button>
          <div class="buddy-avatar">
            <img src="{{ asset('assets/landing/character.png') }}" alt="Buddy" style="width: 100%; height: 100%; border-radius: 50%; object-fit: cover;">
          </div>
          <div class="chat-bot-info">
            <h2>Buddy AI</h2>
            <div class="chat-bot-status">
              <span></span> Online
            </div>
          </div>
        </div>

        <div class="chat-top-actions">
          <a href="{{ route('profile.settings') }}" class="chat-action-btn" title="Chat Settings">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
              <circle cx="12" cy="12" r="3" />
              <path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z" />
            </svg>
          </a>
          <button class="chat-action-btn" id="toggleTopbarBtn" title="Toggle Topbar Display">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
              <path d="M4 8h16M4 16h16"/>
              <path d="M12 4v16" class="toggle-icon-dash" style="opacity: 0.3;"/>
            </svg>
          </button>
          <button class="chat-action-btn" id="toggleSidebarsBtn" title="Toggle Right Sidebar Display">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
              <rect x="3" y="3" width="18" height="18" rx="2" ry="2"/>
              <line x1="15" y1="3" x2="15" y2="21"/>
            </svg>
          </button>
        </div>
      </div>

      <!-- Welcome Stage (Empty State) -->
      <div class="welcome-section" id="welcomeSection">
        <div class="welcome-avatar">
          <img src="{{ asset('assets/landing/character.png') }}" alt="Buddy">
          <div class="avatar-pulse-ring"></div>
        </div>
        <div class="welcome-text">
          <h1 class="welcome-title">Hi, I'm your <span>Campus Buddy.</span></h1>
          <p class="welcome-subtitle">Ask me anything about your routine, class tasks, or find study materials.
            I'm here to help you excel this semester!</p>
        </div>

        <div class="quick-prompts">
          <div class="quick-prompt-chip">
            <span class="chip-icon">📅</span>
            <div class="chip-content">
              <span class="chip-title">Routine</span>
              <span class="chip-desc">"What is my next class?"</span>
            </div>
          </div>
          <div class="quick-prompt-chip">
            <span class="chip-icon">📝</span>
            <div class="chip-content">
              <span class="chip-title">Tasks</span>
              <span class="chip-desc">"Show me upcoming quizzes."</span>
            </div>
          </div>
          <div class="quick-prompt-chip">
            <span class="chip-icon">📚</span>
            <div class="chip-content">
              <span class="chip-title">Materials</span>
              <span class="chip-desc">"Find AI mid-term notes."</span>
            </div>
          </div>
          <div class="quick-prompt-chip">
            <span class="chip-icon">✨</span>
            <div class="chip-content">
              <span class="chip-title">Motivation</span>
              <span class="chip-desc">"Tell me an inspiring quote."</span>
            </div>
          </div>
        </div>
      </div>

      <!-- Messages Window -->
      <div class="chat-messages" id="chatMessages" style="display: none;">
        <!-- Messages will be injected here -->
      </div>

      <!-- Input Area -->
      <div class="chat-input-section">
        <div class="input-form-container">
          <button class="attachment-btn" title="Add Image">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <rect x="3" y="3" width="18" height="18" rx="2" ry="2" />
              <circle cx="8.5" cy="8.5" r="1.5" />
              <polyline points="21 15 16 10 5 21" />
            </svg>
          </button>
          
          <textarea id="chatInput" placeholder="Message Buddy AI..." rows="1"></textarea>
          
          <button class="main-send-btn" id="sendBtn">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
              <line x1="22" y1="2" x2="11" y2="13" />
              <polygon points="22 2 15 22 11 13 2 9 22 2" />
            </svg>
          </button>
        </div>
        <p class="input-info-text">Buddy AI can make mistakes. Check important info.</p>
      </div>

    </main>

    <!-- ================= RIGHT SIDEBAR: Options ================= -->
    <aside class="options-sidebar">
      <div class="section-card">
        <h3>Section Info</h3>
        <p><strong>Major:</strong> {{ Auth::user()->major ?? 'DS' }}</p>
        <p><strong>Batch:</strong> {{ Auth::user()->batch ?? '61' }}</p>
        <p><strong>Section:</strong> {{ Auth::user()->section ?? 'D' }}</p>
      </div>

      <div class="section-card">
        <h3>Context Mode</h3>
        <div class="toggle-group">
          <span>Smart Context</span>
          <div class="switch active"></div>
        </div>
        <p class="option-help">Buddy will prioritize results for your specific section.</p>
      </div>

      <div class="section-card resources">
        <h3>Quick Resources</h3>
        <a href="{{ route('routine') }}" class="res-link">📅 View Full Routine</a>
        <a href="{{ route('classtask') }}" class="res-link">📝 Check Deadlines</a>
        <a href="{{ route('notes') }}" class="res-link">📚 Browse All PDF</a>
      </div>

      <div class="become-pro-card">
        <div class="pro-badge">PRO</div>
        <div class="pro-content">
          <h4>Become Pro</h4>
          <p>Get accurate answer with premium study resources.</p>
        </div>
        <button class="pro-upgrade-btn">Upgrade Now</button>
      </div>
    </aside>

  </div>
@endsection

@push('scripts')
  <script>
    document.addEventListener('DOMContentLoaded', function () {
      const chatInput = document.getElementById('chatInput');
      const sendBtn = document.getElementById('sendBtn');
      const chatMessages = document.getElementById('chatMessages');
      const welcomeSection = document.getElementById('welcomeSection');
      const sidebarToggle = document.getElementById('sidebarToggle');
      const charSidebar = document.querySelector('.chat-sidebar');
      const optionsSidebar = document.querySelector('.options-sidebar');

      // CSRF token for POST requests
      const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

      // Conversation history for context
      let conversationHistory = [];
      let currentChatId = null;
      let isProcessing = false;

      // Auto-resize textarea
      chatInput.addEventListener('input', function () {
        this.style.height = 'auto';
        this.style.height = (this.scrollHeight) + 'px';
        if (this.scrollHeight > 150) {
          this.style.overflowY = 'scroll';
        } else {
          this.style.overflowY = 'hidden';
        }
      });

      // Handle sending messages
      async function sendMessage() {
        const text = chatInput.value.trim();
        if (text === '' || isProcessing) return;

        isProcessing = true;
        sendBtn.disabled = true;
        sendBtn.style.opacity = '0.6';

        // Hide welcome if first message
        if (welcomeSection.style.display !== 'none') {
          welcomeSection.style.display = 'none';
          chatMessages.style.display = 'flex';
        }

        // Add User Message to UI
        addMessage(text, 'user');
        chatInput.value = '';
        chatInput.style.height = 'auto';

        // Show typing indicator
        showTyping();

        try {
          const response = await fetch('/api/buddy-chat', {
            method: 'POST',
            headers: {
              'Content-Type': 'application/json',
              'X-CSRF-TOKEN': csrfToken,
              'Accept': 'application/json',
            },
            body: JSON.stringify({
              chat_id: currentChatId,
              message: text,
              history: conversationHistory.slice(-16), // Send last 16 messages for context
            }),
          });

          hideTyping();

          if (!response.ok) {
            const errData = await response.json().catch(() => ({}));
            const fallback = errData.response || "I'm having trouble connecting right now. Please try again. 🔄";
            addMessage(fallback, 'bot');
            // Add to history
            conversationHistory.push({ role: 'user', content: text });
            conversationHistory.push({ role: 'assistant', content: fallback });
          } else {
            const data = await response.json();
            if (data.chat_id) {
                currentChatId = data.chat_id;
            }
            const aiResponse = data.response || "I couldn't generate a response. Please try again.";
            addMessage(aiResponse, 'bot');
            // Add to conversation history
            conversationHistory.push({ role: 'user', content: text });
            conversationHistory.push({ role: 'assistant', content: aiResponse });
          }
        } catch (error) {
          hideTyping();
          console.error('Buddy AI Error:', error);
          addMessage("Something went wrong while reaching Buddy AI. Please check your connection and try again. 🔄", 'bot');
        }

        isProcessing = false;
        sendBtn.disabled = false;
        sendBtn.style.opacity = '1';
      }

      sendBtn.addEventListener('click', sendMessage);
      chatInput.addEventListener('keypress', (e) => {
        if (e.key === 'Enter' && !e.shiftKey) {
          e.preventDefault();
          sendMessage();
        }
      });

      // Handle loading chat history
      document.querySelectorAll('.chat-history-item').forEach(item => {
          item.addEventListener('click', async (e) => {
              e.preventDefault();
              const id = item.dataset.id;
              if (!id) return;
              
              document.querySelectorAll('.chat-history-item').forEach(i => i.classList.remove('active'));
              item.classList.add('active');

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
      
      // New Chat button
      document.getElementById('newChatBtn').addEventListener('click', () => {
          currentChatId = null;
          conversationHistory = [];
          chatMessages.innerHTML = '';
          welcomeSection.style.display = 'flex';
          chatMessages.style.display = 'none';
          document.querySelectorAll('.chat-history-item').forEach(i => i.classList.remove('active'));
      });

      // Search functionality
      const searchChats = document.getElementById('searchChats');
      searchChats.addEventListener('input', (e) => {
          const query = e.target.value.toLowerCase().trim();
          document.querySelectorAll('.chat-history-item').forEach(item => {
              const text = item.querySelector('.history-text').textContent.toLowerCase().trim();
              if (text.startsWith(query)) {
                  item.style.display = 'flex';
              } else {
                  item.style.display = 'none';
              }
          });
      });

      // ================= AUTOMATIC SEND FROM URL =================
      const urlParams = new URLSearchParams(window.location.search);
      const urlMessage = urlParams.get('message');
      
      if (urlMessage) {
          chatInput.value = urlMessage;
          setTimeout(() => {
              sendMessage();
          }, 300);
      }

      /**
       * Simple Markdown-to-HTML renderer for AI responses.
       * Handles: bold, italic, bullet points, numbered lists, code blocks, line breaks.
       */
      function renderMarkdown(text) {
        let html = text;
        // Code blocks (```...```)
        html = html.replace(/```(\w*)\n?([\s\S]*?)```/g, '<pre><code>$2</code></pre>');
        // Inline code (`...`)
        html = html.replace(/`([^`]+)`/g, '<code style="background:#f1f5f9;padding:2px 6px;border-radius:4px;font-size:0.9em;">$1</code>');
        // Bold (**...**)
        html = html.replace(/\*\*([^*]+)\*\*/g, '<strong>$1</strong>');
        // Italic (*...*)
        html = html.replace(/(?<!\*)\*([^*]+)\*(?!\*)/g, '<em>$1</em>');
        // Headers (## ...)
        html = html.replace(/^### (.+)$/gm, '<strong style="font-size:1.05em;display:block;margin:8px 0 4px;">$1</strong>');
        html = html.replace(/^## (.+)$/gm, '<strong style="font-size:1.1em;display:block;margin:10px 0 4px;">$1</strong>');
        // Numbered lists
        html = html.replace(/^\d+\.\s+(.+)$/gm, '<div style="padding-left:16px;margin:2px 0;">• $1</div>');
        // Bullet points (- or *)
        html = html.replace(/^[-*]\s+(.+)$/gm, '<div style="padding-left:16px;margin:2px 0;">• $1</div>');
        // Line breaks
        html = html.replace(/\n/g, '<br>');
        return html;
      }

      function addMessage(text, sender) {
        const row = document.createElement('div');
        row.className = `message-row ${sender}-row`;

        const time = new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
        const renderedText = sender === 'bot' ? renderMarkdown(text) : text.replace(/</g, '&lt;').replace(/>/g, '&gt;');

        row.innerHTML = `
                <div class="msg-avatar ${sender}-avatar">${sender === 'bot' ? `<img src="{{ asset('assets/landing/character.png') }}" style="width: 100%; height: 100%; border-radius: 50%; object-fit: cover;">` : '👤'}</div>
                <div class="msg-content-wrap">
                    <span class="msg-sender-name">${sender === 'bot' ? 'Buddy' : 'You'}</span>
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
                <div class="msg-avatar bot-avatar"><img src="{{ asset('assets/landing/character.png') }}" style="width: 100%; height: 100%; border-radius: 50%; object-fit: cover;"></div>
                <div class="msg-content-wrap">
                    <div class="typing-indicator">
                        <div class="typing-dot"></div>
                        <div class="typing-dot"></div>
                        <div class="typing-dot"></div>
                    </div>
                </div>
            `;
        chatMessages.appendChild(row);
        chatMessages.scrollTop = chatMessages.scrollHeight;
      }

      function hideTyping() {
        const indicator = document.getElementById('typingIndicator');
        if (indicator) indicator.remove();
      }

      // Left Sidebar Toggle
      sidebarToggle.addEventListener('click', () => {
        if (window.innerWidth <= 768) {
          document.body.classList.toggle('show-left-sidebar');
        } else {
          document.body.classList.toggle('left-sidebar-hidden');
        }
      });

      // Granular UI Toggle logic
      const toggleTopbarBtn = document.getElementById('toggleTopbarBtn');
      const toggleSidebarsBtn = document.getElementById('toggleSidebarsBtn');
      const sidebarOverlay = document.getElementById('sidebarOverlay');

      toggleTopbarBtn.addEventListener('click', () => {
        document.body.classList.toggle('topbar-hidden');
      });

      // Right Sidebar Toggle
      toggleSidebarsBtn.addEventListener('click', () => {
        if (window.innerWidth <= 768) {
          document.body.classList.toggle('show-right-sidebar');
        } else {
          document.body.classList.toggle('right-sidebar-hidden');
        }
      });

      if (sidebarOverlay) {
        sidebarOverlay.addEventListener('click', () => {
          document.body.classList.remove('show-left-sidebar');
          document.body.classList.remove('show-right-sidebar');
        });
      }

      // Toggle functionality for the Smart Context switch
      const contextSwitch = document.querySelector('.switch');
      if (contextSwitch) {
        contextSwitch.addEventListener('click', function() {
          this.classList.toggle('active');
        });
      }

      // Quick prompt click
      document.querySelectorAll('.quick-prompt-chip, .suggestion-pill').forEach(chip => {
        chip.addEventListener('click', function () {
          const prompt = this.querySelector('.chip-desc')?.innerText.replace(/"/g, '') || this.innerText;
          chatInput.value = prompt;
          sendMessage();
        });
      });
    });
  </script>
@endpush