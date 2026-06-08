@extends('layouts.app')

@section('title', 'Account Settings | Campus Buddy')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/settings.css') }}">
@endpush

@section('content')
<div class="settings-container">
    <div class="settings-header">
        <h1>Account</h1>
        <p>Real-time information and activities of your property.</p>
    </div>

    @if(session('success'))
        <div class="alert alert-success" style="padding: 15px; background: #e6fffa; border-left: 5px solid #38b2ac; color: #2c7a7b; margin-bottom: 30px; border-radius: 8px; font-weight: 600;">
            {{ session('success') }}
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger" style="padding: 15px; background: #fff5f5; border-left: 5px solid #f56565; color: #c53030; margin-bottom: 30px; border-radius: 8px; font-weight: 600;">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('profile.settings.update') }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PATCH')

        {{-- Profile Picture Section --}}
        <div class="settings-section">
            <label class="section-label">Profile picture</label>
            <span class="section-desc">PNG, JPEG under 15MB</span>
            <div class="profile-pic-group">
                @if($user->profile_image)
                    <img src="{{ asset('storage/' . $user->profile_image) }}" alt="Profile" class="current-avatar">
                @else
                    <div class="avatar-placeholder">
                        <i class="fas fa-user"></i>
                    </div>
                @endif
                <div class="pic-actions">
                    <label for="profile_image_input" class="btn-upload">Upload new picture</label>
                    <input type="file" id="profile_image_input" name="profile_image" style="display: none;" onchange="this.form.action='{{ route('profile.update') }}'; this.form.submit();">
                    
                    @if($user->profile_image)
                        <button type="button" class="btn-delete" onclick="if(confirm('Are you sure?')) document.getElementById('delete-img-form').submit();">Delete</button>
                    @endif
                </div>
            </div>
        </div>

        {{-- Name Section --}}
        <div class="settings-section">
            <label class="section-label">Full name</label>
            <div class="form-grid">
                @php
                    $names = explode(' ', $user->name, 2);
                    $first_name = $names[0] ?? '';
                    $last_name = $names[1] ?? '';
                @endphp
                <div class="form-group">
                    <label>First name</label>
                    <input type="text" name="first_name" value="{{ old('first_name', $first_name) }}" placeholder="Bryan">
                </div>
                <div class="form-group">
                    <label>Last name</label>
                    <input type="text" name="last_name" value="{{ old('last_name', $last_name) }}" placeholder="Cranston">
                </div>
            </div>
        </div>

        {{-- Email Section --}}
        <div class="settings-section">
            <label class="section-label">Contact email</label>
            <span class="section-desc">Manage your accounts email address for notifications and security.</span>
            <div class="form-grid">
                <div class="form-group has-icon">
                    <label>Email</label>
                    <div class="input-wrapper">
                        <i class="far fa-envelope"></i>
                        <input type="email" name="email" value="{{ old('email', $user->email) }}" placeholder="bryan.cranston@mail.com">
                    </div>
                </div>
                <div class="form-group" style="display: flex; align-items: flex-end; padding-bottom: 25px;">
                    <button type="button" class="add-email">
                        <i class="fas fa-plus-circle"></i>
                        Add another email
                    </button>
                </div>
            </div>
        </div>

        {{-- Campus Information Section (Moved from old modal) --}}
        <div class="settings-section">
            <label class="section-label">Campus Information</label>
            <span class="section-desc">Update your academic details to see relevant routine and notes.</span>
            <div class="form-grid">
                <div class="form-group">
                    <label>Department</label>
                    <input type="text" name="department" value="{{ old('department', $user->department) }}" placeholder="e.g. Computer Science">
                </div>
                <div class="form-group">
                    <label>Batch</label>
                    <input type="text" name="batch" value="{{ old('batch', $user->batch) }}" placeholder="e.g. 61">
                </div>
                <div class="form-group">
                    <label>Semester</label>
                    <input type="text" name="semester" value="{{ old('semester', $user->semester) }}" placeholder="e.g. 3rd">
                </div>
                <div class="form-group">
                    <label>Section</label>
                    <input type="text" name="section" value="{{ old('section', $user->section) }}" placeholder="e.g. D">
                </div>
                <div class="form-group full-width">
                    <label>Major (Optional)</label>
                    <input type="text" name="major" value="{{ old('major', $user->major) }}" placeholder="e.g. DS, CS, Robotics">
                </div>
            </div>
        </div>

        {{-- Password Section --}}
        <div class="settings-section">
            <label class="section-label">Password</label>
            <span class="section-desc">Modify your current password.</span>
            <div class="form-grid">
                <div class="form-group has-icon">
                    <label>Current password</label>
                    <div class="input-wrapper">
                        <i class="fas fa-lock"></i>
                        <input type="password" name="current_password" placeholder="••••••••">
                        <i class="far fa-eye toggle-pass" onclick="togglePassword(this)"></i>
                    </div>
                </div>
                <div class="form-group has-icon">
                    <label>New password</label>
                    <div class="input-wrapper">
                        <i class="fas fa-lock"></i>
                        <input type="password" name="new_password" placeholder="••••••••">
                        <i class="far fa-eye toggle-pass" onclick="togglePassword(this)"></i>
                    </div>
                </div>
                <div class="form-group full-width has-icon">
                    <label>Confirm new password</label>
                    <div class="input-wrapper">
                        <i class="fas fa-lock"></i>
                        <input type="password" name="new_password_confirmation" placeholder="••••••••">
                    </div>
                </div>
            </div>
        </div>

        <div class="settings-actions">
            <button type="submit" class="btn-save">Save changes</button>
        </div>
    </form>

    {{-- Hidden form for image deletion --}}
    <form id="delete-img-form" action="{{ route('profile.image.delete') }}" method="POST" style="display: none;">
        @csrf
        @method('DELETE')
    </form>
</div>

<script>
    function togglePassword(icon) {
        const input = icon.parentElement.querySelector('input');
        if (input.type === 'password') {
            input.type = 'text';
            icon.classList.remove('fa-eye');
            icon.classList.add('fa-eye-slash');
        } else {
            input.type = 'password';
            icon.classList.remove('fa-eye-slash');
            icon.classList.add('fa-eye');
        }
    }
</script>
@endsection
