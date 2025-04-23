<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Tax extends Model
{
    protected $fillable = ['type', 'account_id', 'tax_number', 'legal_names', 'address', 'city', 'state', 'zip', 'country'];
}
