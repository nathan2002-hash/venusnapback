<?php

namespace App\Http\Controllers\Api;

use App\Models\Account;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class AccountController extends Controller
{
    public function index()
    {
       //
        return view('user.account.index', [
           //
        ]);
    }

    public function getPayoutSettings(Request $request)
    {
        $user = $request->user();


        // Check monetization status
        if ($user->account->monetization_status !== 'active') {
            return response()->json([
                'can_set_payment' => false,
                'message' => 'Your monetization account is not active. ' .
                             'Please complete and get approved for monetization first.'
            ], 403);
        }

        return response()->json([
            'can_set_payment' => true,
            'payment_details' => [
                'method' => $user->account->payout_method,
                'email' => $user->account->paypal_email,
                'account_name' => $user->account->account_holder_name,
                'account_number' => $user->account->account_number,
                'routing_number' => $user->account->routing_number,
                'swift_code' => $user->account->swift_code,
                'bank_name' => $user->account->bank_name,
                'bank_address' => $user->account->bank_address,
                'account_type' => $user->account->account_type,
            ]
        ]);
    }

    public function savePayoutSettings(Request $request)
    {
        $user = $request->user();

        $validated = $request->validate([
            'method' => 'required|in:paypal,bank',
            'email' => 'required_if:method,paypal|email',
            'account_name' => 'required_if:method,bank',
            'account_number' => 'required_if:method,bank',
            'routing_number' => 'required_if:method,bank',
            'swift_code' => 'required_if:method,bank',
            'bank_name' => 'required_if:method,bank',
            'bank_address' => 'required_if:method,bank',
        ]);

        // Update payment method
        $user->account->payout_method = $validated['method'];

        if ($validated['method'] === 'paypal') {
            $user->account->paypal_email = $validated['email'];
        } else {
            $user->account->account_holder_name = $validated['account_name'];
            $user->account->account_number = $validated['account_number'];
            $user->account->account_type = $validated['account_type'];
            $user->account->routing_number = $validated['routing_number'];
            $user->account->swift_code = $validated['swift_code'];
            $user->account->bank_name = $validated['bank_name'];
            $user->account->bank_address = $validated['bank_address'];
        }

        $user->account->save();

        return response()->json([
            'message' => 'Payment settings updated successfully'
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
        // Send notification to admin for review
        //$user->notify(new MonetizationApplicationSubmitted($account));
        // Or for admin: Notification::send($adminUsers, new NewMonetizationApplication($account));

        return response()->json([
            'message' => 'Application submitted successfully',
            'status' => 'pending'
        ], 200);
    }
}
