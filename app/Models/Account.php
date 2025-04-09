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
        'paypal_email',
    ];

    public function user(){
        return $this->belongsTo(User::class);
    }
}
