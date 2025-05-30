<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Continent extends Model
{
    protected $fillable = ['name', 'code'];

    public function countries()
    {
        return $this->hasMany(Country::class);
    }
}
