<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DocsSection extends Model
{
    protected $fillable = [
        'slug',
        'title',
        'icon',
        'content',
        'sort_order',
        'is_visible',
    ];

    protected function casts(): array
    {
        return [
            'is_visible' => 'boolean',
        ];
    }

    /**
     * Scope to get only visible sections, ordered by sort_order.
     */
    public function scopeVisible($query)
    {
        return $query->where('is_visible', true)->orderBy('sort_order');
    }
}
