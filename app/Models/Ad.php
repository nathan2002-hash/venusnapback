<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Ad extends Model
{
    protected $fillable = ['adboard_id', 'cta_name', 'status', 'cta_link', 'description', 'cta_type'];

    public function categories()
    {
        return $this->belongsToMany(Category::class, 'ad_categories', 'ad_id', 'category_id');
    }

    public function targets()
    {
        return $this->hasMany(AdTarget::class);
    }

    public function media()
    {
        return $this->hasMany(AdMedia::class);
    }

    public function adboard(){
        return $this->belongsTo(Adboard::class);
    }
}
