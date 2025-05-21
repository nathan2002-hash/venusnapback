<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Post;
use App\Models\PostState;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PostStateController extends Controller
{
    public function state(Request $request, $id)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'reason' => 'nullable|string',
        ]);
        $user = Auth::user();

        $post = Post::findOrFail($id);
        $post->status = $request->state;
        $post->save();

        $poststate = new PostState();
        $poststate->user_id = $user->id;
        $poststate->post_id = $post->id;
        $poststate->title = $request->title;
        $poststate->initiator = 'venusnap_admin';
        $poststate->reason = $request->reason;
        $poststate->state = $request->state;
        $poststate->save();

        return redirect()->back();
    }
}
