<?php

namespace App\Livewire\Cinema;

use App\Models\Screening;
use App\Models\Seat;
use App\Models\Ticket;
use Livewire\Component;

class SeatMap extends Component
{
    public Screening $screening;
    public array $selectedSeats = [];
    public bool $editMode = false;   // Admin: Sitze ein/ausblenden

    public function mount(Screening $screening): void
    {
        $this->screening = $screening;
    }

    public function toggleSeat(int $seatId): void
    {
        if ($this->editMode) {
            // Admin: Sitz für diese Vorstellung aktivieren/deaktivieren
            // (gespeichert in screening_seat_overrides — späterer Schritt)
            return;
        }

        $seat = Seat::find($seatId);
        if (!$seat || $this->isSeatTaken($seatId)) return;

        if (in_array($seatId, $this->selectedSeats)) {
            $this->selectedSeats = array_filter(
                $this->selectedSeats, fn($id) => $id !== $seatId
            );
        } else {
            $this->selectedSeats[] = $seatId;
        }
    }

    public function isSeatTaken(int $seatId): bool
    {
        return Ticket::where('screening_id', $this->screening->id)
            ->where('seat_id', $seatId)
            ->whereIn('status', ['valid', 'used'])
            ->exists();
    }

    public function getSeatStatusClass(int $seatId): string
    {
        if ($this->isSeatTaken($seatId))           return 'seat--taken';
        if (in_array($seatId, $this->selectedSeats)) return 'seat--selected';
        return 'seat--free';
    }

    public function confirmSelection(): void
    {
        if (empty($this->selectedSeats)) return;

        $this->dispatch('seats-selected', seatIds: $this->selectedSeats);
    }

    public function render()
    {
        $seats = $this->screening->venue->seats()
            ->where('is_active', true)
            ->get()
            ->groupBy('row');

        return view('livewire.cinema.seat-map', [
            'seatsByRow' => $seats,
            'totalPrice' => $this->calculateTotal(),
        ]);
    }

    private function calculateTotal(): float
    {
        if (empty($this->selectedSeats)) return 0.0;

        return Seat::whereIn('id', $this->selectedSeats)
            ->get()
            ->sum(fn($s) => $this->screening->base_price * $s->price_modifier);
    }
}
