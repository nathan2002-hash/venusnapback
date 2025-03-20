<?php

namespace App\Http\Controllers\Api;

use Carbon\Carbon;
use App\Models\Comment;
use App\Models\CommentReply;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class CommentController extends Controller
{
    public function getCommentsAndReplies($postMediaId, Request $request)
    {
        // Pagination parameters for comments
        $commentPage = $request->query('comment_page', 1); // Default to page 1
        $commentLimit = $request->query('comment_limit', 10); // Default to 10 comments per page

        // Fetch comments with replies, ordered by creation date (newest first)
        $comments = Comment::with(['commentreplies.user', 'user'])
            ->where('post_media_id', $postMediaId)
            ->orderBy('created_at', 'desc')
            ->paginate($commentLimit, ['*'], 'comment_page', $commentPage);

        // Format the response
        $formattedComments = $comments->map(function ($comment) use ($request) {
            // Pagination parameters for replies
            $replyPage = $request->query('reply_page', 1); // Default to page 1
            $replyLimit = $request->query('reply_limit', 5); // Default to 5 replies per page

            // Fetch replies for the current comment with pagination
            $replies = $comment->commentreplies()
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
                'total_replies' => $comment->commentreplies()->count(), // Total number of replies
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
        $comment = Comment::find($id);
        $comment->status = 'delete';
        $comment->save();
        return response()->json([
            'status' => 'success',
        ], 200);
    }

    public function commentreplydelete($id)
    {
        $commentreply = CommentReply::find($id);
        $commentreply->status = 'delete';
        $commentreply->save();
        return response()->json([
            'status' => 'success',
        ], 200);
    }
}
