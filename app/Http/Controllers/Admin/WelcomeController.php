<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Album;
use App\Models\Artboard;
use App\Models\Post;
use App\Models\User;
use Illuminate\Http\Request;

class WelcomeController extends Controller
{
    public function index()
    {
        $users = User::orderBy('created_at', 'desc')->paginate(10);
        $usersc = User::count();
        $posts = Post::count();
        $album = Album::count();
        return view('admin.welcome', [
           'users' => $users,
           'posts' => $posts,
           'album' => $album,
           'usersc' => $usersc,
        ]);
    }
}
