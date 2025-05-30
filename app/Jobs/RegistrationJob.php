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
    }
}
