<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Saved;
use Illuminate\Http\Request;

class SavedController extends Controller
{
    public function index()
    {
        $saveds = Saved::all();
        return view('user.saved.index', [
           'saveds' => $saveds
        ]);
    }
}
