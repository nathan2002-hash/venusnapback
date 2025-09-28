<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ButtonClick extends Model
{
    protected $fillable = ['button_name', 'user_agent', 'page_url', 'ip_address', 'user_id'];
}
