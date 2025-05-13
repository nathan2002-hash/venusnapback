<?php

namespace App\Http\Controllers\Admin;

use App\Models\Category;
use Illuminate\Http\Request;
use App\Models\AlbumCategory;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class CategoryController extends Controller
{
    public function post()
    {
        $categories = Category::orderBy('created_at', 'desc')->paginate(30);
        return view('admin.categories.index', [
           'categories' => $categories,
        ]);
    }

    public function album()
    {
        $categories = AlbumCategory::orderBy('created_at', 'desc')->paginate(30);
        return view('admin.categories.albumindex', [
           'categories' => $categories,
        ]);
    }

    public function poststore(Request $request)
    {
        $category = new Category();
        $category->name = $request->name;
        $category->status = 'active';
        $category->description = $request->description;
        $category->user_id = Auth::user()->id;
        $category->save();
        return redirect()->back();
    }

    public function albumstore(Request $request)
    {
        $category = new AlbumCategory();
        $category->name = $request->name;
        $category->type = $request->type;
        $category->description = $request->description;
        $category->user_id = Auth::user()->id;
        $category->save();
        return redirect()->back();
    }
}
