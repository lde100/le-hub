<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Customer extends Model
{
    protected $fillable = [
        'name', 'email', 'phone', 'company', 'address',
        'tax_id', 'customer_number', 'barcode', 'context', 'meta', 'is_active'
    ];

    protected $casts = ['meta' => 'array', 'is_active' => 'boolean'];

    protected static function boot(): void
    {
        parent::boot();
        static::creating(function ($customer) {
            if (empty($customer->customer_number)) {
                $last = static::max('id') ?? 0;
                $customer->customer_number = 'LE-K-' . str_pad($last + 1, 4, '0', STR_PAD_LEFT);
            }
        });
    }

    public function orders(): HasMany    { return $this->hasMany(Order::class); }
    public function invoices(): HasMany  { return $this->hasMany(Invoice::class); }
    public function payments(): HasMany  { return $this->hasMany(Payment::class); }
}
