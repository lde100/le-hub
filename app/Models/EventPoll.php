<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EventPoll extends Model
{
    protected $fillable = [
        'event_id', 'type', 'title', 'description',
        'status', 'vote_mode', 'allow_new_options', 'closes_at'
    ];

    protected $casts = ['closes_at' => 'datetime'];

    public function event(): BelongsTo   { return $this->belongsTo(Event::class); }
    public function options(): HasMany   { return $this->hasMany(PollOption::class, 'poll_id')->orderBy('sort_order'); }
    public function votes(): HasMany     { return $this->hasMany(PollVote::class, 'poll_id'); }

    public function getResultsAttribute(): \Illuminate\Support\Collection
    {
        return $this->options->map(function ($option) {
            return [
                'option'     => $option,
                'yes_count'  => $option->votes->where('vote_value', 'yes')->count(),
                'like_count' => $option->votes->where('vote_value', 'like')->count(),
                'no_count'   => $option->votes->where('vote_value', 'no')->count(),
                'total'      => $option->votes->count(),
            ];
        })->sortByDesc('yes_count')->values();
    }
}
