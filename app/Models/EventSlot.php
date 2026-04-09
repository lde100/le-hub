<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EventSlot extends Model
{
    protected $fillable = ['event_id', 'screening_id', 'proposed_at', 'is_confirmed'];
    protected $casts = ['proposed_at' => 'datetime', 'is_confirmed' => 'boolean'];

    public function event(): BelongsTo     { return $this->belongsTo(Event::class); }
    public function screening(): BelongsTo { return $this->belongsTo(Screening::class); }
}
