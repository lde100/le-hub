<?php

namespace App\Livewire\Admin;

use App\Models\Movie;
use App\Models\Screening;
use App\Models\Venue;
use App\Models\Booking;
use App\Models\Ticket;
use Livewire\Component;

class CinemaIndex extends Component
{
    // Screening-Formular
    public bool $showForm     = false;
    public bool $showTicketMgr = false;
    public ?int $editScreeningId = null;

    public string $movieTitle    = '';
    public string $movieGenre    = '';
    public string $movieDuration = '';
    public string $movieRating   = '';
    public string $startsAt      = '';
    public float  $basePrice     = 0.00;
    public string $seatingMode   = 'seated';
    public ?int   $maxCapacity   = null;
    public string $screeningNotes = '';

    // Ticket-Manager
    public ?int $activeScreeningId = null;
    public array $manualGuests = [];   // [{name, seatId}]

    public function openForm(?int $screeningId = null): void
    {
        $this->reset(['movieTitle','movieGenre','movieDuration','movieRating',
                       'startsAt','basePrice','seatingMode','maxCapacity','screeningNotes']);
        $this->editScreeningId = $screeningId;

        if ($screeningId) {
            $s = Screening::with('movie')->find($screeningId);
            $this->movieTitle    = $s->movie?->title ?? '';
            $this->movieGenre    = $s->movie?->genre ?? '';
            $this->movieDuration = $s->movie?->duration_minutes ?? '';
            $this->movieRating   = $s->movie?->rating ?? '';
            $this->startsAt      = $s->starts_at->format('Y-m-d\TH:i');
            $this->basePrice     = $s->base_price;
            $this->seatingMode   = $s->seating_mode;
            $this->maxCapacity   = $s->max_capacity;
            $this->screeningNotes = $s->notes ?? '';
        } else {
            $this->startsAt = now()->addDays(7)->format('Y-m-d\TH:i');
        }

        $this->showForm = true;
    }

    public function saveScreening(): void
    {
        $this->validate([
            'movieTitle' => 'required|min:2',
            'startsAt'   => 'required|date',
        ]);

        $movie = Movie::firstOrCreate(
            ['title' => $this->movieTitle],
            [
                'genre'            => $this->movieGenre ?: null,
                'duration_minutes' => $this->movieDuration ?: null,
                'rating'           => $this->movieRating ?: null,
                'is_active'        => true,
            ]
        );

        $venue = Venue::where('is_active', true)->first();

        $data = [
            'movie_id'      => $movie->id,
            'venue_id'      => $venue?->id,
            'starts_at'     => $this->startsAt,
            'base_price'    => $this->basePrice,
            'status'        => 'scheduled',
            'seating_mode'  => $this->seatingMode,
            'max_capacity'  => $this->seatingMode !== 'seated' ? $this->maxCapacity : null,
            'notes'         => $this->screeningNotes ?: null,
        ];

        if ($this->editScreeningId) {
            Screening::find($this->editScreeningId)->update($data);
        } else {
            Screening::create($data);
        }

        $this->showForm = false;
        $this->dispatch('screening-saved');
    }

    public function openTicketManager(int $screeningId): void
    {
        $this->activeScreeningId = $screeningId;
        $this->manualGuests      = [];
        $this->showTicketMgr     = true;
    }

    public function addGuestRow(): void
    {
        $this->manualGuests[] = ['name' => '', 'seat_id' => '', 'email' => ''];
    }

    public function removeGuestRow(int $index): void
    {
        array_splice($this->manualGuests, $index, 1);
    }

    /**
     * Tickets für alle eingetragenen Gäste generieren.
     * Erstellt Booking + Ticket, gibt Links zurück.
     */
    public function generateTickets(): void
    {
        $screening = Screening::find($this->activeScreeningId);

        foreach ($this->manualGuests as &$guest) {
            if (empty(trim($guest['name']))) continue;

            $booking = Booking::create([
                'screening_id'   => $screening->id,
                'customer_name'  => trim($guest['name']),
                'customer_email' => $guest['email'] ?: null,
                'payment_status' => $screening->base_price > 0 ? 'pending' : 'free',
                'status'         => 'active',
                'total_amount'   => $screening->base_price,
            ]);

            $ticket = Ticket::create([
                'booking_id'   => $booking->id,
                'screening_id' => $screening->id,
                'seat_id'      => $guest['seat_id'] ?: null,
                'price'        => $screening->base_price,
                'status'       => 'valid',
            ]);

            $guest['ticket_url']  = route('ticket.show', $ticket->ticket_code);
            $guest['ticket_code'] = $ticket->ticket_code;
            $guest['generated']   = true;
        }

        $this->dispatch('tickets-generated');
    }

    public function updateStatus(int $screeningId, string $status): void
    {
        Screening::find($screeningId)?->update(['status' => $status]);
    }

    public function deleteScreening(int $screeningId): void
    {
        Screening::find($screeningId)?->delete();
    }

    public function render()
    {
        return view('livewire.admin.cinema-index', [
            'screenings' => Screening::with(['movie', 'venue', 'tickets'])
                ->orderBy('starts_at', 'desc')
                ->get(),
            'venues' => Venue::where('is_active', true)->get(),
        ])->layout('layouts.app', ['title' => 'Vorstellungen']);
    }
}
