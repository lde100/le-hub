<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Booking extends Model
{
    protected $fillable = [
        'booking_ref', 'screening_id', 'customer_name',
        'customer_email', 'customer_phone',
        'total_amount', 'payment_status', 'status', 'checked_in_at'
    ];

    protected $casts = [
        'total_amount'   => 'decimal:2',
        'checked_in_at'  => 'datetime',
    ];

    protected static function boot(): void
    {
        parent::boot();
        static::creating(function ($booking) {
            if (empty($booking->booking_ref)) {
                $booking->booking_ref = 'LE-' . strtoupper(Str::random(6));
            }
        });
    }

    public function screening(): BelongsTo { return $this->belongsTo(Screening::class); }
    public function tickets(): HasMany     { return $this->hasMany(Ticket::class); }
}
