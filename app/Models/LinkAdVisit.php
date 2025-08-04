<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LinkAdVisit extends Model
{
    protected $fillable = [
        'link_ad_share_id', 'ip_address', 'user_id', 'device_info', 'country',
        'is_logged_in', 'user_agent', 'referrer'
    ];
}
