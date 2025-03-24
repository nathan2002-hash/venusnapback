<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Adboard;
use Illuminate\Http\Request;

class AdController extends Controller
{
    public function getUserAlbums(Request $request)
    {
        // Get the authenticated user's albums
        $albums = $request->user()->albums()->select('id', 'name')->get();

        // Return response in JSON format
        return response()->json([
            'albums' => $albums
        ]);
    }


    public function adboard(Request $request)
    {
        $adboard = new Adboard();
        $adboard->album_id = $request->album_id;
        $adboard->status = 'active';
        $adboard->points = $request->points;
        $adboard->description = $request->description;
        $adboard->name = $request->name;
        $adboard->save();
        return response()->json([
            'message' => 'Adboard created successfully!',
            'adboard' => $adboard,
            'id' => $adboard->id
        ], 201);
    }

}
