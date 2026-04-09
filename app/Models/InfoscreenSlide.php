<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InfoscreenSlide extends Model
{
    protected $fillable = [
        'title', 'channel', 'type', 'config',
        'duration_seconds', 'sort_order', 'is_active',
        'active_from', 'active_until'
    ];

    protected $casts = [
        'config'       => 'array',
        'is_active'    => 'boolean',
        'active_from'  => 'datetime',
        'active_until' => 'datetime',
    ];

    public function scopeForChannel($query, string $channel)
    {
        return $query->where('channel', $channel)
            ->where('is_active', true)
            ->where(fn($q) => $q->whereNull('active_from')->orWhere('active_from', '<=', now()))
            ->where(fn($q) => $q->whereNull('active_until')->orWhere('active_until', '>=', now()))
            ->orderBy('sort_order');
    }
}
