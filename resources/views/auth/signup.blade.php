@extends('layouts.guest')

@section('title', 'Join the Buddy!')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/login.css') }}">
    <style>
        .hidden {
            display: none;
        }
    </style>
@endpush

@section('content')
    @include('includes.topbar')

    <main class="page">
        <section class="card" aria-labelledby="signup-title">
            <h1 id="signup-title" class="title">Join Campus Buddy!</h1>

            <form method="POST" action="{{ url('/signup') }}" class="form" enctype="multipart/form-data" novalidate>
                @csrf

                <div class="field">
                    <label for="name" class="label">Full Name</label>
                    <input id="name" name="name" type="text" class="input @error('name') input--error @enderror"
                        value="{{ old('name') }}" placeholder="Your Name" required>
                    @error('name') <p class="error" role="alert">{{ $message }}</p> @enderror
                </div>

                <div class="field">
                    <label for="email" class="label">Email Address</label>
                    <input id="email" name="email" type="email" class="input @error('email') input--error @enderror"
                        value="{{ old('email') }}" placeholder="email@example.com" required>
                    @error('email') <p class="error" role="alert">{{ $message }}</p> @enderror
                </div>

                <div class="field">
                    <label for="student_id" class="label">Varsity ID</label>
                    <input id="student_id" name="student_id" type="text"
                        class="input @error('student_id') input--error @enderror" value="{{ old('student_id') }}"
                        placeholder="ID Number" required>
                    @error('student_id') <p class="error" role="alert">{{ $message }}</p> @enderror
                </div>

                <div class="field">
                    <label for="role" class="label">I am a...</label>
                    <select id="role" name="role" class="input" style="appearance: auto;">
                        <option value="student" {{ old('role')=='student' ? 'selected' : '' }}>Student</option>
                        <option value="cr" {{ old('role')=='cr' ? 'selected' : '' }}>Class Representative (CR)</option>
                    </select>
                </div>

                <div id="additional-info">
                    <div class="field">
                        <label for="department" class="label">Department</label>
                        <input id="department" name="department" type="text"
                            class="input @error('department') input--error @enderror" value="{{ old('department') }}"
                            placeholder="e.g. CSE">
                        @error('department') <p class="error" role="alert">{{ $message }}</p> @enderror
                    </div>
                    <div class="field" style="margin-top: 14px;">
                        <label for="batch" class="label">Batch</label>
                        <input id="batch" name="batch" type="text" class="input @error('batch') input--error @enderror"
                            value="{{ old('batch') }}" placeholder="e.g. 2021">
                        @error('batch') <p class="error" role="alert">{{ $message }}</p> @enderror
                    </div>
                    <div class="field" style="margin-top: 14px;">
                        <label for="semester" class="label">Semester</label>
                        <input id="semester" name="semester" type="text"
                            class="input @error('semester') input--error @enderror" value="{{ old('semester') }}"
                            placeholder="e.g. 5th">
                        @error('semester') <p class="error" role="alert">{{ $message }}</p> @enderror
                    </div>
                    <div class="field" style="margin-top: 14px;">
                        <label for="section" class="label">Section</label>
                        <input id="section" name="section" type="text"
                            class="input @error('section') input--error @enderror" value="{{ old('section') }}"
                            placeholder="e.g. A">
                        @error('section') <p class="error" role="alert">{{ $message }}</p> @enderror
                    </div>

                    <div class="field" style="margin-top: 14px;">
                        <label for="is_major" class="label">Are you a Major student?</label>
                        <select id="is_major" name="is_major" class="input" style="appearance: auto;">
                            <option value="no" {{ old('is_major')=='no' ? 'selected' : '' }}>Non-Major</option>
                            <option value="yes" {{ old('is_major')=='yes' ? 'selected' : '' }}>Major Student</option>
                        </select>
                    </div>

                    <div id="major-name-field" class="field {{ old('is_major') == 'yes' ? '' : 'hidden' }}"
                        style="margin-top: 14px;">
                        <label for="major" class="label">Major Name</label>
                        <input id="major" name="major" type="text" class="input @error('major') input--error @enderror"
                            value="{{ old('major') }}" placeholder="e.g. DS, CS, Robotics">
                        @error('major') <p class="error" role="alert">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div class="field" style="margin-top: 14px;">
                    <label for="profile_image" class="label">Profile Picture (Optional)</label>
                    <input id="profile_image" name="profile_image" type="file" class="input @error('profile_image') input--error @enderror" accept="image/*" style="padding-top: 8px;">
                    @error('profile_image') <p class="error" role="alert">{{ $message }}</p> @enderror
                </div>

                <div class="field">
                    <label for="password" class="label">Password</label>
                    <input id="password" name="password" type="password"
                        class="input @error('password') input--error @enderror" placeholder="••••••••" required>
                    @error('password') <p class="error" role="alert">{{ $message }}</p> @enderror
                    
                    <!-- Password Strength Indicator -->
                    <div id="password-strength-container" style="margin-top: 8px;">
                        <div style="display: flex; gap: 4px; height: 6px; width: 100%; border-radius: 3px; background: #e2e8f0; overflow: hidden;">
                            <div id="strength-bar" style="width: 0%; height: 100%; transition: width 0.3s ease, background-color 0.3s ease; background-color: #ef4444;"></div>
                        </div>
                        <div style="display: flex; justify-content: space-between; font-size: 11px; margin-top: 4px; color: #64748b;">
                            <span>Strength: <span id="strength-text" style="font-weight: 600; color: #ef4444;">Weak</span></span>
                        </div>
                        <!-- Password criteria list -->
                        <ul id="password-criteria" style="margin: 8px 0 0; padding-left: 0; list-style: none; font-size: 12px; display: flex; flex-direction: column; gap: 6px;">
                            <li id="criterion-length" style="color: #ef4444; display: flex; align-items: center; gap: 6px; transition: color 0.2s ease;">
                                <span class="criterion-dot" style="display: inline-block; width: 6px; height: 6px; border-radius: 50%; background-color: #ef4444; transition: background-color 0.2s ease;"></span>
                                At least 6 characters
                            </li>
                            <li id="criterion-special" style="color: #ef4444; display: flex; align-items: center; gap: 6px; transition: color 0.2s ease;">
                                <span class="criterion-dot" style="display: inline-block; width: 6px; height: 6px; border-radius: 50%; background-color: #ef4444; transition: background-color 0.2s ease;"></span>
                                At least 1 special character
                            </li>
                            <li id="criterion-number" style="color: #ef4444; display: flex; align-items: center; gap: 6px; transition: color 0.2s ease;">
                                <span class="criterion-dot" style="display: inline-block; width: 6px; height: 6px; border-radius: 50%; background-color: #ef4444; transition: background-color 0.2s ease;"></span>
                                At least 1 number
                            </li>
                        </ul>
                    </div>
                </div>

                <div class="field">
                    <label for="password_confirmation" class="label">Confirm Password</label>
                    <input id="password_confirmation" name="password_confirmation" type="password" class="input"
                        placeholder="••••••••" required>
                    
                    <!-- Password Match Indicator -->
                    <div id="match-indicator" style="margin-top: 6px; font-size: 12px; display: none; align-items: center; gap: 6px; transition: color 0.2s ease;">
                        <span id="match-dot" style="display: inline-block; width: 6px; height: 6px; border-radius: 50%; transition: background-color 0.2s ease;"></span>
                        <span id="match-text">Passwords match</span>
                    </div>
                </div>

                <button type="submit" class="btn btn--primary">Create Account</button>
            </form>

            <p class="signup">
                Already have an account?
                <a class="link link--strong" href="{{ route('login') }}">Sign In</a>
            </p>
        </section>
    </main>
@endsection

@push('scripts')
    <script>
        document.addEventListener("DOMContentLoaded", function () {
            const isMajorSelect = document.getElementById('is_major');
            const majorField = document.getElementById('major-name-field');

            if (isMajorSelect && majorField) {
                isMajorSelect.addEventListener('change', function () {
                    if (this.value === 'yes') {
                        majorField.classList.remove('hidden');
                    } else {
                        majorField.classList.add('hidden');
                        document.getElementById('major').value = '';
                    }
                });
            }

            // ==================== PASSWORD STRENGTH & MATCH INDICATORS ====================
            const passwordInput = document.getElementById('password');
            const confirmInput = document.getElementById('password_confirmation');
            const strengthBar = document.getElementById('strength-bar');
            const strengthText = document.getElementById('strength-text');
            const lengthCriterion = document.getElementById('criterion-length');
            const specialCriterion = document.getElementById('criterion-special');
            const numberCriterion = document.getElementById('criterion-number');
            const matchIndicator = document.getElementById('match-indicator');
            const matchDot = document.getElementById('match-dot');
            const matchText = document.getElementById('match-text');

            if (passwordInput) {
                passwordInput.addEventListener('input', function () {
                    const val = this.value;
                    
                    // Validation criteria matching
                    const meetsLength = val.length >= 6;
                    const meetsSpecial = /[!@#$%^&*(),.?":{}|<>_+\-=\[\]]/.test(val);
                    const meetsNumber = /\d/.test(val);
                    
                    // Update check item text & dot colors
                    updateCriterionUI(lengthCriterion, meetsLength);
                    updateCriterionUI(specialCriterion, meetsSpecial);
                    updateCriterionUI(numberCriterion, meetsNumber);
                    
                    // Compute criteria score (0 to 3)
                    let score = 0;
                    if (val.length > 0) {
                        if (meetsLength) score++;
                        if (meetsSpecial) score++;
                        if (meetsNumber) score++;
                    }
                    
                    // Map score to bar width and colors (red to blue)
                    let barWidth = '0%';
                    let barColor = '#ef4444'; // Default weak red
                    let strengthLabel = 'Weak';
                    
                    if (val.length > 0) {
                        if (score <= 1) {
                            barWidth = '33%';
                            barColor = '#ef4444'; // Weak = Red
                            strengthLabel = 'Weak';
                        } else if (score === 2) {
                            barWidth = '66%';
                            barColor = '#6366f1'; // Moderate = Purple/Indigo
                            strengthLabel = 'Moderate';
                        } else if (score === 3) {
                            barWidth = '100%';
                            barColor = '#2563eb'; // Strong = Blue
                            strengthLabel = 'Strong';
                        }
                    }
                    
                    strengthBar.style.width = barWidth;
                    strengthBar.style.backgroundColor = barColor;
                    strengthText.textContent = strengthLabel;
                    strengthText.style.color = barColor;

                    // Re-verify confirmation matches
                    checkPasswordMatch();
                });
            }

            if (confirmInput) {
                confirmInput.addEventListener('input', checkPasswordMatch);
            }

            function updateCriterionUI(element, isMet) {
                if (!element) return;
                const dot = element.querySelector('.criterion-dot');
                if (isMet) {
                    element.style.color = '#2563eb'; // Blue for valid
                    if (dot) dot.style.backgroundColor = '#2563eb';
                } else {
                    element.style.color = '#ef4444'; // Red for invalid
                    if (dot) dot.style.backgroundColor = '#ef4444';
                }
            }

            function checkPasswordMatch() {
                if (!confirmInput || !passwordInput || !matchIndicator) return;
                const pVal = passwordInput.value;
                const cVal = confirmInput.value;
                
                // Hide indicator if confirm input is empty
                if (cVal.length === 0) {
                    matchIndicator.style.display = 'none';
                    return;
                }
                
                matchIndicator.style.display = 'flex';
                
                if (pVal === cVal) {
                    matchIndicator.style.color = '#2563eb'; // Blue for match
                    if (matchDot) matchDot.style.backgroundColor = '#2563eb';
                    if (matchText) matchText.textContent = 'Passwords match';
                } else {
                    matchIndicator.style.color = '#ef4444'; // Red for mismatch
                    if (matchDot) matchDot.style.backgroundColor = '#ef4444';
                    if (matchText) matchText.textContent = 'Passwords do not match';
                }
            }
        });
    </script>
@endpush