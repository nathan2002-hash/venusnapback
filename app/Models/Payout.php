<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payout extends Model
{
    protected $fillable = ['user_id', 'amount', 'currency', 'payment_session_id', 'processor', 'status', 'payout_method', 'payout_reason', 'transaction_id', 'requested_at', 'processed_at', 'completed_at', 'failed_at', 'failure_reason'];

    protected $casts = [
        'requested_at' => 'datetime',
    ];

    public function user(){
        return $this->belongsTo(User::class);
    }
}
