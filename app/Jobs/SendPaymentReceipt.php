<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Mail;
use App\Mail\PaymentReceipt;

class SendPaymentReceipt implements ShouldQueue
{
    use Queueable;

    protected $email;
    protected $htmlContent;

    public function __construct($email, $htmlContent)
    {
        $this->email = $email;
        $this->htmlContent = $htmlContent;
    }

    /**
     * Execute the job.
     */
     public function handle()
    {
        Mail::to($this->email)->send(new PaymentReceipt($this->htmlContent));
    }
}
