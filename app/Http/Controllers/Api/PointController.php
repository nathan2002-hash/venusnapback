<?php

namespace App\Http\Controllers\Api;

use App\Models\Ad;
use App\Models\Point;
use Illuminate\Http\Request;
use App\Models\PointTransaction;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Models\Adboard;
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
            'user_points'   => (int) Auth::user()->points,
            'min_points' => (int) config('points.min_points', 1000),
            'stripekey'   => env('STRIPE_PUBLIC'),
        ]);
    }

    public function addPoints(Request $request, $id)
    {
        $request->validate([
            'points' => 'required|integer|min:1|max:100000'
        ]);

        $user = Auth::user();
        $ad = Ad::where('id', $id)->firstOrFail();
        $adboard = Adboard::where('id', $ad->adboard_id)->firstOrFail();

        // Record the transaction attempt immediately
        $transaction = PointTransaction::create([
            'user_id' => $user->id,
            'resource_id' => $ad->id,
            'points' => $request->points,
            'type' => 'ad_points_add',
            'status' => 'pending',
            'description' => 'Attempt to add points to ad',
            'balance_before' => $user->points,
            'balance_after' => $user->points // Will be updated if successful
        ]);

        // Check if user has enough points
        if ($user->points < $request->points) {
            $transaction->update([
                'status' => 'failed',
                'description' => 'Insufficient points: attempted to add '.$request->points.' but only had '.$user->points,
                'metadata' => json_encode([
                    'required_points' => $request->points,
                    'available_points' => $user->points
                ])
            ]);

            return response()->json([
                'message' => 'Insufficient points',
                'errors' => [
                    'points' => ['You only have ' . $user->points . ' points available']
                ],
                'transaction_id' => $transaction->id
            ], 422);
        }

        DB::beginTransaction();

        try {
            // Deduct points from user
            $user->points -= $request->points;
            $user->save();

            // Add points to adboard
            $adboard->points += $request->points;
            $adboard->save();

            // Update the transaction record
            $transaction->update([
                'status' => 'completed',
                'description' => 'Successfully added points to ad',
                'balance_after' => $user->points,
                'metadata' => json_encode([
                    'adboard_id' => $adboard->id,
                    'adboard_points_before' => $adboard->points - $request->points,
                    'adboard_points_after' => $adboard->points
                ])
            ]);

            DB::commit();

            return response()->json([
                'message' => 'Points added successfully',
                'data' => [
                    'id' => $ad->id,
                    'new_points' => $adboard->points,
                    'user_remaining_points' => $user->points,
                    'updated_at' => $adboard->updated_at,
                    'transaction_id' => $transaction->id
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            $transaction->update([
                'status' => 'failed',
                'description' => 'System error: '.$e->getMessage(),
                'metadata' => json_encode([
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ])
            ]);

            return response()->json([
                'message' => 'Failed to add points',
                'error' => $e->getMessage(),
                'transaction_id' => $transaction->id
            ], 500);
        }
    }

    public function paymentinfo(Request $request)
    {
        return response()->json([
            'info' => "Venusnap Payment Information:\n\n" .
                "1. All payments are processed securely through PayPal.\n" .
                "2. Venusnap uses a point-based system: 1000 points = \$1 USD.\n" .
                "3. Points are credited to your account immediately after successful payment.\n" .
                "4. In rare cases where there's a delay, your points will be credited within 24 hours.\n" .
                "5. All purchases are **final and non-refundable**. Once you buy points, they **cannot be converted back** to real money or refunded.\n" .
                "6. Points cannot be transferred to another user or exchanged for goods outside Venusnap.\n" .
                "7. Misuse, fraud, or abuse of the payment system may result in suspension of your account.\n\n" .
                "For any payment-related issues, contact our support team at: support@venusnap.com"
        ]);
    }

}
