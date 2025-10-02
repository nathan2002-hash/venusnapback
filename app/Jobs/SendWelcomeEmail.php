<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Mail;
use App\Models\User;
use App\Mail\WelcomeFromCeoMail;

class SendWelcomeEmail implements ShouldQueue
{
    use Queueable;

    protected $user;
    protected $deviceinfo;
    protected $ipaddress;

    public function __construct(User $user, $deviceinfo, $ipaddress)
    {
        $this->user = $user;
        $this->deviceinfo = $deviceinfo;
        $this->ipaddress = $ipaddress;
    }

    /**
     * Execute the job.
     */
      public function handle(): void
    {
        try {
            Mail::to($this->user->email)->send(new WelcomeFromCeoMail(
                $this->user,
                $this->deviceinfo,
                $this->ipaddress
            ));

            \Log::info('Welcome email sent successfully to: ' . $this->user->email);

        } catch (\Exception $e) {
            \Log::error('Welcome email failed for user ' . $this->user->email . ': ' . $e->getMessage());

            // Optionally, you can retry the job later
            // $this->release(10); // Retry after 10 seconds
        }
    }
}
