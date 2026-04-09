<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    protected $fillable = [
        'invoice_id', 'order_id', 'customer_id', 'participant_name',
        'amount', 'method', 'reference', 'status', 'paid_at', 'notes'
    ];

    protected $casts = [
        'amount'  => 'decimal:2',
        'paid_at' => 'datetime',
    ];

    public function invoice(): BelongsTo  { return $this->belongsTo(Invoice::class); }
    public function order(): BelongsTo    { return $this->belongsTo(Order::class); }
    public function customer(): BelongsTo { return $this->belongsTo(Customer::class); }

    public static function methodLabel(string $method): string
    {
        return match($method) {
            'cash'            => '💵 Bar',
            'paypal_friends'  => '💙 PayPal Freunde',
            'paypal_invoice'  => '🔵 PayPal Rechnung',
            'transfer'        => '🏦 Überweisung',
            'free'            => '🎁 Kostenlos',
            default           => $method,
        };
    }
}
