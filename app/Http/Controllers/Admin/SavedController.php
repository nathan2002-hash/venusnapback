<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Saved;
use Illuminate\Http\Request;

class SavedController extends Controller
{
    public function index()
    {
        $saveds = Saved::orderBy('created_at', 'desc')->paginate(30);
        return view('admin.saved.index', [
           'saveds' => $saveds,
        ]);
    }
}
