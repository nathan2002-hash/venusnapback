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
            ->where('created_at', '>=', now()->subHours(24)) // Last 24 hours
            ->whereHas('postMedia') // Exclude views without postMedia
            ->with(['postMedia.post.album']) // Eager load relationships
            ->orderBy('created_at', 'desc')
            ->get();

        // Group views by post ID
        $groupedHistory = $history->groupBy(fn($view) => $view->postMedia->post->id ?? null)
            ->map(function ($views) {
                $firstView = $views->first();
                $post = $firstView->postMedia->post ?? null;
                $album = $post->album ?? null;

                // Determine the album profile image
                $profileUrl = asset('default/profile.png'); // Default profile
                if ($album) {
                    if (in_array($album->type, ['personal', 'creator'])) {
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
                    'post_id' => $post->id ?? null,
                    'post_description' => $post->description ?? 'No description',
                    'albumName' => $album->name ?? 'Unknown',
                    'albumLogo' => $profileUrl,
                    'latest_view_date' => $firstView->created_at->format('Y-m-d H:i:s'),
                    'viewed_images' => $views->map(fn($view) => [
                        'image_url' => $view->postMedia->media_url ?? '',
                        'view_date' => $view->created_at->format('Y-m-d H:i:s'),
                    ])->toArray(),
                ];
            })->values();

        return response()->json($groupedHistory);
    }


}
