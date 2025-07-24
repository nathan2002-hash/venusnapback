<?php

namespace App\Http\Controllers;

use App\Models\LinkShare;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use App\Models\Post;
use App\Models\PostMedia;
use App\Models\Point;
use Illuminate\Support\Facades\Auth;

class HomeController extends Controller
{
    public function terms()
    {
        return view('terms', [
        ]);
    }

    public function privacy()
    {
        return view('policy', [
        ]);
    }

    public function home()
    {
        return view('welcome', [
        ]);
    }

    public function blocked()
    {
        return view('auth.blocked', [
        ]);
    }

    public function childsafety()
    {
        return view('child', [
        ]);
    }

    public function deeplink(Request $request, $postId, $mediaId)
    {
        $post = Post::with(['user', 'album'])->findOrFail($postId);
        $media = PostMedia::findOrFail($mediaId);
        $album = $post->album;
        $thumbnailUrl = null;
        $thumbnailUrl = Storage::disk('s3')->url($media->file_path_compress);

        // if ($album) {
        //     if ($album->type === 'personal' || $album->type === 'creator') {
        //         $thumbnailUrl = $album->thumbnail_compressed
        //             ? Storage::disk('s3')->url($album->thumbnail_compressed)
        //             : ($album->thumbnail_original
        //                 ? Storage::disk('s3')->url($album->thumbnail_original)
        //                 : null);
        //     } elseif ($album->type === 'business') {
        //         $thumbnailUrl = $album->business_logo_compressed
        //             ? Storage::disk('s3')->url($album->business_logo_compressed)
        //             : ($album->business_logo_original
        //                 ? Storage::disk('s3')->url($album->business_logo_original)
        //                 : null);
        //     }
        // }

        $share = LinkShare::with('user')->where('short_code', $request->ref)->first();

        // Track visit if share exists
        if ($share) {
            $visitData = [
                'ip_address' => $request->header('cf-connecting-ip') ?? $request->ip(),
                'user_agent' => $request->header('User-Agent'),
                'device_info' => $request->header('Device-Info'),
                'referrer' => $request->ref,
                'link_share_id' => $share->id,
                'is_logged_in' => Auth::check(),
            ];

            if (Auth::check()) {
                $visitData['user_id'] = Auth::id();
            }

            $share->visits()->create($visitData);
        }

        return view('deeplink', [
            'post' => $post,
            'media' => $media,
            'thumbnailUrl' => $thumbnailUrl,
            'share' => $share // Pass the share object to the view
        ]);
    }
}
