<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PointRequest extends Model
{
    protected $fillable = [
        'user_id',
        'full_name',
        'business_name',
        'email',
        'phone',
        'points',
        'purpose',
        'status',
        'ip_address',
        'device_info',
        'user_agent',
    ];
}
