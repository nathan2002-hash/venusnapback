<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Ad;
use App\Models\Adboard;
use Illuminate\Http\Request;

class AdController extends Controller
{
    public function index()
    {
        $ads = Ad::orderBy('created_at', 'desc')->paginate(30);
        return view('admin.ads.index', [
           'ads' => $ads,
        ]);
    }

    public function adboards()
    {
        $adboards = Adboard::orderBy('created_at', 'desc')->paginate(30);
        return view('admin.ads.adboards', [
           'adboards' => $adboards,
        ]);
    }
}
