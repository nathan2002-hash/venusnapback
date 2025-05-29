<?php

namespace App\Http\Controllers\Api;

use App\Models\View;
use App\Models\Saved;
use App\Models\Admire;
use App\Models\Report;
use App\Models\LinkShare;
use App\Models\PostMedia;
use App\Jobs\TrackViewJob;
use Illuminate\Http\Request;
use App\Jobs\TrackPostViewJob;
use App\Models\Recommendation;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class ViewController extends Controller
{

    public function view(Request $request)
    {
        $realIp = $request->header('cf-connecting-ip') ?? $request->ip();
        TrackViewJob::dispatch(
            Auth::id(),
            $realIp,
            $request->input('post_media_id'),
            $request->input('duration'),
            $request->header('User-Agent')
        );

        return response()->json(['message' => 'Job dispatched for tracking view']);
    }

    public function viewpost(Request $request)
    {
        $realIp = $request->header('cf-connecting-ip') ?? $request->ip();
        TrackPostViewJob::dispatch(
            Auth::id(),
            $realIp,
            $request->input('post_id'),
            $request->input('post_media_id'),
            $request->input('duration'),
            $request->header('User-Agent')
        );

        return response()->json(['message' => 'Job dispatched for tracking post view']);
    }

    public function viewg(Request $request)
    {
        // Track the view duration
        $view = new View();
        $view->user_id = Auth::user()->id;
        $view->ip_address = $request->ip();
        $view->post_media_id = $request->input('post_media_id');
        $view->duration = $request->input('duration');
        $view->user_agent = $request->header('User-Agent');
        $view->save();

        // Update the recommendation status to "seen"
        $postMediaId = $request->input('post_media_id');
        $userId = Auth::user()->id;

        $postmedia = PostMedia::find($postMediaId);
        if (!$postmedia) {
            return response()->json(['message' => 'Post media not found'], 404);
        }

        $postId = $postmedia->post_id;

        // ✅ Update recommendations with 'active' or 'fetched' status to 'seen'
        $updated = Recommendation::where('user_id', $userId)
            ->where('post_id', $postId)
            ->whereIn('status', ['active', 'fetched']) // Include both statuses
            ->update(['status' => 'seen']);

        return response()->json([
            'message' => 'View duration tracked and recommendation marked as seen',
            'updated_records' => $updated
        ]);
    }

    public function viewfpost(Request $request)
    {
        $userId = Auth::id();
        $postId = $request->input('post_id');
        $duration = $request->input('duration');

        // If post_media_id is not provided, get the one with sequence_no = 1
        $postMediaId = $request->input('post_media_id');

        if (!$postMediaId && $postId) {
            $postMedia = PostMedia::where('post_id', $postId)
                ->where('sequence_no', 1)
                ->first();

            if ($postMedia) {
                $postMediaId = $postMedia->id;
            }
        }
        $realIp = $request->header('cf-connecting-ip') ?? $request->ip();

        if ($postMediaId) {
            // Save the view
            View::create([
                'user_id' => $userId,
                'ip_address' => $realIp,
                'post_media_id' => $postMediaId,
                'duration' => $duration,
                'user_agent' => $request->header('User-Agent'),
            ]);
        }

        // Mark recommendation as seen
        Recommendation::where('user_id', $userId)
            ->where('post_id', $postId)
            ->whereIn('status', ['active', 'fetched'])
            ->update(['status' => 'seen']);

        return response()->json(['message' => 'View duration tracked and recommendation marked as seen']);
    }

    public function dmore(Request $request)
    {
        $mediaId = $request->query('media_id');
        $userId = Auth::id();

        $postmedia = PostMedia::find($mediaId);
        if (!$postmedia) {
            return response()->json(['error' => 'Post media not found'], 404);
        }

        $post_id = $postmedia->post_id;

        $isAdmired = Admire::where('post_media_id', $mediaId)->where('user_id', $userId)->exists();
        $isSaved = Saved::where('post_id', $post_id)->where('user_id', $userId)->exists();

        // Updated report check for new schema
        $isReported = Report::where('resource_id', $mediaId)
            ->where('target', 'post_media')
            ->where('user_id', $userId)
            ->exists();

        return response()->json([
            'isAdmired' => $isAdmired,
            'isSaved' => $isSaved,
            'isReported' => $isReported,
            'url' => "https://www.venusnap.com/post/$post_id",
        ]);
    }

    public function more(Request $request)
    {
        $mediaId = $request->query('media_id');
        $userId = Auth::id();

        $postmedia = PostMedia::find($mediaId);
        if (!$postmedia) {
            return response()->json(['error' => 'Post media not found'], 404);
        }

        $post_id = $postmedia->post_id;

        $isAdmired = Admire::where('post_media_id', $mediaId)
            ->where('user_id', $userId)
            ->exists();

        $isSaved = Saved::where('post_id', $post_id)
            ->where('user_id', $userId)
            ->exists();

        $isReported = Report::where('resource_id', $mediaId)
            ->where('target', 'post_media')
            ->where('user_id', $userId)
            ->exists();

        // Generate share URL with tracking
        $shareUrl = $this->generateTrackableLink($post_id, $mediaId, $userId);

        return response()->json([
            'isAdmired' => $isAdmired,
            'isSaved' => $isSaved,
            'isReported' => $isReported,
            'url' => $shareUrl,
        ]);
    }

    protected function generateTrackableLink($postId, $mediaId, $userId)
    {
        $share = LinkShare::create([
            'user_id' => $userId,
            'post_id' => $postId,
            'post_media_id' => $mediaId,
            'share_method' => 'direct', // Default, can be updated when shared via specific platform
            'share_url' => "https://www.venusnap.com/post/$postId/media/$mediaId",
        ]);

        // Optionally generate a short URL
        $shortCode = $this->generateShortCode();
        $share->update(['short_code' => $shortCode]);

        return "https://www.venusnap.com/post/$postId/media/$mediaId?ref=$shortCode";
    }

    protected function generateShortCode()
    {
        // Implement your short code generation logic
        return substr(md5(uniqid()), 0, 8);
    }

    public function trackVisit(Request $request)
    {
        $request->validate([
            'short_code' => 'string',
            'is_logged_in' => 'sometimes|boolean',
        ]);

        $share = LinkShare::where('short_code', $request->short_code)->first();

        if (!$share) {
            return response()->json(['error' => 'Invalid short code'], 404);
        }

        if (Auth::check()) {
            $userId = Auth::user()->id;
        } else {
            $userId = null;
        }

        $userAgent = $request->header('User-Agent');
        $deviceinfo = $request->header('Device-Info');
        $realIp = $request->header('cf-connecting-ip') ?? $request->ip();


        $visit = $share->visits()->create([
            'ip_address' => $realIp,
            'user_agent' => $userAgent,
            'device_info' => $deviceinfo,
            'referrer' => $request->short_code,
            'user_id' => $userId,
            'link_share_id' => $share->id,
            'is_logged_in' => $request->input('is_logged_in', false),
        ]);

        return response()->json([
            'success' => true,
            'visit_id' => $visit->id,
        ]);
    }
}
