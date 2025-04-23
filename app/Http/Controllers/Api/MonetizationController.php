<?php

namespace App\Http\Controllers\Api;

use App\Models\Album;
use App\Models\Payout;
use App\Models\Account;
use App\Models\Earning;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\MonetizationRequest;
use Illuminate\Support\Facades\Auth;

class MonetizationController extends Controller
{
    public function getMonetizationStatus(Request $request)
    {
        $user = Auth::user();
        $account = Account::where('user_id', $user->id)->first();

        if (!$account) {
            return response()->json([
                'status' => 'inactive',
                'message' => 'Monetization not enabled'
            ], 200);
        }

        return response()->json([
            'status' => $account->monetization_status ?? 'inactive',
            'message' => $this->getStatusMessage($account->monetization_status)
        ], 200);
    }

    public function getUserAlbums(Request $request)
    {
        $perPage = $request->input('per_page', 20);

        $albums = Auth::user()->albums()
            ->where('status', 'active') // ✅ status = 'active'
            ->whereIn('type', ['creator', 'business']) // ✅ type filter
            ->where(function ($query) {
                $query->where('monetization_status', 0)
                    ->orWhereNull('monetization_status'); // ✅ monetization_status is 0 or null
            })
            ->select(['id', 'name', 'type', 'monetization_status', 'status']) // optional fields
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $albums
        ]);
    }

    public function countries()
    {
        $countries = [
            ['code' => 'US', 'name' => 'United States'],
            ['code' => 'GB', 'name' => 'United Kingdom'],
            ['code' => 'CA', 'name' => 'Canada'],
            // Add all countries you support
        ];

        return response()->json([
            'success' => true,
            'data' => $countries,
            'message' => 'Countries fetched successfully'
        ]);
    }



    public function applyFsorMonetization(Request $request)
    {
        $user = Auth::user();

        // $validated = $request->validate([
        //     'payout_method' => 'required|in:paypal,bank_transfer',
        //     'paypal_email' => 'required_if:payout_method,paypal|email',
        //     'bank_account_number' => 'required_if:payout_method,bank_transfer',
        //     'bank_routing_number' => 'required_if:payout_method,bank_transfer',
        //     'bank_name' => 'required_if:payout_method,bank_transfer',
        //     'account_holder_name' => 'required_if:payout_method,bank_transfer',
        //     'country' => 'required|string|max:100',
        // ]);

        $accounntcreate = Account::firstOrCreate(['user_id' => $user->id], [
            'user_id' => $user->id,
            'account_balance' => 0.00,
            'available_balance' => 0.00,
            'monetization_status' => 'inactive',
            'payout_method' => 'paypal',
            'currency' => 'USD',
            'paypal_email' => $user->email
        ]);

        // Find existing account
        $account = Account::where('user_id', $user->id)->first();

        if (!$account) {
            return response()->json([
                'message' => 'Account not found'
            ], 404);
        }

        // Check if already pending or active
        if ($account->monetization_status === 'pending') {
            return response()->json([
                'message' => 'You already have a pending monetization application'
            ], 400);
        }

        if ($account->monetization_status === 'active') {
            return response()->json([
                'message' => 'Your account is already monetized'
            ], 400);
        }
        $account->payout_method = $request->payout_method;
        if ($request->payout_method === 'paypal') {
            $account->paypal_email = $request->paypal_email;
        } else {
            $account->account_number = $request->bank_account_number;
            $account->swift_code = $request->bank_routing_number;
            $account->bank_name = $request->bank_name;
            $account->account_holder_name = $request->account_holder_name;
        }
        $account->country = $request->country;
        $account->monetization_status = 'pending';
        $account->save();

        $album = Album::find($request->album_id);
        $album->monetization_status = 'pending';
        $album->save();
        // Send notification to admin for review
        //$user->notify(new MonetizationApplicationSubmitted($account));
        // Or for admin: Notification::send($adminUsers, new NewMonetizationApplication($account));

        return response()->json([
            'message' => 'Application submitted successfully',
            'status' => 'pending'
        ], 200);
    }

    public function applyForMonetization(Request $request)
    {
        $user = Auth::user();

        // Validate the request
        $validated = $request->validate([
            'country' => 'required|string',
            'album_id' => 'required|exists:albums,id'
        ]);

        // Create or update account
        $account = Account::firstOrCreate(
            ['user_id' => $user->id],
            [
                'user_id' => $user->id,
                'account_balance' => 0.00,
                'available_balance' => 0.00,
                'monetization_status' => 'inactive',
                'payout_method' => 'paypal',
                'country' => $validated['country'],
                'currency' => 'USD',
                'paypal_email' => $user->email
            ]
        );

        // Update album monetization status
        $album = Album::find($validated['album_id']);
        if (!$album) {
            return response()->json(['message' => 'Album not found'], 404);
        }

        $album->monetization_status = 'pending';
        $album->save();

        // Create new monetization request (don't use find() - use create())
        $monetizationrequest = MonetizationRequest::create([
            'country' => $validated['country'],
            'album_id' => $validated['album_id'],
            'user_id' => $user->id,
            'status' => 'pending',
            'device_info' => $request->header('Device-Info'),
            'user_agent' => $request->header('User-Agent'),
            'ip_address' => $request->ip()
        ]);

        // Send notifications if needed
        // $user->notify(new MonetizationApplicationSubmitted($account));

        return response()->json([
            'message' => 'Application submitted successfully',
            'status' => 'pending',
            'data' => $monetizationrequest
        ], 200);
    }

    public function getApplications(Request $request)
    {
        $applications = $request->user()->monetizationrequests()
            ->with(['album' => function($query) {
                $query->select('id', 'name');
            }])
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $applications
        ]);
    }

    public function getUserDashboardData(Request $request)
    {
        // Get the authenticated user
        $user = Auth::user();

        $accounntcreate = Account::firstOrCreate(['user_id' => $user->id], [
            'user_id' => $user->id,
            'account_balance' => 0.00,
            'available_balance' => 0.00,
            'monetization_status' => 'inactive',
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

    private function getStatusMessage($status)
    {
        switch (strtolower($status)) {
            case 'pending':
                return 'Your monetization request is under review. Please wait for approval.';
            case 'rejected':
                return 'Your monetization request was rejected. Please contact support for more information.';
            case 'suspended':
                return 'Your monetization has been suspended due to policy violations.';
            default:
                return 'Monetization is not enabled for your account.';
        }
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
