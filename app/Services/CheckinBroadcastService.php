<?php

namespace App\Services;

use App\Models\Ticket;
use Illuminate\Support\Facades\Cache;

class CheckinBroadcastService
{
    // ── Keys ────────────────────────────────────────────────────────────────

    private function scanKey(int $id): string    { return "checkin_last_scan_{$id}"; }
    private function stateKey(int $id): string   { return "screening_state_{$id}"; }
    private function gongKey(int $id): string    { return "gong_trigger_{$id}"; }
    private function tickerKey(int $id): string  { return "ticker_{$id}"; }

    // ── Scan ────────────────────────────────────────────────────────────────

    public function broadcast(Ticket $ticket): void
    {
        $ticket->load(['seat', 'booking', 'screening']);
        Cache::put($this->scanKey($ticket->screening_id), [
            'ticket_code' => $ticket->ticket_code,
            'guest_name'  => $ticket->booking->customer_name,
            'seat_label'  => $ticket->seat?->label,
            'seat_id'     => $ticket->seat_id,
            'scanned_at'  => now()->toIso8601String(),
            'seq'         => now()->getTimestampMs(),
        ], now()->addMinutes(2));
    }

    public function getLatestScan(int $screeningId): ?array
    {
        return Cache::get($this->scanKey($screeningId));
    }

    public function isNewScan(int $screeningId, ?int $lastSeenSeq): bool
    {
        $latest = $this->getLatestScan($screeningId);
        return $latest && $latest['seq'] > ($lastSeenSeq ?? 0);
    }

    // ── Screening-State ──────────────────────────────────────────────────────
    // States: 'countdown' | 'ready' | 'playing'

    public function setState(int $screeningId, string $state): void
    {
        Cache::put($this->stateKey($screeningId), [
            'state' => $state,
            'seq'   => now()->getTimestampMs(),
        ], now()->addHours(6));
    }

    public function getState(int $screeningId): string
    {
        return Cache::get($this->stateKey($screeningId))['state'] ?? 'countdown';
    }

    public function getStateSeq(int $screeningId): int
    {
        return Cache::get($this->stateKey($screeningId))['seq'] ?? 0;
    }

    // ── Gong-Trigger ─────────────────────────────────────────────────────────
    // Screens pollen seq — wenn neu: Gong spielen

    public function triggerGong(int $screeningId, int $count = 1): void
    {
        Cache::put($this->gongKey($screeningId), [
            'count' => $count,
            'seq'   => now()->getTimestampMs(),
        ], now()->addMinutes(1));
    }

    public function getGongTrigger(int $screeningId): ?array
    {
        return Cache::get($this->gongKey($screeningId));
    }

    // ── Ticker ───────────────────────────────────────────────────────────────

    public function setTicker(int $screeningId, string $text): void
    {
        Cache::put($this->tickerKey($screeningId), [
            'text' => $text,
            'at'   => now()->toIso8601String(),
        ], now()->addMinutes(30));
    }

    public function clearTicker(int $screeningId): void
    {
        Cache::forget($this->tickerKey($screeningId));
    }

    public function getTicker(int $screeningId): ?array
    {
        return Cache::get($this->tickerKey($screeningId));
    }
}
