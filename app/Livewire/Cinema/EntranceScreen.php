<?php

namespace App\Livewire\Cinema;

use App\Models\Screening;
use App\Services\CheckinBroadcastService;
use Livewire\Component;
use Livewire\Attributes\Polling;

class EntranceScreen extends Component
{
    public Screening $screening;
    public ?array $currentScan = null;
    public int $lastSeenSeq = 0;
    public bool $scanMode = false;    // false = Countdown, true = Sitzplan-Anzeige
    public int $scanModeUntil = 0;    // Unix-Timestamp wann zurückschalten

    // Polling alle 800ms — holt neue Scans aus Cache
    #[Polling(800)]
    public function poll(): void
    {
        $broadcast = app(CheckinBroadcastService::class);

        if ($broadcast->isNew($this->screening->id, $this->lastSeenSeq)) {
            $scan = $broadcast->getLatest($this->screening->id);
            $this->currentScan   = $scan;
            $this->lastSeenSeq   = $scan['seq'];
            $this->scanMode      = true;
            $this->scanModeUntil = time() + 30; // 30 Sekunden Sitzplan-Anzeige

            $this->dispatch('new-scan', scan: $scan);
        }

        // Nach 30 Sekunden zurück zu Countdown
        if ($this->scanMode && time() > $this->scanModeUntil) {
            $this->scanMode    = false;
            $this->currentScan = null;
        }
    }

    public function mount(Screening $screening): void
    {
        $this->screening = $screening->load(['venue.seats', 'movie', 'tickets']);
    }

    public function getSecondsUntilStartAttribute(): int
    {
        return max(0, now()->diffInSeconds($this->screening->starts_at, false));
    }

    public function getCheckedInSeatIdsAttribute(): array
    {
        return $this->screening->fresh()->tickets
            ->where('status', 'used')->pluck('seat_id')->filter()->toArray();
    }

    public function getCheckedInCountAttribute(): int
    {
        return count($this->checked_in_seat_ids);
    }

    public function getTotalAttribute(): int
    {
        return $this->screening->tickets->whereIn('status', ['valid', 'used'])->count();
    }

    public function render()
    {
        return view('livewire.cinema.entrance-screen')
            ->layout('layouts.infoscreen');
    }
}
