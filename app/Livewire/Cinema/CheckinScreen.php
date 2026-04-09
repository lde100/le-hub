<?php

namespace App\Livewire\Cinema;

use App\Models\Screening;
use App\Models\Ticket;
use App\Models\Attendance;
use Livewire\Component;
use Livewire\Attributes\On;

class CheckinScreen extends Component
{
    public Screening $screening;
    public ?array $lastScan = null;
    public bool $showWelcome = false;

    public function mount(Screening $screening): void
    {
        $this->screening = $screening->load(['venue.seats', 'movie', 'tickets.seat', 'tickets.booking']);
    }

    public function handleScan(string $code): void
    {
        $ticket = Ticket::with(['seat', 'booking', 'screening.movie'])
            ->where('ticket_code', $code)
            ->where('screening_id', $this->screening->id)
            ->first();

        if (!$ticket) {
            $this->lastScan = ['status' => 'error', 'message' => 'Unbekanntes Ticket', 'code' => $code];
            $this->showWelcome = false;
            $this->dispatch('scan-result', status: 'error');
            return;
        }

        if ($ticket->status === 'used') {
            $this->lastScan = [
                'status'   => 'warning',
                'message'  => 'Bereits entwertet',
                'seat'     => $ticket->seat?->label,
                'name'     => $ticket->booking->customer_name,
                'used_at'  => $ticket->scanned_at?->format('H:i'),
            ];
            $this->dispatch('scan-result', status: 'warning');
            return;
        }

        // Entwerten
        $ticket->markAsUsed(auth()->user()?->name ?? 'Einlass');

        // Attendance erfassen
        Attendance::firstOrCreate(
            ['screening_id' => $this->screening->id, 'guest_id' => null, 'ticket_id' => $ticket->id],
            [
                'seat_id'      => $ticket->seat_id,
                'guest_name'   => $ticket->booking->customer_name,
                'checked_in_at' => now(),
            ]
        );

        $this->lastScan = [
            'status'  => 'success',
            'name'    => $ticket->booking->customer_name,
            'seat'    => $ticket->seat?->label,
            'seat_id' => $ticket->seat_id,
            'code'    => $code,
        ];
        $this->showWelcome = true;

        // Infoscreen-Event broadcasten (für "Jetzt auf Platz"-Overlay)
        $this->dispatch('guest-checked-in',
            name: $ticket->booking->customer_name,
            seat: $ticket->seat?->label
        );

        $this->dispatch('scan-result', status: 'success');
        $this->dispatch('ring-bell'); // Theater-Glocke
    }

    public function dismissWelcome(): void
    {
        $this->showWelcome = false;
        $this->lastScan = null;
    }

    public function getCheckedInCountAttribute(): int
    {
        return $this->screening->tickets->where('status', 'used')->count();
    }

    public function getTotalTicketsAttribute(): int
    {
        return $this->screening->tickets->whereIn('status', ['valid', 'used'])->count();
    }

    public function getCheckedInSeatIdsAttribute(): array
    {
        return $this->screening->tickets
            ->where('status', 'used')
            ->pluck('seat_id')
            ->filter()
            ->toArray();
    }

    public function getAllDoneAttribute(): bool
    {
        return $this->total_tickets > 0 && $this->checked_in_count === $this->total_tickets;
    }

    public function render()
    {
        // Daten frisch laden damit Sitzplan aktuell ist
        $this->screening->load('tickets');

        return view('livewire.cinema.checkin-screen')
            ->layout('layouts.checkin');
    }
}
