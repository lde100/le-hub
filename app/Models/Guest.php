<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Guest extends Model
{
    protected $fillable = [
        'name', 'email', 'phone', 'magic_token', 'guest_number',
        'loyalty_points', 'visit_count', 'last_visit_at',
        'token_expires_at', 'email_verified_at', 'meta'
    ];

    protected $casts = [
        'meta'              => 'array',
        'last_visit_at'     => 'datetime',
        'token_expires_at'  => 'datetime',
        'email_verified_at' => 'datetime',
    ];

    protected $hidden = ['magic_token'];

    protected static function boot(): void
    {
        parent::boot();
        static::creating(function ($guest) {
            if (empty($guest->guest_number)) {
                $last = static::max('id') ?? 0;
                $guest->guest_number = 'LE-G-' . str_pad($last + 1, 4, '0', STR_PAD_LEFT);
            }
            if (empty($guest->magic_token)) {
                $guest->magic_token = Str::random(48);
                $guest->token_expires_at = now()->addYear();
            }
            // Zufällige Avatar-Farbe wenn nicht gesetzt
            if (empty($guest->meta['color'] ?? null)) {
                $colors = ['#E11D48','#7C3AED','#2563EB','#059669','#D97706','#DC2626'];
                $guest->meta = array_merge($guest->meta ?? [], [
                    'color' => $colors[array_rand($colors)]
                ]);
            }
        });
    }

    public function attendances(): HasMany       { return $this->hasMany(Attendance::class); }
    public function pollVotes(): HasMany         { return $this->hasMany(PollVote::class); }
    public function seatRequests(): HasMany      { return $this->hasMany(SeatRequest::class); }
    public function loyaltyTransactions(): HasMany { return $this->hasMany(LoyaltyTransaction::class); }

    /**
     * Punkte gutschreiben und loggen.
     */
    public function earnPoints(int $points, string $type, string $description, Model $source): void
    {
        $this->increment('loyalty_points', $points);
        $this->loyaltyTransactions()->create([
            'points'      => $points,
            'type'        => $type,
            'description' => $description,
            'source_type' => get_class($source),
            'source_id'   => $source->id,
        ]);
    }

    /**
     * Magic-Link URL generieren.
     */
    public function getMagicUrl(string $path = '/'): string
    {
        return url($path . '?token=' . $this->magic_token);
    }

    /**
     * Gast anhand Token finden und validieren.
     */
    public static function findByToken(string $token): ?self
    {
        return static::where('magic_token', $token)
            ->where(fn($q) => $q->whereNull('token_expires_at')
                ->orWhere('token_expires_at', '>', now()))
            ->first();
    }

    public function getAvatarColorAttribute(): string
    {
        return $this->meta['color'] ?? '#6366F1';
    }

    public function getInitialsAttribute(): string
    {
        $parts = explode(' ', trim($this->name));
        return strtoupper(
            substr($parts[0], 0, 1) . (isset($parts[1]) ? substr($parts[1], 0, 1) : '')
        );
    }
}
