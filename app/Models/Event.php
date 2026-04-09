<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Event extends Model
{
    protected $fillable = [
        'title', 'description', 'type', 'status', 'public_token',
        'venue_id', 'seating_mode', 'max_capacity',
        'allow_seat_requests', 'allow_walk_in', 'walk_in_needs_seat', 'meta'
    ];

    protected $casts = [
        'allow_seat_requests' => 'boolean',
        'allow_walk_in'       => 'boolean',
        'walk_in_needs_seat'  => 'boolean',
        'meta'                => 'array',
    ];

    // Status-Lifecycle
    const STATUSES = [
        'draft'          => 'Entwurf',
        'polling_date'   => 'Termin-Abstimmung läuft',
        'polling_film'   => 'Film-Abstimmung läuft',
        'booking_open'   => 'Buchung offen',
        'confirmed'      => 'Bestätigt',
        'finished'       => 'Abgeschlossen',
        'cancelled'      => 'Abgesagt',
    ];

    protected static function boot(): void
    {
        parent::boot();
        static::creating(function ($event) {
            if (empty($event->public_token)) {
                $event->public_token = Str::random(32);
            }
        });
    }

    public function venue(): BelongsTo     { return $this->belongsTo(Venue::class); }
    public function slots(): HasMany       { return $this->hasMany(EventSlot::class); }
    public function polls(): HasMany       { return $this->hasMany(EventPoll::class); }
    public function screenings(): HasMany  { return $this->hasMany(Screening::class); }
    public function seatRequests(): HasMany { return $this->hasMany(SeatRequest::class); }

    public function getPublicUrlAttribute(): string
    {
        return url('/event/' . $this->public_token);
    }

    public function getActiveDatePollAttribute(): ?EventPoll
    {
        return $this->polls->where('type', 'date_selection')->where('status', 'open')->first();
    }

    public function getActiveFilmPollAttribute(): ?EventPoll
    {
        return $this->polls->where('type', 'film_vote')->where('status', 'open')->first();
    }

    public function getConfirmedScreeningAttribute(): ?Screening
    {
        return $this->screenings->first();
    }
}
