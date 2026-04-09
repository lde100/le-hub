<?php

namespace App\Services;

use App\Models\Ticket;
use Illuminate\Support\Facades\Cache;

/**
 * Verbindet Scanner-PC mit beiden Screens via Laravel-Cache.
 * Kein WebSocket-Dienst nötig — Screens pollen alle 800ms.
 */
class CheckinBroadcastService
{
    private function cacheKey(int $screeningId): string
    {
        return "checkin_last_scan_{$screeningId}";
    }

    /**
     * Nach erfolgreichem Scan aufrufen — schreibt in Cache.
     * Beide Screens holen sich das beim nächsten Poll-Tick.
     */
    public function broadcast(Ticket $ticket): void
    {
        $ticket->load(['seat', 'booking', 'screening']);

        Cache::put($this->cacheKey($ticket->screening_id), [
            'ticket_code'   => $ticket->ticket_code,
            'guest_name'    => $ticket->booking->customer_name,
            'seat_label'    => $ticket->seat?->label,
            'seat_id'       => $ticket->seat_id,
            'scanned_at'    => now()->toIso8601String(),
            'seq'           => now()->getTimestampMs(), // Monoton steigend — Screens erkennen neue Scans
        ], now()->addMinutes(2));
    }

    /**
     * Aktuellsten Scan für einen Screening holen.
     * Gibt null zurück wenn nichts im Cache (kein Scan in letzten 2 Min).
     */
    public function getLatest(int $screeningId): ?array
    {
        return Cache::get($this->cacheKey($screeningId));
    }

    /**
     * Prüft ob ein Scan neuer ist als der zuletzt gesehene (anhand seq).
     */
    public function isNew(int $screeningId, ?int $lastSeenSeq): bool
    {
        $latest = $this->getLatest($screeningId);
        if (!$latest) return false;
        return $latest['seq'] > ($lastSeenSeq ?? 0);
    }
}
