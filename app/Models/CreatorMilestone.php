<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CreatorMilestone extends Model
{
    protected $fillable = [
        'influencer_id',
        'creator_user_id',
        'milestone_type',
        'milestone_value',
        'reward',
        'credited',
    ];
}
