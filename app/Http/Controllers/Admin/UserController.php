<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Account;
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

        $creatorAlbums = $user->albums()->where('type', 'creator')->get();
        $businessAlbums = $user->albums()->where('type', 'business')->get();
        $personalAlbums = $user->albums()->where('type', 'personal')->get();
        return view('admin.users.show', [
           'user' => $user,
           'totalposts' => $totalposts,
           'postmedias' => $postmedias,
           'supporters' => $supporters,
           'artworks' => $artworks,
           'posts' => $posts,
           'creatorAlbums' => $creatorAlbums,
           'businessAlbums' => $businessAlbums,
           'personalAlbums' => $personalAlbums,
        ]);
    }

    public function accounts()
    {
        $accounts = Account::whereHas('user') // only accounts with a related user
            ->orderBy('created_at', 'desc')
            ->paginate(40);

        return view('admin.accounts.index', [
        'accounts' => $accounts,
        ]);
    }

}
