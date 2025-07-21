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

        if ($album) {
            if ($album->type === 'personal' || $album->type === 'creator') {
                $thumbnailUrl = $album->thumbnail_compressed
                    ? Storage::disk('s3')->url($album->thumbnail_compressed)
                    : ($album->thumbnail_original
                        ? Storage::disk('s3')->url($album->thumbnail_original)
                        : null);
            } elseif ($album->type === 'business') {
                $thumbnailUrl = $album->business_logo_compressed
                    ? Storage::disk('s3')->url($album->business_logo_compressed)
                    : ($album->business_logo_original
                        ? Storage::disk('s3')->url($album->business_logo_original)
                        : null);
            }
        }

        $userAgent = $request->header('User-Agent');
        $deviceinfo = $request->header('Device-Info');
        $realIp = $request->header('cf-connecting-ip') ?? $request->ip();

        $share = LinkShare::where('short_code', $request->ref)->first();

        if (!$share) {
            return view('deeplink', [
                'post' => $post,
                'media' => $media,
                'thumbnailUrl' => $thumbnailUrl,
            ]);
        } else {
            if (Auth::check()) {
                $userId = Auth::user()->id;
            } else {
                $userId = null;
            }

            $visit = $share->visits()->create([
                'ip_address' => $realIp,
                'user_agent' => $userAgent,
                'device_info' => $deviceinfo,
                'referrer' => $request->ref,
                'user_id' => $userId,
                'link_share_id' => $share->id,
                'is_logged_in' => $request->input('is_logged_in', false),
            ]);

            return view('deeplink', [
                'post' => $post,
                'media' => $media,
                'thumbnailUrl' => $thumbnailUrl,
            ]);
        }
    }
}
