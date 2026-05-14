<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QuestionBank extends Model
{
    protected $fillable = [
        'user_id',
        'department',
        'course_code',
        'course_name',
        'title',
        'difficulty',
        'question_heading',
        'sub_questions',
        'tags',
        'year_semester',
        'file_path',
        'status',
    ];

    protected $casts = [
        'file_path' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
