<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Artwork;
use Illuminate\Http\Request;

class ArtworkController extends Controller
{
    public function index()
    {
        $artworks = Artwork::all();
        return view('user.artwork.index', [
           'artworks' => $artworks
        ]);
    }

    public function store(Request $request)
    {
        $artwork = new Artwork();
        $artwork->content = $request->content;
        $artwork->content_color = $request->color_text;
        $artwork->background_color = $request->color_background;
        $artwork->user_id = '1';
        if ($request-> hasfile('file_path')){
            $filenamewithext = $request->file('file_path')->getClientOriginalName();
            $filename = pathinfo($filenamewithext,PATHINFO_FILENAME);
            $extension = $request->file('file_path')->getClientOriginalExtension();
            $filenametostore = $filename.'_'.time().'.'.$extension;
            $artwork->file_path = $request->file_path->storeAs('/artworks/images', $filenametostore, 'public');
        }
        $artwork->save();
    }
}
