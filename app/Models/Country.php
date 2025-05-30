<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Country extends Model
{
    protected $fillable = ['name', 'code', 'sample_phone', 'phone_number_length', 'continent_id', 'phone_code', 'capital', 'currency', 'currency_code', 'flag', 'description', 'is_active', 'is_verified'];
}
