<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    protected $fillable = ['user_id', 'payment_session_id', 'amount', 'payment_method', 'currency', 'processor', 'payment_no', 'status', 'purpose', 'description'];

    protected $casts = [
        'metadata' => 'array', // This automatically handles JSON encoding/decoding
    ];

    public function user(){
        return $this->belongsTo(User::class);
    }
}
