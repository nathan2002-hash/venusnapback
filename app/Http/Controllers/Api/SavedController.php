<?php

namespace App\Http\Controllers\Api;

use App\Models\Saved;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
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

    public function report(Request $request)
    {
        // Get the authenticated user's ID
        $user_id = Auth::id();

        $saved = new Saved();
        $saved->user_id = $user_id;
        $saved->post_media_id = $request->media_id;
        $saved->status = 'active';
        $saved->reason = 'default reporting';
        $saved->save();
        // Return the simplified response
        return response()->json([
            'status' => 'success',
        ], 200);
    }
}
