<?php

namespace App\Http\Controllers;

use App\Models\AlumniRegistration;
use Illuminate\Http\Request;

class AlumniController extends Controller
{
    /**
     * Show the alumni page with approved alumni cards.
     */
    public function index()
    {
        // Fetch approved alumni, sorting by the newest at the top
        $approvedAlumni = AlumniRegistration::approved()->orderBy('created_at', 'desc')->get();
        $user = auth()->user();
        $pendingRegistration = null;
        $existingRegistration = null;
        $justApproved = false;
        $isAlumni = false;

        if ($user) {
            // Check for any existing registration (pending or approved)
            $existingRegistration = AlumniRegistration::where('email', $user->email)->first();

            if ($existingRegistration) {
                if ($existingRegistration->status === 'pending') {
                    $pendingRegistration = $existingRegistration;
                } elseif ($existingRegistration->status === 'approved') {
                    $isAlumni = true;
                    
                    // Check if just approved (not yet notified)
                    if (!$existingRegistration->is_notified) {
                        $justApproved = true;
                        $existingRegistration->update(['is_notified' => true]);
                    }
                }
            }
            
            // Legacy role check
            if ($user->role === 'alumni') {
                $isAlumni = true;
            }
        }

        return view('alumni', compact('approvedAlumni', 'pendingRegistration', 'existingRegistration', 'justApproved', 'isAlumni'));
    }

    /**
     * Store a new alumni registration request (pending admin approval).
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'full_name'        => 'required|string|max:255',
            'email'            => 'required|email',
            'phone'            => 'nullable|string|max:20',
            'profile_image'    => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'student_id'       => 'required|string|max:50',
            'department'       => 'required|string|max:255',
            'batch'            => 'required|string|max:20',
            'graduation_year'  => 'required|string|max:10',
            'current_position' => 'required|string|max:255',
            'company'          => 'required|string|max:255',
            'company_logo'     => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'category'         => 'required|string|max:100',
            'linkedin_url'     => 'nullable|url|max:500',
        ]);

        // Handle profile image upload
        if ($request->hasFile('profile_image')) {
            $validated['profile_image'] = $request->file('profile_image')
                ->store('alumni/profiles', 'public');
        }

        // Handle company logo upload
        if ($request->hasFile('company_logo')) {
            $validated['company_logo'] = $request->file('company_logo')
                ->store('alumni/logos', 'public');
        }

        $validated['status'] = 'pending';
        $validated['is_notified'] = false; // Reset notification for re-approvals

        AlumniRegistration::updateOrCreate(
            ['email' => $validated['email']],
            $validated
        );

        return back()->with('success', 'Your alumni registration has been submitted! It will be reviewed by admin.');
    }

    /**
     * Delete an alumni registration.
     */
    public function destroy(AlumniRegistration $alumni)
    {
        if (auth()->user()->email !== $alumni->email && auth()->user()->role !== 'admin') {
            return back()->with('error', 'Unauthorized action.');
        }

        $alumni->delete();

        return redirect()->route('alumni.index')->with('success', 'Your alumni card has been removed.');
    }
}
