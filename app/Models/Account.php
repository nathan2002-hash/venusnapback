<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Account extends Model
{
    protected $fillable = [
        'user_id',
        'account_balance',
        'available_balance',
        'monetization_status',
        'payout_method',
        'currency',
        'country',
        'paypal_email',
        'account_holder_name',
        'account_number',
        'account_type',
        'bank_name',
        'bank_address',
        'swift_code',
        'routing_number',
        'reference_no',
    ];

    public function user(){
        return $this->belongsTo(User::class);
    }
}
