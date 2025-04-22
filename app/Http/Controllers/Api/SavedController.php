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
        $saved->save();
        // Return the simplified response
        return response()->json([
            'status' => 'success',
        ], 200);
    }

public function getSavedPosts(Request $request)
{
    $user = $request->user();
    $defaultProfile = 'https://example.com/default-profile.jpg'; // Set your default profile URL
    $defaultMedia = 'https://example.com/default-media.jpg'; // Set your default media URL

    $savedPosts = $user->saveds()
        ->with(['post.postmedias.admires', 'post.postmedias.comments', 'post.album'])
        ->orderBy('created_at', 'desc')
        ->get()
        ->map(function ($saved) use ($defaultProfile, $defaultMedia) {
            $post = $saved->post;
            
            if (!$post) {
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
                        ? Storage::disk('s3')->url($album->thumbnail_compressed)
                        : ($album->thumbnail_original
                            ? Storage::disk('s3')->url($album->thumbnail_original)
                            : $defaultProfile);
                } elseif ($album->type === 'business') {
                    $profileUrl = $album->business_logo_compressed
                        ? Storage::disk('s3')->url($album->business_logo_compressed)
                        : ($album->business_logo_original
                            ? Storage::disk('s3')->url($album->business_logo_original)
                            : $defaultProfile);
                }
            }
            
            return [
                'post_id' => $post->id,
                'post_description' => $post->description ?? '',
                'saved_at' => $saved->created_at->toDateTimeString(),
                'post_medias' => $post->postmedias ? $post->postmedias->map(function ($media) use ($defaultMedia) {
                    $mediaUrl = $defaultMedia;
                    if ($media->file_path_compressed) {
                        try {
                            $mediaUrl = Storage::disk('s3')->url($media->file_path_compress);
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
                'album_verified' => $album ? $album->is_verified : false,
                'total_admires' => $totalAdmires,
                'total_comments' => $totalComments,
            ];
        })
        ->filter()
        ->values();
    
    return response()->json($savedPosts);
}
}
