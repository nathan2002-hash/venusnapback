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
            }, 'commentreplies.user', 'user'])
            ->where('post_media_id', $postMediaId)
            ->where('status', 'active') // Only get active comments
            ->orderBy('created_at', 'desc')
            ->paginate($commentLimit, ['*'], 'comment_page', $commentPage);

        // Format the response
        $formattedComments = $comments->map(function ($comment) use ($request) {
            // Pagination parameters for replies
            $replyPage = $request->query('reply_page', 1); // Default to page 1
            $replyLimit = $request->query('reply_limit', 5); // Default to 5 replies per page

            // Fetch replies for the current comment with pagination
            $replies = $comment->commentreplies()
                ->where('status', 'active') // Only get active replies
                ->with('user')
                ->orderBy('created_at', 'desc')
                ->paginate($replyLimit, ['*'], 'reply_page', $replyPage);

            return [
                'id' => $comment->id,
                'user_id' => $comment->user_id,
                'username' => $comment->user->name,
                'profile_picture_url' => $comment->user->profile_compressed ? Storage::disk('s3')->url($comment->user->profile_compressed) : 'https://www.gravatar.com/avatar/' . md5(strtolower(trim($comment->user->email))) . '?s=100&d=mp',
                'comment' => $comment->comment,
                'created_at' => Carbon::parse($comment->created_at)->diffForHumans(), // Format the timestamp
                'total_replies' => $comment->commentreplies()->where('status', 'active')->count(), // Total number of active replies
                'commentreplies' => $replies->map(function ($commentreply) {
                    return [
                        'id' => $commentreply->id,
                        'user_id' => $commentreply->user_id,
                        'username' => $commentreply->user->name,
                        'profile_picture_url' => $commentreply->user->profile_compressed ? Storage::disk('s3')->url($commentreply->user->profile_compressed) : 'https://www.gravatar.com/avatar/' . md5(strtolower(trim($commentreply->user->email))) . '?s=100&d=mp',
                        'reply' => $commentreply->reply,
                        'created_at' => Carbon::parse($commentreply->created_at)->diffForHumans(), // Format timestamp
                    ];
                })->toArray(), // Convert replies to array
                'replies_next_page' => $replies->hasMorePages() ? $replyPage + 1 : null, // Next page for replies
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
        $profileUrl = $comment->user->profile_compressed ? Storage::disk('s3')->url($comment->user->profile_compressed) : 'https://www.gravatar.com/avatar/' . md5(strtolower(trim($comment->user->email))) . '?s=100&d=mp';
        $postMediaId = $id;

        $postMedia = PostMedia::with('post')->find($postMediaId);
            if (!$postMedia || !$postMedia->post) {
                return response()->json(['message' => 'Post or post media not found'], 404);
            }
        $postOwnerId = $postMedia->post->user_id;
        CreateNotificationJob::dispatch(
            $user,                      // sender (commenting user)
            $postMedia,       // notifiable (postMedia)
            'commented',               // action
            $postOwnerId,              // receiver (post owner)
            [
                'post_id' => $postMedia->post->id,
                'media_id' => $postMedia->id,
            ]
        );
        return response()->json([
            'id' => $comment->id,
            'comment' => $comment->comment,
            'post_media_id' => $comment->post_media_id,
            'user_id' => $comment->user->id,
            'username' => $comment->user->name,
            'profile_picture_url' => $profileUrl,
            'created_at' => Carbon::parse($comment->created_at)->diffForHumans(),
            'commentreplies' => [], // Add an empty array for replies
            'total_replies' => 0, // Add total replies count
        ], 201);
    }

    public function storeReply(Request $request, $id)
    {
        $request->validate([
            'reply' => 'required|string',
        ]);

        $reply = new CommentReply();
        $reply->user_id = Auth::user()->id;
        $reply->comment_id = $id;
        $reply->reply = $request->reply;
        $reply->status = 'active';
        $reply->save();

        $reply->load('user');
        $profileUrl = $reply->user->profile_compressed ? Storage::disk('s3')->url($reply->user->profile_compressed) : 'https://www.gravatar.com/avatar/' . md5(strtolower(trim($reply->user->email))) . '?s=100&d=mp';

          $commentid = $id;

        $comment = Comment::find($commentid);
        $postMedia = PostMedia::with('post')->find($comment->postMedia->id);
            if (!$postMedia || !$postMedia->post) {
                return response()->json(['message' => 'Post or post media not found'], 404);
            }
        $postOwnerId = $postMedia->post->user_id;
        CreateNotificationJob::dispatch(
            $user,                      // sender (commenting user)
            $postMedia,       // notifiable (postMedia)
            'commented',               // action
            $postOwnerId,              // receiver (post owner)
            [
                'post_id' => $postMedia->post->id,
                'media_id' => $postMedia->id,
                 'comment_id' => $comment->id,
            ]
        );
        return response()->json([
            'id' => $reply->id,
            'reply' => $reply->reply,
            'comment_id' => $reply->comment_id,
            'id' => $reply->user->id,
            'username' => $reply->user->name,
            'profile_picture_url' => $profileUrl,

            'created_at' => Carbon::parse($reply->created_at)->diffForHumans(),
        ], 201);
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
