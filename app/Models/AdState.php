<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AdState extends Model
{
    protected $fillable = ['ad_id', 'action','initiator','points', 'user_id', 'meta'];
}
