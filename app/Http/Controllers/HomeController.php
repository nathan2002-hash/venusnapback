<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function terms()
    {
        //$categories = Category::orderBy('created_at', 'desc')->paginate(30);
        return view('terms', [
           //'categories' => $categories,
        ]);
    }
}
