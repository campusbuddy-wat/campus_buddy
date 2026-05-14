<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Join the Buddy!</title>
    <link rel="stylesheet" href="{{ asset('css/login.css') }}">
    <link rel="stylesheet" href="{{ asset('css/topbar.css') }}">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .hidden {
            display: none;
        }
    </style>
</head>

<body>
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
                </div>

                <div class="field">
                    <label for="password_confirmation" class="label">Confirm Password</label>
                    <input id="password_confirmation" name="password_confirmation" type="password" class="input"
                        placeholder="••••••••" required>
                </div>

                <button type="submit" class="btn btn--primary">Create Account</button>
            </form>

            <p class="signup">
                Already have an account?
                <a class="link link--strong" href="{{ route('login') }}">Sign In</a>
            </p>
        </section>
    </main>


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
        });
    </script>
</body>

</html>