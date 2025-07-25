<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Search extends Model
{
    protected $fillable = [
        'user_id',
        'query',
        'results_count',
        'ip_address',
    ];
}
