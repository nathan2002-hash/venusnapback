<?php

namespace App\Http\Controllers\Api;

use App\Models\Category;
use Illuminate\Http\Request;
use App\Models\UserPreference;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class PreferenceController extends Controller
{
    public function index()
    {
        return response()->json(Category::all());
    }

    public function storeUserPreferences(Request $request)
    {
        $request->validate([
            'category_ids' => 'required|array|min:4',
            'category_ids.*' => 'exists:categories,id',
        ]);

        $user = Auth::user();
        UserPreference::where('user_id', $user->id)->delete();

        foreach ($request->category_ids as $categoryId) {
            UserPreference::create([
                'user_id' => $user->id,
                'category_id' => $categoryId,
            ]);
        }

        return response()->json(['message' => 'Preferences saved successfully.']);
    }
}
