<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Schedule extends Model
{
    protected $fillable = [
        'course_code',
        'section',
        'course_title',
        'major',
        'teacher_initial',
        'department',
        'batch',
        'room_no',
        'type',
        'lab_section',
        'day',
        'time_slot',
        'user_id',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}