<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Album extends Model
{
    protected $fillable = ['name', 'description', 'user_id', 'slug', 'type'];

    public function user(){
        return $this->belongsTo(User::class);
    }

    public function supporters(){
        return $this->hasMany(Supporter::class);
    }
}
