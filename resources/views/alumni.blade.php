@extends('layouts.app')

@section('title', 'Alumni Network | Campus Buddy')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/alumni.css') }}">
@endpush

@section('content')
    <!-- ================= HERO SECTION ================= -->
    <section class="hero-banner reveal active">
        <img src="{{ asset('images/alumni/Alumni_BG.png') }}" alt="Alumni Background" class="hero-bg">
        {{-- Decorative dots matching dashboard --}}
        <div class="hero-deco hero-deco-1"></div>
        <div class="hero-deco hero-deco-2"></div>
        <div class="hero-deco hero-deco-3"></div>
        <div class="hero-deco hero-deco-4"></div>
        <div class="hero-overlay"></div>

        <div class="hero-content-wrapper hero-text animate-up">
            <div class="hero-left">
                <span class="hero-date">{{ now()->format('F j, Y') }}</span>
                <span class="hero-tag animate-item up stagger-1">START YOUR BRIGHT CAREER</span>
                <h1 class="animate-item up stagger-2">Now learning from anywhere, and build your <span>bright
                        career.</span></h1>
                <p class="hero-desc animate-item up stagger-3">Connect with a global network of professionals who started
                    exactly where you are. Get mentorship, job alerts, and industry insights from Campus Buddy
                    alumni.</p>
                <a href="https://alumni.daffodilvarsity.edu.bd/" target="_blank" rel="noopener noreferrer" class="hero-btn animate-item up stagger-4 pulse">Explore Network</a>
            </div>
            <div class="hero-right">
                <div class="hero-collage">
                    <div class="collage-item collage-item-1">
                        <img src="{{ asset('images/alumni/alumni_hero-section_image1.jpg') }}" alt="Alumni in New York">
                        <span class="collage-label">New York</span>
                    </div>
                    <div class="collage-item collage-item-2">
                        <img src="{{ asset('images/alumni/alumni_hero-section_image2.png') }}" alt="Graduation Day">
                        <span class="collage-label">Convocation</span>
                    </div>
                    <div class="collage-item collage-item-3">
                        <img src="{{ asset('images/alumni/alumni_hero-section_image3.png') }}" alt="Alumni in Sydney">
                        <span class="collage-label">Sydney</span>
                    </div>
                    <div class="collage-item collage-item-4">
                        <img src="{{ asset('images/alumni/alumni_hero-section_image4.png') }}" alt="Alumni in Germany">
                        <span class="collage-label">Germany</span>
                    </div>
                    <div class="collage-center-badge floating">
                        <span class="count">1,235</span>
                        <span class="label">Alumni</span>
                    </div>
                </div>
            </div>
        </div>
    </section>
    
    <div class="page-container">
    {{-- Status Messages --}}
    <div class="message-container message-container-offset">
        @if($pendingRegistration)
            <div class="registration-status-banner pending animate-up">
                <div class="status-icon-wrapper">
                    <div class="status-icon pending">
                        <i class="fas fa-clock"></i>
                    </div>
                    <div class="status-info">
                        <h4>Registration Pending Approval</h4>
                        <p>Your application as an alumni mentor is currently being reviewed by our administration. Once approved, your profile will be live in the network.</p>
                    </div>
                </div>
                <div class="status-badge pending">Under Review</div>
            </div>
        @endif

        @if($justApproved)
            <div id="approvalToast" class="registration-status-banner approved animate-up">
                <div class="status-icon-wrapper">
                    <div class="status-icon approved">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div class="status-info">
                        <h4>Congratulations, {{ auth()->user()->name }}!</h4>
                        <p>Your alumni registration has been approved. You are now officially a part of our premier mentor network.</p>
                    </div>
                </div>
                <div class="status-badge approved">Just Approved</div>
            </div>
        @endif
    </div>

    <!-- ================= ALUMNI NETWORK SECTION ================= -->
    <section id="alumni-network" class="alumni-header-section reveal">
        <div class="section-title-row">
            <div class="section-title animate-item left stagger-1">
                <h2>Alumni <span>Network</span> of Campus Buddy</h2>
            </div>
            <div class="network-stats-row animate-item right stagger-1">
                <div class="stat-bubble">
                    <i class="fas fa-globe-americas"></i>
                    <div class="stat-text">
                        <strong>12+</strong>
                        <span>Countries</span>
                    </div>
                </div>
                <div class="stat-bubble">
                    <i class="fas fa-building"></i>
                    <div class="stat-text">
                        <strong>50+</strong>
                        <span>Companies</span>
                    </div>
                </div>
                <div class="stat-bubble highlight">
                    <i class="fas fa-user-check"></i>
                    <div class="stat-text">
                        <strong>1.2k+</strong>
                        <span>Mentors</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="filter-container animate-item up stagger-2">
            <button class="scroll-btn prev" id="scrollPrev"><i class="fas fa-chevron-left"></i></button>
            <div class="category-filters" id="categoryFilters">
                <a href="#" class="filter-tag active" data-filter="all">All Categories</a>
                <a href="#" class="filter-tag" data-filter="journalism">Journalism</a>
                <a href="#" class="filter-tag" data-filter="bba">BBA</a>
                <a href="#" class="filter-tag" data-filter="pharmacy">Pharmacy</a>
                <a href="#" class="filter-tag" data-filter="nfe">NFE</a>
                <a href="#" class="filter-tag" data-filter="textile">Textile</a>
                <a href="#" class="filter-tag" data-filter="bcs-govt">BCS/Govt</a>
                <a href="#" class="filter-tag" data-filter="software-engineering">Software Engineering</a>
                <a href="#" class="filter-tag" data-filter="data-science">Data Science</a>
                <a href="#" class="filter-tag" data-filter="marketing">Marketing</a>
                <a href="#" class="filter-tag" data-filter="finance">Finance</a>
                <a href="#" class="filter-tag" data-filter="uiux-design">UI/UX Design</a>
                <a href="#" class="filter-tag" data-filter="cyber-security">Cyber Security</a>
                <a href="#" class="filter-tag" data-filter="digital-marketing">Digital Marketing</a>
                <a href="#" class="filter-tag" data-filter="cloud-architecture">Cloud Architecture</a>
                <a href="#" class="filter-tag" data-filter="ecommerce">E-Commerce</a>
                <a href="#" class="filter-tag" data-filter="ai-engineering">AI Engineering</a>
                <a href="#" class="filter-tag" data-filter="management">Management</a>
            </div>
            <button class="scroll-btn next" id="scrollNext"><i class="fas fa-chevron-right"></i></button>
        </div>
    </section>

    <!-- ================= ALUMNI GRID ================= -->
    <div class="alumni-grid reveal">
        @foreach($approvedAlumni as $alumni)
            <x-alumni-card :alumni="$alumni" />
        @endforeach
    </div>

    <div class="load-more-container">
        <button id="loadMoreBtn" class="see-more-btn">See More <i class="fas fa-chevron-down"></i></button>
    </div>

    <!-- ================= JOIN CTA SECTION ================= -->
    <section class="join-mentor-section reveal">
        <div class="cta-box new-cta-design">
            <!-- Decorative top right yellow dashes -->
            <div class="cta-decor top-right animate-item scale stagger-2">
                <svg width="60" height="45" viewBox="0 0 60 45" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path
                        d="M8 15L15 12M22 9L29 7M36 5L43 4M5 26L12 23M19 20L26 18M33 16L40 15M3 37L10 34M16 31L23 29"
                        stroke="#FAC35A" stroke-width="3.5" stroke-linecap="round" />
                </svg>
            </div>
            <!-- Decorative bottom left yellow dashes -->
            <div class="cta-decor bottom-left animate-item scale stagger-2">
                <svg width="50" height="40" viewBox="0 0 50 40" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path
                        d="M5 8L12 11M18 13L25 15M31 17L38 18M3 19L10 22M16 24L23 26M29 27L36 29M2 30L9 33M14 35L21 37"
                        stroke="#FAC35A" stroke-width="3.5" stroke-linecap="round" />
                </svg>
            </div>

            <div class="cta-content">
                <h4 class="animate-item left stagger-1">Become An Alumni Mentor</h4>
                <h2 class="animate-item left stagger-2">
                    You can join with Campus Buddy <br>
                    as a <span class="highlight-text">mentor?<svg class="curved-underline" viewBox="0 0 160 15"
                            fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M2 13C40 4 100 2 158 8" stroke="#00AAFF" stroke-width="3.5"
                                stroke-linecap="round" />
                        </svg></span>
                </h2>
            </div>

            <div class="cta-arrow animate-item right stagger-3">
                <svg width="120" height="60" viewBox="0 0 120 60" fill="none"
                    xmlns="http://www.w3.org/2000/svg">
                    <path d="M2 58C35 55 65 35 115 15M102 12L118 13L110 26" stroke="#00AAFF" stroke-width="2.5"
                        stroke-linecap="round" stroke-linejoin="round" />
                </svg>
            </div>

            <div class="cta-action animate-item right stagger-4">
                <a href="#" id="registerTodayBtn" class="cta-btn new-cta-btn pulse-primary">Register Today</a>
            </div>
        </div>
    </section>

    <!-- ================= ALUMNI REGISTRATION MODAL ================= -->
    <div id="registrationModal" class="alumni-modal">
        <div class="modal-content">
            <span id="closeModal" class="close-btn">&times;</span>
            <h2>Alumni <span>{{ $isAlumni ? 'Information' : 'Registration' }}</span></h2>
            @if($isAlumni)
                <p class="edit-info-note">You are already a registered alumnus. You can update your information below or delete your card if you wish.</p>
            @endif
            
            <form action="{{ route('alumni.register') }}" method="POST" enctype="multipart/form-data">
                @csrf

                @if($errors->any())
                    <div class="error-list error-list-container">
                        <ul>
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
                <div class="form-grid">
                    <div class="input-group">
                        <label>Full Name *</label>
                        <input type="text" name="full_name" value="{{ $existingRegistration->full_name ?? auth()->user()->name }}" required>
                    </div>
                    <div class="input-group">
                        <label>Email *</label>
                        <input type="email" name="email" value="{{ $existingRegistration->email ?? auth()->user()->email }}" required>
                    </div>
                </div>

                <div class="form-grid">
                    <div class="input-group">
                        <label>Student ID *</label>
                        <input type="text" name="student_id" value="{{ $existingRegistration->student_id ?? auth()->user()->student_id }}" required>
                    </div>
                    <div class="input-group">
                        <label>Phone</label>
                        <input type="text" name="phone" value="{{ $existingRegistration->phone ?? (auth()->user()->phone ?? '') }}">
                    </div>
                </div>

                <div class="form-grid">
                    <div class="input-group">
                        <label>Department *</label>
                        <input type="text" name="department" value="{{ $existingRegistration->department ?? auth()->user()->department }}" required placeholder="e.g. CSE">
                    </div>
                    <div class="input-group">
                        <label>Batch *</label>
                        <input type="text" name="batch" value="{{ $existingRegistration->batch ?? auth()->user()->batch }}" required placeholder="e.g. 52">
                    </div>
                </div>

                <div class="form-grid">
                    <div class="input-group">
                        <label>Graduation Year *</label>
                        <input type="text" name="graduation_year" value="{{ $existingRegistration->graduation_year ?? '' }}" required placeholder="e.g. 2020">
                    </div>
                    <div class="input-group">
                        <label>Linkedin URL</label>
                        <input type="url" name="linkedin_url" value="{{ $existingRegistration->linkedin_url ?? '' }}" placeholder="https://">
                    </div>
                </div>

                <div class="input-group">
                    <label>Select Category *</label>
                    <select name="category" required>
                        <option value="software-engineering" {{ ($existingRegistration->category ?? '') == 'software-engineering' ? 'selected' : '' }}>Software Engineering</option>
                        <option value="data-science" {{ ($existingRegistration->category ?? '') == 'data-science' ? 'selected' : '' }}>Data Science</option>
                        <option value="marketing" {{ ($existingRegistration->category ?? '') == 'marketing' ? 'selected' : '' }}>Marketing</option>
                        <option value="finance" {{ ($existingRegistration->category ?? '') == 'finance' ? 'selected' : '' }}>Finance</option>
                        <option value="journalism" {{ ($existingRegistration->category ?? '') == 'journalism' ? 'selected' : '' }}>Journalism</option>
                        <option value="bba" {{ ($existingRegistration->category ?? '') == 'bba' ? 'selected' : '' }}>BBA</option>
                    </select>
                </div>

                <div class="form-grid">
                    <div class="input-group">
                        <label>Current Position *</label>
                        <input type="text" name="current_position" value="{{ $existingRegistration->current_position ?? '' }}" required placeholder="e.g. Software Engineer">
                    </div>
                    <div class="input-group">
                        <label>Company *</label>
                        <input type="text" name="company" value="{{ $existingRegistration->company ?? '' }}" required placeholder="e.g. Google">
                    </div>
                </div>

                <div class="form-grid form-grid-mb">
                    <div class="input-group">
                        <label>Profile Image</label>
                        <input type="file" name="profile_image" class="input-file-small">
                    </div>
                    <div class="input-group">
                        <label>Company Logo</label>
                        <input type="file" name="company_logo" class="input-file-small">
                    </div>
                </div>

                <div class="form-actions">
                    <button type="submit" class="submit-btn">{{ $isAlumni ? 'Update My Info' : 'Submit for Approval' }}</button>
                    
                    @if($existingRegistration)
                        <button type="button" class="delete-alumni-btn" onclick="confirmDeleteAlumni()">Delete My Card</button>
                    @endif
                </div>
            </form>

            @if($existingRegistration)
                <form id="deleteAlumniForm" action="{{ route('alumni.destroy', $existingRegistration->id) }}" method="POST" style="display: none;">
                    @csrf
                    @method('DELETE')
                </form>
            @endif
        </div>
    </div>

    </div> {{-- End page-container --}}

    @if(session('success'))
        <div class="success-toast-notification">
            {{ session('success') }}
        </div>
    @endif

    @push('scripts')
    <script>
        // Pass Blade data to external JS via data attributes
        document.body.dataset.hasErrors = '{{ $errors->any() ? "true" : "false" }}';
        document.body.dataset.registrationStatus = '{{ $pendingRegistration ? "pending" : ($isAlumni ? "approved" : "none") }}';
        document.body.dataset.registrationLabel = '{{ $pendingRegistration ? "Application Pending" : ($isAlumni ? "Manage Alumni Card" : "Register Today") }}';
    </script>
    <script src="{{ asset('js/alumni.js') }}"></script>
    @endpush

@endsection
