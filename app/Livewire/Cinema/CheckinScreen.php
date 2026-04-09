<?php

namespace App\Livewire\Cinema;

use App\Models\Screening;
use App\Models\Ticket;
use App\Models\Booking;
use App\Models\Seat;
use App\Models\Attendance;
use App\Services\CheckinBroadcastService;
use Livewire\Component;
use Livewire\Attributes\Polling;

class CheckinScreen extends Component
{
    public Screening $screening;
    public ?array $lastScan = null;
    public bool $showWelcome = false;

    // Abendkasse
    public bool $showBoxOffice = false;
    public string $boxName     = '';
    public ?int  $boxSeatId    = null;
    public string $boxEmail    = '';

    // Ticket-Edit
    public bool $showEdit      = false;
    public ?int $editTicketId  = null;
    public string $editName    = '';
    public ?int  $editSeatId   = null;
    public string $editStatus  = 'valid';

    // Spontan-Sitz
    public bool $showAddSeat   = false;
    public string $newSeatLabel = '';
    public string $newSeatRow   = '';
    public string $newSeatType  = 'standard';

    // Spontane Änderungen
    public bool $showEditScreening = false;
    public string $editMovieTitle  = '';
    public string $editStartsAt    = '';

    // Ticker
    public bool $showTickerForm = false;
    public string $tickerText   = '';

    public function mount(Screening $screening): void
    {
        $this->screening = $screening->load(['venue.seats', 'movie', 'tickets.seat', 'tickets.booking']);
    }

    // ── Scan ────────────────────────────────────────────────────────────────

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
                'status'  => 'warning',
                'message' => 'Bereits entwertet',
                'seat'    => $ticket->seat?->label,
                'name'    => $ticket->booking->customer_name,
                'used_at' => $ticket->scanned_at?->format('H:i'),
            ];
            $this->dispatch('scan-result', status: 'warning');
            return;
        }

        if ($ticket->status === 'cancelled') {
            $this->lastScan = ['status' => 'error', 'message' => 'Ticket storniert', 'code' => $code];
            $this->dispatch('scan-result', status: 'error');
            return;
        }

        $ticket->markAsUsed(auth()->user()?->name ?? 'Einlass');

        Attendance::firstOrCreate(
            ['screening_id' => $this->screening->id, 'ticket_id' => $ticket->id],
            ['seat_id' => $ticket->seat_id, 'guest_name' => $ticket->booking->customer_name, 'checked_in_at' => now()]
        );

        $this->lastScan = [
            'status'  => 'success',
            'name'    => $ticket->booking->customer_name,
            'seat'    => $ticket->seat?->label,
            'seat_id' => $ticket->seat_id,
            'code'    => $code,
        ];
        $this->showWelcome = true;

        app(CheckinBroadcastService::class)->broadcast($ticket);
        $this->dispatch('scan-result', status: 'success');
        $this->dispatch('ring-bell');
    }

    // Admin-Selbst-Check-in (kein Barcode nötig)
    public function checkInManually(int $ticketId): void
    {
        $ticket = Ticket::with(['seat','booking'])->find($ticketId);
        if (!$ticket || $ticket->status === 'used') return;
        $this->handleScan($ticket->ticket_code);
    }

    public function dismissWelcome(): void
    {
        $this->showWelcome = false;
        $this->lastScan = null;
    }

    // ── Abendkasse ──────────────────────────────────────────────────────────

    public function openBoxOffice(): void
    {
        $this->reset(['boxName','boxSeatId','boxEmail']);
        $this->showBoxOffice = true;
        $this->showEdit = false;
    }

    public function createWalkIn(): void
    {
        $this->validate(['boxName' => 'required|min:2']);

        $booking = Booking::create([
            'screening_id'   => $this->screening->id,
            'customer_name'  => trim($this->boxName),
            'customer_email' => $this->boxEmail ?: null,
            'payment_status' => 'free',
            'status'         => 'active',
            'total_amount'   => 0,
        ]);

        $ticket = Ticket::create([
            'booking_id'   => $booking->id,
            'screening_id' => $this->screening->id,
            'seat_id'      => $this->boxSeatId ?: null,
            'price'        => 0,
            'status'       => 'valid',
        ]);

        $this->screening->refresh()->load(['venue.seats', 'tickets.seat', 'tickets.booking']);
        $this->showBoxOffice = false;
        $this->dispatch('ticket-created', code: $ticket->ticket_code, url: route('ticket.show', $ticket->ticket_code));
    }

    // ── Ticket bearbeiten ────────────────────────────────────────────────────

    public function openEdit(int $ticketId): void
    {
        $t = Ticket::with(['booking','seat'])->find($ticketId);
        if (!$t) return;

        $this->editTicketId = $ticketId;
        $this->editName     = $t->booking->customer_name;
        $this->editSeatId   = $t->seat_id;
        $this->editStatus   = $t->status;
        $this->showEdit     = true;
        $this->showBoxOffice = false;
    }

    public function saveTicketEdit(): void
    {
        $this->validate(['editName' => 'required|min:2']);

        $ticket = Ticket::with('booking')->find($this->editTicketId);
        if (!$ticket) return;

        $ticket->booking->update(['customer_name' => trim($this->editName)]);
        $ticket->update([
            'seat_id' => $this->editSeatId ?: null,
            'status'  => $this->editStatus,
        ]);

        $this->screening->refresh()->load(['venue.seats', 'tickets.seat', 'tickets.booking']);
        $this->showEdit = false;
    }

    public function cancelTicket(int $ticketId): void
    {
        Ticket::find($ticketId)?->update(['status' => 'cancelled']);
        $this->screening->refresh()->load(['venue.seats', 'tickets.seat', 'tickets.booking']);
    }

    // ── Spontan-Sitz hinzufügen ─────────────────────────────────────────────

    public function openAddSeat(): void
    {
        $this->reset(['newSeatLabel','newSeatRow','newSeatType']);
        $this->showAddSeat = true;
    }

    public function saveNewSeat(): void
    {
        $this->validate(['newSeatLabel' => 'required|min:1']);

        $maxSort = $this->screening->venue->seats()->max('sort_order') ?? 0;

        Seat::create([
            'venue_id'       => $this->screening->venue_id,
            'label'          => trim($this->newSeatLabel),
            'row'            => $this->newSeatRow ?: 'Extra',
            'position'       => 99,
            'type'           => $this->newSeatType,
            'price_modifier' => 1.00,
            'is_active'      => true,
            'sort_order'     => $maxSort + 10,
        ]);

        $this->screening->refresh()->load(['venue.seats', 'tickets.seat', 'tickets.booking']);
        $this->showAddSeat = false;
    }

    // ── Screening schnell ändern ────────────────────────────────────────────

    public function openEditScreening(): void
    {
        $this->editMovieTitle = $this->screening->movie?->title ?? '';
        $this->editStartsAt   = $this->screening->starts_at->format('Y-m-d\TH:i');
        $this->showEditScreening = true;
    }

    public function saveScreeningEdit(): void
    {
        $this->validate([
            'editMovieTitle' => 'required|min:2',
            'editStartsAt'   => 'required|date',
        ]);

        if ($this->screening->movie) {
            $this->screening->movie->update(['title' => $this->editMovieTitle]);
        }
        $this->screening->update(['starts_at' => $this->editStartsAt]);
        $this->screening->refresh()->load(['venue.seats', 'movie', 'tickets.seat', 'tickets.booking']);
        $this->showEditScreening = false;
    }

    // ── Ticker ──────────────────────────────────────────────────────────────

    public function sendTicker(): void
    {
        if (empty(trim($this->tickerText))) return;

        \Illuminate\Support\Facades\Cache::put(
            "ticker_{$this->screening->id}",
            ['text' => trim($this->tickerText), 'at' => now()->toIso8601String()],
            now()->addMinutes(30)
        );

        $this->tickerText = '';
        $this->showTickerForm = false;
        $this->dispatch('ticker-sent');
    }

    public function clearTicker(): void
    {
        \Illuminate\Support\Facades\Cache::forget("ticker_{$this->screening->id}");
    }

    // ── Hilfs-Getter ────────────────────────────────────────────────────────

    public function getCheckedInCountAttribute(): int
    {
        return $this->screening->fresh()->tickets->where('status', 'used')->count();
    }

    public function getTotalTicketsAttribute(): int
    {
        return $this->screening->tickets->whereIn('status', ['valid','used'])->count();
    }

    public function getCheckedInSeatIdsAttribute(): array
    {
        return $this->screening->fresh()->tickets
            ->where('status', 'used')->pluck('seat_id')->filter()->toArray();
    }

    public function getAllDoneAttribute(): bool
    {
        return $this->total_tickets > 0 && $this->checked_in_count === $this->total_tickets;
    }

    public function getPendingTicketsAttribute()
    {
        return $this->screening->fresh()->load('tickets.seat','tickets.booking')
            ->tickets->where('status', 'valid')->values();
    }

    public function render()
    {
        return view('livewire.cinema.checkin-screen')
            ->layout('layouts.checkin');
    }
}
