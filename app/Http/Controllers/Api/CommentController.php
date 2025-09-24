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
    $authUser = Auth::user();

    // Determine current user's profile picture (if authenticated)
    $currentUserProfile = null;
    if ($authUser) {
        $isCurrentUserAlbumOwner = $authUser->id == $albumOwnerId;
        $currentUserProfile = $isCurrentUserAlbumOwner
            ? $this->getProfileUrl($album)
            : ($authUser->profile_compressed
                ? generateSecureMediaUrl($authUser->profile_compressed)
                : 'https://www.gravatar.com/avatar/' . md5(strtolower(trim($authUser->email))) . '?s=100&d=mp');
    }

    $formattedComments = $comments->map(function ($comment) use ($album, $albumOwnerId, $authUserId) {
        $isOwner = $comment->user_id == $albumOwnerId;

        return [
            'id' => $comment->id,
            'user_id' => $comment->user_id,
            'username' => $isOwner ? $album->name : $comment->user->name,
            'profile_picture_url' => $isOwner
                ? $this->getProfileUrl($album)
                : ($comment->user->profile_compressed
                    ? generateSecureMediaUrl($comment->user->profile_compressed)
                    : 'https://www.gravatar.com/avatar/' . md5(strtolower(trim($comment->user->email))) . '?s=100&d=mp'),
            'comment' => $comment->comment,
            'created_at' => $comment->created_at->diffForHumans(),
            'total_replies' => $comment->commentreplies()->where('status', 'active')->count(),
            'is_owner' => $isOwner,
            'is_comment_owner' => $authUserId && $comment->user_id == $authUserId,
            'type' => $comment->type ?? 'text', // Default to 'text' if null
            'gif_id' => $comment->gif_id,
            'gif_url' => $comment->gif_url,
            'gif_provider' => $comment->gif_provider,
        ];
    });

    return response()->json([
        'comments' => $formattedComments,
        'has_more' => $comments->hasMorePages(),
        'current_user_profile' => $currentUserProfile, // Add this
        'album_owner_id' => $albumOwnerId, // Add this
    ]);
}
    public function getCommentReplies($commentId, Request $request)
    {
        // Validate input
        if (!is_numeric($commentId)) {
            return response()->json(['message' => 'Invalid comment ID'], 400);
        }

        $replyPage = $request->query('page', 1);
        $replyLimit = $request->query('limit', 10);

        // Find comment with minimal relationships needed for owner check
        $comment = Comment::with('postmedia.post.album.user')->find($commentId);

        if (!$comment) {
            return response()->json(['message' => 'Comment not found'], 404);
        }

        $postMedia = PostMedia::with('post.album.user')->find($comment->post_media_id);

        if (!$postMedia || !$postMedia->post || !$postMedia->post->album || !$postMedia->post->album->user) {
            return response()->json(['message' => 'Post media, post, or album not found'], 404);
        }

        $album = $postMedia->post->album;
        $albumOwnerId = $album->user_id;

        // Get replies for this comment
        $replies = CommentReply::with('user')
            ->where('comment_id', $commentId)
            ->where('status', 'active')
            ->orderBy('created_at', 'desc')
            ->paginate($replyLimit, ['*'], 'page', $replyPage);

        $authUserId = Auth::check() ? Auth::id() : null;
        $currentUserProfile = null;
        $authUser = Auth::user();

        if ($authUser) {
            $isCurrentUserAlbumOwner = $authUser->id == $albumOwnerId;
            $currentUserProfile = $isCurrentUserAlbumOwner
                ? $this->getProfileUrl($album)
                : ($authUser->profile_compressed
                    ? generateSecureMediaUrl($authUser->profile_compressed)
                    : 'https://www.gravatar.com/avatar/' . md5(strtolower(trim($authUser->email))) . '?s=100&d=mp');
        }

        $formattedReplies = $replies->map(function ($reply) use ($authUserId, $album, $albumOwnerId) {
            $isOwner = $albumOwnerId && ($reply->user_id == $albumOwnerId);

            return [
                'id' => $reply->id,
                'user_id' => $reply->user_id,
                'username' => $isOwner ? $album->name : $reply->user->name,
                'profile_picture_url' => $isOwner
                    ? $this->getProfileUrl($album)
                    : ($reply->user->profile_compressed
                        ? generateSecureMediaUrl($reply->user->profile_compressed)
                        : 'https://www.gravatar.com/avatar/' . md5(strtolower(trim($reply->user->email))) . '?s=100&d=mp'),
                'reply' => $reply->reply,
                'created_at' => $reply->created_at->diffForHumans(),
                'is_owner' => $isOwner,
                'is_reply_owner' => $authUserId && $reply->user_id == $authUserId,
                // Add GIF fields
                'type' => $reply->type ?? 'text', // Default to 'text' if null
                'gif_id' => $reply->gif_id,
                'gif_url' => $reply->gif_url,
                'gif_provider' => $reply->gif_provider,
            ];
        });

        return response()->json([
            'replies' => $formattedReplies,
            'has_more' => $replies->hasMorePages(),
            'current_user_profile' => $currentUserProfile,
            'album_owner_id' => $albumOwnerId,
        ]);
    }

    public function storeComment(Request $request, $id)
    {
        // $request->validate([
        //     'comment' => 'required_if:type,text|string|nullable',
        //     'type' => 'required|in:text,gif',
        //     'gif_id' => 'required_if:type,gif|string|nullable',
        //     'gif_url' => 'required_if:type,gif|url|nullable',
        //     'gif_provider' => 'required_if:type,gif|in:giphy,tenor|nullable',
        //     'gif_width' => 'required_if:type,gif|integer|nullable',
        //     'gif_height' => 'required_if:type,gif|integer|nullable',
        // ]);

        $user = Auth::user();
        $comment = new Comment();
        $comment->user_id = $user->id;
        $comment->post_media_id = $id;
        $comment->type = $request->type;

        if ($request->type === 'text') {
            $comment->comment = $request->comment;
        } else {
            // For GIF comments, store GIF data
            $comment->comment = null;
            $comment->gif_id = $request->gif_id;
            $comment->gif_url = $request->gif_url;
            $comment->gif_provider = $request->gif_provider;
        }

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
                ? generateSecureMediaUrl($user->profile_compressed)
                : 'https://www.gravatar.com/avatar/' . md5(strtolower(trim($user->email))) . '?s=100&d=mp');

        // Send notification if not owner (only for text comments to avoid spam)
        if ($albumOwnerId !== $user->id && $request->type === 'text') {
            CreateNotificationJob::dispatch(
                $user,
                $postMedia,
                'commented',
                $albumOwnerId,
                [
                    'username' => $user->name,
                    'post_id' => $postMedia->post->id,
                    'media_id' => $postMedia->id,
                    'album_id' => $album->id,
                    'album_name' => $album->name,
                    'comment_id' => $comment->id,
                    'comment_type' => $request->type
                ]
            );
        }

        return response()->json([
            'id' => $comment->id,
            'comment' => $comment->comment, // Will be null for GIFs
            'post_media_id' => $comment->post_media_id,
            'user_id' => $user->id,
            'username' => $displayName,
            'profile_picture_url' => $profilePictureUrl,
            'created_at' => Carbon::parse($comment->created_at)->diffForHumans(),
            'commentreplies' => [],
            'total_replies' => 0,
            'is_owner' => $isOwner,
            'type' => $comment->type,
            'gif_id' => $comment->gif_id,
            'gif_url' => $comment->gif_url,
            'gif_provider' => $comment->gif_provider,
        ], 201);
    }

    public function storeReply(Request $request, $id)
    {
        // $request->validate([
        //     'reply' => 'required_if:type,text|string|nullable',
        //     'type' => 'required|in:text,gif',
        //     'gif_id' => 'required_if:type,gif|string|nullable',
        //     'gif_url' => 'required_if:type,gif|url|nullable',
        //     'gif_provider' => 'required_if:type,gif|in:giphy,tenor|nullable',
        //     'gif_width' => 'required_if:type,gif|integer|nullable',
        //     'gif_height' => 'required_if:type,gif|integer|nullable',
        // ]);

        $user = Auth::user();

        // Create the reply
        $reply = new CommentReply();
        $reply->user_id = $user->id;
        $reply->comment_id = $id;
        $reply->type = $request->type;

        if ($request->type === 'text') {
            $reply->reply = $request->reply;
        } else {
            // For GIF replies, store GIF data
            $reply->reply = null;
            $reply->gif_id = $request->gif_id;
            $reply->gif_url = $request->gif_url;
            $reply->gif_provider = $request->gif_provider;
        }

        $reply->status = 'active';
        $reply->save();

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
                ? generateSecureMediaUrl($user->profile_compressed)
                : 'https://www.gravatar.com/avatar/' . md5(strtolower(trim($user->email))) . '?s=100&d=mp');

        // Send notification only for text replies to avoid spam
        if ((int)$comment->user_id !== (int)$user->id && $request->type === 'text') {
            CreateNotificationJob::dispatch(
                $user,
                $postMedia,
                'replied',
                $comment->user_id,
                [
                    'username' => $displayName,
                    'post_id' => $postMedia->post->id,
                    'media_id' => $postMedia->id,
                    'comment_id' => $comment->id,
                    'reply_id' => $reply->id,
                    'album_id' => $album->id,
                    'album_name' => $album->name,
                    'is_reply' => true,
                    'is_album_owner' => $isOwner,
                    'reply_type' => $request->type
                ]
            );
        }

        return response()->json([
            'id' => $reply->id,
            'reply' => $reply->reply, // Will be null for GIFs
            'comment_id' => $reply->comment_id,
            'user_id' => $user->id,
            'username' => $displayName,
            'profile_picture_url' => $profilePictureUrl,
            'created_at' => Carbon::parse($reply->created_at)->diffForHumans(),
            'is_owner' => $isOwner,
            'type' => $reply->type,
            'gif_id' => $reply->gif_id,
            'gif_url' => $reply->gif_url,
            'gif_provider' => $reply->gif_provider,
            'gif_width' => $reply->gif_width,
            'gif_height' => $reply->gif_height,
        ], 201);
    }

    private function getProfileUrl($album)
    {
        if (!$album) {
            return asset('default/profile.png');
        }

        if (in_array($album->type, ['personal', 'creator'])) {
            return $album->thumbnail_compressed
                ? generateSecureMediaUrl($album->thumbnail_compressed)
                : ($album->thumbnail_original
                    ? generateSecureMediaUrl($album->thumbnail_original)
                    : asset('default/profile.png'));
        }

        if ($album->type === 'business') {
            return $album->business_logo_compressed
                ? generateSecureMediaUrl($album->business_logo_compressed)
                : ($album->business_logo_original
                    ? generateSecureMediaUrl($album->business_logo_original)
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
