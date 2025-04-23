<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MonetizationRequest extends Model
{
    protected $fillable = ['album_id', 'country', 'user_id', 'status', 'device_info', 'user_agent', 'ip_address'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function album()
    {
        return $this->belongsTo(Album::class);
    }
}
