<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PollVote extends Model
{
    protected $fillable = ['poll_id', 'option_id', 'guest_id', 'guest_name', 'vote_value'];

    public function poll(): BelongsTo    { return $this->belongsTo(EventPoll::class, 'poll_id'); }
    public function option(): BelongsTo  { return $this->belongsTo(PollOption::class, 'option_id'); }
    public function guest(): BelongsTo   { return $this->belongsTo(Guest::class); }
}
