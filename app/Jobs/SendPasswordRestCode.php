<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Mail;

class SendPasswordRestCode implements ShouldQueue
{
    use Queueable;

    protected $email;
    protected $code;

    /**
     * Create a new job instance.
     */
    public function __construct($email, $code)
    {
        $this->email = $email;
        $this->code = $code;
    }

    /**
     * Execute the job.
     */
    public function handle()
    {
        Mail::raw("Your password reset code is: {$this->code}. It expires in 10 minutes.", function ($message) {
            $message->to($this->email)
                    ->subject('Password Reset Code');
        });
    }
}
