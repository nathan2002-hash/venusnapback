<?php

namespace App\Http\Controllers\Api;

use App\Models\Ad;
use App\Models\Point;
use Illuminate\Http\Request;
use App\Models\PointTransaction;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class PointController extends Controller
{
    public function getPoints(Request $request)
    {
        $request->validate([
            'country' => 'sometimes|string|size:3'
        ]);

        $country = $request->input('country', 'USA');

        $packages = Point::where('country', strtoupper($country))
            ->orderBy('points')
            ->get(['points', 'price'])
            ->map(function ($package) {
                return [
                    'points' => (int) $package->points,
                    'price'  => (int) $package->price, // Ensuring price is also an integer
                ];
            });

        return response()->json([
            'packages'   => $packages,
            'min_points' => (int) config('points.min_points', 1000),
        ]);
    }

    public function addPoints(Request $request, $id)
    {
        $request->validate([
            'points' => 'required|integer|min:1|max:100000' // Added max limit for security
        ]);

        $user = Auth::user();
        $ad = Ad::where('id', $id)
                ->where('user_id', $user->id)
                ->firstOrFail();

        // Check if user has enough points
        if ($user->points < $request->points) {
            return response()->json([
                'message' => 'Insufficient points',
                'errors' => [
                    'points' => ['You only have ' . $user->points . ' points available']
                ]
            ], 422);
        }

        // Start transaction for data consistency
        DB::beginTransaction();

        try {
            // Deduct points from user
            $user->points -= $request->points;
            $user->save();

            // Add points to ad
            $ad->points += $request->points;
            $ad->save();

            // Record the transaction
            PointTransaction::create([
                'user_id' => $user->id,
                'resource_id' => $ad->id,
                'points' => $request->points,
                'type' => 'ad_points_add',
                'description' => 'adding points to an existing ad',
                'balance_after' => $user->points
            ]);

            DB::commit();

            return response()->json([
                'message' => 'Points added successfully',
                'data' => [
                    'id' => $ad->id,
                    'new_points' => $ad->points,
                    'user_remaining_points' => $user->points,
                    'updated_at' => $ad->updated_at
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Failed to add points',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
