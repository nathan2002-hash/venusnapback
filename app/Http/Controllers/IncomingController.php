<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\IncomingSms;
use App\Models\Conversation;

class IncomingController extends Controller
{
     public function recesive(Request $request)
    {
        // Store everything directly
        IncomingSms::create([
            'from' => $request->input('msisdn'),
            'to' => $request->input('to'),
            'text' => $request->input('text'),
            'payload' => json_encode($request->all()), // store full raw payload if needed
        ]);

        return response()->json(['status' => 'success']);
    }

    public function receive(Request $request)
    {
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
            'received_at' => $payload['timestamp'] ?? now(),
        ]);

        return response()->json(['status' => 'success']);
    }

}
