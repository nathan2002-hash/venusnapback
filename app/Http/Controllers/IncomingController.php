<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\IncomingSms;

class IncomingController extends Controller
{
    public function receive(Request $request)
    {
        // Check the secret key in the query string
        $secretKey = 'vIARpkmuzpj6/BNVf1HGl6KFRxBznHVF/LF9NTKC/USuNsRnf+31IGPfaA0beDs8';  // your hardcoded secret key

        if ($request->query('key') !== $secretKey) {
            \Log::warning('Unauthorized SMS webhook access attempt.', ['ip' => $request->ip()]);
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        // Log for debugging (optional)
        \Log::info('Incoming SMS:', $request->all());

        // Save the incoming SMS
        IncomingSms::create([
            'from' => $request->input('msisdn'),
            'to' => $request->input('to'),
            'text' => $request->input('text'),
            'message_id' => $request->input('messageId'),
            'received_at' => $request->input('message-timestamp'),
            'ip_address' => $request->ip(),
            'user_agent' => $request->header('User-Agent'),
        ]);

        return response()->json(['status' => 'SMS received']);
    }

}
