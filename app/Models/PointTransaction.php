<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PointTransaction extends Model
{
    protected $fillable = [
        'resource_id',
        'user_id',
        'points',
        'type',
        'balance_after',
        'description',
    ];
}
