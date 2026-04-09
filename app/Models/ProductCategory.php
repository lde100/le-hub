<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProductCategory extends Model
{
    protected $fillable = ['name', 'slug', 'module', 'icon', 'color', 'sort_order', 'show_on_infoscreen'];

    public function products(): HasMany
    {
        return $this->hasMany(Product::class, 'category_id')->orderBy('sort_order');
    }
}
