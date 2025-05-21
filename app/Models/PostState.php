<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PostState extends Model
{
    protected $fillable = ['post_id', 'user_id', 'title', 'initiator', 'reason', 'state'];
}
