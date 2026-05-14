@extends('layouts.app')

@section('title', 'Campus Buddy | Meet With Talents')

@push('styles')
    <style>
        .talents-hero {
            background: linear-gradient(135deg, #182848 0%, #4b6cb7 100%);
            color: white;
            padding: 80px 35px 60px;
            text-align: center;
            border-radius: 0 0 40px 40px;
            margin-bottom: 40px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }
        
        .talents-hero h1 {
            font-size: 42px;
            font-weight: 800;
            margin-bottom: 15px;
        }

        .talents-hero p {
            font-size: 16px;
            color: #d1d5db;
            max-width: 600px;
            margin: 0 auto;
        }

        .talents-grid {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 30px;
            padding: 0 35px 60px;
        }

        .id-card {
            width: 320px;
            background: #0f7632; /* Primary Dark Green */
            border-radius: 20px;
            position: relative;
            overflow: hidden;
            box-shadow: 0 15px 35px rgba(0,0,0,0.15), 0 5px 15px rgba(0,0,0,0.05);
            font-family: 'Inter', sans-serif;
            color: white;
            transition: transform 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            display: flex;
            flex-direction: column;
        }

        .id-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 25px 45px rgba(15, 118, 50, 0.25);
        }

        .card-top-bg {
            background: white;
            height: 190px;
            position: relative;
        }

        .lanyard-hole {
            position: absolute;
            top: 15px;
            left: 50%;
            transform: translateX(-50%);
            width: 50px;
            height: 12px;
            background: #e2e8f0;
            border-radius: 10px;
            box-shadow: inset 0 2px 5px rgba(0,0,0,0.1);
            z-index: 10;
        }

        .id-avatar-wrap {
            position: absolute;
            top: 60px;
            left: 50%;
            transform: translateX(-50%);
            width: 120px;
            height: 120px;
            border-radius: 50%;
            border: 4px solid #16a34a;
            overflow: hidden;
            z-index: 5;
            background: white;
            box-shadow: 0 8px 20px rgba(0,0,0,0.2);
        }

        .id-avatar-wrap img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .wave {
            position: absolute;
            bottom: -5px;
            left: 0;
            width: 100%;
            z-index: 1;
        }

        .card-body {
            padding: 35px 25px 25px;
            text-align: center;
            display: flex;
            flex-direction: column;
            flex-grow: 1;
            z-index: 3;
            background: #0f7632;
        }

        .card-name {
            font-size: 24px;
            font-weight: 900;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin: 0 0 5px;
            color: #ffffff;
        }

        .card-designation {
            font-size: 14px;
            font-weight: 500;
            color: #86efac; /* Light green text */
            margin: 0 0 15px;
        }

        .card-divider {
            width: 80%;
            height: 1px;
            background: rgba(255,255,255,0.2);
            margin: 0 auto 20px;
        }

        .card-details {
            text-align: left;
            font-size: 11px;
            line-height: 1.8;
            font-weight: 500;
            margin-bottom: 20px;
        }

        .detail-row {
            display: flex;
            margin-bottom: 6px;
        }

        .detail-label {
            width: 65px;
            color: #bbf7d0;
        }
        
        .detail-colon {
            width: 15px;
            color: #bbf7d0;
        }

        .detail-value {
            flex: 1;
            word-break: break-word;
        }

        .card-footer {
            margin-top: auto;
            display: flex;
            justify-content: flex-end;
            align-items: flex-end;
        }

        .qr-code {
            width: 60px;
            height: 60px;
            background: white;
            padding: 4px;
            border-radius: 4px;
        }

        .qr-code img {
            width: 100%;
            height: 100%;
        }

    <!-- SVG abstracts -->
        .bottom-wave {
            position: absolute;
            bottom: 0;
            left: 0;
            width: 150%;
            height: auto;
            z-index: 1;
            opacity: 0.4;
            pointer-events: none;
        }

        /* Modal Styles */
        .modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
            backdrop-filter: blur(5px);
            z-index: 100;
            display: none;
            justify-content: center;
            align-items: center;
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .modal-overlay.active {
            display: flex;
            opacity: 1;
        }

        .modal-content {
            background: white;
            padding: 30px;
            border-radius: 16px;
            width: 90%;
            max-width: 500px;
            box-shadow: 0 20px 50px rgba(0,0,0,0.2);
            transform: scale(0.95);
            transition: transform 0.3s ease;
            max-height: 90vh;
            overflow-y: auto;
        }

        .modal-overlay.active .modal-content {
            transform: scale(1);
        }

        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .modal-header h3 {
            font-size: 22px;
            font-weight: 800;
            color: #1A202C;
            margin: 0;
        }

        .close-modal {
            background: none;
            border: none;
            font-size: 24px;
            color: #A0AEC0;
            cursor: pointer;
        }

        .form-group {
            margin-bottom: 15px;
        }

        .form-group label {
            display: block;
            font-size: 13px;
            font-weight: 700;
            color: #4A5568;
            margin-bottom: 5px;
        }

        .form-group input, .form-group textarea {
            width: 100%;
            padding: 10px 12px;
            border: 1px solid #E2E8F0;
            border-radius: 8px;
            font-size: 14px;
            outline: none;
            transition: border-color 0.2s;
        }

        .form-group input:focus, .form-group textarea:focus {
            border-color: #16a34a;
        }
    </style>
@endpush

@section('content')
    <section class="talents-hero">
        <h1>Meet With Talents</h1>
        <p>Connect with the brightest minds on campus. Get mentored, collaborate on projects, and expand your skills with our top talents.</p>
        <button id="open-talent-modal" style="margin-top: 25px; padding: 12px 28px; background: #16a34a; color: white; border: none; border-radius: 25px; font-weight: 700; cursor: pointer; box-shadow: 0 5px 15px rgba(22, 163, 74, 0.4); text-transform: uppercase; letter-spacing: 1px; transition: transform 0.2s;">Become a Talent</button>
    </section>

    @if(session('success'))
        <div style="background: #c6f6d5; color: #22c55e; padding: 15px; border-radius: 8px; margin: 0 35px 25px; text-align: center; font-weight: 600;">
            {{ session('success') }}
        </div>
    @endif

    <div style="margin: 0 35px 40px; display: flex; justify-content: center; gap: 20px; flex-wrap: wrap;">
        <div class="search-wrap" style="position: relative; width: 100%; max-width: 250px;">
            <i class="fas fa-university" style="position: absolute; left: 16px; top: 50%; transform: translateY(-50%); color: #A0AEC0; font-size: 14px;"></i>
            <input type="text" id="talent-dept-search" placeholder="Filter by Dept (e.g. CSE)" style="width: 100%; padding: 12px 15px 12px 42px; border-radius: 30px; border: 1.5px solid #E2E8F0; font-size: 14px; outline: none; background: white; box-shadow: 0 4px 12px rgba(0,0,0,0.04); transition: all 0.3s ease;">
        </div>
        <div class="search-wrap" style="position: relative; width: 100%; max-width: 300px;">
            <i class="fas fa-star" style="position: absolute; left: 16px; top: 50%; transform: translateY(-50%); color: #A0AEC0; font-size: 14px;"></i>
            <input type="text" id="talent-skill-search" placeholder="Filter by Skill (starts with...)" style="width: 100%; padding: 12px 15px 12px 42px; border-radius: 30px; border: 1.5px solid #E2E8F0; font-size: 14px; outline: none; background: white; box-shadow: 0 4px 12px rgba(0,0,0,0.04); transition: all 0.3s ease;">
        </div>
    </div>

    <div class="talents-grid">
        @forelse($talents as $talent)
            <div class="id-card" 
                 data-dept="{{ strtolower($talent->user->department ?? '') }}" 
                 data-skill="{{ strtolower($talent->designation ?? '') }}">
                
                <div class="card-top-bg">
                    <div class="lanyard-hole"></div>
                    
                    <div class="id-avatar-wrap">
                        @if($talent->user->profile_image)
                            <img src="{{ asset('storage/' . $talent->user->profile_image) }}" alt="{{ $talent->user->name }}" onerror="this.src='https://ui-avatars.com/api/?name={{ urlencode($talent->user->name) }}&background=16a34a&color=fff'">
                        @else
                            <img src="https://ui-avatars.com/api/?name={{ urlencode($talent->user->name) }}&background=16a34a&color=fff" alt="{{ $talent->user->name }}">
                        @endif
                    </div>

                    <!-- Double Wave SVG -->
                    <svg class="wave" viewBox="0 0 320 120" xmlns="http://www.w3.dom/svg" style="bottom: 0;">
                        <!-- Light green wave -->
                        <path fill="#16a34a" d="M0,64L40,58.7C80,53,160,43,240,53.3C320,64,400,96,440,112L480,128L480,120L440,120C400,120,320,120,240,120C160,120,80,120,40,120L0,120Z"></path>
                        <!-- Dark green wave matching body -->
                        <path fill="#0f7632" d="M0,96L40,85.3C80,75,160,53,240,64C320,75,400,117,440,138.7L480,160L480,120L440,120C400,120,320,120,240,120C160,120,80,120,40,120L0,120Z"></path>
                    </svg>
                </div>

                <div class="card-body">
                    <h2 class="card-name">{{ $talent->user->name }}</h2>
                    <p class="card-designation">{{ $talent->designation ?? 'Campus Talent' }}</p>
                    
                    <div class="card-divider"></div>

                    <div class="card-details">
                        <div class="detail-row">
                            <div class="detail-label">ID NO</div>
                            <div class="detail-colon">:</div>
                            <div class="detail-value">{{ $talent->id_no ?? $talent->user->student_id ?? '0000000000' }}</div>
                        </div>
                        <div class="detail-row">
                            <div class="detail-label">Blood</div>
                            <div class="detail-colon">:</div>
                            <div class="detail-value">{{ $talent->blood_group ?? 'B+' }}</div>
                        </div>
                        <div class="detail-row">
                            <div class="detail-label">Phone</div>
                            <div class="detail-colon">:</div>
                            <div class="detail-value">{{ $talent->phone ?? '+000-123-456-789' }}</div>
                        </div>
                        <div class="detail-row">
                            <div class="detail-label">E-mail</div>
                            <div class="detail-colon">:</div>
                            <div class="detail-value">{{ $talent->email ?? $talent->user->email }}</div>
                        </div>
                        <div class="detail-row">
                            <div class="detail-label">Address</div>
                            <div class="detail-colon">:</div>
                            <div class="detail-value">{{ $talent->address ?? 'xyz Road, cty, country' }}</div>
                        </div>
                    </div>

                    <div class="card-footer">
                        <div class="qr-code">
                            <img src="https://api.qrserver.com/v1/create-qr-code/?size=100x100&data={{ urlencode(url('/user/' . $talent->user->id)) }}&color=0f7632" alt="QR">
                        </div>
                    </div>
                </div>

                <svg class="bottom-wave" viewBox="0 0 320 120" xmlns="http://www.w3.dom/svg">
                    <path fill="#16a34a" d="M0,96L40,85.3C80,75,160,53,240,58.7C320,64,400,96,440,112L480,128L480,120L440,120C400,120,320,120,240,120C160,120,80,120,40,120L0,120Z"></path>
                </svg>
            </div>
        @empty
            <div style="text-align: center; color: #718096; padding: 50px 20px; font-weight: 600; width: 100%;">
                🌵 No talents approved yet. Apply to be the first!
            </div>
        @endforelse
    </div>

    <!-- Application Modal -->
    <div class="modal-overlay" id="talent-modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Become a Talent</h3>
                <button class="close-modal" id="close-talent-modal">&times;</button>
            </div>
            <p style="font-size: 14px; color: #718096; margin-bottom: 20px;">Fill out this application. Once approved by an admin, your Talent ID Card will appear publicly!</p>
            
            <form action="{{ route('talents.store') }}" method="POST">
                @csrf
                <div class="form-group">
                    <label>What's your core skill? (Designation)</label>
                    <input type="text" name="designation" placeholder="e.g. Full-Stack Developer, Debate Champion" required>
                </div>
                <div class="form-group">
                    <label>ID NO (Format: 0000000000)</label>
                    <input type="text" name="id_no" value="{{ auth()->user()->student_id ?? '' }}">
                </div>
                <div style="display: flex; gap: 15px;">
                    <div class="form-group" style="flex: 1;">
                        <label>Blood Group</label>
                        <input type="text" name="blood_group" placeholder="e.g. O+">
                    </div>
                    <div class="form-group" style="flex: 2;">
                        <label>Phone Number</label>
                        <input type="text" name="phone" placeholder="+8801...">
                    </div>
                </div>
                <div class="form-group">
                    <label>Public E-mail</label>
                    <input type="email" name="email" value="{{ auth()->user()->email }}">
                </div>
                <div class="form-group">
                    <label>Address</label>
                    <input type="text" name="address" placeholder="e.g. Dhaka, Bangladesh">
                </div>
                <button type="submit" style="width: 100%; padding: 12px; background: #0f7632; color: white; border: none; border-radius: 8px; font-weight: 700; cursor: pointer; margin-top: 10px;">Submit Application</button>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const modal = document.getElementById('talent-modal');
            const openBtn = document.getElementById('open-talent-modal');
            const closeBtn = document.getElementById('close-talent-modal');

            if(openBtn && modal) {
                openBtn.addEventListener('click', () => {
                    modal.classList.add('active');
                });
            }

            if(closeBtn && modal) {
                closeBtn.addEventListener('click', () => {
                    modal.classList.remove('active');
                });
            }

            if(modal) {
                modal.addEventListener('click', (e) => {
                    if (e.target === modal) {
                        modal.classList.remove('active');
                    }
                });
            }

            // Talent Search Logic (Separate Dept and Skill boxes)
            const deptSearch = document.getElementById('talent-dept-search');
            const skillSearch = document.getElementById('talent-skill-search');
            const talentCards = document.querySelectorAll('.id-card');

            const filterTalents = () => {
                const deptQuery = deptSearch.value.toLowerCase().trim();
                const skillQuery = skillSearch.value.toLowerCase().trim();
                
                talentCards.forEach(card => {
                    const cardDept = (card.dataset.dept || '').toLowerCase();
                    const cardSkill = (card.dataset.skill || '').toLowerCase();
                    
                    // Match Dept (Contains)
                    const deptMatch = cardDept.includes(deptQuery);
                    
                    // Match Skill (Starts-with logic: check if designation starts with query OR any word in it starts with query)
                    const skillWords = cardSkill.split(/[\s,/-]+/);
                    const skillMatch = skillQuery === '' || skillWords.some(word => word.startsWith(skillQuery));

                    card.style.display = (deptMatch && skillMatch) ? 'flex' : 'none';
                });
            };

            if (deptSearch) deptSearch.addEventListener('input', filterTalents);
            if (skillSearch) skillSearch.addEventListener('input', filterTalents);
        });
    </script>
@endpush
