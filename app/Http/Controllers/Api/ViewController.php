<?php

namespace App\Http\Controllers\Api;

use App\Models\View;
use App\Models\PostMedia;
use Illuminate\Http\Request;
use App\Models\Recommendation;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class ViewController extends Controller
{
    // public function view(Request $request)
    // {
    //     $view = new View();
    //     $view->user_id = Auth::user()->id; // Replace '2' with the authenticated user ID if available
    //     $view->ip_address = $request->ip();
    //     $view->post_media_id = $request->input('post_media_id');
    //     $view->duration = $request->input('duration');
    //     $view->user_agent = $request->header('User-Agent');
    //     $view->save();
    //     return response()->json(['message' => 'View duration tracked successfully']);
    // }

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
        $postMedia = PostMedia::find($postMediaId);
        if ($postMedia) {
            $postId = $postMedia->post_id;

            // Update the recommendation status
            Recommendation::where('user_id', $userId)
                ->where('post_id', $postId)
                ->where('status', 'active') // Only update active recommendations
                ->update(['status' => 'seen']);
        }

        return response()->json(['message' => 'View duration tracked and recommendation marked as seen']);
    }
}
