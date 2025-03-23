<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Recommendation;

class RecommendationController extends Controller
{
    public function index()
    {
        $recommendations = Recommendation::orderBy('created_at', 'desc')->paginate(30);
        return view('admin.recommendations.index', [
           'recommendations' => $recommendations,
        ]);
    }
}
