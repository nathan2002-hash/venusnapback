<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Album;
use Illuminate\Http\Request;

class AlbumController extends Controller
{
    public function index()
    {
        $albums = Album::orderBy('created_at', 'desc')->paginate(30);
        return view('admin.albums.index', [
           'albums' => $albums,
        ]);
    }
}
