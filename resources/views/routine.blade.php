@php
  // Helper to get class for a specific day and time slot
  $days = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
  $timeSlots = ['8.30 am-10.00 am', '10.00 am-11.30 am', '11.30 am-1.00 pm', '1.00 pm-2.30 pm', '2.30 pm-4.00 pm', '4.00 pm-5.30 pm'];
  
  if (!function_exists('normalizeTimeSlot')) {
      function normalizeTimeSlot($slot) {
          if (empty($slot)) return '';
          $slot = strtolower(trim($slot));
          $slot = str_replace(':', '.', $slot);
          $slot = preg_replace('/\b0(\d)/', '$1', $slot);
          $slot = str_replace(' ', '', $slot);
          $slot = str_replace(' - ', '-', $slot);
          return $slot;
      }
  }

  $scheduleMap = [];
  foreach($schedules as $s) {
      $dayKey = strtolower(trim($s->day));
      $slotKey = normalizeTimeSlot($s->time_slot);
      $scheduleMap[$dayKey][$slotKey] = $s;
  }
  
  if (!function_exists('parseRoutineTime')) {
    function parseRoutineTime($timeStr, $now) {
        $timeStr = str_replace('.', ':', trim($timeStr));
        try {
            return \Carbon\Carbon::parse($timeStr);
        } catch (\Exception $e) {
            // Fallback for old format
            $h_m = explode(':', $timeStr);
            $h = (int)$h_m[0]; $m = isset($h_m[1]) ? (int)$h_m[1] : 0;
            if ($h >= 1 && $h <= 7) $h += 12;
            return $now->copy()->setTime($h, $m, 0);
        }
    }
  }

  // Today's schedule for sidebar
  $todayName = now()->format('l');
  $todaysClasses = $schedules->filter(function($item) use ($todayName) {
      return strtolower(trim($item->day)) === strtolower($todayName);
  })
    ->unique(function ($item) {
        return $item->course_title . $item->time_slot;
    })
    ->sortBy(function($s) {
        return parseRoutineTime(explode('-', $s->time_slot)[0], now());
    });

  if (!function_exists('getClassStatus')) {
      function getClassStatus($timeSlot, $dayName) {
          $now = now();
          $today = $now->format('l');
          
          if ($today !== $dayName) return null;

          $parts = explode('-', $timeSlot);
          if(count($parts) < 2) return 'Upcoming';
          
          $startTime = parseRoutineTime($parts[0], $now);
          $endTime = parseRoutineTime($parts[1], $now);
          
          if ($now->greaterThan($endTime)) return 'Completed';
          if ($now->between($startTime, $endTime)) return 'Ongoing';
          return 'Upcoming';
      }
  }
@endphp

@extends('layouts.app')

@section('title', 'Class Routine - Campus Buddy')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/routine.css') }}">
    <link rel="stylesheet" href="{{ asset('css/buddy-card.css') }}">
@endpush

@section('content')
      <section class="hero-banner">
        <img src="{{ asset('images/routine/hero.png') }}" alt="Campus" class="hero-bg">
        <div class="hero-overlay"></div>

        <div class="hero-content-wrapper hero-text animate-up">
            <div class="hero-deco hero-deco-1"></div>
            <div class="hero-deco hero-deco-2"></div>
            <div class="hero-deco hero-deco-3"></div>
            <div class="hero-deco hero-deco-4"></div>

            <div class="hero-content">
                <span class="hero-date">{{ now()->format('F j, Y') }}</span>
                <span class="hero-tag">STAY ON TRACK</span>
                <h1>Your Class <span>Routine</span></h1>
                <p class="hero-desc">Organize your academic life with your personalized weekly schedule.</p>
            </div>
        </div>
      </section>

      <!-- ================= MAIN LAYOUT — using universal grid ================= -->
      <section class="routine-main page-container" id="routineMain">

        <!-- LEFT SIDEBAR: TODAY'S SCHEDULE -->
        <aside class="today-sidebar">
          <div class="sidebar-header">
            <h2>Today's Schedule</h2>
            <span class="badge">Today</span>
          </div>

          <div class="mini-timeline">
            @forelse($todaysClasses as $class)
            @php $status = getClassStatus($class->time_slot, $todayName); @endphp
            <div class="mini-card {{ strtolower($status) }}">
              <div class="mini-time">{{ str_replace('-', ' - ', $class->time_slot) }}</div>
              <div class="mini-details">
                <div class="status-indicator-dot"></div>
                <h4>{{ $class->course_title }}{{ $class->lab_section ? ' ('.$class->lab_section.')' : '' }} {{
                  $class->section ? '('.$class->section.')' : '' }}</h4>
                <p>Room {{ $class->room_no }} • {{ $class->teacher_initial }}</p>
                <span class="mini-status-tag">{{ $status }}</span>
              </div>
            </div>
            @empty
            <div class="mini-card break">
              <div class="mini-details">
                <h4>No classes today!</h4>
                <p>Enjoy your break.</p>
              </div>
            </div>
            @endforelse
          </div>
        </aside>

        <!-- RIGHT SIDE: WEEKLY ROUTINE & TABS -->
        <div class="weekly-schedule">

          <div class="schedule-header">
            <div class="day-tabs">
              @foreach(['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'] as $day)
              <button class="day-tab {{ strtolower($todayName) == $day ? 'active' : '' }}" data-day="{{ $day }}">{{
                ucfirst(substr($day, 0, 3)) }}</button>
              @endforeach
            </div>
            <button class="download-btn" id="viewFullBtn">
              <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                <polyline points="7 10 12 15 17 10"></polyline>
                <line x1="12" y1="15" x2="12" y2="3"></line>
              </svg>
              Full Routine
            </button>

            <button class="doc-download-btn" id="downloadPdfBtn">
              <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                <polyline points="7 10 12 15 17 10"></polyline>
                <line x1="12" y1="15" x2="12" y2="3"></line>
              </svg>
              Download PDF
            </button>
          </div>

          <div class="schedule-timeline" id="routineTimeline">
            <!-- Day Groups -->
            @foreach(['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'] as $day)
            <div class="day-group {{ $todayName == $day ? 'active' : '' }}" id="group-{{ strtolower($day) }}">
              <h3 class="day-heading">{{ $day }}</h3>

              @php $dayClasses = $schedules->filter(function($item) use ($day) {
                  return strtolower(trim($item->day)) === strtolower($day);
              })
              ->unique(function ($item) {
              return $item->course_title . $item->time_slot;
              })
              ->sortBy(function($s) {
              return parseRoutineTime(explode('-', $s->time_slot)[0], now());
              }); @endphp

              @forelse($dayClasses as $class)
              @php $status = getClassStatus($class->time_slot, $day); @endphp
              <div class="class-card {{ strtolower($status) }}">
                <div class="class-time">
                  <span class="time-range">({{ str_replace('-', ' - ', $class->time_slot) }})</span>
                </div>
                <div class="class-details">
                  <div class="class-header-row">
                    <h3 class="subject">{{ $class->course_title }}</h3>
                    @if($status)
                    <span class="status-badge {{ strtolower($status) }}">{{ $status }}</span>
                    @endif
                  </div>
                  <p class="instructor">Instructor: {{ $class->teacher_initial }} {{ $class->major ? '• '.$class->major
                    : '' }}</p>
                  <div class="class-meta">
                    <span class="venue">Room {{ $class->room_no }}</span>
                    <span class="type type-lecture">{{ $class->course_code }}{{ $class->lab_section ?
                      '('.$class->lab_section.')' : '' }} ({{ $class->section }})</span>
                  </div>
                </div>

                @if(auth()->id() === $class->user_id || auth()->user()->role === 'admin')
                <div class="class-actions">
                  <button onclick="openEditModal({{ json_encode($class) }})" class="action-btn edit-btn">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                      <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                      <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                    </svg>
                    Edit
                  </button>
                  <form action="{{ route('schedule.destroy', $class) }}" method="POST"
                    onsubmit="return confirm('Are you sure you want to delete this class?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="action-btn delete-btn">
                      <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                        stroke-width="2">
                        <polyline points="3 6 5 6 21 6"></polyline>
                        <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                      </svg>
                      Delete
                    </button>
                  </form>
                </div>
                @endif
              </div>
              @empty
              <div class="class-card break-card">
                <div class="class-time"><span class="time-start">Off Day</span></div>
                <div class="class-details">
                  <h3 class="subject">No Classes</h3>
                  <p>Relax and recharge!</p>
                </div>
              </div>
              @endforelse
            </div>
            @endforeach
          </div>

          <!-- ================= FULL WEEK TABLE VIEW ================= -->
          <div class="full-routine-table" id="fullRoutineTable">
            <div class="full-table-header">
              <h3>Class Routine | {{ auth()->user()->department }} | Sec: {{ auth()->user()->section }} | Batch: {{
                auth()->user()->batch }}</h3>
            </div>
            <div class="table-scroll-container">
              <table>
                <thead>
                  <tr>
                    <th class="col-time">Time</th>
                    @foreach($days as $day)
                    <th>{{ $day }}</th>
                    @endforeach
                  </tr>
                </thead>
                <tbody>
                  @foreach($timeSlots as $slot)
                  <tr>
                    <td class="col-time">{{ $slot }}</td>
                    @foreach($days as $day)
                    <td>
                      @php
                        $dayKey = strtolower(trim($day));
                        $slotKey = normalizeTimeSlot($slot);
                      @endphp
                      @if(isset($scheduleMap[$dayKey][$slotKey]))
                      @php $class = $scheduleMap[$dayKey][$slotKey]; @endphp
                      <div class="table-class">
                        <strong>{{ $class->course_code }}{{ $class->lab_section ?
                          '('.$class->lab_section.')' : '' }} ({{ $class->section
                          }})</strong><br>
                        <span>{{ $class->teacher_initial }}</span>
                        <small>Room: {{ $class->room_no }}</small>
                      </div>
                      @endif
                    </td>
                    @endforeach
                  </tr>
                  @endforeach
                </tbody>
              </table>
            </div>
          </div>

        </div>
      </section>

      @if(auth()->user()->role === 'cr' || auth()->user()->role === 'admin')
      <!-- ================= SMART ROUTINE SYNC & IMPORTER ================= -->
      <div class="buddy-card-container" id="smartRoutineImporterSection">
        <div class="buddy-section reveal">
          <div class="buddy-card" style="cursor: default; text-align: left; background: linear-gradient(135deg, rgba(99, 102, 241, 0.08), rgba(118, 75, 162, 0.08)); border: 1px solid rgba(99, 102, 241, 0.25);">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
              <h3 style="margin: 0; color: #4f46e5;">✨ AI OCR Routine Importer</h3>
              <span class="badge" style="background: rgba(99, 102, 241, 0.15); color: #4f46e5; padding: 4px 10px; border-radius: 12px; font-size: 11px; font-weight: 600;">CR & Admin Only</span>
            </div>
            <p style="margin-bottom: 16px; opacity: 0.85; font-size: 14px;">Upload any official DIU Master Routine PDF, Excel sheet, or a screenshot. The Llama 3.3 model will automatically parse, clean, and sync classes strictly matching your student profile (<b>Dept:</b> {{ auth()->user()->department }}, <b>Batch:</b> {{ auth()->user()->batch }}, <b>Sec:</b> {{ auth()->user()->section }}).</p>
            
            <div style="background: rgba(255,255,255,0.4); padding: 18px; border-radius: 12px; border: 1px solid rgba(255,255,255,0.5);">
              <h4 style="margin: 0 0 8px 0; font-size: 15px; color: #334155;">📄 AI OCR Routine Upload</h4>
              <p style="font-size: 13px; opacity: 0.75; margin-bottom: 14px;">Select your 6-7 page master routine PDF or schedule image to automatically overwrite and update your profile schedule.</p>
              
              <div style="display: flex; gap: 8px; flex-wrap: wrap;">
                <input type="file" id="routineFile" accept=".pdf,.png,.jpg,.jpeg" style="display: none;" onchange="handleFileSelected(this)">
                <button onclick="document.getElementById('routineFile').click()" id="uploadBtn" style="flex: 1; min-width: 150px; padding: 10px; border-radius: 8px; border: 1px dashed #4f46e5; background: rgba(99,102,241,0.05); color: #4f46e5; font-weight: 600; cursor: pointer; font-size: 13px; transition: all 0.2s;">
                  Choose file...
                </button>
                <button onclick="triggerFileImport()" id="importBtn" style="padding: 10px 24px; border-radius: 8px; border: none; background: #764ba2; color: #fff; font-weight: 600; cursor: pointer; font-size: 13px; transition: all 0.2s;" disabled>
                  Import ✨
                </button>
              </div>
              <div id="selectedFileName" style="font-size: 12px; color: #4f46e5; font-weight: 500; margin-top: 8px; word-break: break-all;"></div>
            </div>

            <!-- Status Indicator -->
            <div id="importerStatus" style="display: none; margin-top: 14px; padding: 12px; border-radius: 10px; font-size: 13px; font-weight: 500;"></div>
          </div>
        </div>
      </div>
      @endif

      <!-- ================= AI ROUTINE ADVISOR ================= -->
      <div class="buddy-card-container" id="routineAdvisorSection">
        <div class="buddy-section reveal">
          <div class="buddy-card" style="cursor: default; text-align: left;">
            <h3>✨ AI Routine Advisor</h3>
            <p style="margin-bottom: 12px; opacity: 0.85;">Ask Buddy to analyze your schedule, find free slots, or plan your study time.</p>
            
            <div style="display: flex; flex-wrap: wrap; gap: 8px; margin-bottom: 14px;">
              <button class="routine-ai-pill" onclick="askRoutineAI('Generate a personalized study routine for me based on my upcoming classes, tasks, and events.')">🎯 Personalized Routine</button>
              <button class="routine-ai-pill" onclick="askRoutineAI('What is my next class today?')">📍 Next class</button>
              <button class="routine-ai-pill" onclick="askRoutineAI('Find my free time slots today for studying')">🕐 Free time today</button>
              <button class="routine-ai-pill" onclick="askRoutineAI('Which day is the heaviest and which is lightest?')">⚖️ Load analysis</button>
            </div>
            
            <div style="display: flex; gap: 8px;">
              <input type="text" id="routineAiInput" placeholder="Ask anything about your routine..." 
                     style="flex:1; padding:10px 14px; border-radius:10px; border:1px solid rgba(99,102,241,0.2); background:rgba(99,102,241,0.05); color:#334155; font-size:14px; outline:none;" 
                     onkeypress="if(event.key==='Enter') askRoutineAI(this.value)">
              <button onclick="askRoutineAI(document.getElementById('routineAiInput').value)" 
                      style="padding:10px 18px; border-radius:10px; border:none; background:linear-gradient(135deg,#667eea,#764ba2); color:#fff; font-weight:600; cursor:pointer; font-size:14px; white-space:nowrap;">
                Ask ✨
              </button>
            </div>
            
            <div id="routineAiResponse" style="display:none; margin-top:14px; padding:14px; background:rgba(99,102,241,0.05); border-radius:12px; border:1px solid rgba(99,102,241,0.1);">
              <div id="routineAiText" style="color:#334155; font-size:14px; line-height:1.7;"></div>
            </div>
          </div>
        </div>
      </div>

  @if(auth()->user()->role === 'cr' || auth()->user()->role === 'admin')
  <!-- ================= EDIT SCHEDULE MODAL ================= -->
  <div id="editScheduleModal" class="routine-modal">
    <div class="modal-content">
      <div class="modal-header">
        <h2>Edit Schedule</h2>
        <span class="close-modal" onclick="closeEditModal()">&times;</span>
      </div>
      <form id="editScheduleForm" method="POST">
        @csrf
        @method('PUT')
        <div class="form-group-grid">
          <div class="form-field">
            <label>Course Code</label>
            <input type="text" name="course_code" id="edit_course_code" required>
          </div>
          <div class="form-field">
            <label>Course Title</label>
            <input type="text" name="course_title" id="edit_course_title" required>
          </div>
          <div class="form-field">
            <label>Instructor</label>
            <input type="text" name="teacher_initial" id="edit_teacher_initial" required>
          </div>
          <div class="form-field">
            <label>Room No</label>
            <input type="text" name="room_no" id="edit_room_no" required>
          </div>
          <div class="form-field">
            <label>Day</label>
            <select name="day" id="edit_day" required>
              @foreach(['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'] as $day)
              <option value="{{ $day }}">{{ $day }}</option>
              @endforeach
            </select>
          </div>
          <div class="form-field">
            <label>Time Slot</label>
            <select name="time_slot" id="edit_time_slot" required>
              <option value="8.30 am-10.00 am">8.30 am-10.00 am</option>
              <option value="10.00 am-11.30 am">10.00 am-11.30 am</option>
              <option value="11.30 am-1.00 pm">11.30 am-1.00 pm</option>
              <option value="1.00 pm-2.30 pm">1.00 pm-2.30 pm</option>
              <option value="2.30 pm-4.00 pm">2.30 pm-4.00 pm</option>
              <option value="4.00 pm-5.30 pm">4.00 pm-5.30 pm</option>
            </select>
          </div>
          <div class="form-field">
            <label>Type</label>
            <select name="type" id="edit_type" required onchange="toggleEditLabSection(this.value)">
              <option value="theory">Theory</option>
              <option value="lab">Lab</option>
            </select>
          </div>
          <div class="form-field" id="edit_lab_section_group" style="display: none;">
            <label>Lab Section</label>
            <input type="text" name="lab_section" id="edit_lab_section" placeholder="e.g. B1, B2">
          </div>
          <div class="form-field">
            <label>Section</label>
            <input type="text" name="section" id="edit_section" readonly
              style="background: #f4f6f8; cursor: not-allowed;">
          </div>
          <div class="form-field">
            <label>Major</label>
            <input type="text" name="major" id="edit_major" readonly style="background: #f4f6f8; cursor: not-allowed;">
          </div>
        </div>
        <button type="submit" class="save-btn">Update Schedule</button>
      </form>
    </div>
  </div>

  <script>
    function toggleEditLabSection(type) {
      const labGroup = document.getElementById('edit_lab_section_group');
      const labInput = document.getElementById('edit_lab_section');
      if (type === 'lab') {
        labGroup.style.display = 'block';
      } else {
        labGroup.style.display = 'none';
        labInput.value = '';
      }
    }
  </script>
  @endif

@endsection

@push('scripts')
  <script>
    document.addEventListener('DOMContentLoaded', function () {

      // ================= ELEMENT REFS =================
      const tabs            = document.querySelectorAll('.day-tab');
      const groups          = document.querySelectorAll('.day-group');
      const viewFullBtn     = document.getElementById('viewFullBtn');
      const routineTimeline = document.getElementById('routineTimeline');
      const fullRoutineTable= document.getElementById('fullRoutineTable');
      const downloadPdfBtn  = document.getElementById('downloadPdfBtn');
      const dayTabsContainer= document.querySelector('.day-tabs');
      let isFullView = false;

      const FULL_ROUTINE_SVG = `<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="7 10 12 15 17 10"></polyline><line x1="12" y1="15" x2="12" y2="3"></line></svg> Full Routine`;

      // ================= DAY TAB SWITCHING =================
      function showDayGroup(dayName) {
        groups.forEach(g => {
          if (g.id === 'group-' + dayName) {
            g.style.display = 'block';
            setTimeout(() => g.classList.add('active'), 10);
          } else {
            g.classList.remove('active');
            g.style.display = 'none';
          }
        });
      }

      tabs.forEach(tab => {
        tab.addEventListener('click', function () {
          // If full view is open, switch back to tab view first
          if (isFullView) {
            isFullView = false;
            viewFullBtn.innerHTML = FULL_ROUTINE_SVG;
            viewFullBtn.classList.remove('active');
            downloadPdfBtn.style.display = 'none';
            routineTimeline.style.display = 'flex';
            fullRoutineTable.style.display = 'none';
            dayTabsContainer.style.display = 'flex';
            document.body.style.overflowX = '';
          }
          tabs.forEach(t => t.classList.remove('active'));
          this.classList.add('active');
          showDayGroup(this.getAttribute('data-day'));
        });
      });

      // ================= FULL ROUTINE TOGGLE =================
      viewFullBtn.addEventListener('click', function () {
        const routineMain = document.getElementById('routineMain');
        if (!isFullView) {
          // Show full table
          isFullView = true;
          this.innerHTML = 'Hide Routine';
          this.classList.add('active');
          routineMain.classList.add('full-view-active');
          downloadPdfBtn.style.display = 'inline-flex';
          dayTabsContainer.style.display = 'none';
          routineTimeline.style.display = 'none';
          fullRoutineTable.style.display = 'block';
          // Lock horizontal page scroll — table handles left-right scrolling internally
          document.body.style.overflowX = 'hidden';
        } else {
          // Return to tab view
          isFullView = false;
          this.innerHTML = FULL_ROUTINE_SVG;
          this.classList.remove('active');
          routineMain.classList.remove('full-view-active');
          downloadPdfBtn.style.display = 'none';
          fullRoutineTable.style.display = 'none';
          dayTabsContainer.style.display = 'flex';
          routineTimeline.style.display = 'flex';
          // Restore horizontal page scroll
          document.body.style.overflowX = '';
          // Re-show current active day
          const activeTab = document.querySelector('.day-tab.active');
          if (activeTab) showDayGroup(activeTab.getAttribute('data-day'));
        }
      });

      // ================= INITIAL STATE =================
      const activeTab = document.querySelector('.day-tab.active');
      if (activeTab) showDayGroup(activeTab.getAttribute('data-day'));

      // ================= PDF DOWNLOAD =================
      downloadPdfBtn.addEventListener('click', () => window.print());



      // ================= CR EDIT MODAL =================
      window.openEditModal = function (classData) {
        const modal = document.getElementById('editScheduleModal');
        const form = document.getElementById('editScheduleForm');

        // Update form action URL
        form.action = `/schedule/${classData.id}`;

        // Populate fields
        document.getElementById('edit_course_code').value = classData.course_code;
        document.getElementById('edit_course_title').value = classData.course_title;
        document.getElementById('edit_teacher_initial').value = classData.teacher_initial;
        document.getElementById('edit_room_no').value = classData.room_no;
        document.getElementById('edit_day').value = classData.day;
        document.getElementById('edit_time_slot').value = classData.time_slot;
        document.getElementById('edit_section').value = classData.section;
        document.getElementById('edit_major').value = classData.major || '';
        document.getElementById('edit_type').value = classData.type || 'theory';
        document.getElementById('edit_lab_section').value = classData.lab_section || '';

        // Trigger visibility toggle
        toggleEditLabSection(classData.type || 'theory');

        modal.style.display = 'block';
      };

      window.closeEditModal = function () {
        document.getElementById('editScheduleModal').style.display = 'none';
      };

      // Close on outside click
      window.addEventListener('click', function (event) {
        const modal = document.getElementById('editScheduleModal');
        if (event.target == modal) {
          modal.style.display = 'none';
        }
      });
    });

    // ==================== SMART ROUTINE IMPORTER ====================
    let selectedFile = null;

    window.handleFileSelected = function(input) {
      if (input.files && input.files[0]) {
        selectedFile = input.files[0];
        document.getElementById('selectedFileName').innerText = 'Selected: ' + selectedFile.name;
        document.getElementById('importBtn').removeAttribute('disabled');
      }
    };


    window.triggerFileImport = async function() {
      if (!selectedFile) return;

      const btn = document.getElementById('importBtn');
      const statusBox = document.getElementById('importerStatus');
      
      btn.setAttribute('disabled', 'true');
      btn.innerText = 'Parsing...';

      statusBox.style.display = 'block';
      statusBox.style.background = 'rgba(118,75,162,0.08)';
      statusBox.style.color = '#764ba2';
      statusBox.style.border = '1px solid rgba(118,75,162,0.15)';
      statusBox.innerHTML = '⌛ Uploading file and running Llama 3.3 AI OCR extraction...';

      const formData = new FormData();
      formData.append('file', selectedFile);

      try {
        const res = await fetch('/api/routine/parse-file', {
          method: 'POST',
          headers: {
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
          },
          body: formData
        });

        const data = await res.json();

        if (data.success) {
          statusBox.style.background = 'rgba(16,185,129,0.08)';
          statusBox.style.color = '#10b981';
          statusBox.style.border = '1px solid rgba(16,185,129,0.15)';
          statusBox.innerHTML = `✅ ${data.message} Reloading routine...`;
          setTimeout(() => window.location.reload(), 1500);
        } else {
          throw new Error(data.error || 'Parsing failed.');
        }
      } catch (e) {
        statusBox.style.background = 'rgba(239,68,68,0.08)';
        statusBox.style.color = '#ef4444';
        statusBox.style.border = '1px solid rgba(239,68,68,0.15)';
        statusBox.innerHTML = '❌ ' + (e.message || 'AI extraction failed. Ensure file is clear and readable.');
        btn.removeAttribute('disabled');
        btn.innerText = 'Import ✨';
      }
    };

    // ==================== AI ROUTINE ADVISOR ====================
    async function askRoutineAI(message) {
      if (!message || !message.trim()) return;
      
      const responseBox = document.getElementById('routineAiResponse');
      const responseText = document.getElementById('routineAiText');
      const input = document.getElementById('routineAiInput');
      
      responseBox.style.display = 'block';
      responseText.innerHTML = '<span style="opacity:0.6;">🤔 Analyzing your schedule...</span>';
      if (input) input.value = '';

      try {
        const res = await fetch('/api/ai/routine-advisor', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
          },
          body: JSON.stringify({ message: message.trim() })
        });
        
        const data = await res.json();
        let html = (data.response || 'Unable to analyze your routine right now.')
          .replace(/\*\*([^*]+)\*\*/g, '<strong>$1</strong>')
          .replace(/### (.+)/g, '<h4 style="margin:8px 0 4px;color:#4f46e5;">$1</h4>')
          .replace(/## (.+)/g, '<h4 style="margin:10px 0 4px;color:#4f46e5;">$1</h4>')
          .replace(/- (.+)/g, '• $1')
          .replace(/\n/g, '<br>');
        
        responseText.innerHTML = html;
      } catch (e) {
        responseText.innerHTML = '<span style="color:#f87171;">Could not connect to AI. Please try again. 🔄</span>';
      }
    }
  </script>

  <style>
    .routine-ai-pill {
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
    .routine-ai-pill:hover {
      background: rgba(102,126,234,0.3);
      border-color: rgba(102,126,234,0.6);
      transform: translateY(-1px);
    }
  </style>
@endpush