<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Screening extends Model
{
    protected $fillable = [
        'venue_id', 'movie_id', 'starts_at', 'doors_open_at',
        'base_price', 'status', 'notes'
    ];

    protected $casts = [
        'starts_at'     => 'datetime',
        'doors_open_at' => 'datetime',
        'base_price'    => 'decimal:2',
    ];

    public function venue(): BelongsTo   { return $this->belongsTo(Venue::class); }
    public function movie(): BelongsTo   { return $this->belongsTo(Movie::class); }
    public function bookings(): HasMany  { return $this->hasMany(Booking::class); }
    public function tickets(): HasMany   { return $this->hasMany(Ticket::class); }

    public function getAvailableSeatsCountAttribute(): int
    {
        $booked = $this->tickets()->whereIn('status', ['valid', 'used'])->count();
        return $this->venue->seats()->where('is_active', true)->count() - $booked;
    }
}
