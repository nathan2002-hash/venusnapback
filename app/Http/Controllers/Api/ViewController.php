<?php

namespace App\Http\Controllers\Api;

use App\Models\View;
use App\Models\Saved;
use App\Models\Admire;
use App\Models\Report;
use App\Models\PostMedia;
use Illuminate\Http\Request;
use App\Models\Recommendation;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class ViewController extends Controller
{
    public function view(Request $request)
    {
        // Track the view duration
        $view = new View();
        $view->user_id = Auth::user()->id; // Authenticated user ID
        $view->ip_address = $request->ip();
        $view->post_media_id = $request->input('post_media_id');
        $view->duration = $request->input('duration');
        $view->user_agent = $request->header('User-Agent');
        $view->save();

        // Update the recommendation status to "seen"
        $postMediaId = $request->input('post_media_id');
        $userId = Auth::user()->id;

        // Fetch the post ID associated with the post_media_id
        $postmedia = PostMedia::find($postMediaId);

        $postId = $postmedia->post_id;

        // Update the recommendation status
        $updated = Recommendation::where('user_id', $userId)
            ->where('post_id', $postId)
            ->where('status', 'active') // Only update active recommendations
            ->update(['status' => 'seen']);

        if ($updated === 0) {
            //Log::info("No active recommendations found for user $userId and post $postId");
        }

        return response()->json(['message' => 'View duration tracked and recommendation marked as seen']);
    }

    public function viewpost(Request $request)
    {
        // Track the view duration
        $view = new View();
        $view->user_id = Auth::user()->id; // Authenticated user ID
        $view->ip_address = $request->ip();
        $view->post_media_id = $request->input('post_media_id');
        $view->duration = $request->input('duration');
        $view->user_agent = $request->header('User-Agent');
        $view->save();

        // Update the recommendation status to "seen"
        $userId = Auth::user()->id;

        $postId = $request->input('post_id');

        // Update the recommendation status
        $updated = Recommendation::where('user_id', $userId)
            ->where('post_id', $postId)
            ->where('status', 'active') // Only update active recommendations
            ->update(['status' => 'seen']);

        if ($updated === 0) {
            //Log::info("No active recommendations found for user $userId and post $postId");
        }

        return response()->json(['message' => 'View duration tracked and recommendation marked as seen']);
    }

    public function more(Request $request)
    {
        $mediaId = $request->query('media_id');
        $userId = Auth::user()->id;

        $isAdmired = Admire::where('post_media_id', $mediaId)->where('user_id', $userId)->exists();
        $isSaved = Saved::where('post_id', $mediaId)->where('user_id', $userId)->exists();
        $isReported = Report::where('post_media_id', $mediaId)->where('user_id', $userId)->exists();

        return response()->json([
            'isAdmired' => $isAdmired,
            'isSaved' => $isSaved,
            'isReported' => $isReported,
        ]);
    }
}
