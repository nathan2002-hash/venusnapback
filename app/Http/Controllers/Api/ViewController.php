<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ViewController extends Controller
{
    public function view(Request $request)
    {
        $view = new View();
        $view->user_id = Auth::user()->id; // Replace '2' with the authenticated user ID if available
        $view->ip_address = $request->ip();
        $view->post_media_id = $request->input('post_media_id');
        $view->duration = $request->input('duration');
        $view->user_agent = $request->header('User-Agent');
        $view->save();
        return response()->json(['message' => 'View duration tracked successfully']);
    }
}
