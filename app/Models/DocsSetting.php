<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class DocsSetting extends Model
{
    protected $fillable = [
        'is_visible',
        'start_date',
        'end_date',
        'hero_title',
        'hero_subtitle',
        'elevator_pitch',
    ];

    protected function casts(): array
    {
        return [
            'is_visible' => 'boolean',
            'start_date' => 'datetime',
            'end_date' => 'datetime',
        ];
    }

    /**
     * Check if the docs page should currently be publicly accessible.
     */
    public function isCurrentlyAccessible(): bool
    {
        if (!$this->is_visible) {
            return false;
        }

        $now = Carbon::now();

        // If both dates are set, check the window
        if ($this->start_date && $this->end_date) {
            return $now->between($this->start_date, $this->end_date);
        }

        // If only start_date is set, check if we're past it
        if ($this->start_date) {
            return $now->gte($this->start_date);
        }

        // If only end_date is set, check if we're before it
        if ($this->end_date) {
            return $now->lte($this->end_date);
        }

        // If no dates are set but is_visible is true, show it
        return true;
    }

    /**
     * Get the singleton settings record or create a default one.
     */
    public static function getSettings(): self
    {
        return self::firstOrCreate([], [
            'is_visible' => false,
            'start_date' => Carbon::parse('2026-06-10 00:00:00'),
            'end_date' => Carbon::parse('2026-06-14 23:59:59'),
            'hero_title' => 'Campus Buddy',
            'hero_subtitle' => 'Your AI-Powered University Companion',
            'elevator_pitch' => 'Campus Buddy unifies the entire academic experience into one intelligent, AI-powered platform.',
        ]);
    }
}
