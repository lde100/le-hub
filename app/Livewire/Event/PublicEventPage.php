<?php

namespace App\Livewire\Event;

use App\Models\Event;
use App\Models\Guest;
use App\Models\PollVote;
use App\Models\SeatRequest;
use Illuminate\Support\Facades\Hash;
use Livewire\Component;

class PublicEventPage extends Component
{
    public Event $event;
    public ?Guest $guest = null;

    // Auth-State
    public string $step = 'identify'; // identify | set_pin | interact
    public string $authMode = 'login'; // login | register

    // Formular-Felder
    public string $guestName  = '';
    public string $guestPin   = '';
    public string $guestPin2  = ''; // Bestätigung bei Registrierung
    public string $authError  = '';

    // Voting
    public array $selectedOptionIds = []; // [option_id => vote_value]

    // Film-Wunsch
    public string $wishTitle   = '';
    public string $wishYear    = '';
    public bool $showWishForm  = false;

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

        // Session-Token: nach Reload automatisch einloggen
        $sessionToken = session('le_guest_token_' . $this->event->public_token);
        if ($sessionToken) {
            $guest = Guest::findByToken($sessionToken);
            if ($guest) {
                $this->loginGuest($guest);
                return;
            }
        }

        // URL-Token (persönlicher Link)
        $urlToken = request('token');
        if ($urlToken) {
            $guest = Guest::findByToken($urlToken);
            if ($guest) {
                $this->loginGuest($guest);
                return;
            }
        }
    }

    private function loginGuest(Guest $guest): void
    {
        $this->guest      = $guest;
        $this->guestName  = $guest->name;
        $this->step       = 'interact';
        $this->loadExistingVotes();
        session(['le_guest_token_' . $this->event->public_token => $guest->magic_token]);
    }

    // ── Registrierung ─────────────────────────────────────────────────────

    public function register(): void
    {
        $this->authError = '';
        $this->validate([
            'guestName' => 'required|min:2|max:80',
            'guestPin'  => 'required|min:4|max:8|numeric',
            'guestPin2' => 'required|same:guestPin',
        ], [
            'guestPin2.same' => 'PINs stimmen nicht überein.',
            'guestPin.numeric' => 'PIN darf nur Zahlen enthalten.',
            'guestPin.min' => 'PIN muss mindestens 4 Stellen haben.',
        ]);

        // Name bereits vergeben?
        $existing = Guest::where('name', $this->guestName)->whereNotNull('pin_hash')->first();
        if ($existing) {
            $this->authError = 'Dieser Name ist bereits vergeben. Bitte logge dich ein oder wähle einen anderen Namen.';
            $this->authMode = 'login';
            return;
        }

        $guest = Guest::create([
            'name'     => $this->guestName,
            'pin_hash' => Hash::make($this->guestPin),
        ]);

        $this->loginGuest($guest);
    }

    // ── Login ──────────────────────────────────────────────────────────────

    public function login(): void
    {
        $this->authError = '';
        $this->validate([
            'guestName' => 'required|min:2',
            'guestPin'  => 'required|min:4|numeric',
        ]);

        $guest = Guest::where('name', $this->guestName)->whereNotNull('pin_hash')->first();

        if (!$guest || !Hash::check($this->guestPin, $guest->pin_hash)) {
            $this->authError = 'Name oder PIN falsch.';
            return;
        }

        $this->loginGuest($guest);
    }

    public function logout(): void
    {
        session()->forget('le_guest_token_' . $this->event->public_token);
        $this->guest = null;
        $this->step  = 'identify';
        $this->selectedOptionIds = [];
        $this->guestPin = '';
    }

    // ── Votes laden ────────────────────────────────────────────────────────

    private function loadExistingVotes(): void
    {
        if (!$this->guest) return;
        $this->selectedOptionIds = PollVote::where('guest_id', $this->guest->id)
            ->pluck('vote_value', 'option_id')
            ->toArray();
    }

    // ── Termin-Abstimmung ──────────────────────────────────────────────────

    public function voteDate(int $optionId, string $value): void
    {
        if (!$this->guest) return;
        $poll = $this->event->activeDatePoll;
        if (!$poll) return;

        $existing = PollVote::where([
            'poll_id'   => $poll->id,
            'option_id' => $optionId,
            'guest_id'  => $this->guest->id,
        ])->first();

        // Nochmal gleichen Button → Stimme entfernen
        if ($existing && $existing->vote_value === $value) {
            $existing->delete();
            unset($this->selectedOptionIds[$optionId]);
        } else {
            PollVote::updateOrCreate(
                ['poll_id' => $poll->id, 'option_id' => $optionId, 'guest_id' => $this->guest->id],
                ['guest_name' => $this->guest->name, 'vote_value' => $value]
            );
            $this->selectedOptionIds[$optionId] = $value;
        }

        // Event neu laden damit Zähler stimmen
        $this->event->load('polls.options.votes');
    }

    // ── Film-Voting ────────────────────────────────────────────────────────

    public function toggleFilmLike(int $optionId): void
    {
        if (!$this->guest) return;
        $poll = $this->event->activeFilmPoll;
        if (!$poll) return;

        if (isset($this->selectedOptionIds[$optionId])) {
            PollVote::where([
                'poll_id'   => $poll->id,
                'option_id' => $optionId,
                'guest_id'  => $this->guest->id,
            ])->delete();
            unset($this->selectedOptionIds[$optionId]);
        } else {
            PollVote::create([
                'poll_id'    => $poll->id,
                'option_id'  => $optionId,
                'guest_id'   => $this->guest->id,
                'guest_name' => $this->guest->name,
                'vote_value' => 'like',
            ]);
            $this->selectedOptionIds[$optionId] = 'like';
        }

        $this->event->load('polls.options.votes');
    }

    public function submitFilmWish(): void
    {
        if (!$this->guest) return;
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

        PollVote::create([
            'poll_id'    => $poll->id,
            'option_id'  => $option->id,
            'guest_id'   => $this->guest->id,
            'guest_name' => $this->guest->name,
            'vote_value' => 'like',
        ]);

        $this->selectedOptionIds[$option->id] = 'like';
        $this->wishTitle  = '';
        $this->wishYear   = '';
        $this->showWishForm = false;
        $this->event->load('polls.options.votes');
    }

    // ── Sitzplatz-Anfrage ──────────────────────────────────────────────────

    public function toggleSeatRequest(int $seatId): void
    {
        if (in_array($seatId, $this->requestedSeatIds)) {
            $this->requestedSeatIds = array_values(array_filter($this->requestedSeatIds, fn($id) => $id !== $seatId));
        } else {
            $this->requestedSeatIds[] = $seatId;
        }
    }

    public function submitSeatRequest(): void
    {
        if (!$this->guest) return;

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

    public function render()
    {
        return view('livewire.event.public-page')
            ->layout('layouts.public');
    }
}
