<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Order extends Model
{
    protected $fillable = [
        'order_number', 'module', 'status', 'table_ref',
        'notes', 'discount_amount', 'tip_amount', 'meta'
    ];

    protected $casts = [
        'discount_amount' => 'decimal:2',
        'tip_amount'      => 'decimal:2',
        'meta'            => 'array',
    ];

    protected static function boot(): void
    {
        parent::boot();
        static::creating(function ($order) {
            if (empty($order->order_number)) {
                $last = static::max('id') ?? 0;
                $order->order_number = 'LE-O-' . str_pad($last + 1, 5, '0', STR_PAD_LEFT);
            }
        });
    }

    public function items(): HasMany        { return $this->hasMany(OrderItem::class); }
    public function participants(): HasMany { return $this->hasMany(OrderParticipant::class)->orderBy('sort_order'); }
    public function payments(): HasMany     { return $this->hasMany(Payment::class); }

    public function getSubtotalAttribute(): float
    {
        return (float) $this->items->whereNotIn('status', ['cancelled'])->sum('total_price');
    }

    public function getTotalAttribute(): float
    {
        return max(0, $this->subtotal - $this->discount_amount + $this->tip_amount);
    }

    public function getPaidAmountAttribute(): float
    {
        return (float) $this->payments->where('status', 'completed')->sum('amount');
    }

    public function getOpenAmountAttribute(): float
    {
        return max(0, $this->total - $this->paid_amount);
    }
}
