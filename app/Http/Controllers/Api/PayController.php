<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\PaymentSession;
use App\Models\Payment;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use PayPalCheckoutSdk\Core\PayPalHttpClient;
use PayPalCheckoutSdk\Core\SandboxEnvironment;
use PayPalCheckoutSdk\Core\ProductionEnvironment;
use PayPalCheckoutSdk\Orders\OrdersCreateRequest;
use PayPalCheckoutSdk\Orders\OrdersCaptureRequest;


class PayController extends Controller
{
     public function payment(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric',
            'description' => 'required|string',
            'purpose' => 'required|string',
            'points' => 'required|integer',
        ]);

        $user = Auth::user();
        $realIp = $request->header('cf-connecting-ip') ?? $request->ip();
        $ipaddress = $realIp;

        // Create payment session
        $paymentsession = PaymentSession::create([
            'user_id' => $user->id,
            'ip_address' => $ipaddress,
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
                "cancel_url" => url('/paypal/cancel'),
                "return_url" => url('/paypal/return'),
                "brand_name" => config('app.name'),
                "user_action" => "PAY_NOW",
                "shipping_preference" => "NO_SHIPPING"
            ]
        ];

        try {
            $response = $client->execute($paypalRequest);

            // Find the approval URL
            $approvalUrl = collect($response->result->links)->firstWhere(
                fn($link) => $link->rel === 'approve'
            )->href;

            // Save the pending payment
            $payment = Payment::create([
                'user_id' => $user->id,
                'amount' => $request->input('amount'),
                'payment_method' => 'PayPal',
                'currency' => 'USD',
                'processor' => 'PayPal',
                'payment_no' => $response->result->id,
                'status' => 'pending',
                'payment_session_id' => $paymentsession->id,
                'purpose' => $request->input('purpose'),
                'description' => $request->input('description'),
                'metadata' => [
                    'points' => $request->input('points'),
                ],
            ]);

            return response()->json([
                'approval_url' => $approvalUrl,
                'order_id' => $payment->id,
            ]);

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    public function capture(Request $request)
    {
        $request->validate([
            'order_id' => 'required|integer',
        ]);

        $payment = Payment::find($request->order_id);

        if (!$payment) {
            return response()->json(['message' => 'Payment not found'], 404);
        }

        // Check if already completed
        if ($payment->status === 'completed') {
            return response()->json([
                'status' => 'already_completed',
                'message' => 'Payment was already processed'
            ]);
        }

        try {
            $client = $this->getPayPalClient();
            $paypalRequest = new OrdersCaptureRequest($payment->payment_no);
            $response = $client->execute($paypalRequest);

            if ($response->result->status === 'COMPLETED') {
                // Successful payment processing...
                $metadata = $payment->metadata;
                $points = $metadata['points'] ?? 0;

                $payment->update([
                    'status' => 'completed',
                    'metadata->paypal_response' => $response->result,
                ]);

                if ($points > 0 && $payment->user) {
                    $payment->user->increment('points', $points);
                }

                return response()->json([
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
                'status' => 'failed',
                'reason' => 'Payment not completed at PayPal'
            ], 400);

        } catch (\Exception $e) {
            // Mark as failed on any exception
            $payment->update([
                'status' => 'failed',
                'metadata->failure_reason' => $e->getMessage(),
            ]);

            return response()->json([
                'status' => 'failed',
                'error' => $e->getMessage()
            ], 400);
        }
    }

    private function getPayPalClient()
    {
        $environment = env('PAYPAL_ENV', 'sandbox');

        if ($environment === 'production') {
            $clientId = env('LIVE_PAYPAL_CLIENT_ID');
            $clientSecret = env('LIVE_PAYPAL_SECRET');
            $envInstance = new ProductionEnvironment($clientId, $clientSecret);
        } else {
            $clientId = env('SANDBOX_PAYPAL_CLIENT_ID');
            $clientSecret = env('SANDBOX_PAYPAL_SECRET');
            $envInstance = new SandboxEnvironment($clientId, $clientSecret);
        }

        // Validate credentials
        if (empty($clientId) || empty($clientSecret)) {
            Log::error('PayPal credentials missing for ' . $environment);
            throw new \RuntimeException('PayPal credentials not configured properly.');
        }

        return new PayPalHttpClient($envInstance);
    }

    // private function getPayPalClient()
    // {
    //     $clientId = "Adb3ojL6yMqZLSxj8N7ajNCw793eurD7IbX-r8LrDCLmKsmJCJiAxEw7JpKxi6YbXHCbygFBmXeoqhkG";
    //     $clientSecret = "ECxrbuAcYFpqmkRLvj_sCMnyKBnxQzuITku9q91GQ2OABtCByFBKp8sXziJQyZmYWcnjH32RVLGw8Tdn";
    //     return new PayPalHttpClient(
    //         new SandboxEnvironment($clientId, $clientSecret)
    //     );
    // }
}
