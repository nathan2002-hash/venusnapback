<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LinkAdShare extends Model
{
    protected $fillable = [
        'user_id',
        'ad_id',
        'ip_address',
        'device_info',
        'share_method',
        'share_url',
        'short_code'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function ad()
    {
        return $this->belongsTo(Ad::class);
    }

    public function linkadvisits()
    {
        return $this->hasMany(LinkAdVisit::class);
    }
}
