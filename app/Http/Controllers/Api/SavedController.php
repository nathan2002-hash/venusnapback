<?php

namespace App\Http\Controllers\Api;

use App\Models\Saved;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\PostMedia;
use App\Models\Album;
use App\Models\Post;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;

class SavedController extends Controller
{
    public function index()
    {
        $saveds = Saved::all();
        return view('user.saved.index', [
           'saveds' => $saveds
        ]);
    }

    public function save(Request $request)
    {
        // Get the authenticated user's ID
        $user_id = Auth::id();

        $postmedia = PostMedia::find($request->post_media_id);
        $post_id = $postmedia->post_id;

        $saved = new Saved();
        $saved->user_id = $user_id;
        $saved->post_id = $post_id;
        $saved->status = 'saved';
        $saved->save();
        // Return the simplified response
        return response()->json([
            'status' => 'success',
        ], 200);
    }

    public function unsave(Request $request, $postId)
    {
        $user = $request->user();

        // Find the saved post record
        $saved = Saved::where('user_id', $user->id)
                      ->where('post_id', $postId)
                      ->first();

        if (!$saved) {
            return response()->json([
                'success' => false,
                'message' => 'Post not found in saved items'
            ], 404);
        }

        // Delete the record
        $saved->update([
            'status' => 'unsaved',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Post unsaved successfully'
        ]);
    }

    public function getSavedPosts(Request $request)
    {
        $user = $request->user();
        $userId = $user->id;
        $defaultProfile = 'https://example.com/default-profile.jpg'; // Set your default profile URL
        $defaultMedia = 'https://example.com/default-media.jpg'; // Set your default media URL

       $savedPosts = $user->saveds()
            ->where('status', 'saved')
            ->with(['post.postmedias.admires', 'post.postmedias.comments', 'post.album'])
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($saved) use ($defaultProfile, $defaultMedia, $userId) {
                $post = $saved->post;

                if (!$post) {
                    return null;
                }

                if (strtolower($post->visibility) === 'private' && $post->user_id != $userId) {
                    return null;
                }

                // Calculate total admires and comments across all media
                $totalAdmires = 0;
                $totalComments = 0;

                if ($post->postmedias) {
                    foreach ($post->postmedias as $media) {
                        $totalAdmires += $media->admires->count();
                        $totalComments += $media->comments->count();
                    }
                }

                $album = $post->album;
                $profileUrl = $defaultProfile;

                if ($album) {
                    if (in_array($album->type, ['personal', 'creator'])) {
                        $profileUrl = $album->thumbnail_compressed
                            ? generateSecureMediaUrl($album->thumbnail_compressed)
                            : ($album->thumbnail_original
                                ? generateSecureMediaUrl($album->thumbnail_original)
                                : $defaultProfile);
                    } elseif ($album->type === 'business') {
                        $profileUrl = $album->business_logo_compressed
                            ? generateSecureMediaUrl($album->business_logo_compressed)
                            : ($album->business_logo_original
                                ? generateSecureMediaUrl($album->business_logo_original)
                                : $defaultProfile);
                    }
                }

                return [
                    'post_id' => $post->id,
                    'post_description' => $post->description ?? '',
                    'saved_at' => $saved->created_at->toDateTimeString(),
                    'post_medias' => $post->postmedias ? $post->postmedias->map(function ($media) use ($defaultMedia) {
                        $mediaUrl = $defaultMedia;
                        if ($media->file_path_compress) {
                            try {
                                $mediaUrl = generateSecureMediaUrl($media->file_path_compress);
                            } catch (\Exception $e) {
                                // Fallback to default if URL generation fails
                                $mediaUrl = $defaultMedia;
                            }
                        }

                        return [
                            'media_url' => $mediaUrl,
                            'media_type' => 'webp',
                            'admires_count' => $media->admires->count(),
                            'comments_count' => $media->comments->count(),
                        ];
                    }) : [],
                    'album_id' => $album ? $album->id : null,
                    'album_name' => $album ? $album->name : 'Unknown',
                    'album_logo' => $profileUrl,
                    'album_verified' => (bool) $album->is_verified,
                    'total_admires' => $totalAdmires,
                    'total_comments' => $totalComments,
                ];
            })
            ->filter()
            ->values();

        return response()->json($savedPosts);
    }
}
