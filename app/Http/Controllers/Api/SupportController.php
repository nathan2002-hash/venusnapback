<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Supporter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SupportController extends Controller
{

    public function support(Request $request)
    {
        // Validate the request
        $validator = Validator::make($request->all(), [
            'postmedia' => 'required|exists:post_media,id', // Ensure postmedia exists in the post_media table
        ]);

        // If validation fails, return error response
        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        // Get the authenticated user's ID
        $user_id = Auth::id();

        // Find the post media record
        $postMedia = PostMedia::find($request->postmedia);

        if (!$postMedia) {
            return response()->json([
                'status' => 'error',
                'message' => 'Post media not found',
            ], 404);
        }

        // Find the associated post
        $post = Post::find($postMedia->post_id);

        if (!$post) {
            return response()->json([
                'status' => 'error',
                'message' => 'Post not found',
            ], 404);
        }

        // Get the album_id from the post
        $album_id = $post->album_id;

        // Check if the user has already supported this post
        $existingSupport = Supporter::where('user_id', $user_id)
            ->where('post_id', $post->id)
            ->first();

        if ($existingSupport) {
            return response()->json([
                'status' => 'error',
                'message' => 'You have already supported this post.',
            ], 409); // Conflict status code
        }

        // Create a new support record
        $support = Supporter::create([
            'album_id' => $album_id,
            'post_id' => $post->id,
            'user_id' => $user_id,
            'postmedia_id' => $postMedia->id,
        ]);

        // Return success response
        return response()->json([
            'status' => 'success',
            'message' => 'Support added successfully',
            'data' => $support,
        ], 201);
    }

}
