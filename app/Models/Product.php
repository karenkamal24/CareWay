<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
class Product extends Model
{
    protected $fillable = [ 'category_id','name', 'image', 'description', 'price','quantity','status'  , 'active_ingredient',];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }
    public function getMainImageUrlAttribute()
    {
        return $this->image ? url(Storage::url($this->image)) : null;
    }
}
