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
    public bool $scanMode = false;
    public int $scanModeUntil = 0;
    public string $screeningState = 'countdown'; // countdown | ready | playing
    public int $lastStateSeq = 0;
    public int $lastGongSeq = 0;

    // Polling alle 800ms — holt neue Scans aus Cache
    #[Polling(800)]
    public function poll(): void
    {
        $broadcast = app(\App\Services\CheckinBroadcastService::class);

        // State-Änderung
        $stateSeq = $broadcast->getStateSeq($this->screening->id);
        if ($stateSeq > $this->lastStateSeq) {
            $this->screeningState = $broadcast->getState($this->screening->id);
            $this->lastStateSeq   = $stateSeq;
            $this->dispatch('state-changed', state: $this->screeningState);
        }

        // Gong-Trigger
        $gong = $broadcast->getGongTrigger($this->screening->id);
        if ($gong && $gong['seq'] > $this->lastGongSeq) {
            $this->lastGongSeq = $gong['seq'];
            $this->dispatch('play-gong', count: $gong['count']);
        }

        if ($broadcast->isNewScan($this->screening->id, $this->lastSeenSeq)) {
            $scan = $broadcast->getLatestScan($this->screening->id);
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
        $broadcast = app(\App\Services\CheckinBroadcastService::class);
        $this->screeningState = $broadcast->getState($screening->id);
        $this->lastStateSeq   = $broadcast->getStateSeq($screening->id);
    }

    private function computeStats(): array
    {
        $fresh = $this->screening->fresh(['tickets', 'venue.seats', 'movie']);
        $checkedInSeats = $fresh->tickets->where('status', 'used')->pluck('seat_id')->filter()->toArray();
        $checkedInCount = count($checkedInSeats);
        $total          = $fresh->tickets->whereIn('status', ['valid', 'used'])->count();
        $secondsUntil   = max(0, now()->diffInSeconds($fresh->starts_at, false));

        return compact('fresh', 'checkedInSeats', 'checkedInCount', 'total', 'secondsUntil');
    }

    public function render()
    {
        $stats = $this->computeStats();

        return view('livewire.cinema.entrance-screen', [
            'screening'           => $stats['fresh'],
            'checked_in_seat_ids' => $stats['checkedInSeats'],
            'checked_in_count'    => $stats['checkedInCount'],
            'total'               => $stats['total'],
            'seconds_until_start' => $stats['secondsUntil'],
        ])->layout('layouts.infoscreen');
    }
}
