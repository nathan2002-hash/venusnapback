<?php

namespace App\Http\Controllers\Api;

use App\Models\Album;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class ArtboardController extends Controller
{
    public function getUserAlbums()
    {
        // Get the authenticated user
        $user = Auth::user();

        // Fetch albums associated with the user
        $albums = Album::where('user_id', $user->id)
            ->select('id', 'name') // Only fetch necessary fields
            ->get();

        return response()->json([
            'success' => true,
            'data' => $albums,
        ]);
    }
}
