<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    protected $fillable = ['name',  'image'];


    public function products()
    {
        return $this->hasMany(Product::class);
    }
    public function getMainImageUrlAttribute()
    {
        return $this->image ? url(Storage::url($this->image)) : null;
    }
        public function parent(): BelongsTo
    {
    return $this->belongsTo(Category::class, 'parent_id');
    }


    public function children(): HasMany
    {
    return $this->hasMany(Category::class, 'parent_id');
    }
}
