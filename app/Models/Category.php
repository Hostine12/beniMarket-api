<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    protected $fillable = ['name', 'slug', 'icon', 'accent', 'count', 'parent_id', 'description', 'image'];

    public function products()
    {
        return $this->hasMany(Product::class);
    }

    public function parent()
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    public function subcategories()
    {
        return $this->hasMany(Category::class, 'parent_id');
    }

    /** Toutes les catégories racine (sans parent). */
    public function scopeRoot($query)
    {
        return $query->whereNull('parent_id');
    }
}
