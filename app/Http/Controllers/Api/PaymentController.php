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
use App\Mail\PaymentReceipt;
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
            'metadata' => [
                'points' => $request->points,
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
        $request->validate([
            'payment_intent_id' => 'required|string',
        ]);

        // Retrieve the payment record first
        $payment = Payment::where('payment_no', $request->payment_intent_id)->first();

        if (!$payment) {
            return response()->json(['message' => 'Payment not found'], 404);
        }

        // Check if already completed
        if ($payment->status === 'completed') {
            return response()->json([
                'status' => 'already_completed',
                'message' => 'Payment was already processed',
                'points_added' => $payment->metadata['points'] ?? 0,
            ]);
        }

        Stripe::setApiKey(env('STRIPE_SECRET'));
        $paymentIntent = PaymentIntent::retrieve($request->payment_intent_id);

        if ($paymentIntent->status == 'succeeded') {
            $metadata = $payment->metadata;
            $points = $metadata['points'] ?? 0;

            // Update payment status
            $payment->update([
                'status' => 'completed',
                'metadata->stripe_response' => $paymentIntent->toArray(),
            ]);

            // Add points to user
            if ($points > 0 && $payment->user) {
                $payment->user->increment('points', $points);
            }

             $receipt = $this->generateReceipt($payment);
             dispatch(new \App\Jobs\SendPaymentReceipt($payment->user->email, $receipt['html']));

            return response()->json([
                'status' => 'completed',
                'points_added' => $points,
                'receipt' => $this->generateReceipt($payment),
            ]);
        } else {
            // Update DB: Payment failed
            $payment->update([
                'status' => 'failed',
                'metadata->failure_reason' => 'Stripe status: ' . $paymentIntent->status,
            ]);

            return response()->json([
                'status' => 'failed',
                'message' => 'Payment not completed at Stripe',
            ], 400);
        }
    }

    public function confirmgPayment(Request $request)
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

    protected function generateReceipt(Payment $payment)
    {
        // Markdown version (for APIs/logs)
        $markdownReceipt = "## Payment Receipt\n\n";
        $markdownReceipt .= "**Transaction ID:** {$payment->payment_no}\n";
        $markdownReceipt .= "**Date:** " . $payment->created_at->format('F j, Y, g:i a T') . "\n";
        $markdownReceipt .= "**Status:** " . ucfirst($payment->status) . "\n";
        $markdownReceipt .= "**Amount:** $" . number_format($payment->amount, 2) . " {$payment->currency}\n";
        $markdownReceipt .= "**Payment Method:** " . ucfirst($payment->payment_method) . "\n";
        $markdownReceipt .= "**Purpose:** {$payment->purpose}\n";
        $markdownReceipt .= "**Description:** {$payment->description}\n";

        if (isset($payment->metadata['points'])) {
            $markdownReceipt .= "**Points Added:** {$payment->metadata['points']}\n";
        }

        $markdownReceipt .= "\nThank you for your purchase!";

        // HTML version (for email)
        $htmlReceipt = '
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="utf-8">
            <title>Payment Receipt</title>
            <style>
                body { font-family: "Helvetica Neue", Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { text-align: center; margin-bottom: 30px; }
                .logo { max-width: 150px; }
                .receipt-container { background: #f9f9f9; border-radius: 8px; padding: 25px; margin-bottom: 20px; }
                .receipt-title { font-size: 24px; font-weight: 600; margin-bottom: 20px; color: #32325d; }
                .receipt-details { margin-bottom: 25px; }
                .detail-row { display: flex; margin-bottom: 8px; }
                .detail-label { font-weight: 600; width: 150px; }
                .amount-row { background: #f6f9fc; padding: 15px; border-radius: 6px; margin: 20px 0; }
                .amount { font-size: 28px; font-weight: 600; color: #32325d; }
                .footer { text-align: center; margin-top: 30px; font-size: 14px; color: #8898aa; }
            </style>
        </head>
        <body>
            <div class="header">
                <img src="'.url('images/logo.png').'" alt="Company Logo" class="logo">
            </div>

            <div class="receipt-container">
                <div class="receipt-title">Payment Receipt</div>

                <div class="amount-row">
                    <div>Amount Paid</div>
                    <div class="amount">$'.number_format($payment->amount, 2).'</div>
                </div>

                <div class="receipt-details">
                    <div class="detail-row">
                        <div class="detail-label">Transaction ID:</div>
                        <div>'.$payment->payment_no.'</div>
                    </div>
                    <div class="detail-row">
                        <div class="detail-label">Date:</div>
                        <div>'.$payment->created_at->format('F j, Y, g:i a T').'</div>
                    </div>
                    <div class="detail-row">
                        <div class="detail-label">Status:</div>
                        <div>'.ucfirst($payment->status).'</div>
                    </div>
                    <div class="detail-row">
                        <div class="detail-label">Payment Method:</div>
                        <div>'.ucfirst($payment->payment_method).'</div>
                    </div>
                    <div class="detail-row">
                        <div class="detail-label">Purpose:</div>
                        <div>'.$payment->purpose.'</div>
                    </div>';

        if (isset($payment->metadata['points'])) {
            $htmlReceipt .= '
                    <div class="detail-row">
                        <div class="detail-label">Points Added:</div>
                        <div>'.$payment->metadata['points'].'</div>
                    </div>';
        }

        $htmlReceipt .= '
                </div>

                <div style="margin-top: 30px; padding-top: 20px; border-top: 1px solid #eee;">
                    <div style="font-weight: 600; margin-bottom: 10px;">Description:</div>
                    <div>'.$payment->description.'</div>
                </div>
            </div>

            <div class="footer">
                <p>Thank you for your purchase!</p>
                <p>If you have any questions, please contact our support team.</p>
                <p>© '.date('Y').' '.config('app.name').'. All rights reserved.</p>
            </div>
        </body>
        </html>';

        return [
            'markdown' => $markdownReceipt,
            'html' => $htmlReceipt
        ];
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
