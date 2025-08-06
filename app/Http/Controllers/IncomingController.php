<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\IncomingSms;

class IncomingController extends Controller
{
    public function receive(Request $request)
    {
        // Vonage's known User-Agent prefix
        $expectedUserAgent = 'Nexmo/MessagingHUB';

        // Extract User-Agent header
        $userAgent = $request->header('user-agent', '');

        // Optionally get client IP behind Cloudflare
        $clientIp = $request->header('cf-connecting-ip') ?? $request->ip();

        // Example whitelist of Vonage IPs (expand as needed)
        $vonageAllowedIps = [
            '216.147.7.132',
            // Add other IPs here
        ];

        // Check IP whitelist
        $ipAllowed = in_array($clientIp, $vonageAllowedIps);

        // Check User-Agent contains expected prefix
        $userAgentAllowed = str_starts_with($userAgent, $expectedUserAgent);

        if (! $ipAllowed && ! $userAgentAllowed) {
            \Log::warning('Unauthorized SMS webhook access.', [
                'ip' => $clientIp,
                'user_agent' => $userAgent,
            ]);
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        \Log::info('Incoming SMS:', $request->all());

        IncomingSms::create([
            'from' => $request->input('msisdn'),
            'to' => $request->input('to'),
            'text' => $request->input('text'),
            'message_id' => $request->input('messageId'),
            'received_at' => $request->input('message-timestamp'),
        ]);

        return response()->json(['status' => 'SMS received']);
    }


}
