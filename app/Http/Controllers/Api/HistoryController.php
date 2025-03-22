<?php

namespace App\Http\Controllers\Api;

use App\Models\View;
use App\Models\Admire;
use App\Models\Comment;
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
            ->with(['postMedia.post.album', 'postMedia.post.postmedias']) // Load all related post media
            ->orderBy('created_at', 'desc')
            ->get();

        // Group views by post ID
        $groupedHistory = $history->groupBy(fn($view) => $view->postMedia->post->id ?? null)
            ->map(function ($views) {
                $firstView = $views->first();
                $post = $firstView->postMedia->post ?? null;
                $album = $post->album ?? null;

                // Count total admires and comments from all postmedias in the post
                $totalAdmireCount = Admire::whereIn('post_media_id', $post->postmedias->pluck('id'))->count();
                $totalCommentsCount = Comment::whereIn('post_media_id', $post->postmedias->pluck('id'))->count();
                $totalMediaCount = $post->postmedias->count();

                // Check if album is verified
                $albumVerified = $album && $album->is_verified == 1;

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
                    'album_verified' => $albumVerified,
                    'admire_count' => $totalAdmireCount,
                    'comments_count' => $totalCommentsCount,
                    'media_count' => $totalMediaCount,
                    'latest_view_date' => $firstView->created_at->format('Y-m-d H:i:s'),
                    'viewed_images' => $post->postmedias->map(fn($media) => [
                        'image_url' => $media->file_path_compress
                            ? Storage::disk('s3')->url($media->file_path_compress)
                            : '',
                        'view_date' => $firstView->created_at->format('Y-m-d H:i:s'),
                    ])->toArray(),
                ];
            })->values();

        return response()->json($groupedHistory);
    }



}
