<?php

namespace App\Http\Controllers\Api;

use App\Models\Category;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class PreferenceController extends Controller
{
    public function index()
    {
        return response()->json(Category::all());
    }
}
