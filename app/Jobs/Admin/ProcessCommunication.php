<?php

namespace App\Jobs\Admin;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use App\Models\Communication;
use App\Models\User;
use App\Models\Album;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use GuzzleHttp\Client;

class ProcessCommunication implements ShouldQueue
{
    use Queueable;
    public $communication;


    /**
     * Create a new job instance.
     */
    public function __construct(Communication $communication)
    {
        $this->communication = $communication;
    }

    /**
     * Execute the job.
     */
     public function handle()
    {
        try {
            if ($this->communication->type === 'email') {
                $this->sendEmail();
            } else {
                $this->sendSms();
            }

            $this->communication->update(['status' => 'sent']);
        } catch (\Exception $e) {
            $this->communication->update([
                'status' => 'failed',
                'failure_reason' => $e->getMessage()
            ]);

            Log::error("Failed to process communication {$this->communication->id}: " . $e->getMessage());
        }
    }

    protected function sendSms()
    {
        if ($this->communication->sms_provider === 'vonage') {
            $this->sendWithVonage();
        } elseif ($this->communication->sms_provider === 'beem') {
            $this->sendWithBeem();
        }
    }

    private function sendWithVonage()
    {
        $client = new Client();
        $from = env('VONAGE_SENDER_ID', 'Venusnap');

        if ($this->communication->recipient_type === 'user') {
            $user = User::findOrFail($this->communication->user_id);
            $this->sendSingleSmsViaVonage($client, $from, $user, $this->communication->body);
        } else {
            $album = Album::with('user')->findOrFail($this->communication->album_id);
            $this->sendSingleSmsViaVonage($client, $from, $album->user, $this->communication->body);
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

    private function sendWithBeem()
    {
        $client = new Client(['verify' => false]);
        $senderId = env('BEEM_SENDER_ID', 'Venusnap');

        if ($this->communication->recipient_type === 'user') {
            $user = User::findOrFail($this->communication->user_id);
            $this->sendSingleSmsViaBeem($client, $senderId, $user, $this->communication->body);
        } else {
            $album = Album::with('user')->findOrFail($this->communication->album_id);
            $this->sendSingleSmsViaBeem($client, $senderId, $album->user, $this->communication->body);
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
        $phone = preg_replace('/\D/', '', $phone);

        if (Str::startsWith($phone, '+')) {
            $phone = substr($phone, 1);
        }

        if (Str::startsWith($phone, '00')) {
            $phone = substr($phone, 2);
        }

        if (Str::startsWith($phone, '0')) {
            $phone = substr($phone, 1);
        }

        return $phone;
    }

    protected function sendEmail()
    {
        // Implement your email sending logic here
        // Similar to the SMS methods but for email
    }
}
