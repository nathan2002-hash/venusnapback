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
            'payment_method' => 'Stripe',
            'currency'       => 'usd',
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
    // Get Payment Intent ID from the request
    $paymentIntentId = $request->payment_intent_id;

    // If payment failed, update the status in the database
    if ($paymentIntentId == 'failed') {
        // Update DB: Payment failed
        Payment::where('id', $paymentIntentId)->update([
            'status' => 'failed',
            'status_reason' => $request->status_reason, // Reason for failure
        ]);

        return response()->json(['message' => 'Payment failed', 'status_reason' => $request->status_reason], 400);
    }

    // For successful payment, you can retrieve and process the payment
    Stripe::setApiKey(env('STRIPE_SECRET'));
    try {
        $paymentIntent = PaymentIntent::retrieve($paymentIntentId);

        if ($paymentIntent->status == 'succeeded') {
            // Update DB: Payment successful
            Payment::where('payment_no', $paymentIntentId)->update([
                'status' => 'success',
            ]);

            return response()->json(['message' => 'Payment successful']);
        } else {
            // Update DB: Payment failed
            Payment::where('payment_no', $paymentIntentId)->update([
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


}
