<?php

namespace App\Http\Controllers\Api;

use App\Models\Saved;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\PostMedia;
use Illuminate\Support\Facades\Auth;

class SavedController extends Controller
{
    public function index()
    {
        $saveds = Saved::all();
        return view('user.saved.index', [
           'saveds' => $saveds
        ]);
    }

    public function save(Request $request)
    {
        // Get the authenticated user's ID
        $user_id = Auth::id();

        $postmedia = PostMedia::find($request->post_media_id);
        $post_id = $postmedia->post_id;

        $saved = new Saved();
        $saved->user_id = $user_id;
        $saved->post_id = $post_id;
        $saved->save();
        // Return the simplified response
        return response()->json([
            'status' => 'success',
        ], 200);
    }
}
