<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Artwork extends Model
{
    protected $fillable = ['user_id', 'status', 'thumbnail'];

    public function user(){
        return $this->belongsTo(User::class);
    }
}
