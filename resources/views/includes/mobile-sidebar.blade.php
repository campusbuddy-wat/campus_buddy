{{-- Campus Buddy Mobile Sidebar Component --}}
@php
$currentRoute = Route::currentRouteName() ?? '';
@endphp

<!-- Sidebar Overlay -->
<div class="mobile-sidebar-overlay" id="mobileSidebarOverlay"></div>

<!-- Sidebar -->
<aside class="mobile-sidebar" id="mobileSidebar">

    <!-- Brand / Header -->
    <div class="sidebar-header">
        <a href="{{ route('dashboard') }}" class="sidebar-brand">
            <img src="{{ asset('images/eventImage/logo.png') }}" alt="Campus Buddy Logo">
            <span>Campus Buddy</span>
        </a>
        <button class="sidebar-close" id="sidebarCloseBtn">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                stroke-linecap="round" stroke-linejoin="round">
                <line x1="18" y1="6" x2="6" y2="18"></line>
                <line x1="6" y1="6" x2="18" y2="18"></line>
            </svg>
        </button>
    </div>

    <!-- Search -->
    <div class="sidebar-search">
        <div class="search-input-wrapper">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                stroke-linejoin="round">
                <circle cx="11" cy="11" r="8"></circle>
                <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
            </svg>
            <input type="text" placeholder="Search">
            <span style="color: #a0aec0; font-size: 11px;">Ctrl+K</span>
        </div>
    </div>

    <!-- GENERAL Section -->
    <div class="sidebar-section">
        <div class="sidebar-title">GENERAL</div>
        <ul class="sidebar-nav">
            <li>
                <a href="{{ route('dashboard') }}" class="{{ $currentRoute === 'dashboard' ? 'active' : '' }}">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                        stroke-linejoin="round">
                        <rect x="3" y="3" width="7" height="7"></rect>
                        <rect x="14" y="3" width="7" height="7"></rect>
                        <rect x="14" y="14" width="7" height="7"></rect>
                        <rect x="3" y="14" width="7" height="7"></rect>
                    </svg>
                    Dashboard
                </a>
            </li>
            <li>
                <a href="{{ route('routine') }}" class="{{ $currentRoute === 'routine' ? 'active' : '' }}">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                        stroke-linejoin="round">
                        <circle cx="12" cy="12" r="10"></circle>
                        <polyline points="12 6 12 12 16 14"></polyline>
                    </svg>
                    Routine
                </a>
            </li>
            <li>
                <a href="{{ route('classtask') }}" class="{{ $currentRoute === 'classtask' ? 'active' : '' }}">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                        stroke-linejoin="round">
                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                        <polyline points="14 2 14 8 20 8"></polyline>
                        <line x1="16" y1="13" x2="8" y2="13"></line>
                        <line x1="16" y1="17" x2="8" y2="17"></line>
                        <polyline points="10 9 9 9 8 9"></polyline>
                    </svg>
                    Class Tasks <span class="sidebar-badge">05</span>
                </a>
            </li>
            <li>
                <a href="{{ route('clubs') }}" class="{{ $currentRoute === 'clubs' ? 'active' : '' }}">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                        stroke-linejoin="round">
                        <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                        <circle cx="9" cy="7" r="4"></circle>
                        <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                        <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                    </svg>
                    Clubs
                </a>
            </li>
            <li>
                <a href="{{ route('notes') }}" class="{{ $currentRoute === 'notes' ? 'active' : '' }}">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                        stroke-linejoin="round">
                        <path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"></path>
                    </svg>
                    Pdf & Notes <span class="sidebar-badge new">New</span>
                </a>
            </li>
        </ul>
    </div>

    <!-- MY SPACES Section -->
    <div class="sidebar-section">
        <div class="sidebar-title">MY SPACES</div>
        <ul class="sidebar-nav">
            <li>
                <a href="{{ route('community') }}" class="{{ $currentRoute === 'community' ? 'active' : '' }}">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                        stroke-linejoin="round">
                        <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path>
                    </svg>
                    Community
                </a>
            </li>
            <li>
                <a href="{{ route('alumni') }}" class="{{ $currentRoute === 'alumni' ? 'active' : '' }}">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                        stroke-linejoin="round">
                        <circle cx="12" cy="8" r="5"></circle>
                        <path d="M3 21v-2a7 7 0 0 1 14 0v2"></path>
                    </svg>
                    Alumni
                </a>
            </li>
            <li>
                <a href="{{ route('question-bank') }}" class="{{ $currentRoute === 'question-bank' ? 'active' : '' }}">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                        stroke-linejoin="round">
                        <circle cx="12" cy="12" r="10"></circle>
                        <path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"></path>
                        <line x1="12" y1="17" x2="12.01" y2="17"></line>
                    </svg>
                    Question Bank
                </a>
            </li>
            <li>
                <a href="{{ route('buddy-chat') }}" class="{{ $currentRoute === 'buddy-chat' ? 'active' : '' }}">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                        stroke-linejoin="round">
                        <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path>
                        <path d="M21 15V5a2 2 0 0 0-2-2H5a2 2 0 0 0-2 2v10a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2z"></path>
                    </svg>
                    Buddy AI
                </a>
            </li>
        </ul>
    </div>

    <!-- Bottom Actions & Profile -->
    <div class="sidebar-bottom">
        <div class="sidebar-actions">
            <button title="Notifications">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                    stroke-linecap="round" stroke-linejoin="round">
                    <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path>
                    <path d="M13.73 21a2 2 0 0 1-3.46 0"></path>
                </svg>
            </button>
            <button title="More">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                    stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="1"></circle>
                    <circle cx="19" cy="12" r="1"></circle>
                    <circle cx="5" cy="12" r="1"></circle>
                </svg>
            </button>
            <button title="Scan">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                    stroke-linecap="round" stroke-linejoin="round">
                    <path d="M4 7V4h3"></path>
                    <path d="M4 17v3h3"></path>
                    <path d="M20 7V4h-3"></path>
                    <path d="M20 17v3h-3"></path>
                </svg>
            </button>
            <button title="Help">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                    stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="10"></circle>
                    <path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"></path>
                    <line x1="12" y1="17" x2="12.01" y2="17"></line>
                </svg>
            </button>
        </div>

        <div class="sidebar-user" onclick="openModal('profileModal')">
            <div class="sidebar-user-info">
                <div class="sidebar-avatar">
                    {{ substr(Auth::user()->name ?? 'U', 0, 1) }}
                    <div class="status-dot"></div>
                </div>
                <div class="sidebar-name">{{ Auth::user()->name ?? 'User' }}</div>
            </div>
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#666" stroke-width="2"
                stroke-linecap="round" stroke-linejoin="round">
                <circle cx="12" cy="12" r="10"></circle>
                <polyline points="12 16 16 12 12 8"></polyline>
                <line x1="8" y1="12" x2="16" y2="12"></line>
            </svg>
        </div>
    </div>
</aside>

<!-- Include CSS -->
<link rel="stylesheet" href="{{ asset('css/mobile-sidebar.css') }}">

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const hamburgerBtn = document.getElementById('mobileHamburgerBtn');
        const sidebar = document.getElementById('mobileSidebar');
        const overlay = document.getElementById('mobileSidebarOverlay');
        const closeBtn = document.getElementById('sidebarCloseBtn');

        function openSidebar() {
            sidebar.classList.add('active');
            overlay.classList.add('active');
            document.body.style.overflow = 'hidden'; // Prevent background scrolling
        }

        function closeSidebar() {
            sidebar.classList.remove('active');
            overlay.classList.remove('active');
            document.body.style.overflow = '';
        }

        if (hamburgerBtn) {
            hamburgerBtn.addEventListener('click', openSidebar);
        }

        if (closeBtn) {
            closeBtn.addEventListener('click', closeSidebar);
        }

        if (overlay) {
            overlay.addEventListener('click', closeSidebar);
        }

        // Close on escape key
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape' && sidebar.classList.contains('active')) {
                closeSidebar();
            }
        });
    });
</script>