<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules;

class SignupController extends Controller
{
    /**
     * Show the registration form.
     */
    public function showRegistrationForm()
    {
        return view('auth.signup');
    }

    /**
     * Handle a registration request.
     */
    public function register(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required', 
                'string', 
                'lowercase', 
                'email', 
                'max:255', 
                'unique:' . User::class,
                'ends_with:@diu.edu.bd'
            ],
            'student_id' => ['required', 'string', 'max:20', 'unique:' . User::class],
            'role' => ['required', 'string', 'in:student,cr'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'department' => ['required', 'string', 'max:255'],
            'batch' => ['required', 'string', 'max:20'],
            'semester' => ['required', 'string', 'max:20'],
            'section' => ['required', 'string', 'max:10'],
            'major' => ['nullable', 'string', 'max:100'],
            'profile_image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:2048'],
        ], [
            'email.ends_with' => 'Please use your official university email (ending in @diu.edu.bd).',
        ]);

        $isCr = $request->role === 'cr';

        $profileImagePath = null;
        if ($request->hasFile('profile_image')) {
            // Store the image in the 'profile_images' directory on the 'public' disk
            $profileImagePath = $request->file('profile_image')->store('profile_images', 'public');
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'student_id' => $request->student_id,
            'role' => $request->role,
            'is_approved' => !$isCr, // CRs start unapproved; students are auto-approved
            'department' => $request->department,
            'batch' => $request->batch,
            'semester' => $request->semester,
            'section' => $request->section,
            'major' => $request->is_major === 'yes' ? $request->major : null,
            'profile_image' => $profileImagePath,
            'password' => Hash::make($request->password),
        ]);

        if ($isCr) {
            // Do NOT log in - CR must wait for admin approval
            return redirect()->route('login')->with(
                'success',
                '✅ Account created successfully! As a Class Representative, your account is pending admin approval. Please wait for an administrator to verify your request.'
            );
        }

        // Redirect to login page with success message instead of auto-login to dashboard
        return redirect()->route('login')->with('success', '✅ Account created successfully! Please sign in with your credentials.');
    }
}