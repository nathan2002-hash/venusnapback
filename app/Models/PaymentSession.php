<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentSession extends Model
{
    protected $fillable = ['ip_address', 'user_id', 'device_info', 'user_agent'];
}
