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
        // First, get the post media with album and user info
        $postMedia = PostMedia::with(['post.album.user'])->find($postMediaId);

        if (!$postMedia || !$postMedia->post || !$postMedia->post->album) {
            return response()->json(['message' => 'Post media, post, or album not found'], 404);
        }

        $album = $postMedia->post->album;
        $albumOwnerId = $album->user_id;

        // Pagination parameters for comments
        $commentPage = $request->query('comment_page', 1);
        $commentLimit = $request->query('comment_limit', 10);

        // Fetch comments with replies
        $comments = Comment::with([
                'commentreplies' => function ($query) {
                    $query->where('status', 'active');
                },
                'commentreplies.user',
                'user',
                'postMedia.post.album.user' // Ensure we have album info
            ])
            ->where('post_media_id', $postMediaId)
            ->where('status', 'active')
            ->orderBy('created_at', 'desc')
            ->paginate($commentLimit, ['*'], 'comment_page', $commentPage);

        // Format the response
        $formattedComments = $comments->map(function ($comment) use ($request, $album, $albumOwnerId) {
            // Pagination parameters for replies
            $replyPage = $request->query('reply_page', 1);
            $replyLimit = $request->query('reply_limit', 5);


            // Determine if comment is from album owner
            $isOwnerComment = ($comment->user_id == $albumOwnerId);
            $commentUsername = $isOwnerComment ? $album->name : $comment->user->name;
            $commentProfilePic = $isOwnerComment
                ? $this->getProfileUrl($album)
                : ($comment->user->profile_compressed
                    ? Storage::disk('s3')->url($comment->user->profile_compressed)
                    : 'https://www.gravatar.com/avatar/' . md5(strtolower(trim($comment->user->email))) . '?s=100&d=mp');

            // Fetch replies for the current comment with pagination
            $replies = $comment->commentreplies()
                ->where('status', 'active')
                ->with('user')
                ->orderBy('created_at', 'desc')
                ->paginate($replyLimit, ['*'], 'reply_page', $replyPage);

            $formattedReplies = $replies->map(function ($commentreply) use ($album, $albumOwnerId) {
                // Determine if reply is from album owner
                $isOwnerReply = ($commentreply->user_id == $albumOwnerId);
                $replyUsername = $isOwnerReply ? $album->name : $commentreply->user->name;
                $replyProfilePic = $isOwnerReply
                    ? $this->getProfileUrl($album)
                    : ($commentreply->user->profile_compressed
                        ? Storage::disk('s3')->url($commentreply->user->profile_compressed)
                        : 'https://www.gravatar.com/avatar/' . md5(strtolower(trim($commentreply->user->email))) . '?s=100&d=mp');

                return [
                    'id' => $commentreply->id,
                    'user_id' => $commentreply->user_id,
                    'username' => $replyUsername,
                    'profile_picture_url' => $replyProfilePic,
                    'reply' => $commentreply->reply,
                    'created_at' => Carbon::parse($commentreply->created_at)->diffForHumans(),
                    'is_owner' => $isOwnerReply
                ];
            })->toArray();

            return [
                'id' => $comment->id,
                'user_id' => $comment->user_id,
                'username' => $commentUsername,
                'profile_picture_url' => $commentProfilePic,
                'comment' => $comment->comment,
                'created_at' => Carbon::parse($comment->created_at)->diffForHumans(),
                'total_replies' => $comment->commentreplies()->where('status', 'active')->count(),
                'commentreplies' => $formattedReplies,
                'replies_next_page' => $replies->hasMorePages() ? $replyPage + 1 : null,
                'is_owner' => $isOwnerComment,
            ];
        });

        return response()->json([
            'comments' => $formattedComments->toArray(),
            'comments_next_page' => $comments->hasMorePages() ? $commentPage + 1 : null,
        ]);
    }

    public function getBasicComments($postMediaId, Request $request)
    {
        $commentPage = $request->query('page', 1);
        $commentLimit = $request->query('limit', 10);

        // Get post media with the needed relationships
        $postMedia = PostMedia::with('post.album.user')->find($postMediaId);

        // Handle missing or broken relationships
        if (!$postMedia || !$postMedia->post || !$postMedia->post->album || !$postMedia->post->album->user) {
            return response()->json(['message' => 'Post media, post, or album not found'], 404);
        }

        $album = $postMedia->post->album;
        $albumOwnerId = $album->user_id;
        $albumName = $album->name;

        // Load comments
        $comments = Comment::with('user')
            ->where('post_media_id', $postMediaId)
            ->where('status', 'active')
            ->orderBy('created_at', 'desc')
            ->paginate($commentLimit, ['*'], 'page', $commentPage);

        $authUserId = Auth::check() ? Auth::id() : null;

        $formattedComments = $comments->map(function ($comment) use ($album, $albumOwnerId, $authUserId) {
            $isOwner = $comment->user_id == $albumOwnerId;

            return [
                'id' => $comment->id,
                'user_id' => $comment->user_id,
                'username' => $isOwner ? $album->name : $comment->user->name,
                'profile_picture_url' => $isOwner
                    ? $this->getProfileUrl($album)
                    : ($comment->user->profile_compressed
                        ? Storage::disk('s3')->url($comment->user->profile_compressed)
                        : 'https://www.gravatar.com/avatar/' . md5(strtolower(trim($comment->user->email))) . '?s=100&d=mp'),
                'comment' => $comment->comment,
                'created_at' => $comment->created_at->diffForHumans(),
                'total_replies' => $comment->commentreplies()->where('status', 'active')->count(),
                'is_owner' => $isOwner,
                'is_comment_owner' => $authUserId && $comment->user_id == $authUserId,
            ];
        });

        return response()->json([
            'comments' => $formattedComments,
            'has_more' => $comments->hasMorePages(),
        ]);
    }



    public function getCommentReplies($commentId, Request $request)
    {
        $replyPage = $request->query('page', 1);
        $replyLimit = $request->query('limit', 10);

        // Load comment with related models
        $comment = Comment::with('postMedia.post.album.user')->find($commentId);

        // Check if comment exists
        if (!$comment) {
            return response()->json(['message' => 'Comment not found'], 404);
        }

        // Safely extract album and owner if they exist

        $album = $comment->postMedia->post->album;
        //$albumOwnerId = optional($album)->user_id;

        $albumOwnerId = $album->user_id;

        $authUserId = Auth::check() ? Auth::id() : null;

        // Get replies
        $replies = CommentReply::with('user')
            ->where('comment_id', $commentId)
            ->where('status', 'active')
            ->orderBy('created_at', 'desc')
            ->paginate($replyLimit, ['*'], 'page', $replyPage);

        $formattedReplies = $replies->map(function ($reply) use ($album, $albumOwnerId, $authUserId) {
            //$isOwner = $albumOwnerId && $reply->user_id == $albumOwnerId;
            $isOwnerReply = ($reply->user_id == $albumOwnerId);
            //$isOwner = $reply->user_id == $albumOwnerId;


            return [
                'id' => $reply->id,
                'user_id' => $reply->user_id,
                'username' => $isOwnerReply ? optional($album)->name : $reply->user->name,
                'profile_picture_url' => $isOwnerReply
                    ? $this->getProfileUrl($album)
                    : ($reply->user->profile_compressed
                        ? Storage::disk('s3')->url($reply->user->profile_compressed)
                        : 'https://www.gravatar.com/avatar/' . md5(strtolower(trim($reply->user->email))) . '?s=100&d=mp'),
                'reply' => $reply->reply,
                'created_at' => $reply->created_at->diffForHumans(),
                'is_owner' => $isOwnerReply,
                'is_reply_owner' => $authUserId && $reply->user_id == $authUserId,
            ];
        });

        return response()->json([
            'replies' => $formattedReplies,
            'has_more' => $replies->hasMorePages(),
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

        // Load post media with album and owner info
        $postMedia = PostMedia::with('post.album.user')->find($id);
        if (!$postMedia || !$postMedia->post || !$postMedia->post->album) {
            return response()->json(['message' => 'Post or post media not found'], 404);
        }

        $album = $postMedia->post->album;
        $albumOwnerId = $album->user_id;
        $isOwner = ($user->id === $albumOwnerId);

        // Determine display name and profile picture
        $displayName = $isOwner ? $album->name : $user->name;
        $profilePictureUrl = $isOwner
            ? $this->getProfileUrl($album)
            : ($user->profile_compressed
                ? Storage::disk('s3')->url($user->profile_compressed)
                : 'https://www.gravatar.com/avatar/' . md5(strtolower(trim($user->email))) . '?s=100&d=mp');

        // Send notification if not owner
        if ($albumOwnerId !== $user->id) {
            CreateNotificationJob::dispatch(
                $user,
                $postMedia,
                'commented',
                $albumOwnerId,
                [
                    'username' => $user->name,
                    'post_id' => $postMedia->post->id,
                    'media_id' => $postMedia->id,
                    'album_id' => $album->id
                ]
            );
        }

        return response()->json([
            'id' => $comment->id,
            'comment' => $comment->comment,
            'post_media_id' => $comment->post_media_id,
            'user_id' => $user->id,
            'username' => $displayName,
            'profile_picture_url' => $profilePictureUrl,
            'created_at' => Carbon::parse($comment->created_at)->diffForHumans(),
            'commentreplies' => [],
            'total_replies' => 0,
            'is_owner' => $isOwner // Optional: helpful for frontend
        ], 201);
    }

    public function storeReply(Request $request, $id)
{
    $request->validate([
        'reply' => 'required|string',
    ]);

    $user = Auth::user();

    // Create the reply
    $reply = new CommentReply();
    $reply->user_id = $user->id;
    $reply->comment_id = $id;
    $reply->reply = $request->reply;
    $reply->status = 'active';
    $reply->save();

    $reply->load('user');

    // Load the comment and relationships
    $comment = Comment::find($id);
    if (!$comment) {
        return response()->json(['message' => 'Comment not found'], 404);
    }

    $postMedia = PostMedia::with('post.album.user')->find($comment->post_media_id);
    if (!$postMedia || !$postMedia->post || !$postMedia->post->album) {
        return response()->json(['message' => 'Post media, post, or album not found'], 404);
    }

    $album = $postMedia->post->album;
    $albumOwnerId = $album->user_id;
    $isOwner = ($user->id == $albumOwnerId);

    // Determine display name and profile picture
    $displayName = $isOwner ? $album->name : $user->name;
    $profilePictureUrl = $isOwner
        ? $this->getProfileUrl($album)
        : ($user->profile_compressed
            ? Storage::disk('s3')->url($user->profile_compressed)
            : 'https://www.gravatar.com/avatar/' . md5(strtolower(trim($user->email))) . '?s=100&d=mp');

    // Send notification if not owner
    if ($albumOwnerId !== $user->id) {
        CreateNotificationJob::dispatch(
            $user,
            $postMedia,
            'commented',
            $albumOwnerId,
            [
                'username' => $user->name,
                'post_id' => $postMedia->post->id,
                'media_id' => $postMedia->id,
                'comment_id' => $comment->id,
                'album_id' => $album->id
            ]
        );
    }

    return response()->json([
        'id' => $reply->id,
        'reply' => $reply->reply,
        'comment_id' => $reply->comment_id,
        'user_id' => $user->id,
        'username' => $displayName,  // Now shows album name if owner
        'profile_picture_url' => $profilePictureUrl,  // Now shows album image if owner
        'created_at' => Carbon::parse($reply->created_at)->diffForHumans(),
        'is_owner' => $isOwner  // Helpful for frontend styling
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
        if (Auth::id() != $comment->user_id) {
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
        if (Auth::id() != $commentreply->user_id) {
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
