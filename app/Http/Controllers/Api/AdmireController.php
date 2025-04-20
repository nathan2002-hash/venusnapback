<?php

namespace App\Http\Controllers\Api;

use App\Models\Admire;
use App\Models\PostMedia;
use Illuminate\Http\Request;
use App\Jobs\CreateNotificationJob;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class AdmireController extends Controller
{

    public function admire(Request $request)
    {
        $postMediaId = $request->post_media_id;
        $user = Auth::user();

        $admire = Admire::where('user_id', $user->id)
                        ->where('post_media_id', $postMediaId)
                        ->first();

        if ($admire) {
            $admire->delete();
            return response()->json(['message' => 'Unliked']);
        } else {
            Admire::create([
                'user_id' => $user->id,
                'post_media_id' => $postMediaId
            ]);

            // Fetch postMedia and its relationships
            $postMedia = PostMedia::with('post.album')->find($postMediaId);

            if (!$postMedia || !$postMedia->post) {
                return response()->json(['message' => 'Post or post media not found'], 404);
            }

            //$postOwnerId = $postMedia->post->user_id;
            $postOwnerId = $postMedia->post->album->user_id;

            // Prevent self-notification
            if ($postOwnerId !== $user->id) {
                CreateNotificationJob::dispatch(
                    $user,                   // sender
                    $postMedia,             // notifiable model
                    'admired',              // action
                    $postOwnerId,           // receiver
                    [
                        'post_id' => $postMedia->post->id,
                        'media_id' => $postMediaId,
                        'album_id' => $postMedia->post->album_id ?? null // Fixed this line
                    ]
                );
            }

            return response()->json(['message' => 'Liked']);
        }
    }


    public function checkLike(Request $request)
    {
        // Validate the request
        $request->validate([
            'post_media_id' => 'required|exists:post_media,id',
        ]);

        // Get the authenticated user's ID
        $user_id = Auth::id();

        // Check if the user has already liked this post media
        $liked = Admire::where('user_id', $user_id)
            ->where('post_media_id', $request->post_media_id)
            ->exists();

        // Return the simplified response
        return response()->json([
            'status' => 'success',
            'data' => [
                'liked' => $liked,
                'post_media_id'=>$request->post_media_id,
            ],
        ], 200);
    }
}
