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

    public function supportalbum(Request $request)
    {

        // Get the authenticated user's ID
        $user_id = Auth::id();

        // Get the album_id from the post
        $album_id = $request->album_id;

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

    public function toggleSupport(Request $request, $action)
    {
        $user_id = Auth::id();
        $album_id = $request->input('albumid');
        $postmedia_id = $request->input('postmedia');

        if (!$album_id || !$postmedia_id) {
            return response()->json([
                'status' => 'error',
                'message' => 'Missing album ID or post media ID.',
            ], 400);
        }

        $postMedia = PostMedia::find($postmedia_id);
        if (!$postMedia) {
            return response()->json([
                'status' => 'error',
                'message' => 'Post media not found.',
            ], 404);
        }

        $post = Post::find($postMedia->post_id);
        if (!$post) {
            return response()->json([
                'status' => 'error',
                'message' => 'Post not found.',
            ], 404);
        }

        if ($action === 'subscribe') {
            // Check if already actively supported
            $existingActive = Supporter::where('user_id', $user_id)
                ->where('album_id', $album_id)
                ->where('status', 'active')
                ->exists();

            if ($existingActive) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'You already supported this album.',
                ], 409);
            }

            // Always create a new support record
            $support = Supporter::create([
                'album_id' => $album_id,
                'post_id' => $post->id,
                'user_id' => $user_id,
                'status' => 'active',
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Support added successfully.',
                'data' => $support,
            ], 201);

        } elseif ($action === 'unsubscribe') {
            // Find the most recent active support to deactivate
            $existingSupport = Supporter::where('user_id', $user_id)
                ->where('album_id', $album_id)
                ->where('status', 'active')
                ->orderBy('created_at', 'desc')
                ->first();

            if (!$existingSupport) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'You have not supported this album.',
                ], 409);
            }

            $existingSupport->update(['status' => 'inactive']);

            return response()->json([
                'status' => 'success',
                'message' => 'Support removed successfully.',
            ], 200);
        }

        return response()->json([
            'status' => 'error',
            'message' => 'Invalid action.',
        ], 400);
    }
}
