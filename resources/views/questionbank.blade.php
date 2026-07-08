@extends('layouts.app')

@section('title', 'Question Bank | Campus Buddy')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/question-bank.css') }}">
    <link rel="stylesheet" href="{{ asset('css/buddy-card.css') }}">
    <style>
        /* Quiz Sheet Outer Wrapper */
        .quiz-sheet-outer {
            width: 100%;
            max-width: 800px;
            margin: 30px auto;
            font-family: 'Times New Roman', Times, serif;
        }

        /* Quiz Sheet Action Buttons */
        .quiz-download-btn {
            display: inline-flex;
            align-items: center;
            padding: 10px 20px;
            background: linear-gradient(135deg, #10b981, #059669);
            color: white !important;
            font-family: 'Inter', sans-serif;
            font-weight: 600;
            font-size: 14px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.2s ease;
            box-shadow: 0 4px 12px rgba(16, 185, 129, 0.2);
            text-decoration: none;
        }

        .quiz-download-btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 6px 16px rgba(16, 185, 129, 0.3);
            background: linear-gradient(135deg, #059669, #047857);
        }

        .quiz-download-btn:active {
            transform: translateY(0);
        }

        /* Quiz Paper Sheet (mimics physical paper) */
        .quiz-sheet-paper {
            background: white;
            color: #111111;
            padding: 45px 55px;
            border: 1px solid #e2e8f0;
            border-radius: 4px;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.05), 0 8px 10px -6px rgba(0, 0, 0, 0.05);
            line-height: 1.5;
            position: relative;
        }

        /* Print and PDF Specific Rules */
        @media print {
            .quiz-sheet-paper {
                box-shadow: none !important;
                border: none !important;
                padding: 0 !important;
                margin: 0 !important;
            }
        }

        /* Headings & Centered Info */
        .quiz-sheet-header {
            text-align: center;
            margin-bottom: 20px;
        }

        .quiz-sheet-logo {
            display: block;
            margin: 0 auto 12px;
            width: 70px;
            height: auto;
        }

        .university-name {
            font-size: 22px;
            font-weight: bold;
            margin: 0 0 4px;
            color: #000 !important;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .faculty-name {
            font-size: 16px;
            font-weight: normal;
            margin: 0 0 4px;
            color: #222 !important;
        }

        .dept-name {
            font-size: 15px;
            font-weight: bold;
            margin: 0 0 6px;
            color: #111 !important;
        }

        .quiz-title {
            font-size: 16px;
            font-weight: bold;
            margin: 0 0 4px;
            text-transform: uppercase;
            color: #000 !important;
        }

        .semester-name {
            font-size: 14px;
            font-weight: normal;
            margin: 0 0 6px;
            font-style: italic;
            color: #333 !important;
        }

        .course-info {
            font-size: 14px;
            font-weight: bold;
            margin: 0 0 4px;
            color: #000 !important;
        }

        .teacher-info {
            font-size: 13px;
            font-weight: normal;
            margin: 0 0 10px;
            color: #222 !important;
        }

        .exam-meta {
            display: flex;
            justify-content: space-between;
            width: 100%;
            margin-top: 15px;
            font-size: 14px;
            font-weight: bold;
            border-top: 1px dashed #ccc;
            padding-top: 8px;
            color: #000 !important;
        }

        /* Divider */
        .quiz-divider {
            border: 0;
            border-top: 1px solid #111;
            margin: 15px 0 20px;
        }

        /* Set Title styling */
        .quiz-set-title {
            text-align: center;
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 25px;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: #000 !important;
        }

        /* Questions list styling */
        .quiz-questions-wrap {
            font-size: 15px;
            color: #111 !important;
            margin-bottom: 40px;
            text-align: left;
        }

        .quiz-questions-wrap p {
            margin-bottom: 12px;
            line-height: 1.6;
        }

        .quiz-questions-wrap ol, .quiz-questions-wrap ul {
            padding-left: 20px;
            margin-bottom: 15px;
        }

        .quiz-questions-wrap li {
            margin-bottom: 14px;
            line-height: 1.6;
            list-style-type: decimal;
        }

        /* Footer notice style */
        .quiz-sheet-footer {
            margin-top: 40px;
            border-top: 1px dashed #ccc;
            padding-top: 15px;
            font-size: 12px;
            text-align: center;
            font-style: italic;
            color: #555 !important;
            letter-spacing: 0.2px;
        }
    </style>
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

        @if (session('error'))
            <div class="alert alert-danger" style="background: rgba(239, 68, 68, 0.15); border: 1px solid rgba(239, 68, 68, 0.3); color: #f87171; padding: 12px 20px; border-radius: 12px; margin-bottom: 20px;">
                {{ session('error') }}
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
                     data-qb-id="{{ $question->id }}"
                     data-dept="{{ $question->department }}"
                     data-code="{{ $question->course_code }}"
                     data-title="{{ $question->title }}"
                     data-difficulty="{{ $question->difficulty }}"
                     data-heading="{{ $question->question_heading }}"
                     data-subs="{{ $question->sub_questions }}"
                     data-tags="{{ $question->tags }}"
                     data-course="{{ $question->course_name }}"
                     data-date="{{ $question->year_semester }}"
                     data-files="{{ json_encode($question->file_path) }}"
                     data-selected="false">

                    {{-- QB Unique ID + Select Checkbox --}}
                    <div class="qb-select-overlay" onclick="toggleQBSelect(event, this.closest('.question-card'))">
                        <div class="qb-checkbox" id="qbcheck-{{ $question->id }}">☐</div>
                    </div>
                    <div class="qb-id-badge">QB-{{ str_pad($question->id, 4, '0', STR_PAD_LEFT) }}</div>

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
                                <a href="{{ Str::startsWith($question->file_path[0], 'http') ? $question->file_path[0] : asset('storage/' . $question->file_path[0]) }}"
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

        {{-- Floating Generate Quiz Bar (appears when cards are selected) --}}
        <div id="quizFloatingBar" style="display:none; position:fixed; bottom:0; left:0; right:0; z-index:9999;
             background:linear-gradient(135deg,#4f46e5,#7c3aed); color:#fff; padding:14px 24px;
             display:none; align-items:center; justify-content:space-between; gap:16px;
             box-shadow:0 -4px 24px rgba(79,70,229,0.4); border-top: 1px solid rgba(255,255,255,0.15);">
            <div style="font-weight:600; font-size:15px;">
                <span id="quizFloatingCount">0</span> Question(s) Selected
                <span id="quizFloatingCodes" style="opacity:0.75; font-size:13px; margin-left:10px;"></span>
            </div>
            <div style="display:flex; gap:10px;">
                <button onclick="clearQBSelection()" style="padding:8px 18px; border-radius:8px; border:1px solid rgba(255,255,255,0.4); background:transparent; color:#fff; font-size:13px; cursor:pointer; font-weight:500;">✖ Clear</button>
                <button onclick="generateQuizSheet()" style="padding:8px 22px; border-radius:8px; border:none; background:#fff; color:#4f46e5; font-size:14px; font-weight:700; cursor:pointer;">📝 Generate Quiz: Sample</button>
            </div>
        </div>

        <div class="load-more reveal">
            <button class="load-more-btn">Load More Questions</button>
        </div>

        <!-- AI Practice Generator -->
        <div class="buddy-section reveal" id="practiceGeneratorSection">
            <div class="buddy-card" style="cursor: default; text-align: left;">
                <h3>✨ AI Practice Generator</h3>
                <p style="margin-bottom: 12px; opacity: 0.85;">Generate practice quizzes, get explanations, or discover frequently tested topics.</p>
                
                <div style="background:rgba(79,70,229,0.05); border:1px solid rgba(79,70,229,0.15); border-radius:12px; padding:12px 16px; margin-bottom:14px; font-size:13px; color:#4f46e5; font-weight:500;">
                    💡 <strong>Quiz Sample:</strong> Select one or more Question Bank cards above (same course code), then click <em>"Generate Quiz: Sample"</em> in the bar that appears at the bottom.
                </div>

                <div style="display: flex; flex-wrap: wrap; gap: 8px; margin-bottom: 14px; position:relative; z-index:10; pointer-events:auto; background: rgba(99,102,241,0.03); padding: 10px; border-radius: 10px; border: 1px solid rgba(99,102,241,0.1);">
                    <input type="text" id="aiFilterCourse" placeholder="Course Code (e.g. CSE421)" style="flex:1; min-width:150px; padding:8px 12px; border-radius:8px; border:1px solid rgba(99,102,241,0.2); font-size:13px; outline:none;">
                    <select id="aiFilterTerm" style="flex:1; min-width:150px; padding:8px 12px; border-radius:8px; border:1px solid rgba(99,102,241,0.2); font-size:13px; outline:none; background:white;">
                        <option value="">Any Term</option>
                        <option value="Mid">Midterm</option>
                        <option value="Final">Final</option>
                        <option value="Quiz">Quiz</option>
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
        
        <!-- Generated Quiz Sheet Container -->
        <div class="quiz-sheet-outer" id="generatedQuizContainer" style="display: none;">
            <div style="display: flex; justify-content: flex-end; margin-bottom: 15px;">
                <button type="button" onclick="downloadQuizPDF()" class="quiz-download-btn">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="margin-right: 6px;">
                        <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/>
                    </svg>
                    Download PDF
                </button>
            </div>
            
            <div class="quiz-sheet-paper" id="quizPrintArea">
                <div class="quiz-sheet-header">
                    <img src="{{ asset('images/diu_logo.png') }}" alt="DIU Logo" class="quiz-sheet-logo">
                    <h2 class="university-name">Daffodil International University</h2>
                    <h3 class="faculty-name" id="quizFacultyName">Faculty of Science & Information Technology</h3>
                    <h4 class="dept-name">Department of {{ Auth::user()->department ?? 'Software Engineering' }}</h4>
                    <h4 class="quiz-title">Quiz: Sample</h4>
                    <h5 class="semester-name">{{ Auth::user()->semester ?? 'Spring-2026' }}</h5>
                    <h5 class="course-info">
                        Course Code: <span id="quizCourseCode">N/A</span>; 
                        Course: <span id="quizCourseName">N/A</span>
                    </h5>
                    <h5 class="teacher-info">Teacher: <span id="quizTeacher">AI Assistant</span></h5>
                    <div class="exam-meta">
                        <span>Time: 30 minutes</span>
                        <span>Marks: 15</span>
                    </div>
                </div>
                
                <hr class="quiz-divider">
                
                <div class="quiz-sheet-body">
                    
                    <div class="quiz-questions-wrap" id="quizQuestionsContent">
                        <!-- Questions will go here -->
                    </div>
                </div>
                
                <div class="quiz-sheet-footer">
                    This is not a real quiz question, it's a sample so read all from your resource.
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
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
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

        // ==================== MULTI-SELECT QB CARDS ====================
        let selectedQBCards = [];

        function toggleQBSelect(event, card) {
            event.preventDefault();
            event.stopPropagation();

            const isSelected = card.dataset.selected === 'true';
            const qbId = card.dataset.qbId;

            if (isSelected) {
                card.dataset.selected = 'false';
                card.classList.remove('qb-card-selected');
                const checkbox = card.querySelector('.qb-checkbox');
                if (checkbox) checkbox.textContent = '☐';
                selectedQBCards = selectedQBCards.filter(c => c.qbId !== qbId);
            } else {
                card.dataset.selected = 'true';
                card.classList.add('qb-card-selected');
                const checkbox = card.querySelector('.qb-checkbox');
                if (checkbox) checkbox.textContent = '☑';
                selectedQBCards.push({
                    qbId: qbId,
                    code: (card.dataset.code || '').toUpperCase(),
                    course: card.dataset.course || '',
                    title: card.dataset.title || '',
                    difficulty: card.dataset.difficulty || '',
                    heading: card.dataset.heading || '',
                    subs: card.dataset.subs || '',
                    tags: card.dataset.tags || '',
                    date: card.dataset.date || '',
                    dept: card.dataset.dept || ''
                });
            }

            updateFloatingBar();
        }

        function clearQBSelection() {
            document.querySelectorAll('.question-card[data-selected="true"]').forEach(card => {
                card.dataset.selected = 'false';
                card.classList.remove('qb-card-selected');
                const checkbox = card.querySelector('.qb-checkbox');
                if (checkbox) checkbox.textContent = '☐';
            });
            selectedQBCards = [];
            updateFloatingBar();
        }

        function updateFloatingBar() {
            const bar = document.getElementById('quizFloatingBar');
            const countEl = document.getElementById('quizFloatingCount');
            const codesEl = document.getElementById('quizFloatingCodes');

            if (selectedQBCards.length === 0) {
                bar.style.display = 'none';
                return;
            }

            bar.style.display = 'flex';
            countEl.textContent = selectedQBCards.length;

            const uniqueCodes = [...new Set(selectedQBCards.map(c => c.code))].filter(Boolean);
            const uniqueIds = selectedQBCards.map(c => `QB-${String(c.qbId).padStart(4,'0')}`).join(', ');
            codesEl.textContent = `(${uniqueIds})`;
        }

        // ==================== QUIZ SAMPLE GENERATOR ====================
        function getFacultyForDept(dept) {
            if (!dept) return "Faculty of Science & Information Technology";
            const d = dept.toUpperCase();
            if (d.includes("CSE") || d.includes("COMPUTER") || d.includes("SWE") || d.includes("SOFTWARE") || d.includes("CIS") || d.includes("MCT") || d.includes("IT")) {
                return "Faculty of Science & Information Technology";
            }
            if (d.includes("EEE") || d.includes("TEXTILE") || d.includes("CIVIL") || d.includes("ENGINEERING")) {
                return "Faculty of Engineering";
            }
            if (d.includes("BBA") || d.includes("MBA") || d.includes("BUSINESS") || d.includes("ENTREPRENEURSHIP")) {
                return "Faculty of Business & Entrepreneurship";
            }
            if (d.includes("ENGLISH") || d.includes("LAW") || d.includes("HUMANITIES") || d.includes("SOCIAL")) {
                return "Faculty of Humanities & Social Science";
            }
            if (d.includes("PHARMACY") || d.includes("NFE") || d.includes("HEALTH")) {
                return "Faculty of Allied Health Sciences";
            }
            return "Faculty of Science & Information Technology";
        }

        function parseQuizQuestions(text) {
            const lines = text.split(/\r?\n/);
            let olHtml = '<ol style="list-style-type: decimal; padding-left: 20px; margin: 0;">';
            let hasItems = false;

            lines.forEach(line => {
                const trimmed = line.trim();
                if (!trimmed) return;
                const match = trimmed.match(/^\d+[\.\)]\s*(.*)/);
                if (match) {
                    olHtml += `<li style="margin-bottom: 18px; line-height: 1.6; font-family: 'Times New Roman', serif; font-size: 16px; color: #111;">${match[1]}</li>`;
                    hasItems = true;
                } else if (trimmed.length > 3) {
                    if (!trimmed.toLowerCase().startsWith('set-') && !trimmed.toLowerCase().startsWith('set ')) {
                        olHtml += `<p style="margin-bottom: 12px; font-family: 'Times New Roman', serif; font-size: 16px; color: #111;">${trimmed}</p>`;
                    }
                }
            });

            olHtml += '</ol>';
            return hasItems ? olHtml : `<div style="white-space: pre-wrap; font-family: 'Times New Roman', serif; font-size: 16px; line-height: 1.6; color: #111;">${text}</div>`;
        }

        async function generateQuizSheet() {
            if (selectedQBCards.length === 0) {
                alert('Please select at least one Question Bank card first!');
                return;
            }

            // ── Validate: all selected must share the same course code ──
            const uniqueCodes = [...new Set(selectedQBCards.map(c => c.code).filter(Boolean))];
            if (uniqueCodes.length > 1) {
                const savageReplies = [
                    `Bro... seriously? 🤦 You picked questions from ${uniqueCodes.join(', ')}. A quiz has ONE course code, not a buffet menu. Pick ONE course and try again.`,
                    `Come on! ${uniqueCodes.join(' + ')}?? What is this — a crossover episode?? A single quiz belongs to ONE course code. Go back and select questions from the SAME course.`,
                    `Oh wow, ${uniqueCodes.join(' and ')} together in one quiz? Bold move. Sadly, that's not how exams work. Stick to ONE course code per quiz. Thank you.`,
                    `${uniqueCodes.length} different course codes?? You really said "why not all of them"  😭 Please — ONE course code. That's it. That's the rule.`
                ];
                const msg = savageReplies[Math.floor(Math.random() * savageReplies.length)];
                alert('⚠️ Mixed Course Codes Detected!\n\n' + msg);
                return;
            }

            const primaryCard = selectedQBCards[0];
            const courseCode  = primaryCard.code;
            const courseName  = primaryCard.course || 'Subject Course';
            const userDept    = "{{ Auth::user()->department ?? '' }}";

            // Build faculty
            const facultyEl = document.getElementById('quizFacultyName');
            if (facultyEl) facultyEl.textContent = getFacultyForDept(userDept);

            // Fill header
            document.getElementById('quizCourseCode').textContent = courseCode;
            document.getElementById('quizCourseName').textContent = courseName;
            document.querySelector('.quiz-title').textContent = 'Quiz: Sample';

            // Show sheet + loader
            const quizContainer = document.getElementById('generatedQuizContainer');
            const questionsWrap = document.getElementById('quizQuestionsContent');
            quizContainer.style.display = 'block';
            questionsWrap.innerHTML = '<div style="opacity:0.6; text-align:center; padding: 30px 0;">🧠 Analysing selected papers and generating style-matched questions...</div>';
            quizContainer.scrollIntoView({ behavior: 'smooth', block: 'start' });

            try {
                const res = await fetch('/api/ai/practice-generator', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
                    },
                    body: JSON.stringify({
                        message: 'GENERATE_QUIZ_SAMPLE',   // sentinel — backend detects this
                        history: [],
                        course_code: courseCode,
                        selected_qb_data: selectedQBCards   // full rich context
                    })
                });

                const data = await res.json();
                if (data.response) {
                    questionsWrap.innerHTML = parseQuizQuestions(data.response);
                } else {
                    questionsWrap.innerHTML = '<div style="color:red;text-align:center;">Failed to generate. Please try again.</div>';
                }
            } catch (error) {
                console.error(error);
                questionsWrap.innerHTML = '<div style="color:red;text-align:center;">Error communicating with AI. Please try again.</div>';
            }
        }

        function downloadQuizPDF() {
            const element = document.getElementById('quizPrintArea');
            const courseCode = (document.getElementById('quizCourseCode')?.textContent || 'SAMPLE').toUpperCase();
            const opt = {
                margin:       15,
                filename:     `sample_quiz_${courseCode}.pdf`,
                image:        { type: 'jpeg', quality: 0.98 },
                html2canvas:  { scale: 2.5, useCORS: true, letterRendering: true },
                jsPDF:        { unit: 'mm', format: 'a4', orientation: 'portrait' }
            };
            html2pdf().set(opt).from(element).save();
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

        /* QB Unique ID Badge */
        .qb-id-badge {
            position: absolute;
            top: 10px;
            right: 12px;
            background: linear-gradient(135deg, #4f46e5, #7c3aed);
            color: #fff;
            font-size: 10px;
            font-weight: 700;
            padding: 3px 8px;
            border-radius: 20px;
            letter-spacing: 0.5px;
            z-index: 5;
            pointer-events: none;
        }

        /* QB Select Overlay (top-left checkbox) */
        .qb-select-overlay {
            position: absolute;
            top: 10px;
            left: 12px;
            z-index: 10;
            cursor: pointer;
        }
        .qb-checkbox {
            width: 26px;
            height: 26px;
            border-radius: 6px;
            background: rgba(255,255,255,0.9);
            border: 2px solid rgba(99,102,241,0.4);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 16px;
            transition: all 0.18s ease;
            line-height: 1;
            color: #4f46e5;
        }
        .qb-select-overlay:hover .qb-checkbox {
            background: rgba(99,102,241,0.15);
            border-color: #4f46e5;
            transform: scale(1.1);
        }

        /* Selected card highlight */
        .question-card.qb-card-selected {
            outline: 3px solid #4f46e5;
            box-shadow: 0 0 0 5px rgba(79,70,229,0.18), 0 8px 24px rgba(79,70,229,0.15);
            transform: translateY(-2px);
        }
        .question-card.qb-card-selected .qb-checkbox {
            background: #4f46e5;
            border-color: #4f46e5;
            color: #fff;
        }

        /* Ensure cards are positioned relative for absolute children */
        .question-card {
            position: relative;
        }
    </style>
@endpush