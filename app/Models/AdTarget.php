<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AdTarget extends Model
{
    protected $fillable = ['ad_id', 'country', 'region', 'city', 'continent', 'status'];

    public function ad(){
        return $this->belongsTo(Ad::class);
    }
}
