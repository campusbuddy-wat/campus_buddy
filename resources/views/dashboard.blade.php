@extends('layouts.app')

@section('title', 'Campus Buddy | Student Dashboard')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/dashboard.css') }}">
@endpush

@section('content')
{{-- ══════════════════════════════════════════════════
HERO BANNER
Standardized structure matching all pages
══════════════════════════════════════════════════ --}}
<section class="hero-banner" style="background-image: url('{{ asset('images/community/dashboardBG.jpg') }}');">
    <div class="hero-overlay"></div>

    <div class="hero-content-wrapper">
        <div class="hero-text animate-up">
            <div class="hero-deco hero-deco-1"></div>
            <div class="hero-deco hero-deco-2"></div>
            <div class="hero-deco hero-deco-3"></div>
            <div class="hero-deco hero-deco-4"></div>

            <div class="hero-content">
                <span class="hero-date">{{ now()->format('F j, Y') }}</span>
                <span class="hero-tag">STUDENT PORTAL</span>
                <h1>Start your day with <span>CampusBuddy, {{ Auth::user()->name }}!</span></h1>
                <p class="hero-desc">Our goal is to empower your journey by providing essential campus information.</p>
            </div>
        </div>

        <div class="hero-right animate-right delay-2">
            <div class="hero-glass-card">
                <!-- Buddy Mascot positioned relative to card edge -->
                <img src="{{ asset('images/dashboard/image.png') }}" alt="Buddy Mascot" class="hero-mascot">
                
                <div class="glass-header">
                    <div class="glass-dot"></div>
                    <div class="glass-dot"></div>
                </div>
                <div class="glass-content">
                    <span class="glass-tag">Buddy AI — Daily Briefing ✨</span>
                    <div id="aiBriefingContent">
                        <p style="color: rgba(255,255,255,0.6); font-size: 13px; line-height: 1.6;">
                            <span class="briefing-skeleton" style="display:inline-block;width:90%;height:12px;background:rgba(255,255,255,0.15);border-radius:4px;animation:pulse 1.5s infinite;margin-bottom:6px;"></span>
                            <span class="briefing-skeleton" style="display:inline-block;width:75%;height:12px;background:rgba(255,255,255,0.1);border-radius:4px;animation:pulse 1.5s infinite;margin-bottom:6px;"></span>
                            <span class="briefing-skeleton" style="display:inline-block;width:60%;height:12px;background:rgba(255,255,255,0.08);border-radius:4px;animation:pulse 1.5s infinite;"></span>
                        </p>
                    </div>
                    <div style="display: flex; gap: 8px; margin-top: 10px;">
                        <a href="{{ route('buddy-chat') }}" class="glass-btn">
                            <span>Let's Talk</span>
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="22" y1="2" x2="11" y2="13"></line><polygon points="22 2 15 22 11 13 2 9 22 2"></polygon></svg>
                        </a>
                    </div>
                </div>
                <div class="glass-shimmer"></div>
            </div>
            <div class="mascot-trail"></div>
        </div>
    </div>
</section>

<div class="dashboard-container page-container mt-10">
      <div class="dashboard-grid">

        {{-- ── LEFT COLUMN ─────────────────────────────── --}}
        <div class="main-col">

          {{-- Section: Study Overview (= Finance row in Pic 1) --}}
          <div class="section-head animate-up delay-1">
            <h2 class="section-title">Study Overview</h2>
          </div>

          <div class="stat-row">
            {{-- 1. SMART SCHEDULE CARD (Left) --}}
            @php
            $nextClass = null;
            $currentClass = null;
            $currentTime = now();

            foreach($todaySchedule as $class) {
                // Parse time_slot like "08:30 AM - 09:50 AM" or "8.30 am - 10.00 am"
                $parts = explode('-', $class->time_slot);
                $startTimeStr = trim(str_replace('.', ':', $parts[0]));
                $endTimeStr = isset($parts[1]) ? trim(str_replace('.', ':', $parts[1])) : $startTimeStr;
    
                try {
                    // Try parsing with uppercase (AM/PM) and lowercase (am/pm) formats
                    $startTime = null;
                    $endTime = null;

                    foreach(['h:i A', 'h.i A', 'h:i a', 'h.i a'] as $format) {
                        try {
                            if (!$startTime) $startTime = \Carbon\Carbon::createFromFormat($format, $startTimeStr);
                            if (!$endTime) $endTime = \Carbon\Carbon::createFromFormat($format, $endTimeStr);
                        } catch (\Exception $e) {}
                    }

                    if (!$startTime || !$endTime) continue;
    
                    // If it's currently during this class
                    if ($currentTime->between($startTime, $endTime)) {
                        $currentClass = $class;
                        break;
                    }
                    // If this class is in the future
                    if ($startTime->isAfter($currentTime)) {
                        $nextClass = $class;
                        break;
                    }
                } catch (\Exception $e) { continue; }
            }
            @endphp

            @php
              $tasksDueToday = $assignments->filter(function($task) {
                  return \Carbon\Carbon::parse($task->deadline)->isToday();
              });
            @endphp

            <div class="stat-card schedule-card animate-scale delay-2 {{ $currentClass ? 'is-class-now' : '' }}" style="justify-content: flex-start; padding: 18px 15px; height: auto;">
              <div class="stat-icon" style="margin-bottom: 8px;">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                  stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                  <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                  <polyline points="14 2 14 8 20 8"></polyline>
                  <line x1="16" y1="13" x2="8" y2="13"></line>
                  <line x1="16" y1="17" x2="8" y2="17"></line>
                </svg>
              </div>

              <div class="day-tasks-list">
                @php
                    $todayItemsCount = 0;
                @endphp

                @foreach($todaySchedule as $class)
                    @php
                        $parts = explode('-', $class->time_slot);
                        $startTimeStr = trim(str_replace('.', ':', $parts[0]));
                        $endTimeStr = isset($parts[1]) ? trim(str_replace('.', ':', $parts[1])) : $startTimeStr;
                        $startTime = null;
                        $endTime = null;
                        foreach(['h:i A', 'h.i A', 'h:i a', 'h.i a'] as $format) {
                            try {
                                if (!$startTime) $startTime = \Carbon\Carbon::createFromFormat($format, $startTimeStr);
                                if (!$endTime) $endTime = \Carbon\Carbon::createFromFormat($format, $endTimeStr);
                            } catch (\Exception $e) {}
                        }
                        if (!$startTime || !$endTime) continue;
                        
                        $isPast = $endTime->isPast();
                        $isNow = $currentTime->between($startTime, $endTime);
                        if(!$isPast) $todayItemsCount++;
                    @endphp

                    @if(!$isPast)
                    <div class="schedule-mini-item {{ $isNow ? 'is-now' : 'upcoming' }}">
                        <div class="mini-item-header">
                            <span class="mini-item-type {{ $isNow ? 'live' : '' }}">{{ $isNow ? 'LIVE' : 'Upcoming' }}</span>
                            <span class="mini-item-time">{{ $class->time_slot }}</span>
                        </div>
                        <h4 class="mini-item-title">{{ Str::limit($class->course_title, 25) }}</h4>
                        <p class="mini-item-sub">Room {{ $class->room_no }}</p>
                    </div>
                    @endif
                @endforeach

                @foreach($tasksDueToday as $task)
                    @php $todayItemsCount++; @endphp
                    <div class="schedule-mini-item due-today">
                        <div class="mini-item-header">
                            <span class="mini-item-type due">DUE TODAY</span>
                            <span class="mini-item-time">Today</span>
                        </div>
                        <h4 class="mini-item-title">{{ Str::limit($task->title, 25) }}</h4>
                        <p class="mini-item-sub">{{ strtoupper($task->type) }}</p>
                    </div>
                @endforeach

                @if($todayItemsCount === 0)
                    <div class="all-clear-empty">
                        <div class="all-clear-icon">
                            <i class="fas fa-check-circle"></i>
                        </div>
                        <p class="stat-value">All Clear!</p>
                        <p class="stat-sub">No more items for today</p>
                    </div>
                @endif
              </div>

              <p class="stat-label-footer">Day Tasks</p>
            </div>

            {{-- 2. PRIORITY TASK CARD (Center) --}}
            @php $urgentTask = $assignments->first(); @endphp
            <a href="{{ route('classtask') }}{{ $urgentTask ? '#task-'.$urgentTask->id : '' }}"
              class="stat-card active task-card animate-scale delay-3 {{ $urgentTask ? $urgentTask->type . '-card' : '' }}"
              style="padding: 0; align-items: stretch; text-align: left;">
              @if($urgentTask)
              @php
              $createdAt = \Carbon\Carbon::parse($urgentTask->created_at);
              $deadline = \Carbon\Carbon::parse($urgentTask->deadline);
              $totalSeconds = $createdAt->diffInSeconds($deadline);
              $passedSeconds = $createdAt->diffInSeconds(now());
              $percentage = ($totalSeconds > 0) ? min(100, max(0, round(($passedSeconds / $totalSeconds) * 100))) : 0;

              $remaining = \Carbon\Carbon::now()->diffInDays($deadline, false);
              $remaining = round($remaining);
              @endphp

              <div class="card-header">
                <div class="card-title-group">
                  <span class="card-course">{{ $urgentTask->course_code }}</span>
                  <h3 class="card-title">{{ Str::limit($urgentTask->title, 25) }}</h3>
                  <span class="card-progress {{ $urgentTask->type === 'quiz' ? 'quiz-progress' : '' }}">{{ $percentage }}%</span>
                </div>

                <div class="task-type-badge"
                  style="background: {{ $urgentTask->type === 'assignment' ? '#ff6b6b' : ($urgentTask->type === 'quiz' ? '#6496ff' : '#64c850') }};">
                  {{ $urgentTask->type }}
                </div>
              </div>

              <div class="card-timeline">
                <div class="timeline-item">
                  <span class="timeline-icon">📅</span>
                  <div>
                    <p class="timeline-label">Due</p>
                    <p class="timeline-value">{{ $deadline->format('d M') }}</p>
                  </div>
                </div>
                <div class="timeline-divider"></div>
                <div class="timeline-item">
                  <span class="timeline-icon">⏰</span>
                  <div>
                    <p class="timeline-label">Left</p>
                    <p class="timeline-value">
                      @if($remaining > 0) {{ $remaining }}d @elseif($remaining == 0) <span
                        style="color:#ef4444;">Today</span> @else <span style="color:#666;">Overdue</span> @endif
                    </p>
                  </div>
                </div>
              </div>

              @if($urgentTask->topic)
              <div class="card-topic">
                <p class="topic-label">Topic</p>
                <p class="topic-value">{{ Str::limit($urgentTask->topic, 40) }}</p>
              </div>
              @endif

              <p class="priority-label">Priority Task</p>
              @else
              <div
                style="flex: 1; display: flex; flex-direction: column; justify-content: center; align-items: center; padding: 20px;">
                <div class="stat-icon" style="margin-bottom: 15px;">
                  <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                    stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                    <polyline points="14 2 14 8 20 8"></polyline>
                    <line x1="16" y1="13" x2="8" y2="13"></line>
                    <line x1="16" y1="17" x2="8" y2="17"></line>
                  </svg>
                </div>
                <p class="stat-value">Done!</p>
                <p class="stat-sub">All clear for now</p>
                <p class="stat-label">Priority Task</p>
              </div>
              @endif
            </a>

            {{-- 3. LATEST ANNOUNCEMENT CARD (Right) --}}
            <div class="stat-card announcement-card animate-scale delay-4">
              <div class="stat-icon">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                  stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                  <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z">
                  </path>
                  <line x1="12" y1="9" x2="12" y2="13"></line>
                  <line x1="12" y1="17" x2="12.01" y2="17"></line>
                </svg>
              </div>

              @if($announcements->isNotEmpty())
              <div class="announcement-mini-feed">
                @foreach($announcements as $announcement)
                <div id="announcement-{{ $announcement->id }}" class="mini-announcement-item"
                  onclick="openAnnouncementModal('{{ addslashes($announcement->title) }}', '{{ addslashes($announcement->content) }}', '{{ $announcement->created_at->diffForHumans() }}')">
                  @if($announcement->created_at->diffInHours(now()) <= 2) <span class="new-dot">NEW</span>
                    @endif
                    <h4 class="stat-value mini">{{ Str::limit($announcement->title, 25) }}</h4>
                    <p class="announcement-snippet">{{ Str::limit($announcement->content, 60) }}</p>
                    <p class="stat-sub mini">{{ $announcement->created_at->diffForHumans() }}</p>
                </div>
                @endforeach
              </div>
              @else
              <p class="stat-value">Quiet Day</p>
              <p class="stat-sub">No recent updates</p>
              @endif
              <p class="stat-label">Live Announcements</p>
            </div>
          </div>

          {{-- Section: Recent Events (Global) --}}
          <div class="section-head">
            <h2 class="section-title">Recent Events</h2>
            <a href="#" class="section-link">See all</a>
          </div>

          <div class="event-scroll-container">
            @forelse($events as $event)
              <div class="event-card-scroll" 
                   data-title="{{ $event->title }}" 
                   data-description="{{ $event->description }}" 
                   data-date="{{ $event->event_date ? $event->event_date->format('M d, Y') : 'N/A' }}">
                <img src="{{ asset('storage/' . $event->image_path) }}" alt="{{ $event->title }}">
                <div class="event-card-overlay">
                  <div class="event-card-date">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                      <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                      <line x1="16" y1="2" x2="16" y2="6"></line>
                      <line x1="8" y1="2" x2="8" y2="6"></line>
                      <line x1="3" y1="10" x2="21" y2="10"></line>
                    </svg>
                    {{ $event->event_date ? $event->event_date->format('M d, Y') : 'N/A' }}
                  </div>
                  <h4 class="event-card-title">{{ $event->title }}</h4>
                  <span class="event-card-btn">
                    Learn More
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                      <polyline points="9 18 15 12 9 6"></polyline>
                    </svg>
                  </span>
                </div>
              </div>
            @empty
              <p class="empty-msg">No recent events uploaded by Admin.</p>
            @endforelse
          </div>

        </div>{{-- /main-col --}}

        {{-- ── RIGHT SIDEBAR ───────────────────────────── --}}
        <div class="side-col animate-right delay-5">

          {{-- COMMUNITY FEED (Single Card Version) --}}
          <div class="section-head">
            <h2 class="section-title">Community Feed</h2>
            <a href="{{ route('community') }}#posts-section" class="section-link">View all</a>
          </div>

          <div class="community-feed-card animate-right delay-5 clickable" onclick="window.location.href='{{ route('community') }}#posts-section'">
            <div class="community-posts-list">
                @forelse($latestPosts as $index => $post)
                <div class="feed-post-item">
                  <div class="post-user-info">
                      <div class="post-avatar">
                          @if($post->user->profile_image)
                              <img src="{{ asset('storage/' . $post->user->profile_image) }}" onerror="this.src='https://ui-avatars.com/api/?name={{ urlencode($post->user->name) }}&color=00AAFF&background=E0F7FA'">
                          @else
                              👨‍🎓
                          @endif
                      </div>
                      <div class="post-user-details">
                          <h4>{{ $post->user->name }}</h4>
                          <span>{{ $post->created_at->diffForHumans() }}</span>
                      </div>
                  </div>

                  <div class="post-content-preview">
                      {{ $post->content }}
                  </div>

                  <div class="post-card-footer">
                      <div class="post-stats">
                          <span><i class="far fa-heart"></i> {{ $post->likes->count() }}</span>
                          <span><i class="far fa-comment"></i> {{ $post->comments->count() }}</span>
                      </div>
                      <a href="{{ route('community') }}#post-{{ $post->id }}" class="view-post-link" onclick="event.stopPropagation();">
                          Read more <i class="fas fa-arrow-right"></i>
                      </a>
                  </div>
                </div>
                @empty
                <div class="empty-state-card">
                  <p>No community posts yet.</p>
                  <a href="{{ route('community') }}" class="section-link">Be the first to post!</a>
                </div>
                @endforelse
            </div>
          </div>

        </div>{{-- /side-col --}}

      </div>{{-- /dashboard-grid --}}


  @if(session('success'))
  <div class="flash-message success">
    {{ session('success') }}
  </div>
  @endif

  @if(session('error'))
  <div class="flash-message error">
    {{ session('error') }}
  </div>
  @endif

  <style>
    @keyframes slideInRight {
      from {
        transform: translateX(100%);
        opacity: 0;
      }

      to {
        transform: translateX(0);
        opacity: 1;
      }
    }
  </style>

  <!-- ANNOUNCEMENT DETAIL MODAL -->
  <div id="announcementDetailModal" class="modal announcement-modal-custom">
    <div class="modal-content">
      <div class="modal-header">
        <h2 id="modalAnnounceTitle">Announcement Details</h2>
        <span class="close" onclick="closeAnnouncementModal()">&times;</span>
      </div>
      <div class="modal-body">
        <div class="announce-meta-top">
          <span class="announce-badge">LATEST UPDATE</span>
          <span id="modalAnnounceTime" class="announce-time"></span>
        </div>
        <p id="modalAnnounceContent" class="announce-full-text"></p>
      </div>
      <div class="modal-footer">
        <button class="submit-btn" onclick="closeAnnouncementModal()">Got it</button>
      </div>
    </div>
  </div>

  <!-- FULL SCREEN EVENT VIEWER -->
  <style>
    .event-detail-modal { background: #fff; border-radius: 16px; overflow: hidden; width: 95%; max-width: 700px; box-shadow: 0 10px 40px rgba(0,0,0,0.4); display: flex; flex-direction: column; animation: zoomIn 0.3s ease; }
    .event-detail-hero { width: 100%; height: auto; background: #eaeff2; }
    .event-detail-hero img { width: 100%; height: auto; object-fit: cover; border-radius: 0 !important; }
    .event-detail-content { padding: 25px; color: #333; }
    .detail-date { color: var(--primary); font-size: 13px; font-weight: 600; display: block; margin-bottom: 5px;}
    .detail-title { font-size: 22px; font-weight: 800; color: #1a202c; margin-bottom: 12px; }
    .detail-desc { font-size: 15px; color: #4a5568; line-height: 1.6; max-height: 250px; overflow-y: auto; }
    #closeViewer { position: absolute; z-index: 10000; font-size: 40px; cursor: pointer; color: white; top: -10px; right: 20px; }
  </style>

  <div class="image-viewer" id="imageViewer" style="z-index: 99999;">
    <span class="close-btn" id="closeViewer" onclick="document.getElementById('imageViewer').classList.remove('show')">&times;</span>
    <div class="event-detail-modal">
        <div class="event-detail-hero">
            <img src="" alt="Event" id="viewerImage">
        </div>
        <div class="event-detail-content">
            <span id="viewerDate" class="detail-date"></span>
            <h2 id="viewerTitle" class="detail-title"></h2>
            <p id="viewerDescription" class="detail-desc"></p>
        </div>
    </div>
  </div>

  <script>
    (function () {
      // Event Detail Viewer Logic
      const viewer = document.getElementById('imageViewer');
      const viewerImg = document.getElementById('viewerImage');
      const viewerTitle = document.getElementById('viewerTitle');
      const viewerDate = document.getElementById('viewerDate');
      const viewerDesc = document.getElementById('viewerDescription');
      const closeBtn = document.getElementById('closeViewer');
      const learnMoreButtons = document.querySelectorAll('.event-card-btn');

      learnMoreButtons.forEach(btn => {
        btn.addEventListener('click', function (e) {
          e.stopPropagation();
          const card = this.closest('.event-card-scroll');
          const data = card.dataset;
          
          viewerImg.src = card.querySelector('img').src;
          viewerTitle.textContent = data.title;
          viewerDate.textContent = data.date;
          viewerDesc.textContent = data.description;
          
          if (viewer) viewer.classList.add('show');
        });
      });

      if (closeBtn && viewer) {
        closeBtn.addEventListener('click', function () {
          viewer.classList.remove('show');
        });
      }

      window.addEventListener('click', function(event) {
        if (event.target === viewer) {
            viewer.classList.remove('show');
        }
      });
      // Announcement Modal Logic
      window.openAnnouncementModal = function (title, content, time) {
        document.getElementById('modalAnnounceTitle').innerText = title;
        document.getElementById('modalAnnounceContent').innerText = content;
        document.getElementById('modalAnnounceTime').innerText = 'Posted ' + time;
        document.getElementById('announcementDetailModal').style.display = 'flex';
      };

      window.closeAnnouncementModal = function () {
        document.getElementById('announcementDetailModal').style.display = 'none';
      };

      // Close modals on clicking outside
      window.onclick = function (event) {
        if (event.target.classList.contains('modal')) {
          event.target.style.display = "none";
        }
      }
    }) ();
  </script>

  <!-- AI Daily Briefing Auto-Fetch -->
  <script>
    (async function() {
      const briefingEl = document.getElementById('aiBriefingContent');
      if (!briefingEl) return;

      try {
        const res = await fetch('/api/ai/daily-briefing', {
          headers: { 'Accept': 'application/json' }
        });
        const data = await res.json();
        const text = data.response || 'Welcome back! Check your routine and tasks for today.';
        
        let html = text
          .replace(/\*\*([^*]+)\*\*/g, '<strong>$1</strong>')
          .replace(/\n/g, '<br>');
        
        briefingEl.innerHTML = `<p style="color: rgba(255,255,255,0.9); font-size: 13.5px; line-height: 1.65; margin: 0;">${html}</p>`;
      } catch (e) {
        briefingEl.innerHTML = `<p style="color: rgba(255,255,255,0.75); font-size: 13px; line-height: 1.6; margin: 0;">Welcome back! 👋 Check your routine and tasks pages for today's details.</p>`;
      }
    })();
  </script>

  <style>
    @keyframes pulse {
      0%, 100% { opacity: 0.4; }
      50% { opacity: 0.8; }
    }
  </style>

</div>
@endsection