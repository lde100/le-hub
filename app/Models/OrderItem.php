<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class OrderItem extends Model
{
    protected $fillable = [
        'order_id', 'product_id', 'name', 'unit_price',
        'quantity', 'total_price', 'status', 'notes'
    ];

    protected $casts = [
        'unit_price'  => 'decimal:2',
        'quantity'    => 'decimal:3',
        'total_price' => 'decimal:2',
    ];

    public function order(): BelongsTo   { return $this->belongsTo(Order::class); }
    public function product(): BelongsTo { return $this->belongsTo(Product::class); }
    public function splits(): HasMany    { return $this->hasMany(OrderItemSplit::class, 'order_item_id'); }

    /**
     * Posten gleichmäßig auf eine Liste von Participants aufteilen.
     */
    public function splitEqually(array $participantIds): void
    {
        $this->splits()->delete();
        $count = count($participantIds);
        if ($count === 0) return;

        $perPerson = round($this->total_price / $count, 2);
        $remainder = round($this->total_price - ($perPerson * $count), 2);

        foreach ($participantIds as $i => $participantId) {
            $amount = $perPerson + ($i === 0 ? $remainder : 0); // Cent-Rest auf erste Person
            OrderItemSplit::create([
                'order_item_id'  => $this->id,
                'participant_id' => $participantId,
                'share_amount'   => $amount,
                'share_fraction' => round(1 / $count, 4),
                'split_type'     => 'equal',
            ]);
        }
    }

    /**
     * Posten einer einzelnen Person zuweisen.
     */
    public function assignTo(int $participantId): void
    {
        $this->splits()->delete();
        OrderItemSplit::create([
            'order_item_id'  => $this->id,
            'participant_id' => $participantId,
            'share_amount'   => $this->total_price,
            'share_fraction' => 1.0,
            'split_type'     => 'assigned',
        ]);
    }
}
