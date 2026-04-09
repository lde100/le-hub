<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class OrderParticipant extends Model
{
    protected $fillable = ['order_id', 'customer_id', 'name', 'color', 'sort_order'];

    public function order(): BelongsTo    { return $this->belongsTo(Order::class); }
    public function customer(): BelongsTo { return $this->belongsTo(Customer::class); }
    public function splits(): HasMany     { return $this->hasMany(OrderItemSplit::class, 'participant_id'); }

    public function getTotalShareAttribute(): float
    {
        return (float) $this->splits->sum('share_amount');
    }
}
