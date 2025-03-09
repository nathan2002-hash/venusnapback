<?php

namespace App\Http\Controllers\Api;

use Carbon\Carbon;
use App\Models\Post;
use App\Models\Saved;
use App\Models\Admire;
use App\Models\Report;
use App\Models\Comment;
use App\Models\PostMedia;
use App\Models\CommentReply;
use Illuminate\Http\Request;
use App\Jobs\CompressImageJob;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class PostController extends Controller
{

public function index(Request $request)
{
    // Get the page number from the request (default to 1 if not provided)
    $page = $request->query('page', 1);

    // Get the limit (number of posts per page) from the request (default to 5 if not provided)
    $limit = $request->query('limit', 5);

    // Eager load only the necessary relationships to avoid overhead
    $posts = Post::with(['postmedias.comments.user', 'postmedias.admires.user', 'user.supporters'])
        ->where('status', 'active') // <-- Add this line to filter only active posts
        ->paginate($limit, ['*'], 'page', $page);

    $postsData = $posts->map(function ($post) {
        // Transform post media data
        $postMediaData = $post->postMedias->map(function ($media) {
            return [
                'id' => $media->id,
                'filepath' => Storage::disk('s3')->url($media->file_path_compress),
                'sequence_order' => $media->sequence_order,
                'comments_count' => $media->comments->count(),
                'likes_count' => $media->admires->count(),
                'comments' => $media->comments->map(function ($comment) {
                    return [
                        'id' => $comment->id,
                        'user' => $comment->user->name,
                        'user_profile' => $comment->user->profile_photo_path
                            ? asset('storage/' . $comment->user->profile_photo_path)
                            : asset('default/profile.png'),
                        'comment' => $comment->comment,
                        'commentreplies' => $comment->commentreplies->map(function ($reply) {
                            return [
                                'id' => $reply->id,
                                'user' => $reply->user->name,
                                'user_profile' => $reply->user->profile_photo_path
                                    ? asset('storage/' . $reply->user->profile_photo_path)
                                    : asset('default/profile.png'),
                                'reply' => $reply->reply,
                            ];
                        })->toArray(),
                    ];
                })->toArray(),
            ];
        })->toArray();

        return [
            'id' => $post->id,
            'title' => $post->title,
            'user' => $post->user->name,
            'supporters' => (string) $post->user->supporters->count(),
            'profile' => $post->user->profile_photo_path
                ? asset('storage/' . $post->user->profile_photo_path)
                : asset('default/profile.png'),
            'post_media' => $postMediaData,
        ];
    });

    return response()->json([
        'posts' => $postsData,
        'current_page' => $posts->currentPage(),
        'last_page' => $posts->lastPage(),
        'total' => $posts->total(),
    ], 200);
}



    public function store(Request $request)
    {
        $user = Auth::user(); // Get authenticated user
        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        // $validator = Validator::make($request->all(), [
        //     'description' => 'required|string|max:200',
        //     'type' => 'required',
        //     'visibility' => 'required|string|in:Public,Private,Friends Only',
        //     'post_medias' => 'required|array',
        //     'post_medias.*.file' => 'required|file|mimes:jpeg,png,jpg,webp|max:2048',
        //     'post_medias.*.sequence_order' => 'required|integer',
        // ]);

        // if ($validator->fails()) {
        //     return response()->json(['errors' => $validator->errors()], 422);
        // }

        // Create the post
        $post = new Post();
        $post->user_id = Auth::user()->id; // Assign authenticated user's ID
        $post->description = $request->description;
        $post->type = $request->type;
        $post->album_id = $request->album_id;
        $post->visibility = $request->visibility;
        $post->save();

        $sequenceOrders = collect($request->post_medias)
            ->sortBy('sequence_order') // Ensure images are sorted by their sequence order
            ->values(); // Reindex the collection

            foreach ($sequenceOrders as $media) {
                $path = $media['file']->store('uploads/posts/originals', 's3');  // Store the original file

                $postMedia = PostMedia::create([
                    'post_id' => $post->id,
                    'file_path' => $path,
                    'sequence_order' => $media['sequence_order'],
                    'status' => 'original',  // Status set to original
                ]);

                // Dispatch a job to compress the image asynchronously
                CompressImageJob::dispatch($postMedia->fresh());  // Use a queued job for compression
            }

        return response()->json(['message' => 'Post created successfully', 'post' => $post], 200);
    }

    public function storecloud(Request $request)
    {
        $user = Auth::user(); // Get authenticated user
        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $validator = Validator::make($request->all(), [
            'description' => 'required|string|max:200',
            'type' => 'required',
            'visibility' => 'required|string|in:Public,Private,Friends Only',
            'post_medias' => 'required|array',
            'post_medias.*.file_path' => 'required|string',  // Expecting the file path (e.g., S3 URL)
            'post_medias.*.sequence_order' => 'required|integer',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Create the post
        $post = new Post();
        $post->user_id = Auth::user()->id; // Assign authenticated user's ID
        $post->description = $request->description;
        $post->type = $request->type;
        $post->album_id = $request->album_id;
        $post->visibility = $request->visibility;
        $post->save();

        $sequenceOrders = collect($request->post_medias)
            ->sortBy('sequence_order') // Ensure images are sorted by their sequence order
            ->values(); // Reindex the collection

        foreach ($sequenceOrders as $media) {
            // Use the 'file_path' that is provided in the request (this will be the S3 path)
            $path = $media['file_path']; // No need to store the image again in S3

            // Create PostMedia without uploading a new file, just save the file path
            PostMedia::create([
                'post_id' => $post->id,
                'file_path' => $path,
                'file_path_compress' => $path,
                'sequence_order' => $media['sequence_order'],
                'status' => 'compressed',
            ]);
        }

        return response()->json(['message' => 'Post created successfully', 'post' => $post], 200);
    }



    public function save(Request $request, $id)
    {
        $save = new Saved();
        $save->post_id = $id;
        $save->user_id = '2';
        $save->save();
    }

    public function admire(Request $request)
    {
        $postMediaId = $request->post_media_id;
        $user =  $user = Auth::user();

        $admire = Admire::where('user_id', $user->id)->where('post_media_id', $postMediaId)->first();

        if ($admire) {
            $admire->delete();
            return response()->json(['message' => 'Unliked']);
        } else {
            Admire::create(['user_id' => $user->id, 'post_media_id' => $postMediaId]);
            return response()->json(['message' => 'Liked']);
        }
    }

    public function report(Request $request, $id)
    {
        $report = new Report();
        $report->apost_media_id = $id;
        $report->user_id = '2';
        $report->status = 'pending';
        $report->reason = $request->report;
        $report->save();
    }

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
                'profile_picture_url' => $comment->user->profile_photo_path ? Storage::disk('s3')->url($comment->user->profile_photo_path) : 'https://www.gravatar.com/avatar/' . md5(strtolower(trim($comment->user->email))) . '?s=100&d=mp',
                'comment' => $comment->comment,
                'created_at' => Carbon::parse($comment->created_at)->diffForHumans(), // Format the timestamp
                'total_replies' => $comment->commentreplies()->count(), // Total number of replies
                'commentreplies' => $replies->map(function ($commentreply) {
                    return [
                        'id' => $commentreply->id,
                        'user_id' => $commentreply->user_id,
                        'username' => $commentreply->user->name,
                        'profile_picture_url' => $commentreply->user->profile_photo_path ? Storage::disk('s3')->url($commentreply->user->profile_photo_path) : 'https://www.gravatar.com/avatar/' . md5(strtolower(trim($commentreply->user->email))) . '?s=100&d=mp',
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
        $comment->save();

        $comment->load('user');
        $profileUrl = $comment->user->profile_photo_path ? Storage::disk('s3')->url($comment->user->profile_photo_path) : 'https://www.gravatar.com/avatar/' . md5(strtolower(trim($comment->user->email))) . '?s=100&d=mp';

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
        $reply->save();

        $reply->load('user');
        $profileUrl = $reply->user->profile_photo_path ? Storage::disk('s3')->url($reply->user->profile_photo_path) : 'https://www.gravatar.com/avatar/' . md5(strtolower(trim($reply->user->email))) . '?s=100&d=mp';

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
}
