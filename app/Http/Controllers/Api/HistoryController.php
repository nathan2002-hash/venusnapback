<?php

namespace App\Http\Controllers\Api;

use App\Models\View;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class HistoryController extends Controller
{
    public function getUserHistory()
    {
        $userId = Auth::id();
        $history = View::where('user_id', $userId)
            ->where('created_at', '>=', now()->subHours(24)) // Only get views from the last 24 hours
            ->with(['postmedia.post.album'])
            ->orderBy('created_at', 'desc')
            ->get();

        // Group views by post ID
        $groupedHistory = $history->groupBy(function ($view) {
            return $view->postmedia->post->id ?? null;
        })->map(function ($views) {
            $firstView = $views->first(); // Use first view to get post and album details
            $album = $firstView->postmedia->post->album ?? null;

            // Determine the album profile image
            $profileUrl = asset('default/profile.png'); // Default profile
            if ($album) {
                if ($album->type == 'personal' || $album->type == 'creator') {
                    $profileUrl = $album->thumbnail_compressed
                        ? Storage::disk('s3')->url($album->thumbnail_compressed)
                        : ($album->thumbnail_original
                            ? Storage::disk('s3')->url($album->thumbnail_original)
                            : asset('default/profile.png'));
                } elseif ($album->type == 'business') {
                    $profileUrl = $album->business_logo_compressed
                        ? Storage::disk('s3')->url($album->business_logo_compressed)
                        : ($album->business_logo_original
                            ? Storage::disk('s3')->url($album->business_logo_original)
                            : asset('default/profile.png'));
                }
            }

            return [
                'post_id' => $firstView->postmedia->post->id, // Post ID
                'post_description' => $firstView->postmedia->post->description, // Post description
                'albumName' => $album->name ?? 'Unknown', // Album name
                'albumLogo' => $profileUrl, // Album profile image
                'latest_view_date' => $firstView->created_at->format('Y-m-d H:i:s'), // Latest view timestamp
                'viewed_images' => $views->map(function ($view) {
                    return [
                        'image_url' => $view->postmedia->media_url, // Post media image URL
                        'view_date' => $view->created_at->format('Y-m-d H:i:s'), // View timestamp
                    ];
                })->toArray(),
            ];
        })->values();

        return response()->json($groupedHistory);
    }

}
