<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use GuzzleHttp\Client;

class SmsHorizonNotification extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function send($phone, $message)
    {
        $provider = $this->getSmsProvider(); // Assume this fetches API keys from DB or config

        $client = new Client(['verify' => false]);

        $postData = [
            'source_addr' => $provider->sender_id ?? 'Quixnes',
            'encoding' => 0,
            'schedule_time' => '',
            'message' => $message,
            'recipients' => [
                ['recipient_id' => '1', 'dest_addr' => $phone],
            ]
        ];

        $client->post('https://apisms.beem.africa/v1/send', [
            'json' => $postData,
            'headers' => [
                'Authorization' => 'Basic ' . base64_encode("{$provider->api_key}:{$provider->api_secret}"),
                'Content-Type' => 'application/json',
            ],
        ]);
    }

    private function getSmsProvider()
    {
        // You can fetch this from DB, env, or config
        return (object) [
            'sender_id' => env('BEEM_SENDER_ID', 'Quixnes'),
            'api_key' => env('BEEM_API_KEY'),
            'api_secret' => env('BEEM_SECRET'),
        ];
    }
}
