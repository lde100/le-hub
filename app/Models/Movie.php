<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Movie extends Model
{
    protected $fillable = [
        'title', 'original_title', 'synopsis', 'duration_minutes',
        'rating', 'genre', 'poster_path', 'backdrop_path',
        'trailer_url', 'release_year', 'is_active'
    ];

    public function screenings(): HasMany
    {
        return $this->hasMany(Screening::class);
    }

    public function getDurationFormattedAttribute(): string
    {
        if (!$this->duration_minutes) return '–';
        return floor($this->duration_minutes / 60) . 'h ' . ($this->duration_minutes % 60) . 'min';
    }
}
