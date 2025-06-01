<?php

namespace App\Http\Controllers\Api;

use Stripe\Stripe;
use App\Models\Payment;
use Stripe\PaymentIntent;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\PaymentSession;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use PayPalCheckoutSdk\Core\PayPalHttpClient;
use PayPalCheckoutSdk\Core\SandboxEnvironment;
use PayPalCheckoutSdk\Core\ProductionEnvironment;
use PayPalCheckoutSdk\Orders\OrdersCreateRequest;
use PayPalCheckoutSdk\Orders\OrdersCaptureRequest;
use PayPalCheckoutSdk\Orders\OrdersGetRequest;

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

    public function getConfig()
    {
        return response()->json([
            'success' => true,
            'config' => [
                'notice_message' =>
                    'We are currently not accepting direct payments for points. Please fill out the form below to request points.',
                'points_options' =>
                    [1000, 2500, 5000, 10000, 25000, 50000, 100000],
                'min_points' => 1000,
                'max_points' => 100000,
            ],
        ]);
    }
}
