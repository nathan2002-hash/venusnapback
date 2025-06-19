<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PostCategory extends Model
{
     protected $fillable = ['post_id', 'category_id', 'confidence_score'];
}
