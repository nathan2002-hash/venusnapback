<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Conversation;
use Carbon\Carbon;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class MessageController extends Controller
{
    public function receive(Request $request)
    {
        $authHeader = $request->header('authorization');
        $secret = env('VONAGE_SIGNATURE_SECRET'); // store in .env

        if (!$authHeader || !str_starts_with($authHeader, 'Bearer ')) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $jwt = substr($authHeader, 7); // remove "Bearer "

        try {
            $decoded = JWT::decode($jwt, new Key($secret, 'HS256'));
            // ✅ Valid webhook
        } catch (\Exception $e) {
            \Log::warning('Invalid Vonage signature', ['error' => $e->getMessage()]);
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $payload = $request->all();

        $from = $payload['from']['number'] ?? null;
        $to = $payload['to']['number'] ?? null;
        $type = $payload['to']['type'] ?? 'sms';

        // Find or create conversation
        $conversation = Conversation::firstOrCreate(
            ['user_number' => $from, 'our_number' => $to, 'type' => $type]
        );

        // Store message
        $conversation->messages()->create([
            'direction'   => 'inbound',
            'user_id'   => '1',
            'message_id'  => $payload['message_uuid'] ?? null,
            'text'        => $payload['message']['content']['text'] ?? null,
            'payload'     => json_encode($payload),
            'received_at' => isset($payload['timestamp'])
                ? Carbon::parse($payload['timestamp'])->format('Y-m-d H:i:s')
                : now(),
        ]);

        return response()->json(['status' => 'success']);
    }
}
