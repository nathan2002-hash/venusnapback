<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

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
        try {
            Mail::raw("Your password reset code is: {$this->code}. It expires in 10 minutes.", function ($message) {
                $message->to($this->email)
                        ->subject('Password Reset Code');
            });
        } catch (\Exception $e) {
            Log::error('Failed to send password reset email', [
                'email' => $this->email,
                'code' => $this->code,
                'error' => $e->getMessage(),
            ]);

            throw $e; // Re-throw so Laravel can mark it as failed properly
        }
    }
}
