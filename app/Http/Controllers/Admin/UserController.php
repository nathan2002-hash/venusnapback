<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    public function index()
    {
        $users = User::orderBy('created_at', 'desc')->paginate(30);
        return view('admin.users.index', [
           'users' => $users,
        ]);
    }

    public function show($id)
    {
        $user = User::findOrFail($id);
        $totalposts = $user->posts->count();
        $postmedias = $user->posts->sum(fn($post) => $post->postmedias->count());
        $supporters = $user->supporters->count();
        $artworks = $user->artworks->count();
        $posts = $posts = $user->posts()->orderBy('created_at', 'desc')->paginate(6);
        return view('admin.users.show', [
           'user' => $user,
           'totalposts' => $totalposts,
           'postmedias' => $postmedias,
           'supporters' => $supporters,
           'artworks' => $artworks,
           'posts' => $posts,
        ]);
    }
}
