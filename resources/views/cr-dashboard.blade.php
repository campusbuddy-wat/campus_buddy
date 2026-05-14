@extends('layouts.app')

@section('title', 'CR Dashboard - Campus Buddy')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/cr-dashboard.css') }}">
    <style>
        /* Modal Styles */
        .modal {
            display: none;
            position: fixed;
            z-index: 10000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0, 0, 0, 0.5);
            backdrop-filter: blur(5px);
            align-items: center;
            justify-content: center;
        }

        .modal-content {
            background-color: #fefefe;
            margin: auto;
            padding: 30px;
            border: none;
            width: 90%;
            max-width: 500px;
            border-radius: 20px;
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.2);
            animation: slideInDown 0.4s ease;
        }

        @keyframes slideInDown {
            from {
                transform: translateY(-30px);
                opacity: 0;
            }

            to {
                transform: translateY(0);
                opacity: 1;
            }
        }

        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
        }

        .modal-header h2 {
            margin: 0;
            color: #1b5c7a;
            font-size: 24px;
        }

        .close {
            color: #aaa;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
        }

        .close:hover {
            color: #333;
        }

        .form-group {
            margin-bottom: 15px;
        }

        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: 600;
            color: #555;
        }

        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 12px;
            border: 2px solid #edf2f7;
            border-radius: 10px;
            font-size: 14px;
            transition: all 0.3s;
        }

        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: #00AAFF;
            box-shadow: 0 0 0 4px rgba(0, 170, 255, 0.1);
        }

        .submit-btn {
            width: 100%;
            padding: 14px;
            background: #00AAFF;
            color: white;
            border: none;
            border-radius: 12px;
            font-weight: 700;
            font-size: 16px;
            cursor: pointer;
            transition: all 0.3s;
            margin-top: 10px;
        }

        .submit-btn:hover {
            background: #0088cc;
            transform: translateY(-2px);
        }
    </style>
@endpush

@section('content')
    <section class="cr-hero">
        <img src="{{ asset('images/community/dashboardBG.jpg') }}" alt="Campus" class="cr-hero-bg">
        <div class="cr-hero-overlay"></div>

        <a href="{{ route('dashboard') }}" class="back-btn-top-right">← Back to Dashboard</a>

        <div class="cr-hero-content animate-up">
            <span class="cr-hero-tag">CLASS REPRESENTATIVE PORTAL</span>
            <h1>Welcome to the CR Dashboard, <br><span>{{ Auth::user()->name ?? 'CR' }}!</span></h1>
            <p class="cr-hero-subtitle">Manage class schedules, announcements, and resources effectively.</p>
        </div>
    </section>

    @if(session('success'))
        <div class="alert-success">
            ✅ {{ session('success') }}
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
                transform: translateY(-10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
    </style>

    <!-- ================= CR QUICK ACTIONS ================= -->
    <section class="cr-cards reveal" id="crCards">
        <div class="cards-header">
            <h2>⚡ Quick Actions</h2>
        </div>

        <div class="cards-row">
            <div class="card">
                <h3>📢 Announcements</h3>
                <div class="card-box">Post new class announcement</div>
                <button onclick="openModal('announcementModal')">Create Post</button>
            </div>

            <div class="card">
                <h3>📅 Schedule</h3>
                <div class="card-box">Update class routine</div>
                <button onclick="openModal('scheduleModal')">Manage Schedule</button>
            </div>

            <div class="card">
                <h3>📝 ClassTasks</h3>
                <div class="card-box">Post assignment, quiz, or presentation</div>
                <button onclick="openModal('assignmentModal')">Create Task</button>
            </div>

            <div class="card">
                <h3>📚 Class Materials</h3>
                <div class="card-box">Upload PDF, PPTX, or DOCS for your class</div>
                <button onclick="openModal('materialModal')">Upload Materials</button>
            </div>
        </div>
    </section>

    <!-- ================= SCHEDULE MODAL ================= -->
    <div id="scheduleModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Manage Schedule</h2>
                <span class="close" onclick="closeModal('scheduleModal')">&times;</span>
            </div>
            <form action="{{ route('schedule.store') }}" method="POST">
                @csrf
                <div class="form-group">
                    <label for="course_code">Course Code</label>
                    <input type="text" name="course_code" id="course_code" placeholder="e.g. CSE 421" required>
                </div>
                <div class="form-group">
                    <label for="course_title">Course Title</label>
                    <input type="text" name="course_title" id="course_title" placeholder="e.g. Artificial Intelligence"
                        required>
                </div>
                <div class="form-group">
                    <label for="teacher_initial">Teacher Initial</label>
                    <input type="text" name="teacher_initial" id="teacher_initial" placeholder="e.g. AJ" required>
                </div>
                <div class="form-row" style="display: flex; gap: 15px;">
                    <div class="form-group" style="flex: 1;">
                        <label for="section">Section</label>
                        <input type="text" name="section" id="section" value="{{ Auth::user()->section }}" readonly
                            style="background: #f4f6f8; cursor: not-allowed;">
                    </div>
                    <div class="form-group" style="flex: 1;">
                        <label for="major">Major</label>
                        <input type="text" name="major" id="major" value="{{ Auth::user()->major }}" readonly
                            style="background: #f4f6f8; cursor: not-allowed;" placeholder="Non-Major">
                    </div>
                </div>
                <div class="form-group">
                    <label for="room_no">Room No</label>
                    <input type="text" name="room_no" id="room_no" placeholder="e.g. 713" required>
                </div>

                <div class="form-row" style="display: flex; gap: 15px;">
                    <div class="form-group" style="flex: 1;">
                        <label for="type">Course Type</label>
                        <select name="type" id="type" required onchange="toggleLabSection(this.value)">
                            <option value="theory">Theory</option>
                            <option value="lab">Lab</option>
                        </select>
                    </div>
                    <div class="form-group" id="lab_section_group" style="flex: 1; display: none;">
                        <label for="lab_section">Lab Section</label>
                        <input type="text" name="lab_section" id="lab_section" placeholder="e.g. B1, B2">
                    </div>
                </div>

                <div class="form-group">
                    <label for="day">Day</label>
                    <select name="day" id="day" required>
                        @foreach(['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'] as $day)
                            <option value="{{ $day }}">{{ $day }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label for="time_slot">Time Slot</label>
                    <select name="time_slot" id="time_slot" required>
                        <option value="8.30 am-10.00 am">8.30 am-10.00 am</option>
                        <option value="10.00 am-11.30 am">10.00 am-11.30 am</option>
                        <option value="11.30 am-1.00 pm">11.30 am-1.00 pm</option>
                        <option value="1.00 pm-2.30 pm">1.00 pm-2.30 pm</option>
                        <option value="2.30 pm-4.00 pm">2.30 pm-4.00 pm</option>
                        <option value="4.00 pm-5.30 pm">4.00 pm-5.30 pm</option>
                    </select>
                </div>
                <button type="submit" class="submit-btn">Save Schedule</button>
            </form>
        </div>
    </div>

    <!-- ================= ANNOUNCEMENT MODAL ================= -->
    <div id="announcementModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Post Announcement</h2>
                <span class="close" onclick="closeModal('announcementModal')">&times;</span>
            </div>
            <form action="{{ route('announcements.store') }}" method="POST">
                @csrf
                <div class="form-group">
                    <label for="title">Title</label>
                    <input type="text" name="title" id="title" placeholder="e.g. Class Cancelled" required>
                </div>
                <div class="form-group">
                    <label for="content">Content</label>
                    <textarea name="content" id="content" rows="4" placeholder="Details..." required></textarea>
                </div>
                <button type="submit" class="submit-btn">Post Announcement</button>
            </form>
        </div>
    </div>

    <!-- ================= CLASSTASK MODAL ================= -->
    <div id="assignmentModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2 id="taskModalTitle">Create ClassTask</h2>
                <span class="close" onclick="closeModal('assignmentModal')">&times;</span>
            </div>
            <form action="{{ route('assignments.store') }}" method="POST">
                @csrf
                <div class="form-group">
                    <label for="task_type">Task Type</label>
                    <select name="type" id="task_type" required onchange="updateTaskForm(this.value)">
                        <option value="assignment">Assignment</option>
                        <option value="quiz">Quiz</option>
                        <option value="presentation">Presentation</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="course_code_assign">Course Code</label>
                    <input type="text" name="course_code" id="course_code_assign" placeholder="e.g. CSE 421"
                        required>
                </div>

                <div class="form-group">
                    <label id="titleLabel" for="assign_title">Title</label>
                    <input type="text" name="title" id="assign_title"
                        placeholder="e.g. Artificial Intelligence Seminar" required>
                </div>

                <div class="form-group">
                    <label for="task_topic">Topic</label>
                    <input type="text" name="topic" id="task_topic" placeholder="e.g. Machine Learning Models...">
                </div>

                <div class="form-row" style="display: flex; gap: 15px;">
                    <div class="form-group" style="flex: 1;">
                        <label id="dateLabel" for="deadline">Due Date</label>
                        <input type="datetime-local" name="deadline" id="deadline" required>
                    </div>
                    <div class="form-group" id="durationGroup" style="flex: 1;">
                        <label id="durationLabel" for="duration_or_slot">Duration/Slot</label>
                        <input type="text" name="duration_or_slot" id="duration_or_slot"
                            placeholder="e.g. 1 hour or 2:00 PM - 2:30 PM">
                    </div>
                </div>

                <div class="form-group">
                    <label for="tip_1">Buddy Tip 1 (Key Focus)</label>
                    <textarea name="tip_1" id="tip_1" rows="2" placeholder="Expert advice..."></textarea>
                </div>

                <div class="form-group">
                    <label for="tip_2">Buddy Tip 2 (Pro Tip)</label>
                    <textarea name="tip_2" id="tip_2" rows="2" placeholder="Another tip..."></textarea>
                </div>

                <button type="submit" class="submit-btn" style="margin-top: 15px;">Save Task</button>
            </form>
        </div>
    </div>

    <!-- ================= MATERIAL MODAL ================= -->
    <div id="materialModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Upload Class Materials</h2>
                <span class="close" onclick="closeModal('materialModal')">&times;</span>
            </div>
            <form action="{{ route('materials.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="form-row" style="display: flex; gap: 15px;">
                    <div class="form-group" style="flex: 1;">
                        <label>Department</label>
                        <input type="text" value="{{ Auth::user()->department }}" readonly
                            style="background: #f4f6f8;">
                    </div>
                    <div class="form-group" style="flex: 1;">
                        <label>Batch</label>
                        <input type="text" value="{{ Auth::user()->batch }}" readonly style="background: #f4f6f8;">
                    </div>
                </div>
                <div class="form-group">
                    <label for="material_course_code">Course Code</label>
                    <input type="text" name="course_code" id="material_course_code" placeholder="e.g. CSE 421"
                        required>
                </div>
                <div class="form-group">
                    <label for="material_title">Material Title</label>
                    <input type="text" name="title" id="material_title" placeholder="e.g. Lecture 01 - Intro to AI"
                        required>
                </div>
                <div class="form-group">
                    <label for="material_file">Upload File (PDF, PPTX, DOCS - Max 64MB)</label>
                    <input type="file" name="file" id="material_file" accept=".pdf,.pptx,.docx,.doc" required
                        style="padding: 10px; border: 2px dashed #00AAFF; background: #f0faff;">
                </div>
                <button type="submit" class="submit-btn">Upload Material</button>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        function toggleLabSection(type) {
            const labGroup = document.getElementById('lab_section_group');
            const labInput = document.getElementById('lab_section');
            if (type === 'lab') {
                labGroup.style.display = 'block';
                labInput.required = true;
            } else {
                labGroup.style.display = 'none';
                labInput.required = false;
                labInput.value = '';
            }
        }

        function updateTaskForm(type) {
            const titleLabel = document.getElementById('titleLabel');
            const dateLabel = document.getElementById('dateLabel');
            const durationLabel = document.getElementById('durationLabel');
            const modalTitle = document.getElementById('taskModalTitle');

            if (type === 'quiz') {
                modalTitle.innerText = "Create Quiz";
                titleLabel.innerText = "Quiz Title";
                dateLabel.innerText = "Quiz Date";
                durationLabel.innerText = "Duration";
            } else if (type === 'presentation') {
                modalTitle.innerText = "Create Presentation";
                titleLabel.innerText = "Presentation Title";
                dateLabel.innerText = "Presentation Date";
                durationLabel.innerText = "Slot Time";
            } else {
                modalTitle.innerText = "Create Assignment";
                titleLabel.innerText = "Assignment Title";
                dateLabel.innerText = "Due Date";
                durationLabel.innerText = "Status/Progress";
            }
        }

        document.addEventListener('DOMContentLoaded', function () {
            // ================= SCROLL ANIMATIONS =================
            const animateObserver = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.classList.add('active');
                        const children = entry.target.querySelectorAll('.card');
                        children.forEach(child => child.classList.add('active'));
                    }
                });
            }, {
                threshold: 0.15
            });

            const crCards = document.querySelector('.cr-cards');
            if (crCards) animateObserver.observe(crCards);

            // ================= MODAL LOGIC =================
            window.openModal = function (id) {
                const modal = document.getElementById(id);
                if (modal) modal.style.display = "flex";
            };

            window.closeModal = function (id) {
                const modal = document.getElementById(id);
                if (modal) modal.style.display = "none";
            };

            window.onclick = function (event) {
                if (event.target.classList.contains('modal')) {
                    event.target.style.display = "none";
                }
            }
        });
    </script>
@endpush