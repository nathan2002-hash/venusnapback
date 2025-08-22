<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InfluencerBonusMilestone extends Model
{
    protected $fillable = [
        'influencer_id',
        'milestone_type',
        'reward',
        'credited',
    ];
    // Define any relationships or additional methods if needed
}
