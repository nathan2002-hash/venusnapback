<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Models\MediaDownload;
use App\Http\Controllers\Controller;
use App\Models\PostMedia;
use App\Models\Post;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;

class MediaDownloadController extends Controller
{
    public function download(Request $request)
    {
        $userAgent = $request->header('User-Agent');
        $deviceinfo = $request->header('Device-Info');
        $realIp = $request->header('cf-connecting-ip') ?? $request->ip();
        $ipaddress = $realIp;

        $download = new MediaDownload();
        $download->post_media_id = $request->post_media_id;
        $download->user_agent = $userAgent;
        $download->device_info = $deviceinfo;
        $download->user_id = Auth::user()->id;
        $download->ip_address = $ipaddress;
        $download->save();

        $postmedia = PostMedia::find($request->post_media_id);

        if (!$postmedia) {
            return response()->json(['success' => false, 'message' => 'Media not found'], 404);
        }

        $image_url = generateSecureMediaUrl($postmedia->file_path);

        return response()->json([
            'success' => true,
            'image_url' => $image_url
        ]);
    }

    public function downloadpostimages(Request $request)
    {
        $request->validate([
            'post_id' => 'required|exists:posts,id'
        ]);

        $user = Auth::user();
        $post = Post::with('postmedias')->find($request->post_id);

        if (!$post) {
            return response()->json([
                'success' => false,
                'message' => 'Post not found'
            ], 404);
        }

        // Prepare response data
        $response = [
            'success' => true,
            'post_id' => $post->id,
            'image_count' => $post->postmedias->count(),
            'images' => [],
        ];

        // Log each download and prepare URLs
        foreach ($post->postmedias as $media) {
            // Log the download
            $download = new MediaDownload();
            $download->post_media_id = $media->id;
            $download->user_agent = $request->header('User-Agent');
            $download->device_info = $request->header('Device-Info');
            $download->user_id = $user->id;
            $download->ip_address = $request->header('cf-connecting-ip') ?? $request->ip();
            $download->save();
            $path = $media->file_path;
            // Add to response
            $response['images'][] = [
                'id' => $media->id,
                'url' => generateSecureMediaUrl($media->file_path),
                'file_name' => 'VEN_IMG_' . $media->id . '.' . pathinfo($media->file_path, PATHINFO_EXTENSION),
                'mime_type' => Storage::disk('s3')->mimeType($path),
                'size' => Storage::disk('s3')->size($path),
                'order' => $media->order ?? null,
            ];
        }

        return response()->json($response);
    }
}
