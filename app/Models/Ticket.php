<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class Ticket extends Model
{
    protected $fillable = [
        'ticket_code', 'booking_id', 'seat_id',
        'screening_id', 'price', 'status', 'scanned_at', 'scanned_by'
    ];

    protected $casts = [
        'price'      => 'decimal:2',
        'scanned_at' => 'datetime',
    ];

    protected static function boot(): void
    {
        parent::boot();
        static::creating(function ($ticket) {
            if (empty($ticket->ticket_code)) {
                // Format: LE-XXXXXXXXXXXX (12 alphanumerische Zeichen)
                $ticket->ticket_code = 'LE' . strtoupper(Str::random(10));
            }
        });
    }

    public function booking(): BelongsTo   { return $this->belongsTo(Booking::class); }
    public function seat(): BelongsTo      { return $this->belongsTo(Seat::class); }
    public function screening(): BelongsTo { return $this->belongsTo(Screening::class); }

    public function markAsUsed(string $scannedBy = 'system'): void
    {
        $this->update([
            'status'     => 'used',
            'scanned_at' => now(),
            'scanned_by' => $scannedBy,
        ]);
    }

    public function isValid(): bool
    {
        return $this->status === 'valid'
            && $this->screening->starts_at->isFuture();
    }
}
