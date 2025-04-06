<?php

namespace App\Http\Controllers\Api;

use App\Models\Album;
use App\Models\Payout;
use App\Models\Account;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class PayoutController extends Controller
{
    public function requestPayout(Request $request)
    {
        // Get authenticated user
        $user = Auth::user();

        // Retrieve user's account details
        $account = Account::where('user_id', $user->id)->first();

        // Check if account exists
        if (!$account) {
            return response()->json(['message' => 'Account not found.'], 404);
        }

        // Check if user has enough balance after fees
        if ($request->amount < 10) {
            return response()->json(['message' => 'Minimum Balance of 10 is required'], 400);
        }

        // Define payout fee percentage
        $payoutFeePercentage = 2.4; // Example: 2.4%

        // Calculate fee and final payout amount
        $feeAmount = ($request->amount * $payoutFeePercentage) / 100;
        $finalAmount = $request->amount - $feeAmount;

        // Check if user has enough balance after fees
        if ($account->available_balance < $request->amount) {
            return response()->json(['message' => 'Insufficient balance.'], 400);
        }

        // Start database transaction
        DB::beginTransaction();

        try {
            // Prepare the payout data
            $payoutData = [
                'user_id' => $user->id,
                'amount' => $finalAmount, // Amount after fee deduction
                'currency' => $account->currency,
                'payment_session_id' => uniqid('payout_'),
                'processor' => $account->payout_method,
                'status' => 'pending',
                'payout_method' => $account->payout_method,
                'payout_reason' => 'User Payout for their content creation',
                'transaction_id' => uniqid('trx_'),
                'requested_at' => now(),
                'payout_fee' => $feeAmount, // Store the deducted fee
            ];

            // Add payment details based on the payout method
            if ($account->payout_method == 'paypal') {
                $payoutData['paypal_email'] = $account->paypal_email;
            } elseif ($account->payout_method == 'mobile_money') {
                $payoutData['phone_no'] = $account->phone_no;
                $payoutData['account_name'] = $account->account_name;
                $payoutData['network'] = $account->network;
            } elseif ($account->payout_method == 'bank_transfer') {
                $payoutData['account_holder_name'] = $account->account_holder_name;
                $payoutData['account_number'] = $account->account_number;
                $payoutData['bank_name'] = $account->bank_name;
                $payoutData['bank_branch'] = $account->bank_branch;
                $payoutData['swift_code'] = $account->swift_code;
                $payoutData['iban'] = $account->iban;
            }

            // Create the payout record
            $payout = Payout::create($payoutData);

            // Deduct the requested amount from the user's available balance
            $account->decrement('available_balance', $request->amount);

            // Commit the transaction
            DB::commit();

            return response()->json([
                'message' => 'Payout requested successfully!',
                'payout' => [
                    'amount' => $finalAmount,
                    'payout_fee' => $feeAmount,
                    'currency' => $account->currency,
                    'status' => 'pending',
                    'payout_method' => $account->payout_method,
                ],
            ], 200);

        } catch (\Exception $e) {
            // Rollback transaction if there is an error
            DB::rollBack();

            return response()->json([
                'message' => 'Error processing payout.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function fetchUserPayouts(Request $request)
    {
        // Get the authenticated user
        $user = Auth::user();

        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        // Define currency symbols
        $currencySymbols = [
            'USD' => '$',
            'EUR' => '€',
            'GBP' => '£',
            'ZMW' => 'ZK',
            'NGN' => '₦',
            'JPY' => '¥',
            'INR' => '₹',
        ];

        // Retrieve payments for the authenticated user
        $payouts = Payout::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'data' => $payouts->map(function ($payment) use ($currencySymbols) {
                $currencyCode = strtoupper($payment->currency); // Ensure case consistency
                $currencySymbol = $currencySymbols[$currencyCode] ?? $currencyCode; // Default to currency code if symbol not found

                return [
                    'type'           => 'payout',
                    'amount'         => $currencySymbol . '' . number_format((float) $payment->amount, 2),
                    'created_at'     => $payment->created_at->toISOString(),
                    'payment_method' => $payment->payment_method,
                    'currency'       => $currencyCode, // Still keeping currency code
                    'status'         => $payment->status,
                    'description'    => $payment->description,
                ];
            }),
        ]);
    }

}
