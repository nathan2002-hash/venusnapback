<?php

namespace App\Jobs;

use App\Mail\TwoFactorCodeMail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendTwoFactorCodeJob implements ShouldQueue
{
    use Queueable;

    public $user;
    public $code;

    /**
     * Create a new job instance.
     */
    public function __construct($user, $code)
    {
        $this->user = $user;
        $this->code = $code;
    }

    /**
     * Execute the job.
     */
    public function handle()
    {
        Mail::mailer('smtp')->to($this->user->email)->send(
            (new TwoFactorCodeMail($this->user->name, $this->code))
                ->from('security@venusnap.com', 'Venusnap Security Team')
        );
    }
}
