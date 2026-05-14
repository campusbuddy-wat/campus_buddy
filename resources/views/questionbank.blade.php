@extends('layouts.app')

@section('title', 'Question Bank | Campus Buddy')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/question-bank.css') }}">
    <link rel="stylesheet" href="{{ asset('css/buddy-card.css') }}">
@endpush

@section('content')
    <!-- ================= HERO BANNER ================= -->
    <section class="hero-banner">
        <img src="{{ asset('images/community/studygroup.jpg') }}" alt="Study Groups" class="hero-bg">
        <div class="hero-overlay-dark"></div>

        <div class="hero-content-wrapper hero-text animate-up">
            <div class="hero-deco hero-deco-1"></div>
            <div class="hero-deco hero-deco-2"></div>
            <div class="hero-deco hero-deco-3"></div>
            <div class="hero-deco hero-deco-4"></div>

            <div class="hero-content">
                <span class="hero-date">{{ now()->format('F j, Y') }}</span>
                <span class="hero-tag">PRACTICE & EXCEL</span>
                <h1>Master your courses with the <span>Question Bank.</span></h1>
                <p class="hero-desc">
                    Access past exams, midterms, finals, and quizzes to prepare effectively.
                    Filter by department, course, or specific topics to find exactly what you need.
                </p>
            </div>
        </div>
    </section>

    <div class="qb-content page-container" id="qb-content">
        @if (session('success'))
            <div class="alert alert-success">
                ✅ {{ session('success') }}
            </div>
        @endif

        <div class="filter-container reveal">
            <div class="filter-bar">
                <form action="{{ route('question-bank') }}" method="GET" style="display: flex; gap: 15px; flex: 1;">
                    <input type="text" name="course" placeholder="Course Code" class="filter-input" value="{{ request('course') }}">
                    <input type="text" name="semester" placeholder="Semester" class="filter-input" value="{{ request('semester') }}">
                    <button type="submit" class="filter-btn">Search</button>
                </form>
                <button type="button" id="openUploadBtn" class="upload-btn">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                        <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/>
                    </svg>
                    Upload Question
                </button>
            </div>
        </div>

        <div class="question-grid reveal" id="questionGrid">
            @forelse($questions as $question)
                <div class="question-card animate-in trigger-view" 
                     data-dept="{{ $question->department }}"
                     data-code="{{ $question->course_code }}"
                     data-title="{{ $question->title }}"
                     data-difficulty="{{ $question->difficulty }}"
                     data-heading="{{ $question->question_heading }}"
                     data-subs="{{ $question->sub_questions }}"
                     data-tags="{{ $question->tags }}"
                     data-course="{{ $question->course_name }}"
                     data-date="{{ $question->year_semester }}"
                     data-files="{{ json_encode($question->file_path) }}">
                    <div class="question-header">
                        <div class="card-meta">
                            <span class="dept">{{ $question->department }}</span>
                            <span class="code">{{ $question->course_code }}</span>
                        </div>
                        <div class="title-row">
                            <h3>{{ $question->course_name ?: 'Course Name' }}</h3>
                            <div style="display:flex; gap: 8px;">
                                <span class="difficulty {{ strtolower($question->difficulty ?? 'medium') }}">{{ $question->difficulty ?? 'Medium' }}</span>
                            </div>
                        </div>
                        <div style="margin-top: 4px; margin-bottom: 8px; color: #0284c7; font-size: 14px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px;">
                            {{ $question->title ?: 'Question Paper' }}
                        </div>
                    </div>
                    <div class="question-content">
                        <p class="main-question"><strong>{{ $question->question_heading }}</strong></p>
                        <ul class="sub-questions">
                            @foreach(explode("\n", $question->sub_questions) as $sub)
                                @if(trim($sub))
                                    <li>{{ trim($sub) }}</li>
                                @endif
                            @endforeach
                        </ul>
                        <div class="topic-tags">
                            @if($question->tags)
                                @foreach(explode(',', $question->tags) as $tag)
                                    <span>#{{ trim($tag) }}</span>
                                @endforeach
                            @endif
                        </div>
                    </div>
                    <div class="question-footer">
                        <span class="course">{{ $question->course_name }}</span>
                        <span class="date">{{ $question->year_semester }}</span>
                    </div>
                    <div class="card-action-overlay">
                        <button type="button" class="action-btn view-btn">View</button>
                        @if($question->file_path && is_array($question->file_path))
                            @if(count($question->file_path) === 1)
                                <a href="{{ asset('storage/' . $question->file_path[0]) }}" 
                                   class="action-btn download-btn stop-prop" 
                                   download onclick="event.stopPropagation()">
                                    Download
                                </a>
                            @else
                                <div class="multi-download-badge">
                                    {{ count($question->file_path) }} Files
                                </div>
                            @endif
                        @endif
                    </div>
                </div>
            @empty
                <div class="empty-state">
                    <p>No questions found matching your criteria.</p>
                </div>
            @endforelse
        </div>

        <div class="load-more reveal">
            <button class="load-more-btn">Load More Questions</button>
        </div>

        <!-- AI Practice Generator -->
        <div class="buddy-section reveal" id="practiceGeneratorSection">
            <div class="buddy-card" style="cursor: default; text-align: left;">
                <h3>✨ AI Practice Generator</h3>
                <p style="margin-bottom: 12px; opacity: 0.85;">Generate practice quizzes, get explanations, or discover frequently tested topics.</p>
                
                <div style="display: flex; flex-wrap: wrap; gap: 8px; margin-bottom: 14px; position:relative; z-index:10; pointer-events:auto; background: rgba(99,102,241,0.03); padding: 10px; border-radius: 10px; border: 1px solid rgba(99,102,241,0.1);">
                    <input type="text" id="aiFilterCourse" placeholder="Course Code (e.g. CSE421)" style="flex:1; min-width:150px; padding:8px 12px; border-radius:8px; border:1px solid rgba(99,102,241,0.2); font-size:13px; outline:none;">
                    <select id="aiFilterTerm" style="flex:1; min-width:150px; padding:8px 12px; border-radius:8px; border:1px solid rgba(99,102,241,0.2); font-size:13px; outline:none; background:white;">
                        <option value="">Any Term</option>
                        <option value="Mid">Midterm</option>
                        <option value="Final">Final</option>
                    </select>
                </div>
                
                <div style="display: flex; flex-wrap: wrap; gap: 8px; margin-bottom: 14px; position:relative; z-index:10; pointer-events:auto;">
                    <button class="practice-ai-pill" onclick="askPracticeAI('Generate 5 practice MCQ questions from the question bank')">📝 5 MCQs</button>
                    <button class="practice-ai-pill" onclick="askPracticeAI('What are the most frequently tested topics based on the question bank?')">📊 Most tested topics</button>
                    <button class="practice-ai-pill" onclick="askPracticeAI('Create a mini practice quiz with short answer questions')">✍️ Short answer quiz</button>
                    <button class="practice-ai-pill" onclick="askPracticeAI('Suggest a study strategy based on the question patterns and difficulty levels')">🧠 Study strategy</button>
                </div>
                
                <div style="display: flex; gap: 8px; position:relative; z-index:10; pointer-events:auto;">
                    <input type="text" id="practiceAiInput" placeholder="Ask about any course or topic..." 
                           style="flex:1; padding:10px 14px; border-radius:10px; border:1px solid rgba(99,102,241,0.2); background:rgba(99,102,241,0.05); color:#334155; font-size:14px; outline:none;"
                           onkeypress="if(event.key==='Enter') askPracticeAI(this.value)">
                    <button onclick="askPracticeAI(document.getElementById('practiceAiInput').value)" 
                            style="padding:10px 18px; border-radius:10px; border:none; background:linear-gradient(135deg,#667eea,#764ba2); color:#fff; font-weight:600; cursor:pointer; font-size:14px; white-space:nowrap;">
                        Generate ✨
                    </button>
                </div>
                
                <div id="practiceAiResponse" style="display:none; margin-top:14px; padding:14px; background:rgba(99,102,241,0.05); border-radius:12px; border:1px solid rgba(99,102,241,0.1); max-height:450px; overflow-y:auto; position:relative; z-index:10; pointer-events:auto;">
                    <div id="practiceAiText" style="color:#334155; font-size:14px; line-height:1.7;"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Upload Modal -->
    <div id="uploadModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Upload Question Bank</h2>
                <button type="button" class="close-btn" id="closeUploadModal">&times;</button>
            </div>
            <form action="{{ route('question-bank.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="upload-guide-card">
                    <div class="guide-icon">📁</div>
                    <div class="guide-text">
                        <h3>Easy Upload</h3>
                        <p>Upload your question files (PDF or Images). You can select multiple files if the question spans multiple pages.</p>
                    </div>
                </div>

                <div class="form-group full-width" style="margin-top: 20px;">
                    <label>Select Question Files (PDF/Images)</label>
                    <div class="file-drop-zone" id="dropZone">
                        <input type="file" name="files[]" accept=".pdf,image/*" multiple required id="fileInput">
                        <div class="drop-zone-content">
                            <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="#00AAFF" stroke-width="2">
                                <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/>
                            </svg>
                            <p>Click or drag files here to upload</p>
                            <span class="file-name-display" id="fileNameDisplay">No files chosen</span>
                        </div>
                    </div>
                </div>
                
                <div class="modal-footer">
                    <button type="submit" class="submit-btn" style="width: 100%;">Upload for Review</button>
                </div>
            </form>
        </div>
    </div>

    <!-- View Question Modal -->
    <div id="viewQuestionModal" class="modal">
        <div class="modal-content view-modal-content">
            <div class="modal-header">
                <h2>Question Details</h2>
                <button type="button" class="close-btn" onclick="closeModal('viewQuestionModal')">&times;</button>
            </div>
            <div class="view-card-wrapper">
                <div class="question-card static-view">
                    <div class="question-header">
                        <div class="card-meta">
                            <span class="dept" id="viewDept"></span>
                            <span class="code" id="viewCode"></span>
                        </div>
                        <div class="title-row">
                            <h3 id="viewCourseHeading"></h3>
                            <div style="display:flex; gap: 8px;">
                                <span class="difficulty" id="viewDifficulty"></span>
                            </div>
                        </div>
                        <div id="viewTitle" style="margin-top: 4px; margin-bottom: 8px; color: #0284c7; font-size: 14px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px;"></div>
                    </div>
                    <div class="question-content">
                        <p class="main-question"><strong id="viewHeading"></strong></p>
                        <ul class="sub-questions" id="viewSubs"></ul>
                        <div class="topic-tags" id="viewTags"></div>
                    </div>
                    <div class="question-footer">
                        <span class="course" id="viewCourse"></span>
                        <span class="date" id="viewDate"></span>
                    </div>

                    <!-- File Preview Section -->
                    <div id="viewFileSection" class="view-file-section" style="display:none; margin-top: 20px; border-top: 1px solid #eee; padding-top: 15px; text-align: left;">
                        <h4 style="font-size: 14px; color: #1e293b; margin-bottom: 10px;">Attached Files:</h4>
                        <div id="viewFileLinks" style="display: flex; flex-wrap: wrap; gap: 10px;"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // ================= REVEAL ANIMATIONS =================
            const observerOptions = {
                threshold: 0.1,
                rootMargin: '0px 0px -50px 0px'
            };

            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.classList.add('active');
                        // Animate cards if present
                        const children = entry.target.querySelectorAll('.question-card');
                        children.forEach(child => child.classList.add('animate-in'));
                    }
                });
            }, observerOptions);

            document.querySelectorAll('.reveal').forEach(el => {
                observer.observe(el);
            });

            // ================= MODAL LOGICAL (EVENT DELEGATION) =================
            const uploadModal = document.getElementById('uploadModal');
            const viewModal = document.getElementById('viewQuestionModal');
            const openBtn = document.getElementById('openUploadBtn');
            const closeBtn = document.getElementById('closeUploadModal');

            if (openBtn && uploadModal) {
                openBtn.addEventListener('click', (e) => {
                    e.preventDefault();
                    uploadModal.style.display = 'flex';
                });
            }

            if (closeBtn && uploadModal) {
                closeBtn.addEventListener('click', () => {
                    uploadModal.style.display = 'none';
                });
            }

            // Global click listener for cards and buttons
            document.addEventListener('click', (e) => {
                const viewTrigger = e.target.closest('.trigger-view');
                
                if (viewTrigger) {
                    e.preventDefault();
                    e.stopPropagation();
                    const data = viewTrigger.dataset;
                    
                    // Populate Modal
                    const fields = {
                        'viewDept': data.dept,
                        'viewCode': data.code,
                        'viewCourseHeading': data.course,
                        'viewTitle': data.title,
                        'viewDifficulty': data.difficulty,
                        'viewHeading': data.heading,
                        'viewCourse': data.course,
                        'viewDate': data.date
                    };

                    for (const [id, value] of Object.entries(fields)) {
                        const el = document.getElementById(id);
                        if (el) el.textContent = value || '';
                    }

                    // Handle Difficulty Class
                    const diffEl = document.getElementById('viewDifficulty');
                    if (diffEl && data.difficulty) {
                        diffEl.className = `difficulty ${data.difficulty.toLowerCase()}`;
                    }

                    // Subs
                    const subsList = document.getElementById('viewSubs');
                    if (subsList && data.subs) {
                        subsList.innerHTML = '';
                        // Split by any newline sequence
                        data.subs.split(/\r?\n/).forEach(sub => {
                            if (sub.trim()) {
                                const li = document.createElement('li');
                                li.textContent = sub.trim();
                                subsList.appendChild(li);
                            }
                        });
                    }

                    // Tags
                    const tagsDiv = document.getElementById('viewTags');
                    if (tagsDiv) {
                        tagsDiv.innerHTML = '';
                        if (data.tags) {
                            data.tags.split(',').forEach(tag => {
                                const span = document.createElement('span');
                                span.textContent = `#${tag.trim()}`;
                                tagsDiv.appendChild(span);
                            });
                        }
                    }

                    if (viewModal) viewModal.style.display = 'flex';

                    // Handle Files in Modal
                    const fileSection = document.getElementById('viewFileSection');
                    const fileLinksDiv = document.getElementById('viewFileLinks');
                    if (fileSection && fileLinksDiv && data.files) {
                        fileLinksDiv.innerHTML = '';
                        try {
                            const files = JSON.parse(data.files);
                            if (files && Array.isArray(files) && files.length > 0) {
                                fileSection.style.display = 'block';
                                files.forEach(file => {
                                    const isImage = /\.(jpg|jpeg|png|webp)$/i.test(file);
                                    const link = document.createElement('a');
                                    link.href = '/storage/' + file;
                                    link.target = '_blank';
                                    link.className = 'modal-file-link';
                                    
                                    if (isImage) {
                                        link.innerHTML = `<img src="/storage/${file}" class="modal-preview-img">`;
                                    } else {
                                        link.innerHTML = `
                                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/>
                                            </svg>
                                            <span>PDF Document</span>
                                        `;
                                    }
                                    fileLinksDiv.appendChild(link);
                                });
                            } else {
                                fileSection.style.display = 'none';
                            }
                        } catch (e) {
                            fileSection.style.display = 'none';
                        }
                    }
                }

                // Handle outside clicks
                if (e.target === uploadModal) uploadModal.style.display = 'none';
                if (e.target === viewModal) viewModal.style.display = 'none';
            });

            // ================= FILE DROP ZONE LOGIC =================
            const fileInput = document.getElementById('fileInput');
            const fileNameDisplay = document.getElementById('fileNameDisplay');
            const dropZone = document.getElementById('dropZone');

            if (fileInput && fileNameDisplay) {
                fileInput.addEventListener('change', function() {
                    if (this.files && this.files.length > 0) {
                        const count = this.files.length;
                        fileNameDisplay.textContent = count > 1 ? count + ' files selected' : 'Selected: ' + this.files[0].name;
                        fileNameDisplay.style.color = '#10b981'; // Green for success
                        if (dropZone) dropZone.style.borderColor = '#10b981';
                    } else {
                        fileNameDisplay.textContent = 'No file chosen';
                        fileNameDisplay.style.color = '#00AAFF';
                        if (dropZone) dropZone.style.borderColor = '#e2e8f0';
                    }
                });
            }
        });

        function closeModal(id) {
            document.getElementById(id).style.display = 'none';
        }

        // ==================== AI PRACTICE GENERATOR ====================
        let practiceAiHistory = [];

        async function askPracticeAI(message) {
            if (!message || !message.trim()) return;
            
            const responseBox = document.getElementById('practiceAiResponse');
            const responseText = document.getElementById('practiceAiText');
            const input = document.getElementById('practiceAiInput');
            
            responseBox.style.display = 'block';
            
            // If it's a new conversation, clear the box. Otherwise just append.
            if (practiceAiHistory.length === 0) {
                responseText.innerHTML = '';
            } else {
                // Remove any previous error messages if present
                const errorMsg = document.getElementById('practiceAiError');
                if (errorMsg) errorMsg.remove();
            }

            // Append user message
            const userHtml = `<div style="margin-top:15px; margin-bottom:5px; color:#4338ca; font-weight:600;">You: ${message}</div>`;
            const loadingHtml = `<div id="practiceAiLoading" style="opacity:0.6; margin-bottom:15px;">🧠 Generating...</div>`;
            responseText.innerHTML += userHtml + loadingHtml;
            
            if (input) input.value = '';
            
            responseBox.scrollTop = responseBox.scrollHeight;

            try {
                const res = await fetch('/api/ai/practice-generator', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
                    },
                    body: JSON.stringify({ 
                        message: message.trim(),
                        history: practiceAiHistory,
                        course_code: document.getElementById('aiFilterCourse')?.value || '',
                        term: document.getElementById('aiFilterTerm')?.value || ''
                    })
                });

                const data = await res.json();
                
                // Remove loading indicator
                const loadingIndicator = document.getElementById('practiceAiLoading');
                if (loadingIndicator) loadingIndicator.remove();
                let html = (data.response || 'Unable to generate practice content.')
                    .replace(/\*\*([^*]+)\*\*/g, '<strong>$1</strong>')
                    .replace(/### (.+)/g, '<h4 style="margin:10px 0 6px;color:#4f46e5;">$1</h4>')
                    .replace(/## (.+)/g, '<h4 style="margin:12px 0 6px;color:#4f46e5;">$1</h4>')
                    .replace(/# (.+)/g, '<h3 style="margin:14px 0 8px;color:#6366f1;">$1</h3>')
                    .replace(/- (.+)/g, '• $1')
                    .replace(/\n/g, '<br>');

                responseText.innerHTML += `<div style="margin-bottom:20px; padding-bottom:15px; border-bottom:1px solid rgba(0,0,0,0.05);">${html}</div>`;
                responseBox.scrollTop = responseBox.scrollHeight;

                // Save to history
                if (data.response) {
                    practiceAiHistory.push({ role: 'user', content: message.trim() });
                    practiceAiHistory.push({ role: 'assistant', content: data.response });
                }

            } catch (error) {
                console.error(error);
                const loadingIndicator = document.getElementById('practiceAiLoading');
                if (loadingIndicator) loadingIndicator.remove();
                responseText.innerHTML += `<div id="practiceAiError" style="color:#ef4444; margin-top:10px;">Failed to generate content. Please try again.</div>`;
                responseBox.scrollTop = responseBox.scrollHeight;
            }
        }
    </script>

    <style>
        .practice-ai-pill {
            padding: 7px 14px;
            border-radius: 20px;
            border: 1px solid rgba(99,102,241,0.3);
            background: rgba(99,102,241,0.1);
            color: #4f46e5;
            font-size: 13px;
            cursor: pointer;
            transition: all 0.2s ease;
            white-space: nowrap;
        }
        .practice-ai-pill:hover {
            background: rgba(102,126,234,0.3);
            border-color: rgba(102,126,234,0.6);
            transform: translateY(-1px);
        }
    </style>
@endpush