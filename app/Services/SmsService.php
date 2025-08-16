<?php

namespace App\Services;

use App\Models\Country;
use App\Models\SmsProvider;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Exception;

class SmsService
{
    protected $message;
    protected $phone;
    protected $country;
    protected $provider;

    public function send($phone, $message, $countryId = null)
    {
        $this->phone = $phone;
        $this->message = $message;

        try {
            // Get country if provided
            if ($countryId) {
                $this->country = Country::find($countryId);
            }

            // Normalize phone number
            $normalizedPhone = $this->normalizePhoneNumber($this->phone, $this->country);
            if (!$normalizedPhone) {
                throw new Exception('Invalid phone number format');
            }

            // Get active SMS provider (you might have this in config or database)
            $this->provider = SmsProvider::where('is_active', true)->first();

            if (!$this->provider) {
                throw new Exception('No active SMS provider configured');
            }

            // Send via appropriate provider
            switch ($this->provider->name) {
                case 'Vonage':
                    $this->sendWithVonage($normalizedPhone);
                    break;
                case 'Beem':
                    $this->sendWithBeem($normalizedPhone, $this->provider);
                    break;
                default:
                    throw new Exception('Unsupported SMS provider');
            }

            Log::info("SMS sent to $normalizedPhone via {$this->provider->name}");
            return true;

        } catch (Exception $e) {
            Log::error("Failed to send SMS to $phone: " . $e->getMessage());
            throw $e; // Re-throw for controller to handle
        }
    }

    /**
     * Send SMS using Vonage/Nexmo
     */
    private function sendWithVonage($phone)
    {
        $client = new Client();

        $response = $client->post('https://rest.nexmo.com/sms/json', [
            'form_params' => [
                'api_key' => env('VONAGE_API_KEY', $this->provider->api_key),
                'api_secret' => env('VONAGE_API_SECRET', $this->provider->api_secret),
                'to' => $phone,
                'from' => env('VONAGE_SENDER_ID', 'Venusnap'),
                'text' => $this->message,
            ]
        ]);

        $responseData = json_decode($response->getBody(), true);

        if ($responseData['messages'][0]['status'] != '0') {
            throw new Exception('Vonage error: ' . $responseData['messages'][0]['error-text']);
        }
    }

    /**
     * Send SMS using Beem Africa
     */
    private function sendWithBeem($phone, $provider)
    {
        $client = new Client(['verify' => false]);

        $postData = [
            'source_addr' => $provider->sender_id ?? 'Venusnap',
            'encoding' => 0,
            'schedule_time' => '',
            'message' => $this->message,
            'recipients' => [
                ['recipient_id' => '1', 'dest_addr' => $phone],
            ]
        ];

        $response = $client->post('https://apisms.beem.africa/v1/send', [
            'json' => $postData,
            'headers' => [
                'Authorization' => 'Basic ' . base64_encode(
                    $provider->api_key . ':' . $provider->api_secret
                ),
                'Content-Type' => 'application/json',
            ],
        ]);

        $responseData = json_decode($response->getBody(), true);

        if (isset($responseData['code']) && $responseData['code'] != 100) {
            throw new Exception('Beem error: ' . ($responseData['message'] ?? 'Unknown error'));
        }
    }

    /**
     * Normalize phone number format
     */
    private function normalizePhoneNumber($phone, ?Country $country = null)
    {
        if (!$country) {
            // If no country specified, assume international format
            $phone = preg_replace('/\D/', '', $phone);
            return Str::startsWith($phone, '+') ? substr($phone, 1) : $phone;
        }

        $phone = preg_replace('/\D/', '', $phone); // Remove non-digits
        $code = $country->phone_code;
        $length = $country->phone_number_length;
        $localLength = $length - strlen($code);

        if (Str::startsWith($phone, '0') && strlen($phone) == $localLength + 1) {
            return $code . substr($phone, 1);
        }

        if (Str::startsWith($phone, $code) && strlen($phone) == $length) {
            return $phone;
        }

        if (strlen($phone) == $localLength) {
            return $code . $phone;
        }

        // If number already has + or 00 prefix
        if (Str::startsWith($phone, '+')) {
            return substr($phone, 1);
        }

        if (Str::startsWith($phone, '00')) {
            return substr($phone, 2);
        }

        // If all else fails, return as is (might not work for all providers)
        return $phone;
    }
}
