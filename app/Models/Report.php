<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Report extends Model
{
    protected $fillable = ['status', 'reason', 'resource_id', 'target', 'user_id'];
}
