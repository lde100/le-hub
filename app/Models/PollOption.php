<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PollOption extends Model
{
    protected $fillable = [
        'poll_id', 'type', 'label', 'date_value',
        'movie_title', 'movie_year', 'movie_poster_path', 'movie_synopsis',
        'movie_genre', 'movie_duration', 'movie_rating',
        'external_id', 'external_source', 'is_winner', 'sort_order',
        'suggested_by_guest_id'
    ];

    protected $casts = [
        'date_value' => 'datetime',
        'is_winner'  => 'boolean',
    ];

    public function poll(): BelongsTo          { return $this->belongsTo(EventPoll::class, 'poll_id'); }
    public function votes(): HasMany           { return $this->hasMany(PollVote::class, 'option_id'); }
    public function suggestedBy(): BelongsTo   { return $this->belongsTo(Guest::class, 'suggested_by_guest_id'); }

    public function getPosterUrlAttribute(): ?string
    {
        if (!$this->movie_poster_path) return null;
        // Emby: vollständige URL, TMDB: relativer Pfad
        if (str_starts_with($this->movie_poster_path, 'http')) return $this->movie_poster_path;
        return 'https://image.tmdb.org/t/p/w300' . $this->movie_poster_path;
    }
}
