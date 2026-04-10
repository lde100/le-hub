<?php

namespace App\Livewire\Event;

use App\Models\Event;
use App\Models\Guest;
use App\Models\PollVote;
use App\Models\SeatRequest;
use Livewire\Component;

/**
 * Öffentliche Event-Seite (kein Login nötig)
 * URL: /event/{token}?token={magic_token}
 *
 * Lifecycle-Phasen die diese Komponente rendert:
 *  1. polling_date  → Terminabstimmung
 *  2. polling_film  → Film-Voting / Filmwünsche
 *  3. booking_open  → Sitzplatz anfragen
 *  4. confirmed     → Ticket ansehen
 *  5. finished      → Danke + Stats
 */
class PublicEventPage extends Component
{
    public Event $event;
    public ?Guest $guest = null;

    // Gast-Identifikation
    public string $guestName  = '';
    public string $guestEmail = '';
    public bool $showNameForm = false;
    public string $step       = 'identify'; // identify → interact

    // Voting
    public array $selectedOptionIds = [];

    // Film-Wunsch (custom)
    public string $wishTitle    = '';
    public string $wishYear     = '';
    public bool $showWishForm   = false;

    // Seat Request
    public array $requestedSeatIds = [];
    public string $seatNotes       = '';
    public bool $seatRequestSent   = false;

    public function mount(string $token): void
    {
        $this->event = Event::with([
            'polls.options.votes',
            'polls.votes',
            'venue.seats',
            'seatRequests',
            'screenings.movie',
        ])->where('public_token', $token)->firstOrFail();

        // Magic Token → Gast wiedererkennen
        $magicToken = request('token');
        if ($magicToken) {
            $this->guest = Guest::findByToken($magicToken);
            if ($this->guest) {
                $this->guestName  = $this->guest->name;
                $this->guestEmail = $this->guest->email ?? '';
                $this->step = 'interact';
                $this->loadExistingVotes();
            }
        }

        if (!$this->guest) {
            $this->showNameForm = true;
        }
    }

    public function identify(): void
    {
        $this->validate([
            'guestName'  => 'required|min:2|max:80',
            'guestEmail' => 'nullable|email|max:120',
        ]);

        // Bestehenden Gast per E-Mail finden oder neu anlegen
        if ($this->guestEmail) {
            $this->guest = Guest::firstOrCreate(
                ['email' => $this->guestEmail],
                ['name'  => $this->guestName]
            );
            // Name aktualisieren falls geändert
            $this->guest->update(['name' => $this->guestName]);
        } else {
            // Kein E-Mail = anonymer Gast (kein Loyalty)
            $this->guest = Guest::create([
                'name'        => $this->guestName,
                'magic_token' => null, // kein persistenter Token
            ]);
        }

        $this->step = 'interact';
        $this->showNameForm = false;
        $this->loadExistingVotes();
    }

    private function loadExistingVotes(): void
    {
        if (!$this->guest) return;
        // selectedOptionIds: option_id => vote_value (für Termin: 'yes'/'maybe'/'no', für Film: 'like')
        $votes = PollVote::where('guest_id', $this->guest->id)
            ->pluck('vote_value', 'option_id')
            ->toArray();
        $this->selectedOptionIds = $votes;
    }

    // ── Termin-Abstimmung ─────────────────────────────────────────────────

    public function voteDate(int $optionId, string $value): void
    {
        $this->requireGuest();
        $poll = $this->event->activeDatePoll;
        if (!$poll) return;

        $existing = PollVote::where([
            'poll_id'   => $poll->id,
            'option_id' => $optionId,
            'guest_id'  => $this->guest->id,
        ])->first();

        // Nochmal gleichen Button tippen → Stimme entfernen
        if ($existing && $existing->vote_value === $value) {
            $existing->delete();
        } else {
            PollVote::updateOrCreate(
                ['poll_id' => $poll->id, 'option_id' => $optionId, 'guest_id' => $this->guest->id],
                ['guest_name' => $this->guest->name, 'vote_value' => $value]
            );
        }
        $this->loadExistingVotes();
    }

    // ── Film-Voting ───────────────────────────────────────────────────────

    public function toggleFilmLike(int $optionId): void
    {
        $this->requireGuest();
        $poll = $this->event->activeFilmPoll;
        if (!$poll) return;

        $existing = PollVote::where([
            'poll_id'   => $poll->id,
            'option_id' => $optionId,
            'guest_id'  => $this->guest->id,
        ])->first();

        if ($existing) {
            $existing->delete();
            $this->selectedOptionIds = array_filter($this->selectedOptionIds, fn($id) => $id !== $optionId);
        } else {
            PollVote::create([
                'poll_id'    => $poll->id,
                'option_id'  => $optionId,
                'guest_id'   => $this->guest->id,
                'guest_name' => $this->guest->name,
                'vote_value' => 'like',
            ]);
            $this->selectedOptionIds[] = $optionId;
        }
    }

    public function submitFilmWish(): void
    {
        $this->requireGuest();
        $this->validate(['wishTitle' => 'required|min:2|max:200']);

        $poll = $this->event->activeFilmPoll;
        if (!$poll || !$poll->allow_new_options) return;

        $option = $poll->options()->create([
            'type'                  => 'movie_custom',
            'label'                 => $this->wishTitle,
            'movie_title'           => $this->wishTitle,
            'movie_year'            => $this->wishYear ?: null,
            'sort_order'            => 99,
            'suggested_by_guest_id' => $this->guest->id,
        ]);

        // Direkt selbst liken
        PollVote::create([
            'poll_id'    => $poll->id,
            'option_id'  => $option->id,
            'guest_id'   => $this->guest->id,
            'guest_name' => $this->guest->name,
            'vote_value' => 'like',
        ]);

        $this->wishTitle  = '';
        $this->wishYear   = '';
        $this->showWishForm = false;
        $this->loadExistingVotes();
    }

    // ── Sitzplatz-Anfrage ─────────────────────────────────────────────────

    public function toggleSeatRequest(int $seatId): void
    {
        if (in_array($seatId, $this->requestedSeatIds)) {
            $this->requestedSeatIds = array_filter($this->requestedSeatIds, fn($id) => $id !== $seatId);
        } else {
            $this->requestedSeatIds[] = $seatId;
        }
    }

    public function submitSeatRequest(): void
    {
        $this->requireGuest();

        SeatRequest::updateOrCreate(
            ['event_id' => $this->event->id, 'guest_id' => $this->guest->id],
            [
                'guest_name'         => $this->guest->name,
                'guest_email'        => $this->guest->email,
                'requested_seat_ids' => array_values($this->requestedSeatIds),
                'notes'              => $this->seatNotes,
                'status'             => 'pending',
            ]
        );

        $this->seatRequestSent = true;
    }

    private function requireGuest(): void
    {
        if (!$this->guest) {
            $this->showNameForm = true;
            $this->step = 'identify';
        }
    }

    public function getSeatStatusForGuestAttribute(): ?string
    {
        if (!$this->guest) return null;
        $request = $this->event->seatRequests->where('guest_id', $this->guest->id)->first();
        return $request?->status;
    }

    public function render()
    {
        return view('livewire.event.public-page')
            ->layout('layouts.public');
    }
}
