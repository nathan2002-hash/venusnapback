<?php

namespace App\Http\Controllers\Api;

use Stripe\Stripe;
use App\Models\Payment;
use Stripe\PaymentIntent;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\PaymentSession;
use App\Models\PointRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use PayPalCheckoutSdk\Core\PayPalHttpClient;
use PayPalCheckoutSdk\Core\SandboxEnvironment;
use PayPalCheckoutSdk\Core\ProductionEnvironment;
use PayPalCheckoutSdk\Orders\OrdersCreateRequest;
use PayPalCheckoutSdk\Orders\OrdersCaptureRequest;
use PayPalCheckoutSdk\Orders\OrdersGetRequest;
use App\Mail\UserPointsRequestConfirmation;
use App\Mail\AdminPointsRequestNotification;
use Illuminate\Support\Facades\Mail;

class PaymentController extends Controller
{

    public function createPaymentIntent(Request $request)
    {
        Stripe::setApiKey(env('STRIPE_SECRET'));

        // Create Stripe Payment Intent
        $paymentIntent = PaymentIntent::create([
            'amount' => $request->amount * 100, // Convert to cents
            'currency' => 'usd',
            'payment_method_types' => ['card'],
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
            'amount'         => $request->amount,
            'payment_method' => 'Stripe',
            'currency'       => 'usd',
            'processor'      => 'Stripe',
            'payment_no'     => $paymentIntent->id, // Store Stripe Payment ID
            'status'         => 'pending',
            'purpose'        => $request->purpose,
            'payment_session_id' => $paymentsession->id,
            'description'    => $request->description,
        ]);

        return response()->json([
            'clientSecret' => $paymentIntent->client_secret,
            'payment_id'   => $payment->id,
        ]);
    }

    public function confirmPayment(Request $request)
    {
        // Get Payment Intent ID
        $paymentIntentId = $request->payment_intent_id;

        // Retrieve Payment Intent from Stripe
        Stripe::setApiKey(env('STRIPE_SECRET'));
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
            ]);

            return response()->json(['message' => 'Payment failed'], 400);
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

    public function getConfig()
    {
        return response()->json([
            'success' => true,
            'config' => [
                'notice_message' => 'We are currently not accepting direct payments for points. Please fill out the form below to request points.',
                'points_options' => [1000, 2500, 5000, 10000, 25000, 50000, 100000],
                'min_points' => 1000,
                'max_points' => 100000,
                'show_form' => true, // Set this to false to hide the form
            ],
        ]);
    }

    public function requestpoints(Request $request)
    {
        $request->validate([
            'full_name' => 'required|string|max:255',
            'business_name' => 'nullable|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'required|string|max:20',
            'points' => 'required|integer|min:1000|max:100000',
            'purpose' => 'required|string|max:1000',
        ]);

        $userAgent = $request->header('User-Agent');
        $deviceinfo = $request->header('Device-Info');
        $realIp = $request->header('cf-connecting-ip') ?? $request->ip();

        try {
            // Create the points request
            $pointsRequest = PointRequest::create([
                'user_id' => Auth::id(),
                'full_name' => $request->full_name,
                'business_name' => $request->business_name,
                'email' => $request->email,
                'phone' => $request->phone,
                'points' => $request->points,
                'purpose' => $request->purpose,
                'status' => 'pending',
                'user_agent' => $userAgent,
                'device_info' => $deviceinfo,
                'ip_address' => $realIp,
            ]);

            $this->queueEmailNotifications($pointsRequest);


            return response()->json([
                'success' => true,
                'message' => 'Points request submitted successfully',
                'request_id' => $pointsRequest->id,
            ]);

        } catch (\Exception $e) {
            Log::error('Points request failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to submit points request',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    protected function queueEmailNotifications(PointRequest $request)
    {
        try {
            // Send to admin
            Mail::mailer('smtp')->to('quixnes@proton.me')->queue(
                        (new AdminPointsRequestNotification($request))
                            ->from('support@venusnap.com', 'Venusnap Support Team')
            );

            Mail::mailer('smtp')->to($request->email)->queue(
                (new UserPointsRequestConfirmation($request))
                    ->from('support@venusnap.com', 'Venusnap Support Team')
            );

        } catch (\Exception $e) {
            Log::error('Email queue failed: ' . $e->getMessage());
            // Optionally implement a retry mechanism here
        }
    }
}
