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
    public function payment(Request $request)
    {
        Stripe::setApiKey(env('STRIPE_SECRET'));

        // Create Stripe Payment Intent
        $paymentIntent = PaymentIntent::create([
            'amount' => $request->amount * 100, // Convert to cents
            'currency' => 'usd',
            'payment_method_types' => ['card'],
        ]);

        $paymentsession = PaymentSession::create([
            'user_id'        => Auth::user()->id,
            'ip_address'         => $request->ip(),
            'device_info' => $request->header('Device-Info'),
            'user_agent'       => $request->header('User-Agent'),
        ]);

        // Save the pending payment in DB
        $payment = Payment::create([
            'user_id'        => Auth::user()->id,
            'amount'         => $request->amount,
            'payment_method' => 'Card Payment',
            'currency'       => 'USD',
            'processor'      => 'Stripe',
            'payment_no'     => $paymentIntent->id, // Store Stripe Payment ID
            'status'         => 'pending',
            'payment_session_id'         => $paymentsession->id,
            'purpose'        => $request->purpose,
            'description'    => $request->description,
        ]);

        return response()->json([
            'clientSecret' => $paymentIntent->client_secret,
            'payment_id'   => $payment->id,
        ]);
    }

    public function confirmPayment(Request $request)
    {
        // Get Payment ID (payment_intent_id from Flutter)
        $paymentId = $request->payment_intent_id;

        // Find the payment record using the payment_id (which is actually payment_intent_id)
        $payment = Payment::find($paymentId);

        if (!$payment) {
            return response()->json(['message' => 'Payment not found'], 404);
        }

        // Now we get the Stripe Payment Intent ID (payment_no) from the payment record
        $paymentIntentId = $payment->payment_no;

        // Check if the paymentIntentId exists
        if (!$paymentIntentId) {
            return response()->json(['message' => 'Payment Intent ID not found in payment record'], 400);
        }

        // Set Stripe API Key
        Stripe::setApiKey(env('STRIPE_SECRET'));

        try {
            // Retrieve the payment intent from Stripe using the stored paymentIntentId
            $paymentIntent = PaymentIntent::retrieve($paymentIntentId);

            // Check if the payment was successful
            if ($paymentIntent->status == 'succeeded') {
                // Update DB: Payment successful
                $payment->update([
                    'status' => 'success',
                ]);

                $amountPaid = $payment->amount; // Assuming 'value' is the amount in USD
                $pointsEarned = ($amountPaid / 1) * 1000; // 1 USD = 1000 points

                $payment->user->increment('points', $pointsEarned);

                return response()->json(['message' => 'Payment successful']);
            } else {
                // Update DB: Payment failed
                $payment->update([
                    'status' => 'failed',
                    'status_reason' => $request->status_reason,
                ]);

                return response()->json(['message' => 'Payment failed'], 400);
            }
        } catch (\Exception $e) {
            // Handle any errors from Stripe API
            return response()->json(['message' => 'Error: ' . $e->getMessage()], 400);
        }
    }

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
