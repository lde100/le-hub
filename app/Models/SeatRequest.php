<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SeatRequest extends Model
{
    protected $fillable = [
        'event_id', 'guest_id', 'guest_name', 'guest_email',
        'requested_seat_ids', 'status', 'assigned_seat_id', 'notes', 'booking_id'
    ];

    protected $casts = ['requested_seat_ids' => 'array'];

    public function event(): BelongsTo        { return $this->belongsTo(Event::class); }
    public function guest(): BelongsTo        { return $this->belongsTo(Guest::class); }
    public function assignedSeat(): BelongsTo { return $this->belongsTo(Seat::class, 'assigned_seat_id'); }
    public function booking(): BelongsTo      { return $this->belongsTo(Booking::class); }
}
