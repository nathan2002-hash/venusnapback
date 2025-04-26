<?php

namespace App\Http\Controllers\Api;

use Carbon\Carbon;
use App\Models\Comment;
use App\Models\PostMedia;
use App\Models\CommentReply;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Jobs\CreateNotificationJob;

class CommentController extends Controller
{
    public function getCommentsAndReplies($postMediaId, Request $request)
    {
        // Pagination parameters for comments
        $commentPage = $request->query('comment_page', 1); // Default to page 1
        $commentLimit = $request->query('comment_limit', 10); // Default to 10 comments per page

        // Fetch comments with replies that are active, ordered by creation date (newest first)
        $comments = Comment::with(['commentreplies' => function ($query) {
                $query->where('status', 'active'); // Only get active replies
            }, 'commentreplies.user', 'user',  'postmedia.post.album'])
            ->where('post_media_id', $postMediaId)
            ->where('status', 'active') // Only get active comments
            ->orderBy('created_at', 'desc')
            ->paginate($commentLimit, ['*'], 'comment_page', $commentPage);

            $formattedComments = $comments->map(function ($comment) use ($request) {
                $postOwnerId = $comment->postmedia->post->user_id;
                $album = $comment->postmedia->post->album;

                $replyPage = $request->query('reply_page', 1);
                $replyLimit = $request->query('reply_limit', 5);

                $replies = $comment->commentreplies()
                    ->where('status', 'active')
                    ->with('user')
                    ->orderBy('created_at', 'desc')
                    ->paginate($replyLimit, ['*'], 'reply_page', $replyPage);

                $isCommentOwner = $comment->user_id === $postOwnerId;

                return [
                    'id' => $comment->id,
                    'user_id' => $comment->user_id,
                    'username' => $isCommentOwner ? ($album->name ?? 'Unknown Album') : $comment->user->name,
                    'profile_picture_url' => $isCommentOwner ? $this->getProfileUrl($album) : (
                        $comment->user->profile_compressed
                            ? Storage::disk('s3')->url($comment->user->profile_compressed)
                            : 'https://www.gravatar.com/avatar/' . md5(strtolower(trim($comment->user->email))) . '?s=100&d=mp'
                    ),
                    'comment' => $comment->comment,
                    'created_at' => Carbon::parse($comment->created_at)->diffForHumans(),
                    'total_replies' => $comment->commentreplies()->where('status', 'active')->count(),
                    'commentreplies' => $replies->map(function ($commentreply) use ($postOwnerId, $album) {
                        $isReplyOwner = $commentreply->user_id === $postOwnerId;

                        return [
                            'id' => $commentreply->id,
                            'user_id' => $commentreply->user_id,
                            'username' => $isReplyOwner ? ($album->name ?? 'Unknown Album') : $commentreply->user->name,
                            'profile_picture_url' => $isReplyOwner ? $this->getProfileUrl($album) : (
                                $commentreply->user->profile_compressed
                                    ? Storage::disk('s3')->url($commentreply->user->profile_compressed)
                                    : 'https://www.gravatar.com/avatar/' . md5(strtolower(trim($commentreply->user->email))) . '?s=100&d=mp'
                            ),
                            'reply' => $commentreply->reply,
                            'created_at' => Carbon::parse($commentreply->created_at)->diffForHumans(),
                        ];
                    })->toArray(),
                    'replies_next_page' => $replies->hasMorePages() ? $replyPage + 1 : null,
                ];
            });

        return response()->json([
            'comments' => $formattedComments->toArray(), // Convert comments to array
            'comments_next_page' => $comments->hasMorePages() ? $commentPage + 1 : null, // Next page for comments
        ]);
    }


    public function storeComment(Request $request, $id)
    {
        $request->validate([
            'comment' => 'required|string',
        ]);

        $user = Auth::user();
        $comment = new Comment();
        $comment->user_id = $user->id;
        $comment->post_media_id = $id;
        $comment->comment = $request->comment;
        $comment->status = 'active';
        $comment->save();

        $comment->load('user');
        $postMedia = PostMedia::with('post.album')->find($id);

        if (!$postMedia || !$postMedia->post) {
            return response()->json(['message' => 'Post or post media not found'], 404);
        }

        $postOwnerId = $postMedia->post->album->user_id ?? $postMedia->post->user_id; // fallback
        $album = $postMedia->post->album;

        $isOwner = $user->id === $postOwnerId;

        if (!$isOwner) {
            CreateNotificationJob::dispatch(
                $user,
                $postMedia,
                'commented',
                $postOwnerId,
                [
                    'username' => $user->name,
                    'post_id' => $postMedia->post->id,
                    'media_id' => $postMedia->id,
                    'album_id' => $postMedia->post->album_id ?? null
                ]
            );
        }

        return response()->json([
            'id' => $comment->id,
            'comment' => $comment->comment,
            'post_media_id' => $comment->post_media_id,
            'user_id' => $comment->user->id,
            'username' => $isOwner ? ($album->name ?? 'Unknown Album') : $comment->user->name,
            'profile_picture_url' => $isOwner
                ? $this->getProfileUrl($album)
                : (
                    $comment->user->profile_compressed
                        ? Storage::disk('s3')->url($comment->user->profile_compressed)
                        : 'https://www.gravatar.com/avatar/' . md5(strtolower(trim($comment->user->email))) . '?s=100&d=mp'
                ),
            'created_at' => Carbon::parse($comment->created_at)->diffForHumans(),
            'commentreplies' => [],
            'total_replies' => 0,
        ], 201);
    }


    public function storeReply(Request $request, $id)
    {
        $request->validate([
            'reply' => 'required|string',
        ]);

        $user = Auth::user();

        $reply = new CommentReply();
        $reply->user_id = $user->id;
        $reply->comment_id = $id;
        $reply->reply = $request->reply;
        $reply->status = 'active';
        $reply->save();

        $reply->load('user');

        $comment = Comment::find($id);
        $postMedia = PostMedia::with('post.album')->find($comment->post_media_id);

        if (!$postMedia || !$postMedia->post) {
            return response()->json(['message' => 'Post or post media not found'], 404);
        }

        $postOwnerId = $postMedia->post->album->user_id ?? $postMedia->post->user_id; // fallback
        $album = $postMedia->post->album;

        $isOwner = $user->id === $postOwnerId;

        if (!$isOwner) {
            CreateNotificationJob::dispatch(
                $user,
                $postMedia,
                'commented',
                $postOwnerId,
                [
                    'username' => $user->name,
                    'post_id' => $postMedia->post->id,
                    'media_id' => $postMedia->id,
                    'comment_id' => $comment->id,
                    'album_id' => $postMedia->post->album_id ?? null
                ]
            );
        }

        return response()->json([
            'id' => $reply->id,
            'reply' => $reply->reply,
            'comment_id' => $reply->comment_id,
            'user_id' => $reply->user->id,
            'username' => $isOwner ? ($album->name ?? 'Unknown Album') : $reply->user->name,
            'profile_picture_url' => $isOwner
                ? $this->getProfileUrl($album)
                : (
                    $reply->user->profile_compressed
                        ? Storage::disk('s3')->url($reply->user->profile_compressed)
                        : 'https://www.gravatar.com/avatar/' . md5(strtolower(trim($reply->user->email))) . '?s=100&d=mp'
                ),
            'created_at' => Carbon::parse($reply->created_at)->diffForHumans(),
        ], 201);
    }


    private function getProfileUrl($album)
    {
        if (!$album) {
            return asset('default/profile.png');
        }

        if (in_array($album->type, ['personal', 'creator'])) {
            return $album->thumbnail_compressed
                ? Storage::disk('s3')->url($album->thumbnail_compressed)
                : ($album->thumbnail_original
                    ? Storage::disk('s3')->url($album->thumbnail_original)
                    : asset('default/profile.png'));
        }

        if ($album->type === 'business') {
            return $album->business_logo_compressed
                ? Storage::disk('s3')->url($album->business_logo_compressed)
                : ($album->business_logo_original
                    ? Storage::disk('s3')->url($album->business_logo_original)
                    : asset('default/profile.png'));
        }

        return asset('default/profile.png');
    }

    public function commentdelete($id)
    {
        // Find the comment by ID
        $comment = Comment::find($id);

        // Check if the comment exists
        if (!$comment) {
            return response()->json([
                'status' => 'error',
                'message' => 'Comment not found.'
            ], 404);
        }

        // Check if the authenticated user is the owner of the comment
        if (Auth::id() !== $comment->user_id) {
            return response()->json([
                'status' => 'error',
                'message' => 'You are not authorized to delete this comment.'
            ], 403);
        }

        // Mark the comment as deleted
        $comment->status = 'deleted';
        $comment->save();

        // Also delete all associated replies for this comment
        $comment->commentreplies()->update(['status' => 'deleted']);

        return response()->json([
            'status' => 'success',
            'message' => 'Comment and associated replies deleted successfully.'
        ], 200);
    }

    public function commentreplydelete($id)
    {
        // Find the comment reply by ID
        $commentreply = CommentReply::find($id);

        // Check if the reply exists
        if (!$commentreply) {
            return response()->json([
                'status' => 'error',
                'message' => 'Comment reply not found.'
            ], 404);
        }

        // Check if the authenticated user is the owner of the reply
        if (Auth::id() !== $commentreply->user_id) {
            return response()->json([
                'status' => 'error',
                'message' => 'You are not authorized to delete this reply.'
            ], 403);
        }

        // Mark the reply as deleted
        $commentreply->status = 'deleted';
        $commentreply->save();

        return response()->json([
            'status' => 'success',
            'message' => 'Comment reply deleted successfully.'
        ], 200);
    }

}
