<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    protected $fillable = [
        'user_id',
        'action',
        'notifiable_id',
        'notifiable_type',
        'data',
        'is_read',
        'group_count',
    ];
}
