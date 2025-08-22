<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InfluencerReferral extends Model
{
    protected $fillable = [
        'influencer_id',
        'referred_user_id',
        'post_id',
        'reward',
        'milestone_type',
        'milestone_value',
        'credited',
    ];
}
