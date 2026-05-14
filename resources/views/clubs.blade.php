@extends('layouts.app')

@section('title', 'University Clubs | Campus Buddy')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/clubs.css') }}">
@endpush

@section('content')
<!-- ================= HERO SECTION ================= -->
<section class="hero-banner">
    <img src="{{ asset('images/clubs/hero_bg.png') }}" alt="University Clubs" class="hero-bg">
    <div class="hero-overlay-dark"></div>

    <div class="hero-content-wrapper hero-text animate-up">
        <div class="hero-deco hero-deco-1"></div>
        <div class="hero-deco hero-deco-2"></div>
        <div class="hero-deco hero-deco-3"></div>
        <div class="hero-deco hero-deco-4"></div>

        <div class="hero-content">
            <span class="hero-date">{{ now()->format('F j, Y') }}</span>
            <span class="hero-tag">EXTRACURRICULAR ACTIVITIES</span>
            <h1>Explore & Join <span>University Clubs.</span></h1>
            <p class="hero-desc">Connect with students who share your passions and build lasting friendships outside the classroom.</p>
        </div>
    </div>
</section>

<div class="clubs-page">
    <div class="page-container mt-10">

            <!-- ================= CLUBS GRID DIRECTORY ================= -->
            <section id="explore-clubs" class="clubs-section">
                <div class="section-header reveal">
                    <div class="section-title">
                        <h2>Explore Organizations</h2>
                        <p>Find and join clubs happening right now on campus.</p>
                    </div>
                    <div class="club-filters">
                        <button class="filter-btn active" data-filter="all">All Clubs</button>
                        <button class="filter-btn" data-filter="tech">Technology</button>
                        <button class="filter-btn" data-filter="arts">Arts & Culture</button>
                        <button class="filter-btn" data-filter="sports">Sports</button>
                        <button class="filter-btn" data-filter="academic">Academic</button>
                    </div>
                </div>

                <div class="clubs-grid">
                    @forelse($clubs as $club)
                    <div class="club-card reveal" data-category="{{ $club->type }}">
                        {{-- Glowing accent strip --}}
                        <div class="club-accent-strip"></div>
                        
                        {{-- Logo showcase - shows full image --}}
                        <div class="club-logo-showcase">
                            <div class="club-logo-glow"></div>
                            <img src="{{ Str::startsWith($club->image_path, 'http') ? $club->image_path : asset('storage/' . $club->image_path) }}" 
                                 alt="{{ $club->name }}">
                        </div>

                        {{-- Category badge --}}
                        <span class="club-category-badge">{{ ucfirst($club->type) }}</span>

                        {{-- Card body --}}
                        <div class="club-body">
                            <div class="club-info">
                                <h3>{{ $club->name }}</h3>
                                <p>{{ Str::limit($club->description, 100) }}</p>
                            </div>
                            <div class="club-action">
                                <div class="club-members-badge">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                                        <circle cx="9" cy="7" r="4"/>
                                        <path d="M23 21v-2a4 4 0 0 0-3-3.87"/>
                                        <path d="M16 3.13a4 4 0 0 1 0 7.75"/>
                                    </svg>
                                    <span>{{ rand(50, 500) }}+ members</span>
                                </div>
                                @if($club->website_link)
                                <a href="{{ $club->website_link }}" target="_blank" class="club-visit-btn">
                                    Visit
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                        <line x1="7" y1="17" x2="17" y2="7"/>
                                        <polyline points="7 7 17 7 17 17"/>
                                    </svg>
                                </a>
                                @endif
                            </div>
                        </div>
                    </div>
                    @empty
                    <p style="color:var(--text-muted); padding: 20px;">No clubs uploaded by Admin yet.</p>
                    @endforelse
                </div>
            </section>

            <!-- ================= CREATE A CLUB BANNER ================= -->
            <section id="create-club" class="create-club-banner reveal">
                <div class="cc-container">
                    <div class="cc-decor"></div>
                    <div class="cc-content">
                        <h2>Can't find a club for your interest?</h2>
                        <p>Start a new student organization! Gather at least 10 members, draft a charter, and apply to
                            become an officially recognized campus club.</p>
                    </div>
                    <div class="cc-action">
                        <a href="#" class="btn-primary">Coming Soon</a>
                    </div>
                </div>
            </section>

        </main>
    </div>

@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Reveal Animations using Intersection Observer
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

            // Filtering Logic
            const filterBtns = document.querySelectorAll('.filter-btn');
            const clubCards = document.querySelectorAll('.club-card');

            filterBtns.forEach(btn => {
                btn.addEventListener('click', () => {
                    // Remove active class from all buttons
                    filterBtns.forEach(b => b.classList.remove('active'));
                    // Add active class to clicked button
                    btn.classList.add('active');

                    const filterValue = btn.getAttribute('data-filter');

                    clubCards.forEach(card => {
                        if (filterValue === 'all' || card.getAttribute('data-category') === filterValue) {
                            card.style.display = 'flex';
                            // Re-trigger animation
                            setTimeout(() => {
                                card.classList.remove('active');
                                setTimeout(() => card.classList.add('active'), 50);
                            }, 10);
                        } else {
                            card.style.display = 'none';
                        }
                    });
                });
            });

            // Handle #sports-club hash from community page Sports Association link
            if (window.location.hash === '#sports-club') {
                // Find and click the Sports filter button
                const sportsFilterBtn = document.querySelector('.filter-btn[data-filter="sports"]');
                if (sportsFilterBtn) {
                    sportsFilterBtn.click();
                }

                // Scroll to the clubs section and highlight sports cards
                setTimeout(() => {
                    const clubsSection = document.getElementById('explore-clubs');
                    if (clubsSection) {
                        clubsSection.scrollIntoView({ behavior: 'smooth', block: 'start' });
                    }

                    // Add highlight animation to all visible sports cards
                    setTimeout(() => {
                        const sportsCards = document.querySelectorAll('.club-card[data-category="sports"]');
                        sportsCards.forEach((card, index) => {
                            setTimeout(() => {
                                card.classList.add('highlighted');
                            }, index * 150); // Stagger the highlight for each card
                        });

                        // Remove highlight class after animation completes
                        setTimeout(() => {
                            sportsCards.forEach(card => {
                                card.classList.remove('highlighted');
                            });
                        }, 5000);
                    }, 600);
                }, 300);
            }
        });
    </script>
@endpush