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

    public function createPayment(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'description' => 'required|string|max:255',
            'purpose' => 'required|string|max:255',
            'points' => 'required|integer|min:1',
        ]);

        $user = Auth::user();
        $realIp = $request->header('cf-connecting-ip') ?? $request->ip();

        // Create payment session
        $paymentSession = PaymentSession::create([
            'user_id' => $user->id,
            'ip_address' => $realIp,
            'device_info' => $request->header('Device-Info'),
            'user_agent' => $request->header('User-Agent'),
        ]);

        // Create the PayPal order
        $client = $this->getPayPalClient();
        $paypalRequest = new OrdersCreateRequest();
        $paypalRequest->prefer('return=representation');

        $paypalRequest->body = [
            "intent" => "CAPTURE",
            "purchase_units" => [[
                "reference_id" => "points_purchase_" . uniqid(),
                "description" => $request->input('description'),
                "amount" => [
                    "value" => $request->input('amount'),
                    "currency_code" => "USD",
                    "breakdown" => [
                        "item_total" => [
                            "currency_code" => "USD",
                            "value" => $request->input('amount')
                        ]
                    ]
                ]
            ]],
            "application_context" => [
                "cancel_url" => config('app.url') . '/api/paypal/cancel',
                "return_url" => config('app.url') . '/api/paypal/return',
                "brand_name" => config('app.name'),
                "user_action" => "PAY_NOW",
                "shipping_preference" => "NO_SHIPPING"
            ]
        ];

        try {
            $response = $client->execute($paypalRequest);

            // Find the approval URL
            $approvalUrl = collect($response->result->links)
                ->firstWhere('rel', 'approve')->href;

            // Save the pending payment
            $payment = Payment::create([
                'user_id' => $user->id,
                'amount' => $request->input('amount'),
                'payment_method' => 'PayPal',
                'currency' => 'USD',
                'processor' => 'PayPal',
                'payment_no' => $response->result->id,
                'status' => 'pending',
                'payment_session_id' => $paymentSession->id,
                'purpose' => $request->input('purpose'),
                'description' => $request->input('description'),
                'metadata' => [
                    'points' => $request->input('points'),
                    'approval_url' => $approvalUrl,
                ],
            ]);

            return response()->json([
                'success' => true,
                'approval_url' => $approvalUrl,
                'order_id' => $payment->id,
                'paypal_order_id' => $response->result->id,
            ]);

        } catch (\Exception $e) {
            Log::error('PayPal create error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'Could not create PayPal order',
                'message' => $e->getMessage()
            ], 400);
        }
    }

    public function handleReturn(Request $request)
    {
        $request->validate([
            'token' => 'required|string' // PayPal order ID
        ]);

        $payment = Payment::where('payment_no', $request->token)
            ->whereIn('status', ['pending', 'approved'])
            ->first();

        if (!$payment) {
            return redirect(config('app.frontend_url') . '/payment/error?reason=payment_not_found');
        }

        // Verify order status with PayPal
        $client = $this->getPayPalClient();
        $orderRequest = new OrdersGetRequest($payment->payment_no);

        try {
            $response = $client->execute($orderRequest);

            if (!in_array($response->result->status, ['APPROVED', 'COMPLETED'])) {
                return redirect(config('app.frontend_url') . '/payment/error?reason=not_approved');
            }

            // Update payment status
            $payment->update([
                'status' => 'approved',
                'metadata->paypal_response' => $response->result
            ]);

            return redirect(config('app.frontend_url') . '/payment/success?order_id='.$payment->id);

        } catch (\Exception $e) {
            Log::error('PayPal return error: ' . $e->getMessage());
            return redirect(config('app.frontend_url') . '/payment/error?reason=paypal_error');
        }
    }

    public function capturePayment(Request $request)
    {
        $request->validate([
            'order_id' => 'required|integer',
        ]);

        $payment = Payment::where('status', 'approved')
            ->find($request->order_id);

        if (!$payment) {
            return response()->json([
                'success' => false,
                'error' => 'Payment not found or not in approved state'
            ], 404);
        }

        try {
            $client = $this->getPayPalClient();
            $paypalRequest = new OrdersCaptureRequest($payment->payment_no);
            $response = $client->execute($paypalRequest);

            if ($response->result->status === 'COMPLETED') {
                // Successful payment processing
                $points = $payment->metadata['points'] ?? 0;

                $payment->update([
                    'status' => 'completed',
                    'metadata->capture_response' => $response->result,
                ]);

                if ($points > 0 && $payment->user) {
                    $payment->user->increment('points', $points);
                }

                return response()->json([
                    'success' => true,
                    'status' => 'completed',
                    'points_added' => $points
                ]);
            }

            // Payment failed at PayPal side
            $payment->update([
                'status' => 'failed',
                'metadata->failure_reason' => 'PayPal status: ' . $response->result->status,
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Payment not completed at PayPal',
                'status' => $response->result->status
            ], 400);

        } catch (\Exception $e) {
            Log::error('PayPal capture error: ' . $e->getMessage());

            $payment->update([
                'status' => 'failed',
                'metadata->failure_reason' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Payment capture failed',
                'message' => $e->getMessage()
            ], 400);
        }
    }

    public function handleCancel(Request $request)
    {
        $request->validate([
            'token' => 'required|string' // PayPal order ID
        ]);

        $payment = Payment::where('payment_no', $request->token)
            ->where('status', 'pending')
            ->first();

        if ($payment) {
            $payment->update([
                'status' => 'cancelled',
                'metadata->cancelled_at' => now(),
            ]);
        }

        return redirect(config('app.frontend_url') . '/payment/cancelled');
    }

    private function getPayPalClient()
    {
        $environment = config('services.paypal.env', 'sandbox');

        if ($environment === 'production') {
            $clientId = config('services.paypal.live_client_id');
            $clientSecret = config('services.paypal.live_secret');
            $envInstance = new ProductionEnvironment($clientId, $clientSecret);
        } else {
            $clientId = config('services.paypal.sandbox_client_id');
            $clientSecret = config('services.paypal.sandbox_secret');
            $envInstance = new SandboxEnvironment($clientId, $clientSecret);
        }

        if (empty($clientId) || empty($clientSecret)) {
            Log::error('PayPal credentials missing for ' . $environment);
            throw new \RuntimeException('PayPal credentials not configured properly.');
        }

        return new PayPalHttpClient($envInstance);
    }

}
