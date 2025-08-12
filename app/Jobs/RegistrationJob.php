<?php

namespace App\Jobs;

use App\Models\User;
use App\Models\Album;
use App\Models\Account;
use App\Models\Activity;
use App\Models\UserSetting;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Mail;
use App\Mail\WelcomeFromCeoMail;
use Illuminate\Support\Facades\Http;

class RegistrationJob implements ShouldQueue
{
    use Queueable;

    protected $user;
    protected $userAgent;
    protected $ipaddress;
    protected $deviceinfo;

    public function __construct(User $user, $userAgent, $deviceinfo, $ipaddress)
    {
        $this->user = $user;
        $this->userAgent = $userAgent;
        $this->ipaddress = $ipaddress;
        $this->deviceinfo = $deviceinfo;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $randomNumber = mt_rand(1000, 9999);

        $timezone = 'Africa/Lusaka'; // default fallback
        try {
            $key = 'RcpPDNk33XlJPkUgFiSsyecGdGWPD3oOG0b0rHRVzuIhTzcabQ';
            $response = Http::get("https://ipapi.co/{$this->ipaddress}/json/?key={$key}");

            if ($response->successful()) {
                $data = $response->json();
                $timezone = $data['timezone'] ?? 'Africa/Lusaka';

                // Save timezone to user
                $this->user->timezone = $timezone;
                $this->user->save();
            }
        } catch (\Exception $e) {
            \Log::error("Failed to fetch timezone from ipapi.co: " . $e->getMessage());
        }

        $activity = new Activity();
        $activity->title = 'Account Created';
        $activity->description = 'Your account has been created';
        $activity->source = 'Registration';
        $activity->user_id = $this->user->id;
        $activity->status = true;
        $activity->user_agent = $this->userAgent;
        $activity->device_info = $this->deviceinfo;
        $activity->ipaddress = $this->ipaddress;
        $activity->save();

        $usersetting = new UserSetting();
        $usersetting->user_id = $this->user->id;
        $usersetting->save();

        $account = Account::firstOrCreate(
            ['user_id' => $this->user->id],
            [
                'user_id' => $this->user->id,
                'account_balance' => 0.00,
                'available_balance' => 0.00,
                'monetization_status' => 'inactive',
                'payout_method' => 'paypal',
                'country' => $this->user->country,
                'currency' => 'USD',
                'paypal_email' => $this->user->email
            ]
        );

        Mail::to($this->user->email)->send(new WelcomeFromCeoMail(
            $this->user,
            $this->deviceinfo,
            $this->ipaddress
        ));

        $this->sendWithVonage('260970333596', 'New Venusnap user registered: ' . $this->user->name);
    }

    private function sendWithVonage($phone, $message)
    {
        $client = new \GuzzleHttp\Client();

        $api_key = env('VONAGE_API_KEY');
        $api_secret = env('VONAGE_API_SECRET');
        $from = 'Venusnap';

        try {
            $client->post('https://rest.nexmo.com/sms/json', [
                'form_params' => [
                    'api_key' => $api_key,
                    'api_secret' => $api_secret,
                    'to' => $phone,
                    'from' => $from,
                    'text' => $message,
                ]
            ]);
        } catch (\Exception $e) {
            \Log::error('Vonage SMS failed: ' . $e->getMessage());
        }
    }
}
