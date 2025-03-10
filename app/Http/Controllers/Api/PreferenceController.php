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

    public function contentpre(Request $request)
    {
        // Fetch the logged-in user
        $user = Auth::user();

        // Fetch active categories
        $categories = Category::where('status', 'active')->get();

        // Fetch the user's selected categories from the user_preferences table
        $userPreferences = UserPreference::where('user_id', $user->id)->pluck('category_id')->toArray();

        // Mark the selected categories
        $categories->map(function ($category) use ($userPreferences) {
            $category->is_selected = in_array($category->id, $userPreferences);  // Add a new property `is_selected`
            return $category;
        });

        return response()->json($categories);
    }

    public function getUserPreferences(Request $request)
    {
        // Assuming the user is authenticated and we can access user id from the request
        $userId = $request->user()->id;

        // Retrieve the category IDs that the user has selected
        $userPreferences = UserPreference::where('user_id', $userId)
                                        ->where('status', 'active') // or any status that indicates preference
                                        ->pluck('category_id');

        return response()->json($userPreferences);
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
