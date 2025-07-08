<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class HomeController extends Controller
{
    public function terms()
    {
        return view('terms', [
        ]);
    }

    public function privacy()
    {
        return view('policy', [
        ]);
    }

    public function home()
    {
        return view('welcome', [
        ]);
    }

    public function blocked()
    {
        return view('auth.blocked', [
        ]);
    }

    public function childsafety()
    {
        return view('child', [
        ]);
    }
}
