<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    protected $fillable = ['user_id', 'content', 'attachment', 'type', 'action_text', 'action_link'];

    public function user(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function comments(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Comment::class)->latest();
    }

    public function likes(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Like::class);
    }

    public function isLikedBy($user = null): bool
    {
        if (!$user) {
            return false;
        }
        return $this->likes()->where('user_id', $user->id)->exists();
    }
}
