<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DocsTeamMember extends Model
{
    protected $fillable = [
        'name',
        'role',
        'email',
        'profile_image',
        'github_url',
        'bio',
        'sort_order',
    ];
}
