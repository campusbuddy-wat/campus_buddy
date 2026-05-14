<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class ProfileController extends Controller
{
    public function update(Request $request)
    {
        $user = auth()->user();

        $request->validate([
            'department' => 'required|string|max:255',
            'batch' => 'required|string|max:20',
            'semester' => 'required|string|max:20',
            'section' => 'required|string|max:10',
            'major' => 'nullable|string|max:100',
            'profile_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        $data = $request->only(['department', 'batch', 'semester', 'section', 'major']);

        if ($request->hasFile('profile_image')) {
            // Delete old image if it exists
            if ($user->profile_image && \Illuminate\Support\Facades\Storage::disk('public')->exists($user->profile_image)) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($user->profile_image);
            }

            $path = $request->file('profile_image')->store('profile_images', 'public');
            $data['profile_image'] = $path;
        }

        $user->update($data);

        return back()->with('success', 'Profile updated successfully! Your dashboard has been updated.');
    }

    public function settings()
    {
        return view('profile.settings', ['user' => auth()->user()]);
    }

    public function updateSettings(Request $request)
    {
        $user = auth()->user();

        $request->validate([
            'first_name' => 'required|string|max:100',
            'last_name' => 'required|string|max:100',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'department' => 'required|string|max:255',
            'batch' => 'required|string|max:20',
            'semester' => 'required|string|max:20',
            'section' => 'required|string|max:10',
            'major' => 'nullable|string|max:100',
            'current_password' => 'nullable|required_with:new_password|current_password',
            'new_password' => ['nullable', 'confirmed', Password::defaults()],
        ]);

        $user->name = $request->first_name . ' ' . $request->last_name;
        $user->email = $request->email;
        $user->department = $request->department;
        $user->batch = $request->batch;
        $user->semester = $request->semester;
        $user->section = $request->section;
        $user->major = $request->major;

        if ($request->filled('new_password')) {
            $user->password = Hash::make($request->new_password);
        }

        $user->save();

        return back()->with('success', 'Account settings updated successfully!');
    }

    public function deleteProfileImage()
    {
        $user = auth()->user();

        if ($user->profile_image && \Illuminate\Support\Facades\Storage::disk('public')->exists($user->profile_image)) {
            \Illuminate\Support\Facades\Storage::disk('public')->delete($user->profile_image);
            $user->update(['profile_image' => null]);
            return back()->with('success', 'Profile picture deleted.');
        }

        return back()->with('error', 'No profile picture found.');
    }
}