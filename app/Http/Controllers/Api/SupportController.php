<?php

namespace App\Http\Controllers\Api;

use App\Models\PostMedia;
use App\Models\Supporter;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Ad;
use App\Models\Adboard;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Models\Post;

class SupportController extends Controller
{

    public function supportpost(Request $request)
    {
        // Validate the request
        // $validator = Validator::make($request->all(), [
        //     'postmedia' => 'required|exists:post_media,id', // Ensure postmedia exists in the post_media table
        // ]);

        // // If validation fails, return error response
        // if ($validator->fails()) {
        //     return response()->json([
        //         'status' => 'error',
        //         'message' => 'Validation failed',
        //         'errors' => $validator->errors(),
        //     ], 422);
        // }

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
            ->where('album_id', $album_id)
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
            'status' => 'active',
        ]);

        // Return success response
        return response()->json([
            'status' => 'success',
            'message' => 'Support added successfully',
            'data' => $support,
        ], 201);
    }

    public function supportad(Request $request, $id)
    {
        // Validate the request
        // $validator = Validator::make($request->all(), [
        //     'postmedia' => 'required|exists:post_media,id', // Ensure postmedia exists in the post_media table
        // ]);

        // // If validation fails, return error response
        // if ($validator->fails()) {
        //     return response()->json([
        //         'status' => 'error',
        //         'message' => 'Validation failed',
        //         'errors' => $validator->errors(),
        //     ], 422);
        // }

        // Get the authenticated user's ID
        $user_id = Auth::id();

        // Find the post media record
        $ad = Ad::find($id);

        if (!$ad) {
            return response()->json([
                'status' => 'error',
                'message' => 'Ad not found',
            ], 404);
        }

        // Find the associated post
        $adboard = Adboard::find($ad->adboard_id);

        if (!$adboard) {
            return response()->json([
                'status' => 'error',
                'message' => 'Adboard not found',
            ], 404);
        }

        // Get the album_id from the post
        $album_id = $adboard->album_id;

        // Check if the user has already supported this post
        $existingSupport = Supporter::where('user_id', $user_id)
            ->where('album_id', $album_id)
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
            'ad_id' => $ad->id,
            'user_id' => $user_id,
            'status' => 'active',
        ]);

        // Return success response
        return response()->json([
            'status' => 'success',
            'message' => 'Support added successfully',
            'data' => $support,
        ], 200);
    }

    public function checkSupport(Request $request)
    {
        // Get the authenticated user's ID
        $user_id = Auth::id();

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
            ->where('album_id', $album_id)
            ->first();

        if ($existingSupport) {
            return response()->json([
                'status' => 'success',
                'message' => 'User has supported this post',
                'data' => [
                    'supported' => true,
                ],
            ], 200);
        } else {
            return response()->json([
                'status' => 'success',
                'message' => 'User has not supported this post',
                'data' => [
                    'supported' => false,
                ],
            ], 200);
        }
    }
}
