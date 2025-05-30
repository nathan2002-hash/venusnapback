<?php

namespace App\Jobs;

use App\Mail\PasswordResetCodeMail;
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
        Mail::to($this->email)
            ->send(
                (new PasswordResetCodeMail($this->code))
                    ->from('security@venusnap.com', 'Venusnap Security')
            );
    }
}
