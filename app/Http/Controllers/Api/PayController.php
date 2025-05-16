<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\PaymentSession;
use App\Models\Payment;
use Illuminate\Support\Facades\Auth;
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

        // Create payment session
        $paymentsession = PaymentSession::create([
            'user_id' => $user->id,
            'ip_address' => $request->ip(),
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

        $payment = Payment::find($request->input('order_id'));

        if (!$payment) {
            return response()->json(['message' => 'Payment not found'], 404);
        }

        $paypalOrderId = $payment->payment_no;
        $client = $this->getPayPalClient();
        $paypalRequest = new OrdersCaptureRequest($paypalOrderId);

        try {
            $response = $client->execute($paypalRequest);

            if ($response->result->status === 'COMPLETED') {
                // Update payment status
                $payment->update([
                    'status' => 'completed',
                    'metadata->paypal_response' => json_encode($response->result),
                ]);

                // Add points to user
                $points = $payment->metadata['points'] ?? 0;
                if ($points > 0) {
                    $payment->user->increment('points', $points);
                }

                return response()->json(['status' => 'completed']);
            }

            return response()->json(['status' => 'failed'], 400);

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    private function getPayPalClient()
    {
        $clientId = config('services.paypal.client_id');
        $clientSecret = config('services.paypal.secret');
        $environment = config('services.paypal.env') === 'production'
            ? new ProductionEnvironment($clientId, $clientSecret)
            : new SandboxEnvironment($clientId, $clientSecret);

        return new PayPalHttpClient($environment);
    }
}
