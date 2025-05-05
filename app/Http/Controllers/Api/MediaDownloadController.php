<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Models\MediaDownload;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class MediaDownloadController extends Controller
{
    public function download(Request $request)
    {
        $userAgent = $request->header('User-Agent');
        $deviceinfo = $request->header('Device-Info');

        $download = new MediaDownload();
        $download->post_media_id = $request->post_media_id;
        $download->user_agent = $userAgent;
        $download->device_info = $deviceinfo;
        $download->user_id = Auth::user()->id;
        $download->ip_address = $request->ip();
        $download->save();
        return response()->json(['success' => true]);
    }
}
