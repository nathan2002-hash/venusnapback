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

        // Check if account exists and if the user has enough available balance
        if (!$account) {
            return response()->json(['message' => 'Account not found.'], 404);
        }

        if ($account->available_balance < $request->amount) {
            return response()->json(['message' => 'Insufficient balance.'], 400);
        }

        // Prepare the payout data
        $payoutData = [
            'user_id' => $user->id,
            'amount' => $request->amount,
            'currency' => $account->currency,
            'payment_session_id' => uniqid('payout_'),
            'processor' => 'paypal', // Default processor, can change based on the selected method
            'status' => 'pending',
            'payout_method' => $account->payout_method,
            'payout_reason' => $request->payout_reason,
            'transaction_id' => uniqid('trx_'),
            'requested_at' => now(),
        ];

        // If the payout method is PayPal, add PayPal email to the payout data
        if ($account->payout_method == 'paypal') {
            $payoutData['paypal_email'] = $account->paypal_email;
        }
        // If the payout method is Mobile Money, add mobile money data to the payout
        if ($account->payout_method == 'mobile_money') {
            $payoutData['phone_no'] = $account->phone_no;
            $payoutData['account_name'] = $account->account_name;
            $payoutData['network'] = $account->network;
        }
        // If the payout method is Bank Transfer, add bank transfer details
        if ($account->payout_method == 'bank_transfer') {
            $payoutData['account_holder_name'] = $account->account_holder_name;
            $payoutData['account_number'] = $account->account_number;
            $payoutData['bank_name'] = $account->bank_name;
            $payoutData['bank_branch'] = $account->bank_branch;
            $payoutData['swift_code'] = $account->swift_code;
            $payoutData['iban'] = $account->iban;
        }

        // Start database transaction to ensure data consistency
        DB::beginTransaction();

        try {
            // Create the payout record
            $payout = Payout::create($payoutData);

            // Deduct the requested amount from the user's available balance
            $account->decrement('available_balance', $request->amount);

            // Commit the transaction
            DB::commit();

            return response()->json([
                'message' => 'Payout requested successfully!',
                'payout' => $payout,
            ], 201);

        } catch (\Exception $e) {
            // Rollback transaction if there is an error
            DB::rollBack();

            return response()->json([
                'message' => 'Error processing payout.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
