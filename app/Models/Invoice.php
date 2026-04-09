<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Invoice extends Model
{
    protected $fillable = [
        'invoice_number', 'customer_id', 'order_id',
        'recipient_name', 'recipient_address', 'recipient_tax_id',
        'invoice_date', 'due_date',
        'subtotal', 'tax_rate', 'tax_amount', 'total', 'discount_amount',
        'status', 'notes', 'pdf_path'
    ];

    protected $casts = [
        'invoice_date' => 'date',
        'due_date'     => 'date',
        'subtotal'     => 'decimal:2',
        'tax_rate'     => 'decimal:2',
        'tax_amount'   => 'decimal:2',
        'total'        => 'decimal:2',
        'discount_amount' => 'decimal:2',
    ];

    protected static function boot(): void
    {
        parent::boot();
        static::creating(function ($invoice) {
            if (empty($invoice->invoice_number)) {
                $last = static::max('id') ?? 0;
                $invoice->invoice_number = 'LE-R-' . str_pad($last + 1, 5, '0', STR_PAD_LEFT);
            }
        });
    }

    public function customer(): BelongsTo { return $this->belongsTo(Customer::class); }
    public function order(): BelongsTo    { return $this->belongsTo(Order::class); }
}
