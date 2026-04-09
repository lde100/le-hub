<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Venue extends Model
{
    protected $fillable = ['name', 'slug', 'description', 'is_active'];

    public function seats(): HasMany
    {
        return $this->hasMany(Seat::class)->orderBy('sort_order');
    }

    public function screenings(): HasMany
    {
        return $this->hasMany(Screening::class);
    }
}
