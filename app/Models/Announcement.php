<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Announcement extends Model
{
    protected $fillable = [
        'user_id',
        'title',
        'content',
        'department',
        'batch',
        'section',
        'major',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}