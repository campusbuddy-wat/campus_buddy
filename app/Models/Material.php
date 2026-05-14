<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Material extends Model
{
    protected $fillable = [
        'user_id',
        'type',
        'department',
        'major',
        'section',
        'batch',
        'course_code',
        'title',
        'file_path',
        'file_extension',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}