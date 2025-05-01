<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\PaymentSession;
use Stripe\Stripe;
use Stripe\Token;
use App\Models\Payment;
use Stripe\PaymentIntent;
use Illuminate\Support\Facades\Auth;

class PayController extends Controller
{
    // In your Laravel controller
    public function payment(Request $request) {
        try {
            Stripe::setApiKey(env('STRIPE_SECRET'));
            
            // Create token from card details
            $token = Token::create([
                'card' => [
                    'number' => $request->card_number,
                    'exp_month' => $request->exp_month,
                    'exp_year' => $request->exp_year,
                    'cvc' => $request->cvc,
                ],
            ]);
            
            // Create charge
            $charge = Charge::create([
                'amount' => $request->amount * 100, // Convert to cents
                'currency' => 'usd',
                'source' => $token->id,
                'description' => "Purchase of {$request->points} points",
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
            
            // Update user's points
            $user = auth()->user();
            $user->points += $request->points;
            $user->save();
            
            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }
}
