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
        return response()->json(Category::where('status', 'active')->get());
    }


    public function storeUserPreferences(Request $request)
    {
        $request->validate([
            'category_ids' => 'required|array|min:2',
            'category_ids.*' => 'exists:categories,id',
        ]);

        $user = Auth::user();
        $userAgent = $request->header('User-Agent');

        // Mark all existing preferences as inactive instead of deleting
        UserPreference::where('user_id', $user->id)->update(['status' => 'inactive']);

        // Save new preferences as active
        foreach ($request->category_ids as $categoryId) {
            UserPreference::updateOrCreate(
                ['user_id' => $user->id, 'category_id' => $categoryId],
                ['status' => 'active']
            );
        }

         // Update user's preference column to 0
        $user->update(['preference' => 0]);

        return response()->json(['message' => 'Preferences updated successfully.']);
    }
}
