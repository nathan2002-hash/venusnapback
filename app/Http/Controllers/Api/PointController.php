<?php

namespace App\Http\Controllers\Api;

use App\Models\Point;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

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
}
