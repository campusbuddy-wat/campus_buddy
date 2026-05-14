<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClassTask extends Model
{
    protected $table = 'class_tasks';

    protected $fillable = [
        'type',
        'user_id',
        'course_code',
        'title',
        'topic',
        'description',
        'deadline',
        'duration_or_slot',
        'progress_status',
        'tip_1',
        'tip_2',
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