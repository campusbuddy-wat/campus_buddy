<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Mail\ResetPasswordCode;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class ForgotPasswordController extends Controller
{
    /**
     * Show the forgot password form.
     */
    public function showLinkRequestForm()
    {
        return view('auth.forgot-password');
    }

    /**
     * Send the reset code to user's email.
     */
    public function sendResetCode(Request $request)
    {
        $request->validate(['email' => 'required|email|exists:users,email']);

        $email = $request->email;
        // Generate a 6-digit numeric code
        $code = rand(100000, 999999);

        // Store the code in the database
        DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $email],
            [
                'token' => Hash::make($code),
                'created_at' => now(),
            ]
        );

        // Send the email
        Mail::to($email)->send(new ResetPasswordCode($code));

        return redirect()->route('password.reset.form', ['email' => $email])
            ->with('status', 'We have sent a 6-digit code to your email!');
    }

    /**
     * Show the form to reset password using code.
     */
    public function showResetForm(Request $request)
    {
        $email = $request->get('email');
        return view('auth.reset-password', compact('email'));
    }

    /**
     * Handle the password reset.
     */
    public function reset(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
            'code' => 'required|string|size:6',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $reset = DB::table('password_reset_tokens')->where('email', $request->email)->first();

        if (!$reset || !Hash::check($request->code, $reset->token)) {
            return back()->withErrors(['code' => 'Invalid or expired code.']);
        }

        // Check if code is older than 15 minutes
        if (now()->diffInMinutes($reset->created_at) > 15) {
            return back()->withErrors(['code' => 'Your reset code has expired. Please request a new one.']);
        }

        // Update user's password
        $user = User::where('email', $request->email)->first();
        $user->update(['password' => Hash::make($request->password)]);

        // Delete reset token
        DB::table('password_reset_tokens')->where('email', $request->email)->delete();

        return redirect()->route('login')->with('success', 'Your password has been successfully reset. Please log in.');
    }
}
