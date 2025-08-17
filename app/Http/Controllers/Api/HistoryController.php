<?php

namespace App\Http\Controllers\Api;

use App\Models\View;
use App\Models\Admire;
use App\Models\Comment;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class HistoryController extends Controller
{

public function getUserHistory(Request $request)
{
    $userId = Auth::id();
    $perPage = 15; // Number of post groups per page
    $page = $request->input('page', 1);
    $viewerTimezone = Auth::check() ? Auth::user()->timezone : 'Africa/Lusaka';

    // 1. Get all views with needed relations
    $history = View::where('user_id', $userId)
        ->where('history_status', null)
        ->whereHas('postMedia.post', fn($q) => $q->where('status', 'active'))
        ->with([
            'postMedia.post.album',
            'postMedia.post.postmedias' => fn($q) => $q->withCount(['admires', 'comments']),
        ])
        ->orderBy('created_at', 'desc')
        ->get();

    // 2. Group by post ID (each post appears once)
    $groupedPosts = $history->groupBy(fn($view) => $view->postMedia->post->id ?? null)
        ->map(fn($views) => $views->first())
        ->filter()
        ->values();

    // 3. Paginate manually
    $paginatedPosts = $groupedPosts->slice(($page - 1) * $perPage, $perPage)->values();

    // 4. Format response grouped by date
    $result = [];

    $groupedByDate = $paginatedPosts->groupBy(fn($view) => $view->created_at->format('Y-m-d'));

    foreach ($groupedByDate as $date => $viewsOnDate) {
        $items = $viewsOnDate->map(function ($view) use ($userId, $viewerTimezone) {
            $post = $view->postMedia->post;
            if (!$post) return null;

            $album = $post->album ?? null;

            // Skip private posts if user is not owner
            if (strtolower($post->visibility) === 'private' && $post->user_id != $userId) return null;

            // Album image
            $profileUrl = asset('default/profile.png');
            if ($album) {
                if (in_array($album->type, ['personal', 'creator'])) {
                    $profileUrl = $album->thumbnail_compressed
                        ? generateSecureMediaUrl($album->thumbnail_compressed)
                        : ($album->thumbnail_original
                            ? generateSecureMediaUrl($album->thumbnail_original)
                            : asset('default/profile.png'));
                } elseif ($album->type === 'business') {
                    $profileUrl = $album->business_logo_compressed
                        ? generateSecureMediaUrl($album->business_logo_compressed)
                        : ($album->business_logo_original
                            ? generateSecureMediaUrl($album->business_logo_original)
                            : asset('default/profile.png'));
                }
            }

            return [
                'post_id' => $post->id,
                'post_description' => $post->description ?? 'No description',
                'albumName' => $album->name ?? 'Unknown',
                'albumLogo' => $profileUrl,
                'album_verified' => $album && $album->is_verified == 1,
                'admire_count' => $post->postmedias->sum('admires_count'),
                'comments_count' => $post->postmedias->sum('comments_count'),
                'media_count' => $post->postmedias->count(),
                'latest_view_date' => formatDateTimeForUser($view->created_at, $viewerTimezone),
                'viewed_images' => $post->postmedias->map(fn($media) => [
                    'image_url' => $media->file_path_compress
                        ? generateSecureMediaUrl($media->file_path_compress)
                        : '',
                    'view_date' => $view->created_at->format('Y-m-d H:i:s'),
                ])->toArray(),
            ];
        })->filter()->values();

        if ($items->isNotEmpty()) {
            $result[] = [
                'date' => Carbon::parse($date)->format('D, d M, Y'),
                'items' => $items->toArray(),
            ];
        }
    }

    // 5. Return paginated response metadata
    return response()->json([
        'current_page' => (int) $page,
        'per_page' => (int) $perPage,
        'total' => $groupedPosts->count(),
        'last_page' => ceil($groupedPosts->count() / $perPage),
        'data' => $result,
    ]);
}


    public function deleteHistory(Request $request)
    {
        $userId = Auth::id();
        $postId = $request->post_id;

        // Update all views for this post by this user
        View::where('user_id', $userId)
            ->whereHas('postMedia', function($query) use ($postId) {
                $query->where('post_id', $postId);
            })
            ->update(['history_status' => 'deleted']);

        return response()->json([
            'status' => 'success',
            'message' => 'History deleted successfully'
        ]);
    }
}
