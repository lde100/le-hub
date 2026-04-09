<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderItemSplit extends Model
{
    protected $fillable = ['order_item_id', 'participant_id', 'share_amount', 'share_fraction', 'split_type'];

    protected $casts = [
        'share_amount'   => 'decimal:2',
        'share_fraction' => 'decimal:4',
    ];

    public function item(): BelongsTo        { return $this->belongsTo(OrderItem::class, 'order_item_id'); }
    public function participant(): BelongsTo { return $this->belongsTo(OrderParticipant::class, 'participant_id'); }
}
