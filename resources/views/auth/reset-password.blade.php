<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Enter Code | Campus Buddy</title>
    <link rel="stylesheet" href="{{ asset('css/login.css') }}">
    <link rel="stylesheet" href="{{ asset('css/topbar.css') }}">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>

<body>
    @include('includes.topbar')

    <main class="page">
        <section class="card" aria-labelledby="reset-title">
            <h1 id="reset-title" class="title">Create Password</h1>
            <p style="text-align: center; color: var(--muted); margin-bottom: 15px;">Please enter the 6-digit code we sent to your email along with your new password.</p>

            <div style="background-color: rgba(0, 170, 255, 0.08); border: 1px solid rgba(0, 170, 255, 0.2); border-radius: 10px; padding: 12px 16px; margin-bottom: 25px; font-size: 13px; color: #0077b3; display: flex; align-items: center; gap: 10px; line-height: 1.4;">
                <span style="font-size: 16px;">💡</span>
                <span>If you do not see the email in your Inbox, please check your <strong>Spam or Junk folder</strong>.</span>
            </div>

            @if (session('status'))
            <div class="alert alert--success" role="alert">
                <svg class="alert__icon" viewBox="0 0 20 20" fill="currentColor" width="20" height="20">
                    <path fill-rule="evenodd"
                        d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                        clip-rule="evenodd" />
                </svg>
                {{ session('status') }}
            </div>
            @endif

            <form method="POST" action="{{ route('password.reset.update') }}" class="form">
                @csrf
                <input type="hidden" name="email" value="{{ $email }}">

                <div class="field">
                    <label for="code" class="label">6-Digit Code</label>
                    <input id="code" name="code" type="text" maxlength="6" class="input @error('code') input--error @enderror"
                        placeholder="Enter 6-digit code" autocomplete="one-time-code" required autofocus>
                    @error('code')
                    <p class="error" role="alert">{{ $message }}</p>
                    @enderror
                </div>

                <div class="field">
                    <label for="password" class="label">New Password</label>
                    <input id="password" name="password" type="password" class="input @error('password') input--error @enderror"
                        placeholder="New Password" required autocomplete="new-password">
                    @error('password')
                    <p class="error" role="alert">{{ $message }}</p>
                    @enderror
                </div>

                <div class="field" style="margin-bottom: 10px;">
                    <label for="password_confirmation" class="label">Confirm Password</label>
                    <input id="password_confirmation" name="password_confirmation" type="password" class="input"
                        placeholder="Confirm New Password" required autocomplete="new-password">
                </div>

                <button type="submit" class="btn btn--primary">Reset Password</button>

                <div class="links">
                    <a class="link" href="{{ route('password.request') }}">Resend Code</a>
                </div>
            </form>
        </section>
    </main>
</body>
</html>
