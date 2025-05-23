<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class HomeController extends Controller
{
    public function terms()
    {
        $terms = File::get(resource_path('markdown/terms.md'));

        // If you want to convert Markdown to HTML (optional)
        $terms = Str::markdown($terms);
        return view('terms', [
            'terms' => $terms
        ]);
    }

    public function home()
    {
        return view('welcome', [
        ]);
    }
}
