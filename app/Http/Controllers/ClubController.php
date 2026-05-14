<?php

namespace App\Http\Controllers;

use App\Models\Club;
use Illuminate\Support\Facades\Cache;

class ClubController extends Controller
{
    public function index()
    {
        $clubs = Club::all();
        return view('clubs', compact('clubs'));
    }
}
