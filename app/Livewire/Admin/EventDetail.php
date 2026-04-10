<?php

namespace App\Livewire\Admin;

use App\Models\Event;
use App\Models\EventPoll;
use App\Models\EventSlot;
use App\Models\PollOption;
use App\Models\Screening;
use App\Models\Movie;
use App\Models\Booking;
use App\Models\Ticket;
use App\Models\SeatRequest;
use Livewire\Component;

class EventDetail extends Component
{
    public Event $event;

    // Direktes Datum + Film setzen (ohne Poll)
    public bool   $showDirectDate   = false;
    public string $directDate       = '';
    public string $directTime       = '20:00';
    public string $directFilmTitle  = '';
    public string $directFilmYear   = '';

    // Date Poll
    public bool   $showDatePollForm = false;
    public string $datePollTitle    = 'Wann passt es euch?';
    public array  $dateOptions      = [];   // [{date, time}]

    // Film Poll
    public bool   $showFilmPollForm  = false;
    public string $filmPollTitle     = 'Welchen Film wollt ihr sehen?';
    public bool   $allowWishes       = true;
    public array  $filmOptions       = [];  // [{title, year}]

    // Sitzplatz-Anfragen
    public bool $showRequests = false;

    // Ticket-Generierung
    public ?int $confirmingRequestId = null;
    public ?int $assignSeatId        = null;

    public function mount(Event $event): void
    {
        $this->event = $event->load([
            'polls.options.votes',
            'polls.votes',
            'venue.seats',
            'seatRequests',
            'screenings.movie',
            'slots',
        ]);
        $this->dateOptions = [['date' => now()->addDays(7)->format('Y-m-d'), 'time' => '20:00']];
        $this->filmOptions = [['title' => '', 'year' => '']];
    }

    // ── Status-Übergänge ───────────────────────────────────────────────────

    public function advanceTo(string $status): void
    {
        $this->event->update(['status' => $status]);
        $this->event->refresh()->load(['polls.options.votes','polls.votes','venue.seats','seatRequests','screenings.movie','slots']);
    }

    // ── Termin-Umfrage ─────────────────────────────────────────────────────

    public function addDateOption(): void
    {
        $this->dateOptions[] = ['date' => now()->addDays(14)->format('Y-m-d'), 'time' => '20:00'];
    }

    public function removeDateOption(int $i): void
    {
        array_splice($this->dateOptions, $i, 1);
    }

    public function createDatePoll(): void
    {
        $this->validate([
            'datePollTitle'     => 'required',
            'dateOptions.*.date' => 'required|date',
        ]);

        // Bestehende Date-Poll schließen falls vorhanden
        $this->event->polls()->where('type', 'date_selection')->update(['status' => 'closed']);

        $poll = $this->event->polls()->create([
            'type'             => 'date_selection',
            'title'            => $this->datePollTitle,
            'status'           => 'open',
            'vote_mode'        => 'multi',
            'allow_new_options'=> false,
        ]);

        foreach ($this->dateOptions as $i => $opt) {
            $poll->options()->create([
                'type'       => 'date',
                'label'      => \Carbon\Carbon::parse("{$opt['date']} {$opt['time']}")->isoFormat('dddd, D. MMMM · HH:mm [Uhr]'),
                'date_value' => "{$opt['date']} {$opt['time']}:00",
                'sort_order' => $i,
            ]);
        }

        $this->event->update(['status' => 'polling_date']);
        $this->showDatePollForm = false;
        $this->reload();
    }

    public function closeDatePoll(int $pollId): void
    {
        EventPoll::find($pollId)?->update(['status' => 'closed']);
        $this->reload();
    }

    public function confirmDate(int $optionId): void
    {
        $option = PollOption::find($optionId);
        if (!$option) return;

        $option->update(['is_winner' => true]);

        // Slot anlegen (Screening wird erst bei Film-Bestätigung erstellt)
        EventSlot::updateOrCreate(
            ['event_id' => $this->event->id, 'is_confirmed' => true],
            ['proposed_at' => $option->date_value, 'is_confirmed' => true]
        );

        $this->event->polls()->where('type', 'date_selection')->update(['status' => 'confirmed']);
        $this->event->update(['status' => 'polling_film']);
        $this->reload();
    }

    // ── Film-Abstimmung ────────────────────────────────────────────────────

    public function addFilmOption(): void
    {
        $this->filmOptions[] = ['title' => '', 'year' => ''];
    }

    public function removeFilmOption(int $i): void
    {
        array_splice($this->filmOptions, $i, 1);
    }

    public function createFilmPoll(): void
    {
        $this->validate(['filmPollTitle' => 'required']);

        $this->event->polls()->where('type', 'film_vote')->update(['status' => 'closed']);

        $poll = $this->event->polls()->create([
            'type'              => 'film_vote',
            'title'             => $this->filmPollTitle,
            'status'            => 'open',
            'vote_mode'         => 'multi',
            'allow_new_options' => $this->allowWishes,
        ]);

        foreach ($this->filmOptions as $i => $opt) {
            if (empty(trim($opt['title']))) continue;
            $poll->options()->create([
                'type'         => 'movie_custom',
                'label'        => $opt['title'],
                'movie_title'  => $opt['title'],
                'movie_year'   => $opt['year'] ?: null,
                'sort_order'   => $i,
            ]);
        }

        $this->showFilmPollForm = false;
        $this->reload();
    }

    public function closeFilmPoll(int $pollId): void
    {
        EventPoll::find($pollId)?->update(['status' => 'closed']);
        $this->reload();
    }

    public function confirmFilm(int $optionId): void
    {
        $option = PollOption::find($optionId);
        if (!$option) return;

        $option->update(['is_winner' => true]);

        // Movie anlegen/finden
        $movie = Movie::firstOrCreate(
            ['title' => $option->movie_title],
            [
                'genre'            => $option->movie_genre,
                'duration_minutes' => $option->movie_duration,
                'rating'           => $option->movie_rating,
                'is_active'        => true,
            ]
        );

        // Bestätigten Slot holen
        $slot = EventSlot::where('event_id', $this->event->id)
            ->where('is_confirmed', true)->first();

        // Screening erstellen
        $screening = Screening::create([
            'event_id'     => $this->event->id,
            'venue_id'     => $this->event->venue_id,
            'movie_id'     => $movie->id,
            'starts_at'    => $slot?->proposed_at ?? now()->addDays(7),
            'seating_mode' => $this->event->seating_mode,
            'status'       => 'scheduled',
            'base_price'   => 0,
        ]);

        $slot?->update(['screening_id' => $screening->id]);

        $this->event->polls()->where('type', 'film_vote')->update(['status' => 'confirmed']);
        $this->event->update(['status' => 'booking_open']);
        $this->reload();
    }

    public function setDirectDate(): void
    {
        $this->validate(['directDate' => 'required|date']);

        EventSlot::updateOrCreate(
            ['event_id' => $this->event->id, 'is_confirmed' => true],
            [
                'proposed_at'  => $this->directDate . ' ' . $this->directTime . ':00',
                'is_confirmed' => true,
            ]
        );

        $this->showDirectDate = false;
        $this->event->update(['status' => 'polling_film']);
        $this->reload();
    }

    public function setAdminFilm(): void
    {
        $this->validate(['directFilmTitle' => 'required|min:2']);
        $title = $this->directFilmTitle;
        $movie = Movie::firstOrCreate(['title' => $title], ['is_active' => true]);

        $slot = EventSlot::where('event_id', $this->event->id)
            ->where('is_confirmed', true)->first();

        if (!$slot) {
            // Kein Termin aus Poll — einfach jetzt + 7 Tage
            $slot = EventSlot::create([
                'event_id'     => $this->event->id,
                'proposed_at'  => now()->addDays(7)->setTime(20, 0),
                'is_confirmed' => true,
            ]);
        }

        $screening = Screening::firstOrCreate(
            ['event_id' => $this->event->id],
            [
                'venue_id'     => $this->event->venue_id,
                'movie_id'     => $movie->id,
                'starts_at'    => $slot->proposed_at,
                'seating_mode' => $this->event->seating_mode,
                'status'       => 'scheduled',
                'base_price'   => 0,
            ]
        );

        $slot->update(['screening_id' => $screening->id]);
        $this->directFilmTitle = '';
        $this->directFilmYear  = '';
        $this->event->update(['status' => 'booking_open']);
        $this->reload();
    }

    // ── Sitzplatz-Anfragen ─────────────────────────────────────────────────

    public function openConfirmRequest(int $requestId): void
    {
        $req = SeatRequest::with('guest')->find($requestId);
        $this->confirmingRequestId = $requestId;
        $this->assignSeatId = $req?->requested_seat_ids[0] ?? null;
    }

    public function confirmRequest(): void
    {
        $req = SeatRequest::with(['guest'])->find($this->confirmingRequestId);
        if (!$req) return;

        $screening = $this->event->screenings()->first();
        if (!$screening) return;

        // Booking + Ticket
        $booking = Booking::create([
            'screening_id'   => $screening->id,
            'customer_name'  => $req->guest_name,
            'customer_email' => $req->guest_email,
            'payment_status' => 'free',
            'status'         => 'active',
            'total_amount'   => 0,
        ]);

        $ticket = Ticket::create([
            'booking_id'   => $booking->id,
            'screening_id' => $screening->id,
            'seat_id'      => $this->assignSeatId ?: null,
            'price'        => 0,
            'status'       => 'valid',
        ]);

        $req->update([
            'status'           => 'confirmed',
            'assigned_seat_id' => $this->assignSeatId ?: null,
            'booking_id'       => $booking->id,
        ]);

        // Loyalitätspunkte — Platzhalter für später
        // if ($req->guest) $req->guest->earnPoints(10, 'visit', '...', $booking);

        $this->confirmingRequestId = null;
        $this->assignSeatId = null;
        $this->reload();
    }

    public function declineRequest(int $requestId): void
    {
        SeatRequest::find($requestId)?->update(['status' => 'declined']);
        $this->reload();
    }

    public function confirmAllRequests(): void
    {
        $screening = $this->event->screenings()->first();
        if (!$screening) return;

        foreach ($this->event->seatRequests->where('status', 'pending') as $req) {
            $booking = Booking::create([
                'screening_id'   => $screening->id,
                'customer_name'  => $req->guest_name,
                'customer_email' => $req->guest_email,
                'payment_status' => 'free',
                'status'         => 'active',
                'total_amount'   => 0,
            ]);

            $preferredSeat = collect($req->requested_seat_ids ?? [])->first();

            Ticket::create([
                'booking_id'   => $booking->id,
                'screening_id' => $screening->id,
                'seat_id'      => $preferredSeat ?: null,
                'price'        => 0,
                'status'       => 'valid',
            ]);

            $req->update(['status' => 'confirmed', 'booking_id' => $booking->id]);
        }

        $this->event->update(['status' => 'confirmed']);
        $this->reload();
    }

    private function reload(): void
    {
        $this->event = $this->event->fresh([
            'polls.options.votes', 'polls.votes',
            'venue.seats', 'seatRequests', 'screenings.movie', 'slots',
        ]);
    }

    public function render()
    {
        return view('livewire.admin.event-detail')
            ->layout('layouts.app', ['title' => $this->event->title]);
    }
}
