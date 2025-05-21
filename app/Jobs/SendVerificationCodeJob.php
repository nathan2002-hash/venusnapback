<?php

namespace App\Jobs;

use App\Mail\VerificationEmail;
use GuzzleHttp\Client;
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

    /**
     * Execute the job.
     */
    public function handle()
    {
        if ($this->type === 'phone') {
            $this->sendSms();
        } elseif ($this->type === 'email') {
            $this->sendEmail();
        }
    }

     protected function sendSms()
    {
        try {
            $beemPhone = $this->formatPhoneTo260($this->user->phone);

            $api_key = env('BEEM_API_KEY');
            $secret_key = env('BEEM_SECRET_KEY');

            $postData = [
                'source_addr' => 'Quixnes',
                'encoding' => 0,
                'schedule_time' => '',
                'message' => $this->message,
                'recipients' => [
                    ['recipient_id' => '1', 'dest_addr' => $beemPhone],
                ]
            ];

            $client = new Client(['verify' => false]);
            $client->post('https://apisms.beem.africa/v1/send', [
                'json' => $postData,
                'headers' => [
                    'Authorization' => 'Basic ' . base64_encode("$api_key:$secret_key"),
                    'Content-Type' => 'application/json',
                ],
            ]);
        } catch (\Exception $e) {
            \Log::error("Failed to send SMS verification: " . $e->getMessage());
            throw $e;
        }
    }

    protected function sendEmail()
    {
        try {
            Mail::to($this->user->email)
                ->send(new VerificationEmail($this->code, $this->user));

            \Log::info("Email verification code sent to {$this->user->email}");
        } catch (\Exception $e) {
            \Log::error("Failed to send email verification: " . $e->getMessage());
            throw $e;
        }
    }

    private function formatPhoneTo260($phone)
    {
        $phone = preg_replace('/\D/', '', $phone);

        if (preg_match('/^0(\d{9})$/', $phone)) {
            return '260' . substr($phone, 1);
        }

        return $phone;
    }
}
