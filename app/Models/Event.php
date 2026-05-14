<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    protected $fillable = [
        'title',
        'description',
        'image_path',
        'event_date',
    ];

    protected $casts = [
        'event_date' => 'date',
    ];
}
