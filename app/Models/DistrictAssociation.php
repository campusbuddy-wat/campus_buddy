<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DistrictAssociation extends Model
{
    protected $fillable = ['name', 'division', 'district', 'image', 'link', 'members_count', 'cover_image'];
}
