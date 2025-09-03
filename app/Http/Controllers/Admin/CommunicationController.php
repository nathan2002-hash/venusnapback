<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Jobs\Admin\ProcessCommunication;
use App\Models\Album;
use App\Models\Communication;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class CommunicationController extends Controller
{
    public function create()
    {
        $users = User::where('role', 'user')->get(); // Assuming you want to get only regular users
        $albums = Album::where('visibility', 'public')->get(); // Assuming you want to get only public albums

        return view('admin.communication.create', compact('users', 'albums'));
    }

    public function emailcreate()
    {
        $users = User::where('role', 'user')->get(); // Assuming you want to get only regular users
        $albums = Album::where('visibility', 'public')->get(); // Assuming you want to get only public albums

        return view('admin.communication.createemail', compact('users', 'albums'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'type' => 'required|in:sms,email',
            'recipient_type' => 'required|in:user,album',
            'user_id' => 'required_if:recipient_type,user|exists:users,id',
            'album_id' => 'required_if:recipient_type,album|exists:albums,id',
            'sms_provider' => 'required_if:type,sms|in:vonage,beem',
            'subject' => 'required|string|max:255',
            'body' => 'required|string',
            'attachment' => 'nullable|file|mimes:pdf,doc,docx,jpg,png|max:2048'
        ]);

        // Handle attachment if present
        $attachmentPath = null;
        if ($request->hasFile('attachment')) {
            $attachmentPath = $request->file('attachment')->store('communication_attachments');
        }

        // Create communication record
        $communication = Communication::create([
            'type' => $validated['type'],
            'subject' => $validated['subject'],
            'body' => $validated['body'],
            'recipient_type' => $validated['recipient_type'],
            'user_id' => $validated['recipient_type'] === 'user' ? $validated['user_id'] : null,
            'album_id' => $validated['recipient_type'] === 'album' ? $validated['album_id'] : null,
            'sms_provider' => $validated['type'] === 'sms' ? $validated['sms_provider'] : null,
            'attachment_path' => $attachmentPath,
            'sent_by' => Auth::id(),
            'status' => 'pending'
        ]);

        // Send the communication
        ProcessCommunication::dispatch($communication);
        return redirect()->back()
        ->with('success', 'Communication is being processed. You will be notified when completed.');
    }

    protected function sendSms(Communication $communication)
    {
        if ($communication->sms_provider === 'vonage') {
            $this->sendWithVonage($communication);
        } elseif ($communication->sms_provider === 'beem') {
            $this->sendWithBeem($communication);
        }
    }

    private function sendWithVonage(Communication $communication)
{
    $client = new \GuzzleHttp\Client();
    $from = env('VONAGE_SENDER_ID', 'Venusnap');

    if ($communication->recipient_type === 'user') {
        // Send to single selected user
        $user = User::findOrFail($communication->user_id);
        $this->sendSingleSmsViaVonage($client, $from, $user, $communication->body);
    } else {
        // Send to the user who owns the selected album
        $album = Album::with('user')->findOrFail($communication->album_id);
        $this->sendSingleSmsViaVonage($client, $from, $album->user, $communication->body);
    }
}

private function sendSingleSmsViaVonage($client, $from, $user, $message)
{
    if (empty($user->phone)) {
        throw new \Exception("User {$user->id} has no phone number");
    }

    $phone = $this->normalizePhoneNumber($user->phone);

    $response = $client->post('https://rest.nexmo.com/sms/json', [
        'form_params' => [
            'api_key' => env('VONAGE_API_KEY'),
            'api_secret' => env('VONAGE_API_SECRET'),
            'to' => $phone,
            'from' => $from,
            'text' => $message,
        ]
    ]);

    $responseData = json_decode($response->getBody(), true);

    if ($responseData['messages'][0]['status'] != '0') {
        throw new \Exception('Vonage error: ' . $responseData['messages'][0]['error-text']);
    }
}

private function sendWithBeem(Communication $communication)
{
    $client = new \GuzzleHttp\Client(['verify' => false]);
    $senderId = env('BEEM_SENDER_ID', 'Quixnes');

    if ($communication->recipient_type === 'user') {
        // Send to single selected user
        $user = User::findOrFail($communication->user_id);
        $this->sendSingleSmsViaBeem($client, $senderId, $user, $communication->body);
    } else {
        // Send to the user who owns the selected album
        $album = Album::with('user')->findOrFail($communication->album_id);
        $this->sendSingleSmsViaBeem($client, $senderId, $album->user, $communication->body);
    }
}

private function sendSingleSmsViaBeem($client, $senderId, $user, $message)
{
    if (empty($user->phone)) {
        throw new \Exception("User {$user->id} has no phone number");
    }

    $phone = $this->normalizePhoneNumber($user->phone);

    $postData = [
        'source_addr' => $senderId,
        'encoding' => 0,
        'schedule_time' => '',
        'message' => $message,
        'recipients' => [
            ['recipient_id' => '1', 'dest_addr' => $phone],
        ]
    ];

    $response = $client->post('https://apisms.beem.africa/v1/send', [
        'json' => $postData,
        'headers' => [
            'Authorization' => 'Basic ' . base64_encode(env('BEEM_API_KEY') . ':' . env('BEEM_API_SECRET')),
            'Content-Type' => 'application/json',
        ],
    ]);

    $responseData = json_decode($response->getBody(), true);

    if (isset($responseData['code']) && $responseData['code'] != 100) {
        throw new \Exception('Beem error: ' . ($responseData['message'] ?? 'Unknown error'));
    }
}

    private function normalizePhoneNumber($phone)
    {
        // Remove all non-digit characters
        $phone = preg_replace('/\D/', '', $phone);

        // If starts with +, remove it
        if (Str::startsWith($phone, '+')) {
            $phone = substr($phone, 1);
        }

        // If starts with 00, replace with +
        if (Str::startsWith($phone, '00')) {
            $phone = substr($phone, 2);
        }

        // If starts with 0, remove it (assuming international format)
        if (Str::startsWith($phone, '0')) {
            $phone = substr($phone, 1);
        }

        return $phone;
    }
}
