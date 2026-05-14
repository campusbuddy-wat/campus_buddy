<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Welcome Buddy!</title>
    <!-- Use Laravel asset helper for the CSS path -->
    <link rel="stylesheet" href="{{ asset('css/login.css') }}">
    <link rel="stylesheet" href="{{ asset('css/topbar.css') }}">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>

<body>
    @include('includes.topbar')

    <main class="page">
        <section class="card" aria-labelledby="login-title">
            <h1 id="login-title" class="title">Welcome Campus Buddy!</h1>

            @if (session('success'))
            <div class="alert alert--success" role="alert">
                <svg class="alert__icon" viewBox="0 0 20 20" fill="currentColor" width="20" height="20">
                    <path fill-rule="evenodd"
                        d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                        clip-rule="evenodd" />
                </svg>
                {{ session('success') }}
            </div>
            @endif

            <form method="POST" action="{{ url('/login') }}" class="form" novalidate>
                @csrf
                <div class="field">
                    <label for="login" class="label">Varsity ID / Email</label>
                    <input id="login" name="login" type="text" class="input @error('login') input--error @enderror"
                        value="{{ old('login') }}" placeholder="Varsity ID / Email" autocomplete="username" required>
                    @error('login')
                    <p class="error" role="alert">{{ $message }}</p>
                    @enderror
                </div>

                <div class="field">
                    <label for="password" class="label">Password</label>
                    <input id="password" name="password" type="password"
                        class="input @error('password') input--error @enderror" placeholder="Password"
                        autocomplete="current-password" required>
                    @error('password')
                    <p class="error" role="alert">{{ $message }}</p>
                    @enderror
                </div>

                <button type="submit" class="btn btn--primary">Sign In</button>

                <div class="links">
                    <a class="link" href="{{ url('/forgot-password') }}">Forgot Password?</a>
                </div>
            </form>

            <div class="divider" role="separator" aria-label="Or">
                <span class="divider__line"></span>
                <span class="divider__text">Or</span>
                <span class="divider__line"></span>
            </div>

            <form method="POST" action="{{ url('/login/guest') }}" class="form" style="gap: 10px;">
                @csrf
                <button type="submit" class="btn btn--secondary">Log In as Guest</button>
            </form>

            <p class="signup">
                Don’t have an account?
                <a class="link link--strong" href="{{ route('signup') }}">Sign Up</a>
            </p>
        </section>
    </main>

</body>

</html>