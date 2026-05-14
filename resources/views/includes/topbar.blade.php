{{-- Campus Buddy Topbar Component --}}

<header class="topbar">
  <!-- Mobile Hamburger Menu Button -->
  <button class="mobile-hamburger-btn" id="mobileMenuToggle" aria-label="Open Menu">
    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
      stroke-linecap="round" stroke-linejoin="round">
      <line x1="3" y1="12" x2="21" y2="12"></line>
      <line x1="3" y1="6" x2="21" y2="6"></line>
      <line x1="3" y1="18" x2="21" y2="18"></line>
    </svg>
  </button>

  <!-- Logo (Link to Home or Dashboard) -->
  <a href="{{ Auth::check() ? route('dashboard') : url('/') }}" class="logo">
    <img src="{{ asset('images/eventImage/logo.png') }}" alt="Campus Buddy Logo" class="logo-img">
    <div class="logo-text">
      <span>Campus</span>
      <span>Buddy</span>
    </div>
  </a>

  {{-- 1. MIDDLE SECTION: Desktop Nav (Dashboard) OR Visitor AI (Landing) --}}
  @if(!$isLandingPage && !$isAuthPage)
    @auth
    <!-- Desktop inline nav -->
    <nav class="desktop-nav">
      <a href="{{ route('dashboard') }}" class="{{ $currentRoute === 'dashboard' ? 'active' : '' }}">Home</a>
      <a href="{{ route('routine') }}" class="{{ $currentRoute === 'routine' ? 'active' : '' }}">Routine</a>
      <a href="{{ route('classtask') }}" class="{{ $currentRoute === 'classtask' ? 'active' : '' }}">ClassTask</a>
      <a href="{{ route('clubs') }}" class="{{ $currentRoute === 'clubs' ? 'active' : '' }}">Clubs</a>
      <a href="{{ route('notes') }}" class="{{ $currentRoute === 'notes' ? 'active' : '' }}">Pdf & Notes</a>
      <a href="{{ route('community') }}" class="{{ $currentRoute === 'community' ? 'active' : '' }}">Community</a>
      <a href="{{ route('alumni') }}" class="{{ $currentRoute === 'alumni' ? 'active' : '' }}">Alumni</a>
      <a href="{{ route('question-bank') }}" class="{{ $currentRoute === 'question-bank' ? 'active' : '' }}">Q Bank</a>

      <div class="buddy-nav-item" style="position: relative; display: flex; align-items: center;">
        <img src="{{ asset('assets/landing/character.png') }}" class="peeking-buddy" alt="Buddy">
        <a href="{{ route('buddy-chat') }}" class="{{ $currentRoute === 'buddy-chat' ? 'active' : '' }}">Buddy AI</a>
      </div>
    </nav>
    @endauth
  @elseif($isLandingPage)
    <div class="nav-middle" style="flex: 1; display: flex; justify-content: center; margin: 0 40px;">
      <a href="{{ route('buddy-visitor') }}" class="btn-visitor-central" style="background: rgba(0, 170, 255, 0.08); color: #00aaff; border: 2.2px solid #00aaff; padding: 6px 25px; border-radius: 30px; font-weight: 800; text-decoration: none; font-size: 13px; transition: all 0.3s ease; display: flex; align-items: center; gap: 10px; box-shadow: 0 4px 15px rgba(0, 170, 255, 0.1); white-space: nowrap;">
          <img src="{{ asset('assets/landing/character.png') }}" alt="Buddy" style="width: 24px; height: 24px; object-fit: contain;">
          <span>Not a Student? Ask Buddy AI Everything!</span>
      </a>
    </div>
  @endif

  {{-- 2. RIGHT SECTION: Dashboard Tools OR Landing Auth --}}
  <div class="top-right-section">
    @if(!$isLandingPage && !$isAuthPage)
      @auth
        <!-- Search -->
        <a href="#" class="top-action-btn" aria-label="Search">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#4a5568" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
        </a>

        <!-- Notifications -->
        <div class="notification-container">
          <a href="javascript:void(0)" class="top-action-btn notification-btn" id="notificationBtn" aria-label="Notifications">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#4a5568" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path><path d="M13.73 21a2 2 0 0 1-3.46 0"></path></svg>
            @if($unreadCount > 0) <span class="notification-badge" id="notifBadge">{{ $unreadCount }}</span> @endif
          </a>
          <!-- ... Notification Dropdown Content ... -->
          <div class="notification-dropdown" id="notificationDropdown">
              <div class="notif-header"><h3>Notifications</h3><span class="mark-all" id="markAllRead">Mark all read</span></div>
              <div class="notif-body">
                @forelse($notifications as $notif)
                    @php 
                        $nUrl = 'javascript:void(0)';
                        if($notif->notif_type === 'announcement') $nUrl = route('dashboard').'#announcement-'.$notif->id;
                        elseif($notif->notif_type === 'task') $nUrl = route('classtask').'#task-'.$notif->id;
                        elseif($notif->notif_type === 'material') $nUrl = route('notes').'#material-'.$notif->id;
                        elseif($notif->notif_type === 'alumni') $nUrl = route('alumni');
                    @endphp
                    <a href="{{ $nUrl }}" class="notif-item unread">
                        <div class="notif-info"><p class="notif-text">{{ $notif->notif_label }}: {{ $notif->title ?? 'New Update' }}</p><span class="notif-time">{{ $notif->created_at->diffForHumans() }}</span></div>
                    </a>
                @empty
                    <div class="notif-empty">No new notifications</div>
                @endforelse
              </div>
          </div>
        </div>

        <div class="topbar-divider"></div>

        <!-- Profile -->
        <div class="user-profile-container">
            <div class="user-profile-trigger" id="userProfileIcon">
                <div class="user-avatar-circle">
                    <img src="{{ Auth::user()->profile_image ? asset('storage/'.Auth::user()->profile_image) : asset('images/eventImage/profile.png') }}" alt="Profile" style="width: 100%; height: 100%; object-fit: cover; border-radius: 50%;">
                </div>
                <div class="user-info">
                    <span class="user-name">{{ Auth::user()->name }}</span>
                    <span class="user-role">{{ ucfirst(Auth::user()->role) }}</span>
                </div>
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#4a5568" stroke-width="2"><polyline points="6 9 12 15 18 9"></polyline></svg>
            </div>
            <div class="user-dropdown" id="userDropdown">
                <div class="dropdown-header">
                    <p class="dropdown-name">{{ Auth::user()->name }}</p>
                    <p class="dropdown-email">{{ Auth::user()->student_id }}</p>
                </div>
                <div class="dropdown-divider"></div>
                @if(in_array(Auth::user()->role, ['cr', 'admin']))
                    <a href="{{ route('cr-dashboard') }}" class="dropdown-item">CR Panel</a>
                @endif
                <a href="{{ route('profile.settings') }}" class="dropdown-item">Settings</a>
                <div class="dropdown-divider"></div>
                <form action="{{ route('logout') }}" method="POST">@csrf<button type="submit" class="logout-btn">Log Out</button></form>
            </div>
        </div>
      @endauth
    @elseif($isLandingPage)
      <div class="guest-auth-actions" style="display: flex; align-items: center; gap: 15px;">
          <a href="{{ route('login') }}" style="color: #4a5568; font-weight: 700; text-decoration: none; font-size: 15px; padding: 8px 16px;">Log In</a>
          <a href="{{ route('signup') }}" style="background: #00AAFF; color: white; padding: 8px 18px; border-radius: 30px; font-weight: 800; text-decoration: none; font-size: 14px; box-shadow: 0 4px 15px rgba(0, 170, 255, 0.25); transition: all 0.3s ease;">Sign Up</a>
      </div>
    @endif
  </div>

  <script>
    // Global Modal Functions
    window.openModal = function(modalId) {
      const modal = document.getElementById(modalId);
      if (modal) modal.classList.add('show');
    };

    window.closeModal = function(modalId) {
      const modal = document.getElementById(modalId);
      if (modal) modal.classList.remove('show');
    };

    document.addEventListener('DOMContentLoaded', function () {
      // Toggle User Dropdown
      const profileTrigger = document.getElementById('userProfileIcon');
      const userDropdown = document.getElementById('userDropdown');
      if (profileTrigger && userDropdown) {
        profileTrigger.addEventListener('click', function(e) {
          e.stopPropagation();
          userDropdown.classList.toggle('show');
        });
      }

      // Notification Dropdown
      const notifBtn = document.getElementById('notificationBtn');
      const notifDropdown = document.getElementById('notificationDropdown');
      if (notifBtn && notifDropdown) {
        notifBtn.addEventListener('click', function(e) {
          e.stopPropagation();
          notifDropdown.classList.toggle('show');
        });
      }

      // Close dropdowns on outside click
      document.addEventListener('click', function() {
        if (userDropdown) userDropdown.classList.remove('show');
        if (notifDropdown) notifDropdown.classList.remove('show');
      });
    });
  </script>
</header>

@include('includes.mobile-sidebar')