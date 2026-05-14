<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Post;
use App\Models\Like;
use App\Models\DistrictAssociation;
use App\Models\Talent;
use Illuminate\Support\Facades\Auth;

class PostController extends Controller
{
    /**
     * Display the community page with posts, districts, and talents.
     */
    public function index()
    {
        $posts = Post::with(['user', 'likes', 'comments.user'])
            ->orderBy('created_at', 'desc')
            ->get();

        $districtAssociations = DistrictAssociation::all();
        $talents = Talent::where('status', '=', 'approved')->with('user')->get();

        return view('community', compact('posts', 'districtAssociations', 'talents'));
    }

    /**
     * Store a new community post.
     */
    public function store(Request $request)
    {
        $request->validate([
            'content' => 'required|string|max:1000',
            'attachment' => 'nullable|file|mimes:jpg,jpeg,png,pdf,docx|max:10240',
            'type' => 'nullable|string',
            'action_text' => 'nullable|string|max:50',
            'action_link' => 'nullable|string|max:255',
        ]);

        $attachmentPath = null;
        if ($request->hasFile('attachment')) {
            $attachmentPath = $request->file('attachment')->store('posts/attachments', 'public');
        }

        Post::create([
            'user_id' => Auth::id(),
            'content' => $request->input('content'),
            'attachment' => $attachmentPath,
            'type' => $request->type ?? 'general',
            'action_text' => $request->action_text,
            'action_link' => $request->action_link,
        ]);

        return redirect()->back()->with('success', 'Post created successfully!');
    }

    /**
     * Toggle like on a post.
     */
    public function like(Post $post)
    {
        $like = Like::where('post_id', $post->id)->where('user_id', Auth::id())->first();

        if ($like) {
            $like->delete();
            $liked = false;
        } else {
            Like::create([
                'post_id' => $post->id,
                'user_id' => Auth::id(),
            ]);
            $liked = true;
        }

        return response()->json([
            'success' => true,
            'liked' => $liked,
            'likes_count' => $post->likes()->count(),
        ]);
    }
}
