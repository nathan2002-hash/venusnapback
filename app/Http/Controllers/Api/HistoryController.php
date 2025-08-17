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

public function getUserHistory(Request $request)
{
    $userId = Auth::id();
    $perPage = 10; // Number of items per page
    $page = $request->input('page', 1);

    // First get all distinct dates with views
    $dates = View::where('user_id', $userId)
        ->where('history_status', null)
        ->whereHas('postMedia')
        ->selectRaw('DATE(created_at) as view_date')
        ->groupBy('view_date')
        ->orderBy('view_date', 'desc')
        ->paginate($perPage, ['*'], 'page', $page);

    $result = [];

    foreach ($dates as $date) {
        // Get views for this specific date
        $viewsOnDate = View::where('user_id', $userId)
            ->where('history_status', null)
            ->whereDate('created_at', $date->view_date)
            ->whereHas('postMedia')
            ->with(['postMedia.post.album', 'postMedia.post.postmedias'])
            ->orderBy('created_at', 'desc')
            ->get();

        // Group by post ID
        $groupedByPost = $viewsOnDate->groupBy(fn($view) => $view->postMedia->post->id ?? null)
            ->map(function ($views) use ($userId) {
                $firstView = $views->first();
                $post = $firstView->postMedia->post ?? null;

                if (!$post || $post->status !== 'active') {
                    return null;
                }

                if (strtolower($post->visibility) === 'private' && $post->user_id != $userId) {
                    return null;
                }

                $album = $post->album ?? null;

                $totalAdmireCount = Admire::whereIn('post_media_id', $post->postmedias->pluck('id'))->count();
                $totalCommentsCount = Comment::whereIn('post_media_id', $post->postmedias->pluck('id'))->count();
                $totalMediaCount = $post->postmedias->count();
                $albumVerified = $album && $album->is_verified == 1;

                $profileUrl = asset('default/profile.png');
                if ($album) {
                    if (in_array($album->type, ['personal', 'creator'])) {
                        $profileUrl = $album->thumbnail_compressed
                            ? generateSecureMediaUrl($album->thumbnail_compressed)
                            : ($album->thumbnail_original
                                ? generateSecureMediaUrl($album->thumbnail_original)
                                : asset('default/profile.png'));
                    } elseif ($album->type == 'business') {
                        $profileUrl = $album->business_logo_compressed
                            ? generateSecureMediaUrl($album->business_logo_compressed)
                            : ($album->business_logo_original
                                ? generateSecureMediaUrl($album->business_logo_original)
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
                    'latest_view_date' => formatDateTimeForUser($firstView->created_at, Auth::user()->timezone ?? 'Africa/Lusaka'),
                    'viewed_images' => $post->postmedias->map(fn($media) => [
                        'image_url' => $media->file_path_compress
                            ? generateSecureMediaUrl($media->file_path_compress)
                            : '',
                        'view_date' => $firstView->created_at->format('Y-m-d H:i:s'),
                    ])->toArray(),
                ];
            })->filter()->values();

        $result[] = [
            'date' => Carbon::parse($date->view_date)->format('D, d M, Y'),
            'items' => $groupedByPost->toArray()
        ];
    }

    return response()->json([
        'data' => $result,
        'current_page' => $dates->currentPage(),
        'next_page_url' => $dates->nextPageUrl(),
        'total' => $dates->total(),
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
