<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Jobs\SendPaymentReceipt;
use App\Models\PaymentSession;
use Stripe\Stripe;
use App\Models\Payment;
use Stripe\PaymentIntent;
use Illuminate\Support\Facades\Auth;

class PaymentController extends Controller
{
    public function index()
    {
        $country = 'USA';
        return view('chat', [
            'userPoints' => 9000,
            'min_points' => config('points.min_points', 1000),
            'stripekey' => env('STRIPE_PUBLIC')
        ]);
    }

    public function createPaymentIntent(Request $request)
    {
        $validated = $request->validate([
            'points' => 'required|integer|min:1000',
            'amount' => 'required|numeric|min:1',
            'purpose' => 'required|string',
            'description' => 'required|string'
        ]);

        Stripe::setApiKey(env('STRIPE_SECRET'));

        // Create Stripe Payment Intent (amount is already in dollars)
        $paymentIntent = PaymentIntent::create([
            'amount' => $validated['amount'] * 100, // Convert to cents
            'currency' => 'usd',
            'payment_method_types' => ['card'],
            'metadata' => [
                'points' => $validated['points']
            ]
        ]);

        $realIp = $request->header('cf-connecting-ip') ?? $request->ip();
        $ipaddress = $realIp;

        $paymentsession = PaymentSession::create([
            'user_id' => Auth::user()->id,
            'ip_address' => $ipaddress,
            'device_info' => $request->header('Device-Info'),
            'user_agent' => $request->header('User-Agent'),
        ]);

        // Save the pending payment in DB
        $payment = Payment::create([
            'user_id'        => Auth::user()->id,
            'amount'         => $validated['amount'],
            'payment_method' => 'Stripe',
            'currency'       => 'usd',
            'processor'      => 'Stripe',
            'payment_no'     => $paymentIntent->id,
            'status'         => 'pending',
            'purpose'        => $validated['purpose'],
            'payment_session_id' => $paymentsession->id,
            'description'    => $validated['description'],
            'metadata' => [
                'points' => $validated['points'],
                'stripe_response' => $paymentIntent->toArray(),
            ],
        ]);

        return response()->json([
            'clientSecret' => $paymentIntent->client_secret,
            'payment_id'   => $payment->id,
        ]);
    }

    public function confirmPayment(Request $request)
    {
        $validated = $request->validate([
            'payment_intent_id' => 'required|string'
        ]);

        $payment = Payment::findOrFail($validated['payment_intent_id']);

        // Retrieve Payment Intent from Stripe
        Stripe::setApiKey(env('STRIPE_SECRET'));
        $paymentIntent = PaymentIntent::retrieve($payment->payment_no);

        if ($paymentIntent->status == 'succeeded') {
            // Update DB: Payment successful
            $points = $payment->metadata['points'] ?? 0;

            $payment->update([
                'status' => 'success',
            ]);

            if ($points > 0 && $payment->user) {
                $payment->user->increment('points', $points);
            }

            SendPaymentReceipt::dispatch($payment->user->email, $payment);

            return response()->json(['message' => 'Payment successful']);
        }

        // Update DB: Payment failed
        $payment->update([
            'status' => 'failed',
        ]);

        return response()->json(['message' => 'Payment failed'], 400);
    }
}
