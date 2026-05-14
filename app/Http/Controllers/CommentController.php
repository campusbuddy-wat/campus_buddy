<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Comment;
use App\Models\CommentLike;
use App\Models\Post;
use Illuminate\Support\Facades\Auth;

class CommentController extends Controller
{
    /**
     * Store a new comment on a post.
     */
    public function store(Request $request, Post $post)
    {
        $request->validate([
            'content' => 'required|string|max:500',
            'parent_id' => 'nullable|exists:comments,id',
        ]);

        $comment = Comment::create([
            'post_id' => $post->id,
            'user_id' => Auth::id(),
            'content' => $request->input('content'),
            'parent_id' => $request->parent_id,
        ]);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'comment' => $comment->load('user'),
                'comments_count' => $post->comments()->count()
            ]);
        }

        return redirect()->back()->with('success', 'Comment added successfully!');
    }

    /**
     * Update an existing comment.
     */
    public function update(Request $request, Comment $comment)
    {
        if ($comment->user_id !== Auth::id()) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $request->validate([
            'content' => 'required|string|max:500',
        ]);

        $comment->update([
            'content' => $request->input('content'),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Comment updated',
            'content' => $comment->content
        ]);
    }

    /**
     * Delete a comment.
     */
    public function destroy(Comment $comment)
    {
        if ($comment->user_id !== Auth::id()) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $post = $comment->post;
        $comment->delete();

        return response()->json([
            'success' => true,
            'message' => 'Comment deleted',
            'comments_count' => $post->comments()->count()
        ]);
    }

    /**
     * Toggle like on a comment.
     */
    public function like(Comment $comment)
    {
        $like = CommentLike::where('comment_id', '=', $comment->id)->where('user_id', '=', Auth::id())->first();

        if ($like) {
            $like->delete();
            $liked = false;
        } else {
            CommentLike::create([
                'comment_id' => $comment->id,
                'user_id' => Auth::id(),
            ]);
            $liked = true;
        }

        return response()->json([
            'success' => true,
            'liked' => $liked,
            'likes_count' => $comment->likes()->count(),
        ]);
    }

    /**
     * Reply to a comment.
     */
    public function reply(Request $request, Comment $comment)
    {
        $request->validate([
            'content' => 'required|string|max:500',
        ]);

        $reply = Comment::create([
            'post_id' => $comment->post_id,
            'user_id' => Auth::id(),
            'parent_id' => $comment->id,
            'content' => $request->input('content'),
        ]);

        return response()->json([
            'success' => true,
            'reply' => $reply->load('user'),
        ]);
    }
}
