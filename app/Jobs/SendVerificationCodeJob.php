<?php

namespace App\Jobs;

use App\Mail\VerificationEmail;
use GuzzleHttp\Client;
use App\Models\Country;
use App\Models\SmsProvider;
use Illuminate\Support\Str;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Mail;

class SendVerificationCodeJob implements ShouldQueue
{
    use Queueable;

    protected $user;
    protected $code;
    protected $type; // 'phone' or 'email'
    protected $message;

    /**
     * Create a new job instance.
     */
    public function __construct($user, $code, $type, $message = null)
    {
        $this->user = $user;
        $this->code = $code;
        $this->type = $type;
        $this->message = $message ?? "Your Venusnap verification code is: {$code}";
    }

    public function handle()
    {
        if ($this->type === 'phone') {
            $this->sendSms();
        } elseif ($this->type === 'email') {
            $this->sendEmail();
        }
    }

    /**
     * Execute the job.
     */
   protected function sendSms()
    {
        try {
            $country = Country::where('name', $this->user->country)->first();

            if (!$country) {
                \Log::error("Country not found for user {$this->user->id}");
                return;
            }

            $code = $country->phone_code; // e.g. 260
            $partial = preg_replace('/\D/', '', $this->user->phone); // remove non-digits

            // Remove leading 0 if present
            if (Str::startsWith($partial, '0')) {
                $partial = substr($partial, 1);
            }

            $formattedPhone = $code . $partial;

            if (!preg_match('/^\d{10,15}$/', $formattedPhone)) {
                \Log::error("Invalid formatted phone: {$formattedPhone} for user {$this->user->id}");
                return;
            }

            $provider = SmsProvider::where('country_id', $country->id)->first();
            if (!$provider) {
                \Log::error("No SMS provider found for country {$country->name}");
                return;
            }

            $driver = strtolower($provider->driver);
            $method = "sendWith" . ucfirst($driver);

            if (method_exists($this, $method)) {
                $this->$method($formattedPhone, $provider);
                \Log::info("SMS sent to {$formattedPhone} via {$driver}");
            } else {
                \Log::error("SMS driver method {$method} not implemented");
            }

        } catch (\Exception $e) {
            \Log::error("Failed to send SMS verification: " . $e->getMessage());
            throw $e;
        }
    }

    protected function sendEmail()
    {
        try {
            Mail::to($this->user->email)
                ->send(
                    (new VerificationEmail($this->code, $this->user))
                        ->from('security@venusnap.com', 'Venusnap Security')
                );

            \Log::info("Email verification code sent to {$this->user->email}");
        } catch (\Exception $e) {
            \Log::error("Failed to send email verification: " . $e->getMessage());
            throw $e;
        }
    }


    private function normalizePhoneNumber($phone, Country $country)
    {
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

        return null;
    }

    /**
     * Provider: Beem
     */
    private function sendWithBeem($phone, $provider)
    {
        $client = new Client(['verify' => false]);

        $postData = [
            'source_addr' => $provider->sender_id ?? 'Quixnes',
            'encoding' => 0,
            'schedule_time' => '',
            'message' => $this->message,
            'recipients' => [
                ['recipient_id' => '1', 'dest_addr' => $phone],
            ]
        ];

        $api_key = $provider->api_key;
        $secret_key = $provider->api_secret;

        $client->post('https://apisms.beem.africa/v1/send', [
            'json' => $postData,
            'headers' => [
                'Authorization' => 'Basic ' . base64_encode("$api_key:$secret_key"),
                'Content-Type' => 'application/json',
            ],
        ]);
    }
}
