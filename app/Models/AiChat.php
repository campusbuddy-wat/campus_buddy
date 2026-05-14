<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AiChat extends Model
{
    protected $fillable = [
        'user_id',
        'session_id',
        'type',
        'title',
        'history',
    ];

    protected $casts = [
        'history' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
