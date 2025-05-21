<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Ad;
use App\Models\Adboard;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AdController extends Controller
{
    public function index()
{
    $ads = Ad::with(['adboard.album', 'media'])
             ->orderBy('created_at', 'desc')
             ->paginate(30);

    $transformedAds = $this->transformAds($ads);

    return view('admin.ads.index', [
       'ads' => $ads,
       'transformedAds' => $transformedAds
    ]);
}

     private function transformAds($ads)
    {
        return $ads->map(function ($ad) {
            $album = $ad->adboard->album ?? null;

            return [
                'type' => 'ad',
                'id' => $ad->id,
                'album_name' => $album->name ?? 'Advertiser',
                'post_media' => $ad->media->map(function ($media) {
                    return [
                        'id' => $media->id,
                        'filepath' => Storage::disk('s3')->url($media->file_path),
                        'sequence_order' => $media->sequence_order,
                    ];
                })->toArray(),
                'is_verified' => false,
                'supporters_count' => '0',
                'is_ad' => true,
                'cta_name' => $ad->cta_name,
                'cta_link' => $ad->cta_link,
                'created_at' => $ad->created_at->toDateTimeString(),
            ];
        });
    }

    public function adboards()
    {
        $adboards = Adboard::orderBy('created_at', 'desc')->paginate(30);
        return view('admin.ads.adboards', [
           'adboards' => $adboards,
        ]);
    }

    public function updateStatus(Request $request)
    {
        $request->validate([
            'ad_id' => 'required|exists:ads,id',
            'status' => 'required|in:active,rejected',
        ]);

        $ad = Ad::findOrFail($request->ad_id);
        $ad->status = $request->status;
        $ad->save();

        // Optional: You can update the adboard status or log this change if needed
        $ad->adboard->update([
            'status' => $request->status,
        ]);

        return response()->json(['success' => true, 'message' => 'Ad status updated successfully.']);
    }
}
