<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Conversation extends Model
{
     protected $fillable = [
        'user_number',
        'our_number',
        'type',
    ];
}
