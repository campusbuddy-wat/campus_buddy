@extends('layouts.app')

@section('title', 'Class Tasks | Campus Buddy')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/classtask.css') }}">
@endpush

@section('content')
    <!-- ================= HERO SECTION ================= -->
    <section class="classtask-hero reveal active">
        <img src="{{ asset('images/community/dashboardBG.jpg') }}" alt="Class Tasks" class="hero-bg">
        <div class="hero-overlay-dark"></div>

        <div class="hero-content-wrapper hero-text animate-up">
            <div class="hero-deco hero-deco-1"></div>
            <div class="hero-deco hero-deco-2"></div>
            <div class="hero-deco hero-deco-3"></div>
            <div class="hero-deco hero-deco-4"></div>

            <div class="hero-inner">
                <span class="hero-date">{{ now()->format('F j, Y') }}</span>
                <span class="hero-tag">ORGANIZATION & DEADLINES</span>
                <h1 class="hero-title">Stay Ahead of Your <span>Class Tasks.</span></h1>
                <p class="hero-subtitle">Your personalized dashboard for tracking assignments, quizzes, and presentations.</p>

                <div class="hero-stats">
                    <div class="stat-box">
                        <span class="stat-value">{{ $tasks->filter(fn($t) => \Carbon\Carbon::parse($t->deadline)->isFuture())->count() }}</span>
                        <span class="stat-label">Active</span>
                    </div>
                    <div class="stat-box">
                        <span class="stat-value">{{ $tasks->filter(fn($t) => \Carbon\Carbon::parse($t->deadline)->isFuture() && $t->progress_status !== 'Completed')->count() }}</span>
                        <span class="stat-label">Due</span>
                    </div>
                    <div class="stat-box">
                        <span class="stat-value">{{ $tasks->where('progress_status', 'Completed')->count() }}</span>
                        <span class="stat-label">Completed</span>
                    </div>
                    <div class="stat-box highlight-overdue">
                        <span class="stat-value">{{ $tasks->filter(fn($t) => \Carbon\Carbon::parse($t->deadline)->isPast() && $t->progress_status !== 'Completed')->count() }}</span>
                        <span class="stat-label">Overdue</span>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ================= FILTER BAR ================= -->
    <div class="filter-bar-wrapper">
        <div class="filter-bar">
            <button class="filter-btn active" data-filter="all">
                <i class="fas fa-th-large filter-icon"></i>
                All Tasks
                <span class="count">{{ $tasks->count() }}</span>
            </button>
            <button class="filter-btn" data-filter="assignment">
                <i class="fas fa-file-alt filter-icon"></i>
                Assignments
                <span class="count">{{ $tasks->where('type', 'assignment')->count() }}</span>
            </button>
            <button class="filter-btn" data-filter="quiz">
                 <i class="fas fa-clipboard-check filter-icon"></i>
                Quizzes
                <span class="count">{{ $tasks->where('type', 'quiz')->count() }}</span>
            </button>
            <button class="filter-btn" data-filter="presentation">
                <i class="fas fa-chalkboard-teacher filter-icon"></i>
                Presentations
                <span class="count">{{ $tasks->where('type', 'presentation')->count() }}</span>
            </button>
        </div>
    </div>

    <!-- ================= MAIN CONTAINER ================= -->
    <div class="classtask-container page-container">
        @php
            $types = [
                'assignment' => ['title' => 'Assignments', 'desc' => 'Courseworks and projects', 'icon' => 'fa-file-alt', 'color' => 'assignment'],
                'quiz' => ['title' => 'Quizzes', 'desc' => 'Class tests', 'icon' => 'fa-clipboard-check', 'color' => 'quiz'],
                'presentation' => ['title' => 'Presentations', 'desc' => 'Team projects', 'icon' => 'fa-chalkboard-teacher', 'color' => 'presentation']
            ];
        @endphp

        @foreach($types as $type => $info)
            @if($tasks->where('type', $type)->isNotEmpty())
            <section class="task-section active" data-type="{{ $type }}">
                <div class="section-header-wrapper active">
                    <div class="section-icon {{ $info['color'] }}-icon"><i class="fas {{ $info['icon'] }}"></i></div>
                    <div class="section-title-group">
                        <h2 class="section-title">{{ $info['title'] }}</h2>
                        <p class="section-desc">{{ $info['desc'] }}</p>
                    </div>
                    <div class="section-badge">{{ $tasks->where('type', $type)->count() }} Items</div>
                </div>
                <div class="task-cards-grid">
                    @foreach($tasks->where('type', $type) as $task)
                        @php
                            $deadline = \Carbon\Carbon::parse($task->deadline);
                            $isPast = $deadline->isPast();
                            $isCompleted = $task->progress_status === 'Completed';
                            
                            $statusClass = 'status-active';
                            if ($isCompleted) $statusClass = 'status-completed';
                            elseif ($isPast) $statusClass = 'status-overdue';
                        @endphp
                        <div id="task-{{ $task->id }}" class="task-card {{ $statusClass }} active" data-type="{{ $type }}">
                            <div class="card-status-bar {{ $type }}-bar"></div>
                            <div class="card-header">
                                <h3 class="card-title">{{ $task->title }}</h3>
                                <span class="card-progress {{ $type == 'quiz' ? 'quiz-progress' : '' }}">{{ $task->course_code }}</span>
                            </div>
                            
                            <div class="card-timeline">
                                <div class="timeline-item">
                                    <div class="timeline-icon">📅</div>
                                    <div class="timeline-content">
                                        <p class="timeline-label">Date</p>
                                        <p class="timeline-value">{{ \Carbon\Carbon::parse($task->deadline)->format('M d, Y') }}</p>
                                    </div>
                                </div>
                                <div class="timeline-divider"></div>
                                <div class="timeline-item">
                                    <div class="timeline-icon">⏰</div>
                                    <div class="timeline-content">
                                        <p class="timeline-label">Time</p>
                                        <p class="timeline-value">{{ \Carbon\Carbon::parse($task->deadline)->format('h:i A') }}</p>
                                    </div>
                                </div>
                            </div>

                            <div class="card-topic">
                                <p class="topic-label">Topic</p>
                                <p class="topic-value">{{ $task->topic }}</p>
                            </div>

                            <div class="buddy-box {{ $type }}-buddy">
                                <div class="buddy-header">
                                    <div class="buddy-mascot-wrapper">
                                        <img src="{{ asset('images/mascot.png') }}" class="buddy-mascot" alt="Buddy">
                                    </div>
                                    <div class="buddy-title-group">
                                        <span class="buddy-label">Buddy's Tip</span>
                                        <span class="buddy-subtitle">Study Assistant</span>
                                    </div>
                                    <button class="ai-tip-btn" onclick='generateAITips(this, @json($task))' title="Get AI-powered tips">
                                        ✨ AI Tips
                                    </button>
                                </div>
                                <div class="buddy-content">
                                    <p class="buddy-tip-text" id="tip-text-{{ $task->id }}">{{ $task->tip_1 }}</p>
                                </div>
                                <div class="card-footer-actions">
                                    <button class="buddy-help-btn" onclick='showTaskDetails(@json($task))'>View Details</button>
                                    <form action="{{ route('classtask.complete', $task) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="complete-btn {{ $task->progress_status === 'Completed' ? 'is-completed' : '' }}">
                                            <i class="fas {{ $task->progress_status === 'Completed' ? 'fa-check-circle' : 'fa-circle-notch' }}"></i>
                                            {{ $task->progress_status === 'Completed' ? 'Done' : 'Mark Done' }}
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </section>
            @endif
        @endforeach

        @if($tasks->isEmpty())
            <div class="no-tasks-box">
                <p>No tasks found for your section.</p>
            </div>
        @endif
    </div>

    <!-- Modal -->
    <div id="taskModal" class="modal">
        <div class="modal-content reveal">
            <span class="close-modal" onclick="closeTaskModal()">&times;</span>
            <div id="modalBody"></div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        function showTaskDetails(task) {
            const modal = document.getElementById('taskModal');
            const body = document.getElementById('modalBody');
            body.innerHTML = `
                <div class="modal-detail-header ${task.type}">
                    <h2>${task.title}</h2>
                    <span class="modal-type">${task.type}</span>
                </div>
                <div class="modal-main-details">
                    <div class="detail-row"><strong>Course:</strong> <span>${task.course_code}</span></div>
                    <div class="detail-row"><strong>Topic:</strong> <span>${task.topic}</span></div>
                    <div class="detail-row"><strong>Deadline:</strong> <span>${new Date(task.deadline).toLocaleString()}</span></div>
                    <div class="detail-desc"><strong>Description:</strong><p>${task.description || 'No description available.'}</p></div>
                    <div class="modal-buddy-tips">
                        <div class="tip-card"><h6>Tip 1</h6><p>${task.tip_1}</p></div>
                        <div class="tip-card"><h6>Tip 2</h6><p>${task.tip_2}</p></div>
                    </div>
                </div>
                <div class="modal-footer"><a href="{{ route('buddy-chat') }}" class="chat-btn">Ask Buddy AI</a></div>
            `;
            modal.style.display = 'flex';
        }

        function closeTaskModal() { document.getElementById('taskModal').style.display = 'none'; }
        window.onclick = function(event) { if (event.target == document.getElementById('taskModal')) closeTaskModal(); }

        // Filtering
        document.querySelectorAll('.filter-btn').forEach(btn => {
            btn.addEventListener('click', () => {
                document.querySelectorAll('.filter-btn').forEach(b => b.classList.remove('active'));
                btn.classList.add('active');
                const filter = btn.dataset.filter;
                document.querySelectorAll('.task-section').forEach(section => {
                    section.style.display = (filter === 'all' || section.dataset.type === filter) ? 'block' : 'none';
                });
            });
        });

        // ==================== AI TASK TIPS ====================
        async function generateAITips(btn, task) {
            const tipEl = document.getElementById('tip-text-' + task.id);
            if (!tipEl) return;
            
            const originalText = tipEl.textContent;
            btn.disabled = true;
            btn.textContent = '⏳ Generating...';
            tipEl.style.opacity = '0.5';
            tipEl.textContent = 'Generating AI-powered tips...';

            try {
                const res = await fetch('/api/ai/task-tips', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
                    },
                    body: JSON.stringify({
                        title: task.title || '',
                        type: task.type || 'task',
                        course_code: task.course_code || '',
                        topic: task.topic || '',
                        deadline: task.deadline || '',
                        description: task.description || ''
                    })
                });

                const data = await res.json();
                tipEl.innerHTML = `<strong>✨ Tip 1:</strong> ${data.tip_1}<br><br><strong>✨ Tip 2:</strong> ${data.tip_2}`;
                tipEl.style.opacity = '1';
                btn.textContent = '✅ Done';
                setTimeout(() => { btn.textContent = '✨ AI Tips'; btn.disabled = false; }, 3000);
            } catch (e) {
                tipEl.textContent = originalText;
                tipEl.style.opacity = '1';
                btn.textContent = '❌ Failed';
                setTimeout(() => { btn.textContent = '✨ AI Tips'; btn.disabled = false; }, 2000);
            }
        }
    </script>

    <style>
        .ai-tip-btn {
            margin-left: auto;
            padding: 4px 12px;
            border-radius: 16px;
            border: 1px solid rgba(167,139,250,0.4);
            background: rgba(167,139,250,0.15);
            color: #a78bfa;
            font-size: 11px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s ease;
            white-space: nowrap;
        }
        .ai-tip-btn:hover {
            background: rgba(167,139,250,0.3);
            transform: scale(1.05);
        }
        .ai-tip-btn:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none;
        }
    </style>
@endpush