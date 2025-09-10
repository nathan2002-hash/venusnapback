<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VenusnapSystem extends Model
{
    protected $fillable = [
        'system_money',
        'reserved_points',
        'points_per_dollar',
        'points_per_discovery',
        'points_per_milestone',
        'points_per_admire',
        'total_points_spent',
        'total_points_earned',
    ];
}
