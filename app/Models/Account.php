<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Account extends Model
{
    protected $fillable = ['account_balance', 'available_balance', 'monetization_status'];

    public function user(){
        return $this->belongsTo(User::class);
    }
}
