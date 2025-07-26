<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Models\MediaDownload;
use App\Http\Controllers\Controller;
use App\Models\PostMedia;
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
}
