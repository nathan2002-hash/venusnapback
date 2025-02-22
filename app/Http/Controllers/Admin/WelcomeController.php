<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
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
        $artboards = Artboard::count();
        return view('admin.welcome', [
           'users' => $users,
           'posts' => $posts,
           'artboards' => $artboards,
           'usersc' => $usersc,
        ]);
    }
}
