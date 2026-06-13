@extends('layouts.guest')

@section('title', 'Campus Buddy - Your AI University Companion')

@push('styles')
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="{{ asset('css/landing.css') }}">
@endpush

@section('content')
    <!-- Navigation -->
    @include('includes.topbar')

    <!-- Global Character Container -->
    <div id="character-container" class="character-container">
        <img src="{{ asset('assets/landing/character.png') }}" alt="Campus Buddy Character" id="mascot">
    </div>

    <!-- Scrollable Story Sections -->
    <main class="story-container">

        <!-- Section 1: Introduction -->
        <section class="story-section" id="section-intro">
            <div class="section-content box-left intro-content">
                <div class="glass-card">
                    <span class="badge">Meet your buddy</span>
                    <h1>Welcome to <span class="highlight">Campus Buddy</span></h1>
                    <p>Your AI-based companion for daily university life. We are here to guide you, answer questions, and help you navigate through your academic journey smoothly.</p>
                </div>
            </div>
        </section>

        <!-- Section 2: Problems -->
        <section class="story-section" id="section-problems">
            <div class="section-content box-right problem-content">
                <div class="glass-card problem-card">
                    <span class="badge badge-warning">The Challenge</span>
                    <h2>Navigating University is Hard</h2>
                    <ul class="problem-list">
                        <li><i class="icon-cross"></i> Lacking a reliable friend network</li>
                        <li><i class="icon-cross"></i> Struggling to find class materials or news</li>
                        <li><i class="icon-cross"></i> Difficulty connecting with alumni or associations</li>
                        <li><i class="icon-cross"></i> Unanswered queries for newcomers</li>
                    </ul>
                </div>
            </div>
        </section>

        <!-- Section 3: Solutions -->
        <section class="story-section" id="section-solutions">
            <div class="section-content box-left solution-content">
                <div class="glass-card solution-card">
                    <span class="badge badge-success">The Solution</span>
                    <h2>Here to Guide You</h2>
                    <ul class="solution-list">
                        <li><i class="icon-check"></i> Instant AI chatbot for newcomers</li>
                        <li><i class="icon-check"></i> Proper roadmaps and resources for existing students</li>
                        <li><i class="icon-check"></i> Connect, network, and grow effortlessly</li>
                        <li><i class="icon-check"></i> 24/7 Support from your Campus Buddy</li>
                    </ul>
                </div>
            </div>
        </section>

        <!-- Section 4: Call to Action -->
        <section class="story-section cta-section" id="section-cta">
            <div class="section-content cta-content centered">
                <div class="glass-card cta-card">
                    <h2>Ready to start your journey?</h2>
                    <p>Join thousands of students who have already found their perfect companion.</p>
                    <a href="{{ route('signup') }}" class="btn btn-primary btn-large cta-btn">Let's be my friend buddy!</a>
                </div>
            </div>
        </section>

    </main>
@endsection

@push('scripts')
    <!-- GSAP and ScrollTrigger -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.2/gsap.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.2/ScrollTrigger.min.js"></script>
    <!-- Custom JS -->
    <script src="{{ asset('js/landing.js') }}"></script>
@endpush