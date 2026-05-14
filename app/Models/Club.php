<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Club extends Model
{
    protected $fillable = [
        'name',
        'type',
        'description',
        'image_path',
        'website_link',
    ];
}
