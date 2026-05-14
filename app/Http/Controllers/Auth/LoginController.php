<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    /**
     * Show the application's login form.
     */
    public function showLoginForm()
    {
        return view('auth.login');
    }

    /**
     * Handle a login request to the application.
     */
    public function login(Request $request)
    {
        $request->validate([
            'login' => 'required|string',
            'password' => 'required|string',
        ], [
            'login.required' => 'Varsity ID / Email is required.',
            'password.required' => 'Password is required.',
        ]);

        $loginType = filter_var($request->login, FILTER_VALIDATE_EMAIL) ? 'email' : 'student_id';

        $credentials = [
            $loginType => $request->login,
            'password' => $request->password,
        ];

        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();

            $user = Auth::user();

            // Block unapproved CR accounts
            if ($user->role === 'cr' && !$user->is_approved) {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return back()->withErrors([
                    'login' => '⏳ Your CR account is pending approval from the Admin. Please wait for approval before logging in.',
                ])->onlyInput('login');
            }

            return redirect()->intended('dashboard');
        }

        return back()->withErrors([
            'login' => 'The provided credentials do not match our records.',
        ])->onlyInput('login');
    }

    /**
     * Log the user out of the application.
     */
    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login');
    }
}