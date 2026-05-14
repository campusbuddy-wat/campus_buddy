<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Forgot Password | Campus Buddy</title>
    <link rel="stylesheet" href="{{ asset('css/login.css') }}">
    <link rel="stylesheet" href="{{ asset('css/topbar.css') }}">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>

<body>
    @include('includes.topbar')

    <main class="page">
        <section class="card" aria-labelledby="forgot-title">
            <h1 id="forgot-title" class="title">Reset Password</h1>
            <p style="text-align: center; color: var(--muted); margin-bottom: 25px;">Enter your email and we'll send you a 6-digit code to reset your password.</p>

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

            <form method="POST" action="{{ route('password.email') }}" class="form">
                @csrf
                <div class="field">
                    <label for="email" class="label">Email Address</label>
                    <input id="email" name="email" type="email" class="input @error('email') input--error @enderror"
                        value="{{ old('email') }}" placeholder="Enter your email" required autofocus>
                    @error('email')
                    <p class="error" role="alert">{{ $message }}</p>
                    @enderror
                </div>

                <button type="submit" class="btn btn--primary">Send Reset Code</button>

                <div class="links">
                    <a class="link" href="{{ route('login') }}">Back to Login</a>
                </div>
            </form>
        </section>
    </main>
</body>
</html>
