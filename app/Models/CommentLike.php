<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CommentLike extends Model
{
    protected $fillable = ['comment_id', 'user_id'];

    public function comment(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Comment::class);
    }

    public function user(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
