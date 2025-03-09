<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function index()
    {
        $categories = Category::orderBy('created_at', 'desc')->paginate(30);
        return view('admin.categories.index', [
           'categories' => $categories,
        ]);
    }

    public function store(Request $request)
    {
        $category = new Category();
        $category->name = $request->name;
        $category->status = 'active';
        $category->description = $request->description;
        $category->save();
        return redirect()->back();
    }
}
