<?php

namespace App\Http\Controllers\Api;

use App\Models\View;
use App\Models\Saved;
use App\Models\Admire;
use App\Models\Report;
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
        TrackViewJob::dispatch(
            Auth::id(),
            $request->ip(),
            $request->input('post_media_id'),
            $request->input('duration'),
            $request->header('User-Agent')
        );

        return response()->json(['message' => 'Job dispatched for tracking view']);
    }

    public function viewpost(Request $request)
    {
        TrackPostViewJob::dispatch(
            Auth::id(),
            $request->ip(),
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

        if ($postMediaId) {
            // Save the view
            View::create([
                'user_id' => $userId,
                'ip_address' => $request->ip(),
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


    public function more(Request $request)
    {
        $mediaId = $request->query('media_id');
        $userId = Auth::user()->id;

        $postmedia = PostMedia::find($mediaId);
        $post_id = $postmedia->post_id;

        $isAdmired = Admire::where('post_media_id', $mediaId)->where('user_id', $userId)->exists();
        $isSaved = Saved::where('post_id', $post_id)->where('user_id', $userId)->exists();
        $isReported = Report::where('post_media_id', $mediaId)->where('user_id', $userId)->exists();

        return response()->json([
            'isAdmired' => $isAdmired,
            'isSaved' => $isSaved,
            'isReported' => $isReported,
            'url' => "https://app.venusnap.com/post/$post_id",
        ]);
    }
}
