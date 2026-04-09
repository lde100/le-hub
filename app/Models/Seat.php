<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Seat extends Model
{
    protected $fillable = [
        'venue_id', 'label', 'row', 'position',
        'type', 'price_modifier', 'is_active', 'sort_order'
    ];

    protected $casts = [
        'price_modifier' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function venue(): BelongsTo
    {
        return $this->belongsTo(Venue::class);
    }
}
