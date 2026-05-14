@extends('layouts.app')

@section('title', 'Pdf & Notes | Campus Buddy')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/notes.css') }}">
    <link rel="stylesheet" href="{{ asset('css/buddy-card.css') }}">
@endpush

@section('content')
    <!-- ================= HERO BANNER ================= -->
    <section class="hero-banner">
        <img src="{{ asset('images/notes/notes_hero.png') }}" alt="Pdf & Notes" class="hero-bg">
        <div class="hero-overlay-dark"></div>

        <div class="hero-content-wrapper hero-text animate-up">
            <div class="hero-deco hero-deco-1"></div>
            <div class="hero-deco hero-deco-2"></div>
            <div class="hero-deco hero-deco-3"></div>
            <div class="hero-deco hero-deco-4"></div>

            <div class="hero-content">
                <span class="hero-date">{{ now()->format('F j, Y') }}</span>
                <span class="hero-tag">RESOURCES & MATERIALS</span>
                <h1 class="desktop-only">Access your <span>Pdf & Notes</span> <em>anytime, anywhere.</em></h1>
                <h1 class="mobile-only">Access your <span>Pdf & Notes</span></h1>

                <p class="hero-desc">
                    Your centralized repository for class materials, lecture slides, and student-contributed notes.
                    Stay organized and prepare for your exams with ease.
                </p>

                <div class="search-container">
                    <div class="filter-bar">
                        <div class="filter-input-group">
                            <input type="text" id="deptFilter" placeholder="Department">
                        </div>
                        <div class="filter-input-group">
                            <input type="text" id="courseFilter" placeholder="Course">
                        </div>
                        <div class="filter-input-group">
                            <input type="text" id="semesterFilter" placeholder="Semester">
                        </div>
                        <button class="search-btn" id="filterBtn">Search</button>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <div class="page-container">
    @if(session('success'))
        <div class="alert-success">
            ✅ {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="alert-error">
            ❌ {{ session('error') }}
        </div>
    @endif

    @if($errors->any())
        <div class="alert-error">
            ❌ Please correct the following errors:
            <ul>
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <style>
        .alert-success,
        .alert-error {
            margin: 20px 40px;
            padding: 15px 25px;
            border-radius: 10px;
            font-weight: 600;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
            animation: slideDown 0.5s ease;
            position: relative;
            z-index: 10;
        }

        .alert-success {
            background: #e6fffa;
            border-left: 5px solid #38b2ac;
            color: #2c7a7b;
        }

        .alert-error {
            background: #fff5f5;
            border-left: 5px solid #f56565;
            color: #c53030;
        }

        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Custom scrollbar for AI Response */
        #notesSummaryResponse::-webkit-scrollbar {
            width: 8px;
        }
        #notesSummaryResponse::-webkit-scrollbar-track {
            background: rgba(99, 102, 241, 0.05);
            border-radius: 4px;
        }
        #notesSummaryResponse::-webkit-scrollbar-thumb {
            background: rgba(99, 102, 241, 0.3);
            border-radius: 4px;
        }
        #notesSummaryResponse::-webkit-scrollbar-thumb:hover {
            background: rgba(99, 102, 241, 0.5);
        }
    </style>

    <!-- ================= SPLIT SECTION ================= -->
    <div class="notes-container">

        <!-- LEFT: CLASS MATERIALS (PDFs) -->
        <div class="resources-section pdf-section reveal">
            <div class="section-header">
                <div class="header-title">
                    <div class="icon-box pdf">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2">
                            <path d="M14.5 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7.5L14.5 2z" />
                            <polyline points="14 2 14 8 20 8" />
                        </svg>
                    </div>
                    <h2>Class Materials</h2>
                </div>
                <div style="display: flex; align-items: center; gap: 10px;">
                    @if(Auth::check() && in_array(Auth::user()->role, ['cr', 'admin']))
                    <button onclick="openModal('pdfUploadModal')" class="upload-trigger-btn pdf-upload-btn">
                        + Upload PDF
                    </button>
                    @endif
                    <span class="count">{{ $classMaterials->count() }} Files</span>
                </div>
            </div>
            <div class="resources-grid collapsed" id="pdfGrid">
                @foreach($classMaterials as $index => $material)
                    <div id="material-{{ $material->id }}" class="resource-card pdf-card animate-up" 
                         data-dept="{{ strtolower($material->department) }}"
                         data-course="{{ strtolower($material->course_code) }}"
                         style="animation-delay: {{ 0.1 * ($index + 1) }}s">
                        <div class="pdf-visual">
                            <div class="pdf-corner"></div>
                            <div class="pdf-logo">{{ strtoupper($material->file_extension) }}</div>
                            <div class="pdf-icon-symbol">
                                @if($material->file_extension == 'pdf')
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z" />
                                        <polyline points="14 2 14 8 20 8" />
                                    </svg>
                                @else
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <rect x="3" y="3" width="18" height="18" rx="2" ry="2" />
                                        <line x1="12" y1="8" x2="12" y2="16" />
                                        <line x1="8" y1="12" x2="16" y2="12" />
                                    </svg>
                                @endif
                            </div>
                        </div>
                        <div class="card-info">
                            <h3>{{ $material->title }}</h3>
                            <p>{{ $material->course_code }}</p>
                            <div class="card-meta-row">
                                <span class="size-badge">{{ strtoupper($material->file_extension) }}</span>
                                <div class="card-actions-mini">
                                    <button class="mini-ai-btn" 
                                        data-ai-title="{{ $material->title }}" 
                                        data-ai-course="{{ $material->course_code }}" 
                                        data-ai-dept="{{ $material->department }}" 
                                        data-ai-filetype="{{ $material->file_extension }}" 
                                        data-ai-type="class_material" 
                                        data-ai-filepath="{{ $material->file_path }}"
                                        onclick="summarizeFromBtn(this)" title="AI Summary">
                                        ✨
                                    </button>
                                    <a href="{{ asset('storage/' . $material->file_path) }}" target="_blank"
                                        class="mini-view-btn pdf">
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none"
                                            stroke="currentColor" stroke-width="2.5">
                                            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z" />
                                            <circle cx="12" cy="12" r="3" />
                                        </svg>
                                    </a>
                                    <a href="{{ asset('storage/' . $material->file_path) }}" download
                                        class="mini-dl-btn pdf">
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none"
                                            stroke="currentColor" stroke-width="2.5">
                                            <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4" />
                                            <polyline points="7 10 12 15 17 10" />
                                            <line x1="12" y1="15" x2="12" y2="3" />
                                        </svg>
                                    </a>
                                </div>
                            </div>
                        </div>
                        <div class="card-dept-info">
                            <span class="dept-badge">{{ $material->department }}</span>
                            <span class="batch-badge">Batch {{ $material->batch }}</span>
                        </div>
                    </div>
                @endforeach

                @if($classMaterials->isEmpty())
                    <div class="empty-resources">
                        <p>No class materials uploaded yet for your section.</p>
                    </div>
                @endif
            </div>
            <div class="view-more-container">
                <button class="view-more-btn" onclick="toggleGrid('pdfGrid', this)">
                    <span>View More</span>
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                        stroke-width="2.5">
                        <polyline points="6 9 12 15 18 9" />
                    </svg>
                </button>
            </div>
        </div>

        <!-- RIGHT: HAND NOTES -->
        <div class="resources-section notes-section reveal">
            <div class="section-header">
                <div class="header-title">
                    <div class="icon-box note">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2">
                            <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7" />
                            <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z" />
                        </svg>
                    </div>
                    <h2>Hand Notes</h2>
                </div>
                <div style="display: flex; align-items: center; gap: 10px;">
                    <button onclick="openModal('noteUploadModal')" class="upload-trigger-btn">
                        + Upload Note
                    </button>
                    <span class="count">{{ $handNotes->count() }} Files</span>
                </div>
            </div>

            <div class="resources-grid collapsed" id="notesGrid">
                @foreach($handNotes as $index => $material)
                    <div id="material-{{ $material->id }}" class="resource-card notebook-card animate-up" 
                         data-dept="{{ strtolower($material->department) }}"
                         data-course="{{ strtolower($material->course_code) }}"
                         style="animation-delay: {{ 0.1 * ($index + 1) }}s">
                        <div class="notebook-visual">
                            <div class="notebook-rings">
                                <span></span><span></span><span></span><span></span><span></span>
                            </div>
                            <div class="notebook-body">
                                <div class="notebook-text">{{ strtoupper($material->file_extension) }}</div>
                            </div>
                        </div>
                        <div class="card-info">
                            <h3>{{ $material->title }}</h3>
                            <p>{{ $material->course_code }}</p>
                            <div class="card-meta-row">
                                <span class="size-badge note">{{ strtoupper($material->file_extension) }}</span>
                                <div class="card-actions-mini">
                                    <button class="mini-ai-btn" 
                                        data-ai-title="{{ $material->title }}" 
                                        data-ai-course="{{ $material->course_code }}" 
                                        data-ai-dept="{{ $material->department }}" 
                                        data-ai-filetype="{{ $material->file_extension }}" 
                                        data-ai-type="hand_note" 
                                        data-ai-filepath="{{ $material->file_path }}"
                                        onclick="summarizeFromBtn(this)" title="AI Summary">
                                        ✨
                                    </button>
                                    <a href="{{ asset('storage/' . $material->file_path) }}" target="_blank"
                                        class="mini-view-btn note">
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none"
                                            stroke="currentColor" stroke-width="2.5">
                                            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z" />
                                            <circle cx="12" cy="12" r="3" />
                                        </svg>
                                    </a>
                                    <a href="{{ asset('storage/' . $material->file_path) }}" download
                                        class="mini-dl-btn note">
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none"
                                            stroke="currentColor" stroke-width="2.5">
                                            <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4" />
                                            <polyline points="7 10 12 15 17 10" />
                                            <line x1="12" y1="15" x2="12" y2="3" />
                                        </svg>
                                    </a>
                                </div>
                            </div>
                        </div>
                        <div class="card-dept-info">
                            <span class="dept-badge">{{ $material->department }}</span>
                            <span class="batch-badge">Batch {{ $material->batch }}</span>
                        </div>
                    </div>
                @endforeach

                @if($handNotes->isEmpty())
                    <div class="empty-resources">
                        <p>No hand notes uploaded yet.</p>
                    </div>
                @endif
            </div>
            <div class="view-more-container">
                <button class="view-more-btn" onclick="toggleGrid('notesGrid', this)">
                    <span>View More</span>
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                        stroke-width="2.5">
                        <polyline points="6 9 12 15 18 9" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- ================= AI NOTES SUMMARIZER ================= -->
    <div class="buddy-card-container" id="notesSummarizerSection">
        <div class="buddy-section reveal">
            <div class="buddy-card" style="cursor: default; text-align: left;">
                <h3>✨ AI Notes Summarizer</h3>
                <p style="margin-bottom: 10px; opacity: 0.85;">Click the ✨ button on any PDF or note above to get an AI-powered summary, key topics, and practice questions.</p>
                <div id="notesSummaryResponse" style="display:none; margin-top:12px; padding:18px; background:rgba(99,102,241,0.05); border-radius:12px; border:1px solid rgba(99,102,241,0.15); max-height: 500px; overflow-y: auto;">
                    <div id="notesSummaryTitle" style="font-weight:700; color:#4338ca; margin-bottom:16px; font-size:18px; border-bottom:1px solid rgba(99,102,241,0.2); padding-bottom:10px;"></div>
                    <div id="notesSummaryText" style="color:#334155; font-size:14px; line-height:1.7;"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- ================= PDF UPLOAD MODAL ================= -->
    <div id="pdfUploadModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Upload Class Material</h2>
                <span class="close" onclick="closeModal('pdfUploadModal')">&times;</span>
            </div>
            <form action="{{ route('materials.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="type" value="class_material">
                <div class="form-row">
                    <div class="form-group">
                        <label for="pdf_department">Department</label>
                        <input type="text" name="department" id="pdf_department" value="{{ auth()->user()->department }}" placeholder="e.g. CSE" required>
                    </div>
                    <div class="form-group">
                        <label for="pdf_batch">Batch</label>
                        <input type="text" name="batch" id="pdf_batch" value="{{ auth()->user()->batch }}" placeholder="e.g. 61" required>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="pdf_section">Section</label>
                        <input type="text" name="section" id="pdf_section" value="{{ auth()->user()->section }}" placeholder="e.g. A" required>
                    </div>
                    <div class="form-group">
                        <label for="pdf_course_code">Course Code</label>
                        <input type="text" name="course_code" id="pdf_course_code" placeholder="e.g. CSE 421" required>
                    </div>
                </div>
                <div class="form-group">
                    <label for="pdf_title">Material Title</label>
                    <input type="text" name="title" id="pdf_title" placeholder="e.g. Lecture 5 Slides" required>
                </div>
                <div class="form-group">
                    <label for="pdf_file">Upload File (PDF, PPTX, DOCS - Max 64MB)</label>
                    <input type="file" name="file" id="pdf_file" accept=".pdf,.pptx,.docx,.doc" required class="file-input">
                </div>
                <button type="submit" class="submit-btn">Upload Material</button>
            </form>
        </div>
    </div>

    <!-- ================= HAND NOTE UPLOAD MODAL ================= -->
    <div id="noteUploadModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Upload Hand Note</h2>
                <span class="close" onclick="closeModal('noteUploadModal')">&times;</span>
            </div>
            <form action="{{ route('materials.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="type" value="hand_note">
                <div class="form-row">
                    <div class="form-group">
                        <label for="note_department">Department</label>
                        <input type="text" name="department" id="note_department" value="{{ auth()->user()->department }}" placeholder="e.g. CSE" required>
                    </div>
                    <div class="form-group">
                        <label for="note_batch">Batch</label>
                        <input type="text" name="batch" id="note_batch" value="{{ auth()->user()->batch }}" placeholder="e.g. 61" required>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="note_section">Section</label>
                        <input type="text" name="section" id="note_section" value="{{ auth()->user()->section }}" placeholder="e.g. A" required>
                    </div>
                    <div class="form-group">
                        <label for="note_course_code">Course Code</label>
                        <input type="text" name="course_code" id="note_course_code" placeholder="e.g. CSE 421" required>
                    </div>
                </div>
                <div class="form-group">
                    <label for="note_title">Note Title</label>
                    <input type="text" name="title" id="note_title" placeholder="e.g. Chapter 3 Summary" required>
                </div>
                <div class="form-group">
                    <label for="note_file">Upload File (PDF, PPTX, DOCS - Max 64MB)</label>
                    <input type="file" name="file" id="note_file" accept=".pdf,.pptx,.docx,.doc" required class="file-input">
                </div>
                <button type="submit" class="submit-btn">Upload Note</button>
            </form>
        </div>
    </div>
    </div> {{-- End page-container --}}
@endsection

@push('scripts')
    <script>
        // openModal and closeModal are defined globally in topbar.blade.php
        // They use classList.add/remove('show')

        window.addEventListener('click', function (event) {
            if (event.target.classList && event.target.classList.contains('modal')) {
                event.target.classList.remove('show');
            }
        })

        function toggleGrid(gridId, btn) {
            const grid = document.getElementById(gridId);
            const span = btn.querySelector('span');
            const svg = btn.querySelector('svg');

            grid.classList.toggle('collapsed');

            if (grid.classList.contains('collapsed')) {
                span.textContent = 'View More';
                svg.style.transform = 'rotate(0deg)';
            } else {
                span.textContent = 'Show Less';
                svg.style.transform = 'rotate(180deg)';
            }
        }

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
                    }
                });
            }, observerOptions);

            document.querySelectorAll('.reveal').forEach(el => {
                observer.observe(el);
            });

            // ================= SEARCH FILTERING LOGIC =================
            const deptFilter = document.getElementById('deptFilter');
            const courseFilter = document.getElementById('courseFilter');
            const semesterFilter = document.getElementById('semesterFilter');
            const filterBtn = document.getElementById('filterBtn');
            const allCards = document.querySelectorAll('.resource-card');

            function applyFilters() {
                const deptVal = deptFilter.value.toLowerCase().trim();
                const courseVal = courseFilter.value.toLowerCase().trim();
                const semVal = semesterFilter.value.toLowerCase().trim();

                allCards.forEach(card => {
                    const cardDept = card.getAttribute('data-dept') || "";
                    const cardCourse = card.getAttribute('data-course') || "";
                    
                    let isMatch = true;

                    if (deptVal && !cardDept.includes(deptVal)) isMatch = false;
                    if (courseVal && !cardCourse.includes(courseVal)) isMatch = false;

                    if (isMatch) {
                        card.classList.remove('hidden-search');
                    } else {
                        card.classList.add('hidden-search');
                    }
                });

                updateSectionVisiblity();
            }

            function updateSectionVisiblity() {
                const sections = [
                    { gridId: 'pdfGrid', countClass: '.pdf-section .count' },
                    { gridId: 'notesGrid', countClass: '.notes-section .count' }
                ];

                sections.forEach(section => {
                    const grid = document.getElementById(section.gridId);
                    const allCardsInSection = grid.querySelectorAll('.resource-card');
                    const visibleCards = grid.querySelectorAll('.resource-card:not(.hidden-search)');
                    const emptyMsg = grid.querySelector('.search-empty');
                    
                    if (visibleCards.length === 0) {
                        if (!emptyMsg) {
                            const msg = document.createElement('div');
                            msg.className = 'empty-resources search-empty';
                            msg.innerHTML = '<p>No results match your search filters.</p>';
                            grid.appendChild(msg);
                        } else {
                            emptyMsg.style.display = 'block';
                        }
                    } else if (emptyMsg) {
                        emptyMsg.style.display = 'none';
                    }

                    // Update count display
                    const countEl = document.querySelector(section.countClass);
                    if (countEl) {
                        countEl.textContent = `${visibleCards.length} ${visibleCards.length === 1 ? 'File' : 'Files'}`;
                    }
                });
            }

            filterBtn.addEventListener('click', applyFilters);

            // Optional: Live filtering as user types
            [deptFilter, courseFilter, semesterFilter].forEach(input => {
                input.addEventListener('keyup', (e) => {
                    if (e.key === 'Enter') applyFilters();
                });
            });
        });
    </script>

    <!-- AI Notes Summarizer Script -->
    <script>
        function summarizeFromBtn(btn) {
            summarizeNote({
                title: btn.dataset.aiTitle,
                course_code: btn.dataset.aiCourse,
                department: btn.dataset.aiDept,
                file_type: btn.dataset.aiFiletype,
                type: btn.dataset.aiType,
                file_path: btn.dataset.aiFilepath
            });
        }

        async function summarizeNote(materialData) {
            const responseBox = document.getElementById('notesSummaryResponse');
            const titleEl = document.getElementById('notesSummaryTitle');
            const textEl = document.getElementById('notesSummaryText');
            
            responseBox.style.display = 'block';
            titleEl.textContent = `📝 Summarizing: ${materialData.title}`;
            textEl.innerHTML = '<span style="opacity:0.6;">Generating AI summary... This may take a moment. ✨</span>';
            
            // Scroll to the response
            responseBox.scrollIntoView({ behavior: 'smooth', block: 'center' });

            try {
                const res = await fetch('/api/ai/summarize-notes', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
                    },
                    body: JSON.stringify(materialData)
                });

                const data = await res.json();
                let html = (data.response || 'Could not generate summary.')
                    .replace(/\*\*([^*]+)\*\*/g, '<strong>$1</strong>')
                    .replace(/### (.+)/g, '<h4 style="margin:10px 0 6px;color:#4f46e5;">$1</h4>')
                    .replace(/## (.+)/g, '<h4 style="margin:12px 0 6px;color:#4f46e5;">$1</h4>')
                    .replace(/# (.+)/g, '<h3 style="margin:14px 0 8px;color:#6366f1;">$1</h3>')
                    .replace(/- (.+)/g, '• $1')
                    .replace(/\d+\. /g, (match) => `<br>${match}`)
                    .replace(/\n/g, '<br>');

                titleEl.textContent = `✨ Summary: ${materialData.title} (${materialData.course_code})`;
                textEl.innerHTML = html;
            } catch (e) {
                textEl.innerHTML = '<span style="color:#f87171;">Could not connect to AI. Please try again. 🔄</span>';
            }
        }
    </script>

    <style>
        .mini-ai-btn {
            width: 26px;
            height: 26px;
            border-radius: 6px;
            border: 1px solid rgba(167,139,250,0.3);
            background: rgba(167,139,250,0.12);
            cursor: pointer;
            font-size: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.2s ease;
            padding: 0;
        }
        .mini-ai-btn:hover {
            background: rgba(167,139,250,0.3);
            transform: scale(1.1);
            border-color: rgba(167,139,250,0.6);
        }
    </style>
@endpush