<?php

namespace App\Http\Controllers\Api;

use App\Models\Album;
use App\Models\Payout;
use App\Models\Account;
use App\Models\Earning;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class MonetizationController extends Controller
{
    public function getUserDashboardData(Request $request)
    {
        // Get the authenticated user
        $user = Auth::user();

        $accounntcreate = Account::firstOrCreate(['user_id' => $user->id], [
            'user_id' => $user->id,
            'account_balance' => 0.00,
            'available_balance' => 0.00,
            'monetization_status' => 1,
            'payout_method' => 'paypal',
            'currency' => 'USD',
            'paypal_email' => $user->email
        ]);
        // Retrieve the user's account data (total earnings and available balance)
        $account = Account::where('user_id', $user->id)->first();

        // Check if account exists
        if (!$account) {
            return response()->json([
                'message' => 'Account not found.',
            ], 404);
        }

        // Get the user's albums (optional: paginate if there are many)
        $albums = Album::where('user_id', $user->id)->get();

        // Get the latest 5 payouts for the user
        $latestPayouts = Payout::where('user_id', $user->id)
            ->orderBy('requested_at', 'desc')
            ->limit(5)
            ->get(['amount', 'requested_at', 'status', 'payout_method']);

        // Format the payout data
        $latestPayouts = $latestPayouts->map(function ($payout) {
            return [
                'amount' => $payout->amount,
                'requested_at' => $payout->requested_at->toIso8601String(), // Convert to ISO format
                'status' => $payout->status,
                'payout_method' => $payout->payout_method,
            ];
        });

       // Get the current month's earnings from the 'earnings' table
        $currentMonthEarnings = Earning::where('user_id', $user->id)
        ->whereBetween('created_at', [now()->startOfMonth(), now()->endOfMonth()])
        ->sum('earning');

        // Get the last month's earnings from the 'earnings' table
        $lastMonthEarnings = Earning::where('user_id', $user->id)
        ->whereBetween('created_at', [now()->subMonth()->startOfMonth(), now()->subMonth()->endOfMonth()])
        ->sum('earning');


        // Calculate the change in earnings (optional)
        $earningsChange = $currentMonthEarnings - $lastMonthEarnings;

        // Prepare the response data
        $response = [
            'total_earnings' => $account->account_balance, // Total earnings
            'available_balance' => $account->available_balance, // Available balance
            'albums' => $albums->count(), // List of user's albums
            'latest_payouts' => $latestPayouts, // Latest 5 payouts
            'current_month_earning' => number_format($currentMonthEarnings, 2), // Current month earnings
            'last_month_earning' => number_format($lastMonthEarnings, 2), // Last month earnings
            'change' => number_format($earningsChange, 2), // Earnings change
        ];

        return response()->json($response, 200);
    }

    public function getPayoutDetails(Request $request)
    {
        // Get the authenticated user
        $user = Auth::user();

        // Retrieve the user's account
        $account = Account::where('user_id', $user->id)->first();

        // Check if account exists
        if (!$account) {
            return response()->json([
                'message' => 'Account not found.',
            ], 404);
        }

        // Define the minimum payout amount (can be dynamic if needed)
        $minimumPayout = 10.00;

        // Define the payout fee percentage
        $payoutFeePercentage = 2.4; // Example: 2.4%

        // Get the payout email (assuming PayPal is used)
        $payoutEmail = $account->paypal_email ?? null;

        // Prepare the response
        $response = [
            'available_balance' => number_format($account->available_balance, 2),
            'minimum_payout' => number_format($minimumPayout, 2),
            'payout_email' => $payoutEmail,
            'payout_fee_percentage' => number_format($payoutFeePercentage, 2)
        ];

        return response()->json($response, 200);
    }


}
