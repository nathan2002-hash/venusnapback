<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Post;
use Illuminate\Http\Request;

class PostController extends Controller
{
    public function index()
    {
        $posts = Post::orderBy('created_at', 'desc')->paginate(30);
        return view('admin.posts.index', [
           'posts' => $posts,
        ]);
    }

    public function show($id)
    {
        $post = Post::findOrFail($id);
        return view('admin.posts.show', [
           'post' => $post,
        ]);
    }
}
