<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Talent;

class TalentController extends Controller
{
    public function index()
    {
        $talents = Talent::where('status', '=', 'approved')->with('user')->get();
        return view('talents', compact('talents'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'designation' => 'required|string|max:255',
            'id_no' => 'nullable|string|max:255',
            'blood_group' => 'nullable|string|max:10',
            'phone' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string',
            'website' => 'nullable|url|max:255',
            'facebook_link' => 'nullable|url|max:255',
        ]);

        $talent = Talent::where('user_id', auth()->id())->first();

        // If they already have an application (approved or pending), don't recreate, just update and set to pending
        if ($talent) {
            $talent->update(array_merge($request->all(), ['status' => 'pending']));
            return back()->with('success', 'Your talent application has been updated and is pending admin approval.');
        }

        Talent::create(array_merge($request->all(), [
            'user_id' => auth()->id(),
            'status' => 'pending',
        ]));

        return back()->with('success', 'Application submitted! You will appear here once an admin approves it.');
    }
}
