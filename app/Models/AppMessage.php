<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AppMessage extends Model
{
    protected $fillable = ['type', 'title', 'content', 'button_text', 'button_action'];
}
