<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AlumniRegistration extends Model
{
    use HasFactory;

    protected $fillable = [
        'full_name',
        'email',
        'phone',
        'profile_image',
        'student_id',
        'department',
        'batch',
        'graduation_year',
        'current_position',
        'company',
        'company_logo',
        'category',
        'linkedin_url',
        'card_bg_image',
        'badge_text',
        'badge_style',
        'top_img_class',
        'profile_img_class',
        'container_class',
        'subtitle',
        'status',
        'admin_note',
    ];

    /**
     * Scope to get only approved alumni
     */
    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    /**
     * Scope to get only pending alumni
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Check if alumni is approved
     */
    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }
}
