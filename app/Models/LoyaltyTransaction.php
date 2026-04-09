<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class LoyaltyTransaction extends Model
{
    protected $fillable = ['guest_id', 'points', 'type', 'description', 'source_type', 'source_id'];

    public function guest(): BelongsTo { return $this->belongsTo(Guest::class); }
    public function source(): MorphTo  { return $this->morphTo(); }
}
