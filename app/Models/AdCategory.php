<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AdCategory extends Model
{
    protected $fillable = ['ad_id', 'category_id'];

    public function ad(){
        return $this->belongsTo(Ad::class);
    }

    public function category(){
        return $this->belongsTo(Category::class);
    }
}
