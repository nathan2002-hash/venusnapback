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

        // Remove the 24-hour limit to get all history
        $history = View::where('user_id', $userId)
            ->where('history_status', null)
            ->whereHas('postMedia') // Exclude views without postMedia
            ->with(['postMedia.post.album', 'postMedia.post.postmedias'])
            ->orderBy('created_at', 'desc')
            ->get();

        // First group by date (day)
        $groupedByDate = $history->groupBy(function($view) {
            return $view->created_at->format('Y-m-d'); // Group by date only
        });

        // Then process each day's views
        $result = [];
        foreach ($groupedByDate as $date => $viewsOnDate) {
            // Get the first view of this date group for the date formatting
            $firstViewOfDate = $viewsOnDate->first();

            // Now group these views by post ID
            $groupedByPost = $viewsOnDate->groupBy(fn($view) => $view->postMedia->post->id ?? null)
                ->map(function ($views) use ($userId) {
                    $firstView = $views->first();
                    $post = $firstView->postMedia->post ?? null;

                    // Skip if no post or not active
                    if (!$post || $post->status !== 'active') {
                        return null;
                    }

                    // Skip private posts unless viewer is owner
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
                        'latest_view_date' => $firstView->created_at->format('Y-m-d H:i:s'),
                        'viewed_images' => $post->postmedias->map(fn($media) => [
                            'image_url' => $media->file_path_compress
                                ? generateSecureMediaUrl($media->file_path_compress)
                                : '',
                            'view_date' => $firstView->created_at->format('Y-m-d H:i:s'),
                        ])->toArray(),
                    ];
                })->filter()->values(); // Remove null entries

            $result[] = [
                'date' => $firstViewOfDate->created_at->format('D, d M, Y'), // Formatted date
                'items' => $groupedByPost->toArray()
            ];
        }

        return response()->json($result);
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
