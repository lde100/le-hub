<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Attendance extends Model
{
    protected $fillable = [
        'screening_id', 'guest_id', 'ticket_id', 'seat_id',
        'guest_name', 'checked_in_at', 'loyalty_points_earned'
    ];

    protected $casts = ['checked_in_at' => 'datetime'];

    public function screening(): BelongsTo { return $this->belongsTo(Screening::class); }
    public function guest(): BelongsTo     { return $this->belongsTo(Guest::class); }
    public function ticket(): BelongsTo    { return $this->belongsTo(Ticket::class); }
    public function seat(): BelongsTo      { return $this->belongsTo(Seat::class); }
}
