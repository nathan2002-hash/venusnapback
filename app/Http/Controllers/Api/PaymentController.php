<?php

namespace App\Http\Controllers\Api;

use Stripe\Stripe;
use App\Models\Payment;
use Stripe\PaymentIntent;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\PaymentSession;
use Illuminate\Support\Facades\Auth;

class PaymentController extends Controller
{
    public function fetchUserPayments(Request $request)
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
        $payments = Payment::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'data' => $payments->map(function ($payment) use ($currencySymbols) {
                $currencyCode = strtoupper($payment->currency); // Ensure case consistency
                $currencySymbol = $currencySymbols[$currencyCode] ?? $currencyCode; // Default to currency code if symbol not found

                return [
                    'type'           => 'payment',
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
