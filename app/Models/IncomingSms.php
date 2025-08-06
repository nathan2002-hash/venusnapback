<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IncomingSms extends Model
{
    protected $fillable = [
        'from',
        'to',
        'text',
        'message_id',
        'received_at',
        'ip_address',
        'user_agent',
        'status'
    ];
}
