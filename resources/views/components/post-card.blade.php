@props(['post'])

<div class="post" id="post-{{ $post->id }}" style="background: transparent; padding: 12px 10px; border-bottom: 1px solid #E2E8F0; border-radius: 0; box-shadow: none; margin-bottom: 8px; transition: transform 0.2s ease; position: relative;">
    @if($post->user_id == Auth::id())
        <div class="post-actions-dropdown" style="position: absolute; top: 12px; right: 10px; z-index: 10;">
            <button class="post-actions-trigger" style="background: none; border: none; padding: 4px 8px; font-size: 16px; cursor: pointer; color: #a0aec0; transition: color 0.2s ease;" title="Post options">
                <i class="fas fa-ellipsis-v"></i>
            </button>
            <div class="post-actions-menu" style="display: none; position: absolute; right: 0; top: 28px; background: white; border: 1px solid #e2e8f0; border-radius: 12px; box-shadow: 0 10px 25px rgba(0,0,0,0.1); z-index: 100; min-width: 120px; overflow: hidden; padding: 4px 0;">
                <button class="post-action-edit" 
                        data-id="{{ $post->id }}" 
                        data-content="{{ $post->content }}"
                        data-type="{{ $post->type }}"
                        data-action-text="{{ $post->action_text }}"
                        data-action-link="{{ $post->action_link }}"
                        data-attachment="{{ $post->attachment }}"
                        style="display: flex; align-items: center; gap: 8px; width: 100%; border: none; background: none; padding: 10px 16px; font-size: 13px; text-align: left; cursor: pointer; color: #4a5568; transition: background 0.2s; font-weight: 600;">
                    <i class="fas fa-pencil-alt" style="font-size: 11px; color: #00AAFF;"></i> Edit
                </button>
                <button class="post-action-delete" 
                        data-id="{{ $post->id }}" 
                        style="display: flex; align-items: center; gap: 8px; width: 100%; border: none; background: none; padding: 10px 16px; font-size: 13px; text-align: left; cursor: pointer; color: #e53e3e; transition: background 0.2s; font-weight: 600;">
                    <i class="fas fa-trash-alt" style="font-size: 11px; color: #e53e3e;"></i> Delete
                </button>
            </div>
        </div>
    @endif

    <div class="post-top">
        <!-- Avatar with Flex Fallback -->
        <div class="avatar">
            @if($post->user->profile_image)
                <img src="{{ Str::startsWith($post->user->profile_image, 'http') ? $post->user->profile_image : asset('storage/' . $post->user->profile_image) }}" style="width: 100%; height: 100%; border-radius: 50%; object-fit: cover;" onerror="this.src='https://ui-avatars.com/api/?name={{ urlencode($post->user->name) }}&color=00AAFF&background=E0F7FA'">
            @else
                👨‍🎓
            @endif
        </div>
        <div style="flex: 1; padding-right: 24px;">
            <h4>{{ $post->user->name }} <span>{{ $post->user->batch ?? 'Batch' }} | {{ $post->user->department ?? 'Dept' }}</span></h4>
            <p class="post-text-content">{{ $post->content }}</p>
        </div>
    </div>

    @if($post->attachment)
        <a class="file" href="{{ Str::startsWith($post->attachment, 'http') ? $post->attachment : asset('storage/' . $post->attachment) }}" target="_blank" style="margin-top: 10px; display: inline-flex; align-items: center; gap: 6px; padding: 6px 14px; background: rgba(0,170,255,0.08); color: #00AAFF; border-radius: 10px; font-size: 13px; font-weight: 600; text-decoration: none;">
            <i class="fas fa-paperclip"></i> View File
        </a>
    @endif

    @if($post->action_text)
        <a class="join" href="{{ $post->action_link && Str::startsWith($post->action_link, 'http') ? $post->action_link : 'http://' . ($post->action_link ?? '#') }}" target="_blank" style="margin-top: 10px; display: inline-flex; align-items: center; gap: 6px; padding: 6px 14px; background: #fff; border: 1.5px solid #1A202C; color: #1A202C; border-radius: 10px; font-size: 13px; font-weight: 700; text-decoration: none; margin-left: 8px; transition: all 0.2s ease;">
            <i class="fas fa-external-link-alt"></i> {{ $post->action_text }}
        </a>
    @endif

    <div class="meta" style="display: flex; gap: 18px; margin-top: 8px; font-weight: 700; font-size: 13px; color: #555;">
        <!-- Like Trigger -->
        <span class="like-btn" data-id="{{ $post->id }}" style="cursor: pointer; display: flex; align-items: center; gap: 6px; transition: color 0.2s ease;">
            <i class="{{ $post->isLikedBy(auth()->user()) ? 'fas' : 'far' }} fa-heart" style="{{ $post->isLikedBy(auth()->user()) ? 'color: #E0245E;' : 'color: #718096;' }}"></i> 
            <span class="count">{{ $post->likes->count() }}</span>
        </span>

        <!-- Comment Trigger -->
        <span class="comment-trigger" data-id="{{ $post->id }}" style="cursor: pointer; display: flex; align-items: center; gap: 6px; color: #718096;">
            <i class="far fa-comment-dots"></i> 
            <span class="count">{{ $post->comments->count() }}</span>
        </span>
        
        <span style="margin-left: auto; font-weight: 500; font-size: 12px; color: #A0AEC0;">{{ $post->created_at->format('j M, Y') }}</span>
    </div>

    <!-- Collapsible Comment Drawer -->
    <div class="comment-section" id="comments-{{ $post->id }}" style="display: none; margin-top: 15px; border-top: 1px dashed #EDF2F7; padding-top: 15px;">
        <div class="comments-list" style="max-height: 200px; overflow-y: auto; display: flex; flex-direction: column; gap: 10px; margin-bottom: 12px;">
            @forelse($post->comments->where('parent_id', null) as $comment)
                <div class="comment-item" id="comment-{{ $comment->id }}" style="display: flex; gap: 10px; align-items: flex-start; margin-bottom: 12px;">
                    <div style="width: 32px; height: 32px; min-width: 32px; border-radius: 50%; overflow: hidden; background: #E2E8F0; display: flex; align-items: center; justify-content: center; font-size: 16px;">
                        @if($comment->user->profile_image)
                            <img src="{{ Str::startsWith($comment->user->profile_image, 'http') ? $comment->user->profile_image : asset('storage/' . $comment->user->profile_image) }}" style="width:100%; height:100%; object-fit:cover;" onerror="this.src='https://ui-avatars.com/api/?name={{ urlencode($comment->user->name) }}&color=00AAFF&background=E0F7FA'">
                        @else
                            🎓
                        @endif
                    </div>
                    <div style="flex: 1;">
                        <div style="background: #F0F2F5; padding: 10px 14px; border-radius: 18px; display: inline-block; max-width: 88%; position: relative; box-shadow: 0 1px 2px rgba(0,0,0,0.03);">
                            <span style="font-weight: 700; color: #1A202C; font-size: 13px; display: block; margin-bottom: 2px;">{{ $comment->user->name }}</span>
                            <span class="comment-content" style="color: #4A5568; font-size: 13px; line-height: 1.4; word-break: break-word;">{{ $comment->content }}</span>
                            
                            @if($comment->user_id == Auth::id())
                                <div style="position: absolute; top: 8px; right: -25px; display: flex; flex-direction: column; gap: 4px;">
                                    <i class="fas fa-pencil-alt edit-comment-btn" data-id="{{ $comment->id }}" style="cursor: pointer; color: #A0AEC0; font-size: 10px;" title="Edit"></i>
                                    <i class="fas fa-trash-alt delete-comment-btn" data-id="{{ $comment->id }}" style="cursor: pointer; color: #FC8181; font-size: 10px;" title="Delete"></i>
                                </div>
                            @endif
                        </div>
                        <div style="display: flex; gap: 12px; font-size: 11px; color: #718096; margin-top: 4px; margin-left: 10px; font-weight: 600;">
                            <span>{{ $comment->created_at->diffForHumans() }}</span>
                            <span class="comment-like-btn" data-id="{{ $comment->id }}" style="cursor: pointer; color: {{ $comment->isLikedBy(auth()->user()) ? '#00AAFF' : '#4A5568' }};">
                                Like <span class="like-count">{{ $comment->likes->count() > 0 ? '(' . $comment->likes->count() . ')' : '' }}</span>
                            </span>
                            <span class="comment-reply-btn" data-id="{{ $comment->id }}" data-username="{{ $comment->user->name }}" style="cursor: pointer; color: #4A5568;">Reply</span>
                        </div>

                        <!-- Replies List -->
                        <div class="replies-list" id="replies-{{ $comment->id }}" style="margin-left: 20px; margin-top: 8px; display: flex; flex-direction: column; gap: 8px;">
                            @foreach($comment->replies as $reply)
                                <div class="comment-item" id="comment-{{ $reply->id }}" style="display: flex; gap: 8px; align-items: flex-start;">
                                    <div style="width: 24px; height: 24px; min-width: 24px; border-radius: 50%; overflow: hidden; background: #E2E8F0; display: flex; align-items: center; justify-content: center; font-size: 12px;">
                                        @if($reply->user->profile_image)
                                            <img src="{{ Str::startsWith($reply->user->profile_image, 'http') ? $reply->user->profile_image : asset('storage/' . $reply->user->profile_image) }}" style="width:100%; height:100%; object-fit:cover;" onerror="this.src='https://ui-avatars.com/api/?name={{ urlencode($reply->user->name) }}&color=00AAFF&background=E0F7FA'">
                                        @else
                                            🎓
                                        @endif
                                    </div>
                                    <div style="flex: 1;">
                                        <div style="background: #F0F2F5; padding: 6px 10px; border-radius: 14px; display: inline-block; max-width: 90%; position: relative;">
                                            <span style="font-weight: 700; color: #1A202C; font-size: 12px; display: block;">{{ $reply->user->name }}</span>
                                            <span class="comment-content" style="color: #4A5568; font-size: 12px;">{{ $reply->content }}</span>
                                            
                                            @if($reply->user_id == Auth::id())
                                                <div style="position: absolute; top: 5px; right: -20px; display: flex; flex-direction: column; gap: 2px;">
                                                    <i class="fas fa-pencil-alt edit-comment-btn" data-id="{{ $reply->id }}" style="cursor: pointer; color: #A0AEC0; font-size: 9px;" title="Edit"></i>
                                                    <i class="fas fa-trash-alt delete-comment-btn" data-id="{{ $reply->id }}" style="cursor: pointer; color: #FC8181; font-size: 9px;" title="Delete"></i>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            @empty
                <p class="no-comments" style="text-align: center; color: #A0AEC0; font-size: 12px; padding: 10px 0;">No comments yet. Be the first!</p>
            @endforelse
        </div>

        <!-- Add Comment Form -->
        <form class="comment-form" data-id="{{ $post->id }}" style="display: flex; gap: 8px; align-items: center; margin-top: 10px;">
            <input type="hidden" name="parent_id" value="">
            <input type="text" name="content" class="comment-input" placeholder="Write a comment..." style="flex: 1; padding: 8px 15px; border: 1px solid #E2E8F0; border-radius: 20px; font-size: 13px; outline: none; transition: border-color 0.2s ease;">
            <button type="submit" class="send-comment-btn" style="width: 32px; height: 32px; border-radius: 50%; background: #00AAFF; color: white; border: none; display: flex; align-items: center; justify-content: center; cursor: pointer; transition: transform 0.2s ease;">
                <i class="fas fa-paper-plane" style="font-size: 12px;"></i>
            </button>
        </form>
    </div>
</div>
