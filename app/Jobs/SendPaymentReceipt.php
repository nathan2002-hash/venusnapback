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
    protected $payment;

    public function __construct($email, $payment)
    {
       $this->email = $email;
       $this->payment = $payment;
    }

    /**
     * Execute the job.
     */
    public function handle()
    {
         Mail::to($this->email)
            ->send(
                (new PaymentReceipt($this->payment))
                    ->from('billing@venusnap.com', 'Venusnap Billing Team')
            );
    }
}
