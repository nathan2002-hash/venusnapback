<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Supporter extends Model
{
    public function users(){
        return $this->hasMany(Supporter::class);
    }

    public function user(){
        return $this->belongsTo(User::class);
    }

    public function artboard(){
        return $this->belongsTo(Artboard::class);
    }
}
