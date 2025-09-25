<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Conversation;
use App\Events\MessageSent;
use Carbon\Carbon;
use App\Models\Message;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Auth;

class MessageController extends Controller
{
    private $vonageApiKey;
    private $vonageApiSecret;
    private $vonageWhatsAppNumber;

    public function __construct()
    {
        $this->vonageApiKey = '57165f13';
        $this->vonageApiSecret = 'p0Q9ap59IqgH0iWt';
        $this->vonageWhatsAppNumber = '14157386102';
    }

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

    public function index()
    {
        $conversations = Conversation::with(['latestMessage'])
            ->orderBy('updated_at', 'desc')
            ->get();

        return view('admin.message.index', compact('conversations'));
    }

    public function getMessages(Request $request)
    {
        $conversationId = $request->get('conversation_id');
        $messages = Message::where('conversation_id', $conversationId)
            ->orderBy('created_at', 'asc')
            ->get();

        $html = view('admin.message._messages', compact('messages'))->render();

        return response()->json(['html' => $html]);
    }

     public function sendMessage(Request $request)
    {
        $request->validate([
            'conversation_id' => 'required|exists:conversations,id',
            'message' => 'required|string',
            'message_type' => 'required|in:sms,whatsapp'
        ]);

        $conversation = Conversation::find($request->conversation_id);

        try {
            // Send message via Vonage
            if ($request->message_type === 'whatsapp') {
                $response = $this->sendWhatsAppMessage($conversation, $request->message);
            } else {
                $response = $this->sendSMSMessage($conversation, $request->message);
            }

            // Store message in database
            $message = $conversation->messages()->create([
                'direction' => 'outbound',
                'user_id' => Auth::user()->id,
                'message_id' => $response['message_uuid'] ?? ($response['messages'][0]['message-id'] ?? null),
                'text' => $request->message,
                'payload' => json_encode($response),
                'received_at' => now(),
            ]);

            // Update conversation timestamp
            $conversation->touch();

            // Broadcast the message to Pusher
            broadcast(new MessageSent($message))->toOthers();

            return response()->json(['success' => true, 'message' => $message]);

        } catch (\Exception $e) {
            Log::error('Error sending message: ' . $e->getMessage());
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    // Add this method to handle incoming webhook messages
    public function receiveWebhook(Request $request)
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

        // Handle different webhook formats (SMS vs WhatsApp)
        if (isset($payload['msisdn'])) {
            // SMS webhook format
            $from = $payload['msisdn'] ?? null;
            $to = $payload['to'] ?? null;
            $type = 'sms';
            $messageUuid = $payload['messageId'] ?? null;
            $text = $payload['text'] ?? null;
            $timestamp = $payload['message-timestamp'] ?? null;
        } else {
            // WhatsApp webhook format
            $from = $payload['from']['number'] ?? null;
            $to = $payload['to']['number'] ?? null;
            $type = $payload['to']['type'] ?? 'whatsapp';
            $messageUuid = $payload['message_uuid'] ?? null;
            $text = $payload['message']['content']['text'] ?? null;
            $timestamp = $payload['timestamp'] ?? null;
        }

        if (!$from || !$to) {
            return response()->json(['error' => 'Invalid payload'], 400);
        }

        // Find or create conversation
        $conversation = Conversation::firstOrCreate(
            ['user_number' => $from, 'our_number' => $to, 'type' => $type]
        );

        // Store message
        $message = $conversation->messages()->create([
            'direction'   => 'inbound',
            'user_id'     => '1', // Map this to an actual user if needed
            'message_id'  => $messageUuid,
            'text'        => $text,
            'payload'     => json_encode($payload),
            'received_at' => $timestamp ? \Carbon\Carbon::parse($timestamp) : now(),
        ]);

        // Broadcast the incoming message via Pusher
        broadcast(new MessageSent($message));

        return response()->json(['status' => 'success']);
    }

    // Your existing sendWhatsAppMessage and sendSMSMessage methods...
    private function sendWhatsAppMessage(Conversation $conversation, string $message)
    {
        $url = 'https://api.nexmo.com/v1/messages';

        $payload = [
            'from' => $conversation->our_number,
            'to' => $conversation->user_number,
            'message_type' => 'text',
            'text' => $message,
            'channel' => 'whatsapp'
        ];

        $response = Http::withBasicAuth($this->vonageApiKey, $this->vonageApiSecret)
            ->timeout(30)
            ->withHeaders([
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ])
            ->post($url, $payload);

        if (!$response->successful()) {
            throw new \Exception('WhatsApp API error: ' . $response->status() . ' - ' . $response->body());
        }

        return $response->json();
    }

    private function sendSMSMessage(Conversation $conversation, string $message)
    {
        $url = 'https://rest.nexmo.com/sms/json';

        $payload = [
            'from' => $conversation->our_number,
            'to' => $conversation->user_number,
            'text' => substr($message, 0, 160),
            'api_key' => $this->vonageApiKey,
            'api_secret' => $this->vonageApiSecret,
        ];

        $response = Http::asForm()
            ->timeout(30)
            ->post($url, $payload);

        if (!$response->successful()) {
            throw new \Exception('SMS API error: ' . $response->status() . ' - ' . $response->body());
        }

        $responseData = $response->json();

        if (isset($responseData['messages'][0]['status']) && $responseData['messages'][0]['status'] != '0') {
            throw new \Exception('SMS sending failed: ' . ($responseData['messages'][0]['error-text'] ?? 'Unknown error'));
        }

        return $responseData;
    }

}
