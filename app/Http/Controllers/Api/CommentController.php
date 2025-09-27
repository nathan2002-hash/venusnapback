<?php

namespace App\Http\Controllers\Api;

use Carbon\Carbon;
use App\Models\Comment;
use App\Models\PostMedia;
use App\Models\CommentReply;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Models\Album;
use App\Jobs\CreateNotificationJob;

class CommentController extends Controller
{
    public function getBasicComments($postMediaId, Request $request)
    {
        $commentPage = $request->query('page', 1);
        $commentLimit = $request->query('limit', 10);

        // Get post media with the needed relationships
        $postMedia = PostMedia::with('post.album.user')->find($postMediaId);

        if (!$postMedia || !$postMedia->post || !$postMedia->post->album || !$postMedia->post->album->user) {
            return response()->json(['message' => 'Post media, post, or album not found'], 404);
        }

        $album = $postMedia->post->album;
        $albumOwnerId = $album->user_id;

        // Load comments with album relationship for comment_as_album_id
        $comments = Comment::with(['user', 'commentAsAlbum'])
            ->where('post_media_id', $postMediaId)
            ->where('status', 'active')
            ->orderBy('created_at', 'desc')
            ->paginate($commentLimit, ['*'], 'page', $commentPage);

        $authUserId = Auth::check() ? Auth::id() : null;
        $authUser = Auth::user();

        // Get user's albums for commenting as (only if user is authenticated)
        $userAlbums = [];
        $currentUserProfile = null;

        if ($authUser) {
            // Get all albums owned by the current user
            $userAlbums = Album::where('user_id', $authUser->id)
                ->where('status', 'active')
                ->get();
                //->get(['id', 'name', 'profile_picture', 'user_id']);

            // Add the user's personal profile as an option too
            $userAlbums->prepend((object)[
                'id' => $authUser->id,
                'name' => $authUser->name,
                'profile_picture' => $authUser->profile_compressed,
                'user_id' => $authUser->id,
                'type' => 'user'
            ]);

            // Current user's default profile (for the input section)
            $currentUserProfile = $authUser->profile_compressed
                ? generateSecureMediaUrl($authUser->profile_compressed)
                : 'https://www.gravatar.com/avatar/' . md5(strtolower(trim($authUser->email))) . '?s=100&d=mp';
        }

        $formattedComments = $comments->map(function ($comment) use ($authUserId) {
            // Determine commenter identity based on comment_as_album_id
            $commenterType = 'user'; // Default to user
            $displayName = $comment->user->name;
            $profilePictureUrl = $comment->user->profile_compressed
                ? generateSecureMediaUrl($comment->user->profile_compressed)
                : 'https://www.gravatar.com/avatar/' . md5(strtolower(trim($comment->user->email))) . '?s=100&d=mp';

            // Check if commenting as album
            if ($comment->comment_as_album_id && $comment->commentAsAlbum) {
                $commenterType = 'album';
                $displayName = $comment->commentAsAlbum->name;
                $profilePictureUrl = $this->getProfileUrl($comment->commentAsAlbum);
            }

            return [
                'id' => $comment->id,
                'user_id' => $comment->user_id,
                'username' => $displayName,
                'profile_picture_url' => $profilePictureUrl,
                'comment' => $comment->comment,
                'created_at' => $comment->created_at->diffForHumans(),
                'total_replies' => $comment->commentreplies()->where('status', 'active')->count(),
                'is_owner' => $commenterType === 'album', // Album comments are considered "owner"
                'is_comment_owner' => $authUserId && $comment->user_id == $authUserId,
                'type' => $comment->type ?? 'text',
                'gif_id' => $comment->gif_id,
                'gif_url' => $comment->gif_url,
                'gif_provider' => $comment->gif_provider,
                'comment_as_album_id' => $comment->comment_as_album_id,
                'commenter_type' => $commenterType, // 'user' or 'album'
                'attachment_path' => $comment->attachment_path ? generateSecureMediaUrl($comment->attachment_path) : null,
                'attachment_type' => $comment->attachment_type,
            ];
        });

        return response()->json([
            'comments' => $formattedComments,
            'has_more' => $comments->hasMorePages(),
            'current_user_profile' => $currentUserProfile,
            'album_owner_id' => $albumOwnerId,
            'user_identities' => $userAlbums->map(function($album) {
                 $isUser = isset($album->type) && $album->type === 'user';
                return [
                    'id' => $album->id,
                    'name' => $album->name,
                    'profile_picture_url' => isset($album->type) && $album->type === 'user'
                        ? ($album->profile_picture ? generateSecureMediaUrl($album->profile_picture) : 'https://www.gravatar.com/avatar/' . md5(strtolower(trim($album->email ?? ''))) . '?s=100&d=mp')
                        : $this->getProfileUrl($album),
                    'type' => $isUser ? 'user' : 'album',
                ];
            }),
        ]);
    }
    public function getCommentReplies($commentId, Request $request)
    {
        if (!is_numeric($commentId)) {
            return response()->json(['message' => 'Invalid comment ID'], 400);
        }

        $replyPage = $request->query('page', 1);
        $replyLimit = $request->query('limit', 10);

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

        // Load replies with album relationship
        $replies = CommentReply::with(['user', 'replyAsAlbum'])
            ->where('comment_id', $commentId)
            ->where('status', 'active')
            ->orderBy('created_at', 'desc')
            ->paginate($replyLimit, ['*'], 'page', $replyPage);

        $authUserId = Auth::check() ? Auth::id() : null;
        $currentUserProfile = null;
        $authUser = Auth::user();

        // Get user's albums for replying as (only if user is authenticated)
        $userAlbums = [];

        if ($authUser) {
            // Get all albums owned by the current user
            $userAlbums = Album::where('user_id', $authUser->id)
                ->where('status', 'active')
                ->get();
                //->get(['id', 'name', 'profile_picture', 'user_id']);

            // Add the user's personal profile as an option too
            $userAlbums->prepend((object)[
                'id' => $authUser->id,
                'name' => $authUser->name,
                'profile_picture' => $authUser->profile_compressed,
                'user_id' => $authUser->id,
                'type' => 'user'
            ]);

            $currentUserProfile = $authUser->profile_compressed
                ? generateSecureMediaUrl($authUser->profile_compressed)
                : 'https://www.gravatar.com/avatar/' . md5(strtolower(trim($authUser->email))) . '?s=100&d=mp';
        }

        $formattedReplies = $replies->map(function ($reply) use ($authUserId) {
            // Determine replier identity based on reply_as_album_id
            $replierType = 'user'; // Default to user
            $displayName = $reply->user->name;
            $profilePictureUrl = $reply->user->profile_compressed
                ? generateSecureMediaUrl($reply->user->profile_compressed)
                : 'https://www.gravatar.com/avatar/' . md5(strtolower(trim($reply->user->email))) . '?s=100&d=mp';

            // Check if replying as album
            if ($reply->reply_as_album_id && $reply->replyAsAlbum) {
                $replierType = 'album';
                $displayName = $reply->replyAsAlbum->name;
                $profilePictureUrl = $this->getProfileUrl($reply->replyAsAlbum);
            }

            return [
                'id' => $reply->id,
                'user_id' => $reply->user_id,
                'username' => $displayName,
                'profile_picture_url' => $profilePictureUrl,
                'reply' => $reply->reply,
                'created_at' => $reply->created_at->diffForHumans(),
                'is_owner' => $replierType === 'album', // Album replies are considered "owner"
                'is_reply_owner' => $authUserId && $reply->user_id == $authUserId,
                'type' => $reply->type ?? 'text',
                'gif_id' => $reply->gif_id,
                'gif_url' => $reply->gif_url,
                'gif_provider' => $reply->gif_provider,
                'reply_as_album_id' => $reply->reply_as_album_id,
                'replier_type' => $replierType, // 'user' or 'album'
                'attachment_path' => $reply->attachment_path ? generateSecureMediaUrl($reply->attachment_path) : null,
                'attachment_type' => $reply->attachment_type,
            ];
        });

        return response()->json([
            'replies' => $formattedReplies,
            'has_more' => $replies->hasMorePages(),
            'current_user_profile' => $currentUserProfile,
            'album_owner_id' => $albumOwnerId,
            'user_identities' => $userAlbums->map(function($album) {
                return [
                    'id' => $album->id,
                    'name' => $album->name,
                    'profile_picture_url' => isset($album->type) && $album->type === 'user'
                        ? ($album->profile_picture ? generateSecureMediaUrl($album->profile_picture) : 'https://www.gravatar.com/avatar/' . md5(strtolower(trim($album->email ?? ''))) . '?s=100&d=mp')
                        : $this->getProfileUrl($album),
                    'type' => 'album',
                ];
            }),
        ]);
    }

    public function storeComment(Request $request, $id)
    {
        $user = Auth::user();
        $comment = new Comment();
        $comment->user_id = $user->id;
        $comment->post_media_id = $id;
        $comment->type = $request->type;

        // Handle identity (comment as user or album)
        if ($request->has('comment_as_album_id') && $request->comment_as_album_id) {
            $comment->comment_as_album_id = $request->comment_as_album_id;
        }

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

        // Load relationships for response
        $comment->load(['user', 'commentAsAlbum']);

        $postMedia = PostMedia::with('post.album.user')->find($id);
        if (!$postMedia || !$postMedia->post || !$postMedia->post->album) {
            return response()->json(['message' => 'Post or post media not found'], 404);
        }

        $album = $postMedia->post->album;
        $albumOwnerId = $album->user_id;

        // Determine commenter identity
        $commenterType = 'user';
        $displayName = $user->name;
        $profilePictureUrl = $user->profile_compressed
            ? generateSecureMediaUrl($user->profile_compressed)
            : 'https://www.gravatar.com/avatar/' . md5(strtolower(trim($user->email))) . '?s=100&d=mp';

        if ($comment->comment_as_album_id && $comment->commentAsAlbum) {
            $commenterType = 'album';
            $displayName = $comment->commentAsAlbum->name;
            $profilePictureUrl = $this->getProfileUrl($comment->commentAsAlbum);
        }

        $isOwner = ($user->id === $albumOwnerId);

        // Send notification if not owner (only for text comments to avoid spam)
        // Send notification if not owner (for both text and GIF comments)
        if ($albumOwnerId !== $user->id) {
            $action = $request->type === 'gif' ? 'gif' : 'commented';

            CreateNotificationJob::dispatch(
                $user,
                $postMedia,
                'commented',
                $albumOwnerId,
                [
                    'username' => $displayName,
                    'sender_id' => $user->id,
                    'post_id' => $postMedia->post->id,
                    'media_id' => $postMedia->id,
                    'album_id' => $album->id,
                    'album_name' => $album->name,
                    'comment_id' => $comment->id,
                    'comment_type' => $request->type,
                    'commenter_type' => $commenterType,
                    'comment_as_album_id' => $comment->comment_as_album_id,
                    'comment_as_album_name' => $commenterType === 'album' ? $displayName : null,
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
            'total_replies' => 0,
            'is_owner' => $isOwner,
            'is_comment_owner' => true,
            'type' => $comment->type,
            'gif_id' => $comment->gif_id,
            'gif_url' => $comment->gif_url,
            'gif_provider' => $comment->gif_provider,
            'comment_as_album_id' => $comment->comment_as_album_id,
            'commenter_type' => $commenterType,
        ], 201);
    }

    public function storeReply(Request $request, $id)
    {
        $user = Auth::user();

        // Create the reply
        $reply = new CommentReply();
        $reply->user_id = $user->id;
        $reply->comment_id = $id;
        $reply->type = $request->type;

        // Handle identity (reply as user or album)
        if ($request->has('reply_as_album_id') && $request->reply_as_album_id) {
            $reply->reply_as_album_id = $request->reply_as_album_id;
        }

        if ($request->type === 'text') {
            $reply->reply = $request->reply;
        } else {
            // For GIF replies, store GIF data
            $reply->reply = null;
            $reply->gif_id = $request->gif_id;
            $reply->gif_url = $request->gif_url;
            $reply->gif_provider = $request->gif_provider;

            // Store GIF dimensions if provided
            if ($request->has('gif_width')) {
                $reply->gif_width = $request->gif_width;
            }
            if ($request->has('gif_height')) {
                $reply->gif_height = $request->gif_height;
            }
        }

        $reply->status = 'active';
        $reply->save();

        // Load relationships for proper identity detection
        $reply->load(['user', 'replyAsAlbum']);

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

        // Determine replier identity based on reply_as_album_id
        $replierType = 'user'; // Default to user
        $displayName = $user->name;
        $profilePictureUrl = $user->profile_compressed
            ? generateSecureMediaUrl($user->profile_compressed)
            : 'https://www.gravatar.com/avatar/' . md5(strtolower(trim($user->email))) . '?s=100&d=mp';

        // Check if replying as album
        if ($reply->reply_as_album_id && $reply->replyAsAlbum) {
            $replierType = 'album';
            $displayName = $reply->replyAsAlbum->name;
            $profilePictureUrl = $this->getProfileUrl($reply->replyAsAlbum);
        }

        $isOwner = ($user->id == $albumOwnerId);
        $isReplyOwner = ($user->id == $reply->user_id);

        // Send notification only for text replies to avoid spam
        // In storeReply method, update the notification creation:
if ((int)$comment->user_id !== (int)$user->id) {
    $action = $request->type === 'gif' ? 'reacted' : 'replied';

    CreateNotificationJob::dispatch(
        $user,
        $postMedia,
        'replied',
        $comment->user_id,
        [
            'username' => $displayName,
            'sender_id' => $user->id,
            'post_id' => $postMedia->post->id,
            'media_id' => $postMedia->id,
            'album_id' => $album->id,
            'album_name' => $album->name,
            'comment_id' => $comment->id,
            'reply_id' => $reply->id,
            'reply_type' => $request->type,
            'replier_type' => $replierType,
            'reply_as_album_id' => $reply->reply_as_album_id,
            'reply_as_album_name' => $replierType === 'album' ? $displayName : null,
            'is_reply' => true,
            'is_album_owner' => $isOwner,
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
            'is_owner' => $replierType === 'album', // Album replies are considered "owner"
            'is_reply_owner' => $isReplyOwner,
            'type' => $reply->type,
            'gif_id' => $reply->gif_id,
            'gif_url' => $reply->gif_url,
            'gif_provider' => $reply->gif_provider,
            'gif_width' => $reply->gif_width,
            'gif_height' => $reply->gif_height,
            'reply_as_album_id' => $reply->reply_as_album_id,
            'replier_type' => $replierType, // 'user' or 'album'
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
