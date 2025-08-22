<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Influencer extends Model
{
    protected $fillable = [
        'user_id',
        'monetization_balance',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
