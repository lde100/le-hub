<div>

    {{-- ── NAME FORM (erster Besuch) ──────────────────────────────────── --}}
    @if ($showNameForm)
    <div style="background:var(--le-surface); border:1px solid var(--le-border); border-radius:16px; padding:2rem; margin-bottom:1.5rem;">
        <h2 style="font-size:1.25rem; font-weight:700; margin:0 0 .5rem;">Wer bist du? 👋</h2>
        <p style="color:#888; font-size:.9rem; margin:0 0 1.5rem;">Damit wir deinen Platz reservieren und Punkte gutschreiben können.</p>

        <div style="display:flex; flex-direction:column; gap:1rem;">
            <div>
                <label style="font-size:.85rem; color:#aaa; display:block; margin-bottom:.4rem;">Name *</label>
                <input wire:model="guestName" type="text" placeholder="Max Mustermann"
                    style="width:100%; background:#111; border:1px solid #333; border-radius:10px; padding:.75rem 1rem; color:#fff; font-size:1rem; outline:none;"
                    autofocus>
                @error('guestName') <span style="color:#EF4444; font-size:.8rem;">{{ $message }}</span> @enderror
            </div>
            <div>
                <label style="font-size:.85rem; color:#aaa; display:block; margin-bottom:.4rem;">
                    E-Mail <span style="color:#666;">(optional — für Treuepunkte & Ticket-Link)</span>
                </label>
                <input wire:model="guestEmail" type="email" placeholder="max@beispiel.de"
                    style="width:100%; background:#111; border:1px solid #333; border-radius:10px; padding:.75rem 1rem; color:#fff; font-size:1rem; outline:none;">
                @error('guestEmail') <span style="color:#EF4444; font-size:.8rem;">{{ $message }}</span> @enderror
            </div>
            <button wire:click="identify"
                style="background:var(--le-gold); color:#000; font-weight:700; padding:.875rem; border-radius:10px; border:none; font-size:1rem; cursor:pointer;">
                Weiter →
            </button>
        </div>
    </div>
    @endif

    @if ($step === 'interact')

    {{-- ── EVENT HEADER ────────────────────────────────────────────────── --}}
    <div style="margin-bottom:2rem;">
        <div style="font-size:.8rem; color:var(--le-gold); letter-spacing:.1em; text-transform:uppercase; margin-bottom:.5rem;">
            {{ Event::STATUSES[$event->status] ?? $event->status }}
        </div>
        <h1 style="font-size:1.75rem; font-weight:800; margin:0 0 .5rem; line-height:1.2;">{{ $event->title }}</h1>
        @if($event->description)
            <p style="color:#888; margin:0; font-size:.95rem;">{{ $event->description }}</p>
        @endif

        {{-- Gast-Badge --}}
        @if($guest)
        <div style="display:inline-flex; align-items:center; gap:.5rem; background:{{$guest->avatar_color}}22; border:1px solid {{$guest->avatar_color}}44; border-radius:20px; padding:.375rem .875rem; margin-top:1rem;">
            <div style="width:24px;height:24px;border-radius:50%;background:{{$guest->avatar_color}};display:flex;align-items:center;justify-content:center;font-size:10px;font-weight:700;color:#000;">{{ $guest->initials }}</div>
            <span style="font-size:.875rem; font-weight:600;">{{ $guest->name }}</span>
            @if($guest->loyalty_points > 0)
                <span style="font-size:.8rem; color:var(--le-gold);">⭐ {{ $guest->loyalty_points }} Punkte</span>
            @endif
        </div>
        @endif
    </div>

    {{-- ── TERMIN-ABSTIMMUNG ───────────────────────────────────────────── --}}
    @if ($event->status === 'polling_date' && $event->activeDatePoll)
    @php $poll = $event->activeDatePoll; @endphp
    <div style="margin-bottom:2rem;">
        <h3 style="font-size:1.1rem; font-weight:700; margin:0 0 1rem;">📅 {{ $poll->title }}</h3>
        @if($poll->description)
            <p style="color:#888; font-size:.875rem; margin:0 0 1rem;">{{ $poll->description }}</p>
        @endif

        <div style="display:flex; flex-direction:column; gap:.75rem;">
        @foreach ($poll->options as $option)
        @php
            $myVote = collect($selectedOptionIds)->contains($option->id)
                ? PollVote::where(['poll_id'=>$poll->id,'option_id'=>$option->id,'guest_id'=>$guest?->id])->value('vote_value')
                : null;
            $yesCount = $option->votes->where('vote_value','yes')->count();
            $maybeCount = $option->votes->where('vote_value','maybe')->count();
            $noCount = $option->votes->where('vote_value','no')->count();
        @endphp
        <div style="background:var(--le-surface); border:1px solid {{ $myVote === 'yes' ? 'var(--le-gold)' : 'var(--le-border)' }}; border-radius:12px; padding:1rem 1.25rem;">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:.75rem;">
                <div>
                    <div style="font-weight:600; font-size:1rem;">{{ $option->date_value?->isoFormat('dddd, D. MMMM YYYY') }}</div>
                    <div style="color:#888; font-size:.85rem;">{{ $option->date_value?->format('H:i') }} Uhr</div>
                </div>
                <div style="display:flex; gap:.5rem; font-size:.8rem; color:#666;">
                    <span>✅ {{ $yesCount }}</span>
                    <span>🤔 {{ $maybeCount }}</span>
                    <span>❌ {{ $noCount }}</span>
                </div>
            </div>
            <div style="display:flex; gap:.5rem;">
                <button wire:click="voteDate({{ $option->id }}, 'yes')"
                    style="flex:1; padding:.5rem; border-radius:8px; border:1px solid {{ $myVote==='yes'?'#22C55E':'#333' }}; background:{{ $myVote==='yes'?'#22C55E22':'transparent' }}; color:{{ $myVote==='yes'?'#22C55E':'#aaa' }}; cursor:pointer; font-size:.875rem;">
                    ✅ Passt
                </button>
                <button wire:click="voteDate({{ $option->id }}, 'maybe')"
                    style="flex:1; padding:.5rem; border-radius:8px; border:1px solid {{ $myVote==='maybe'?'#F59E0B':'#333' }}; background:{{ $myVote==='maybe'?'#F59E0B22':'transparent' }}; color:{{ $myVote==='maybe'?'#F59E0B':'#aaa' }}; cursor:pointer; font-size:.875rem;">
                    🤔 Vielleicht
                </button>
                <button wire:click="voteDate({{ $option->id }}, 'no')"
                    style="flex:1; padding:.5rem; border-radius:8px; border:1px solid {{ $myVote==='no'?'#EF4444':'#333' }}; background:{{ $myVote==='no'?'#EF444422':'transparent' }}; color:{{ $myVote==='no'?'#EF4444':'#aaa' }}; cursor:pointer; font-size:.875rem;">
                    ❌ Kann nicht
                </button>
            </div>
        </div>
        @endforeach
        </div>
    </div>
    @endif

    {{-- ── FILM-VOTING ─────────────────────────────────────────────────── --}}
    @if (in_array($event->status, ['polling_film', 'booking_open', 'confirmed']) && $event->activeFilmPoll)
    @php $filmPoll = $event->activeFilmPoll; @endphp
    <div style="margin-bottom:2rem;">
        <h3 style="font-size:1.1rem; font-weight:700; margin:0 0 1rem;">🎬 {{ $filmPoll->title }}</h3>

        <div style="display:flex; flex-direction:column; gap:.75rem;">
        @foreach ($filmPoll->options->sortByDesc(fn($o) => $o->votes->count()) as $option)
        @php $liked = in_array($option->id, $selectedOptionIds); $likeCount = $option->votes->where('vote_value','like')->count(); @endphp
        <div style="background:var(--le-surface); border:1px solid {{ $liked?'var(--le-gold)':'var(--le-border)' }}; border-radius:12px; padding:1rem; display:flex; gap:1rem; align-items:center;">
            @if($option->poster_url)
                <img src="{{ $option->poster_url }}" style="width:56px; height:80px; object-fit:cover; border-radius:6px; flex-shrink:0;" alt="">
            @else
                <div style="width:56px;height:80px;background:#222;border-radius:6px;display:flex;align-items:center;justify-content:center;flex-shrink:0;font-size:1.5rem;">🎬</div>
            @endif
            <div style="flex:1; min-width:0;">
                <div style="font-weight:700; font-size:.95rem; margin-bottom:.2rem;">{{ $option->movie_title }}</div>
                <div style="color:#666; font-size:.8rem; margin-bottom:.4rem;">
                    {{ implode(' · ', array_filter([$option->movie_year, $option->movie_genre, $option->movie_duration ? $option->movie_duration.'min' : null])) }}
                </div>
                @if($option->suggestedBy)
                    <div style="font-size:.75rem; color:#555;">Vorschlag von {{ $option->suggestedBy->name }}</div>
                @endif
            </div>
            <button wire:click="toggleFilmLike({{ $option->id }})"
                style="flex-shrink:0; background:{{ $liked?'var(--le-gold)22':'transparent' }}; border:1px solid {{ $liked?'var(--le-gold)':'#333' }}; border-radius:10px; padding:.5rem .75rem; cursor:pointer; color:{{ $liked?'var(--le-gold)':'#666' }}; font-size:.85rem; white-space:nowrap;">
                {{ $liked ? '❤️' : '🤍' }} {{ $likeCount }}
            </button>
        </div>
        @endforeach
        </div>

        {{-- Film wünschen --}}
        @if($filmPoll->allow_new_options)
        @if(!$showWishForm)
        <button wire:click="$set('showWishForm', true)"
            style="width:100%; margin-top:1rem; padding:.875rem; background:transparent; border:1px dashed #333; border-radius:12px; color:#666; font-size:.9rem; cursor:pointer;">
            + Anderen Film wünschen
        </button>
        @else
        <div style="background:var(--le-surface); border:1px solid var(--le-border); border-radius:12px; padding:1.25rem; margin-top:1rem;">
            <div style="font-weight:600; margin-bottom:1rem;">🎬 Filmwunsch</div>
            <input wire:model="wishTitle" type="text" placeholder="Filmtitel"
                style="width:100%; background:#111; border:1px solid #333; border-radius:8px; padding:.75rem; color:#fff; font-size:.95rem; margin-bottom:.75rem; outline:none;">
            <input wire:model="wishYear" type="text" placeholder="Jahr (optional)"
                style="width:100%; background:#111; border:1px solid #333; border-radius:8px; padding:.75rem; color:#fff; font-size:.95rem; margin-bottom:.75rem; outline:none;">
            @error('wishTitle') <span style="color:#EF4444; font-size:.8rem; display:block; margin-bottom:.5rem;">{{ $message }}</span> @enderror
            <div style="display:flex; gap:.75rem;">
                <button wire:click="submitFilmWish"
                    style="flex:1; background:var(--le-gold); color:#000; font-weight:700; padding:.75rem; border-radius:8px; border:none; cursor:pointer;">
                    Wunsch einreichen
                </button>
                <button wire:click="$set('showWishForm', false)"
                    style="padding:.75rem 1.25rem; background:transparent; border:1px solid #333; border-radius:8px; color:#888; cursor:pointer;">
                    Abbrechen
                </button>
            </div>
        </div>
        @endif
        @endif
    </div>
    @endif

    {{-- ── SITZPLATZ-ANFRAGE ───────────────────────────────────────────── --}}
    @if ($event->status === 'booking_open' && $event->allow_seat_requests && $event->seating_mode !== 'open')
    @php $existingRequest = $event->seatRequests->where('guest_id', $guest?->id)->first(); @endphp
    <div style="margin-bottom:2rem;">
        <h3 style="font-size:1.1rem; font-weight:700; margin:0 0 .5rem;">💺 Platz anfragen</h3>
        <p style="color:#888; font-size:.875rem; margin:0 0 1rem;">Wähle deinen Wunschplatz. Finale Bestätigung durch den Admin.</p>

        @if($existingRequest && !$seatRequestSent)
        <div style="background:#22C55E11; border:1px solid #22C55E33; border-radius:12px; padding:1rem 1.25rem; color:#22C55E; font-size:.9rem;">
            ✅ Deine Anfrage ist eingegangen (Status: {{ $existingRequest->status }})
            @if($existingRequest->assignedSeat)
                — Du hast Platz <strong>{{ $existingRequest->assignedSeat->label }}</strong>
            @endif
        </div>
        @elseif($seatRequestSent)
        <div style="background:#22C55E11; border:1px solid #22C55E33; border-radius:12px; padding:1rem 1.25rem; color:#22C55E; font-size:.9rem;">
            ✅ Anfrage gesendet! Du bekommst eine Bestätigung.
        </div>
        @else
        {{-- Saalplan mini --}}
        @if($event->venue)
        @php $seatsByRow = $event->venue->seats->where('is_active', true)->groupBy('row'); @endphp
        <div style="margin-bottom:1rem;">
            @foreach($seatsByRow as $row => $seats)
            <div style="margin-bottom:.75rem;">
                <div style="font-size:.75rem; color:#666; margin-bottom:.4rem; text-transform:uppercase; letter-spacing:.05em;">{{ $row }}</div>
                <div style="display:flex; gap:.5rem; flex-wrap:wrap;">
                @foreach($seats as $seat)
                @php $requested = in_array($seat->id, $requestedSeatIds); @endphp
                <button wire:click="toggleSeatRequest({{ $seat->id }})"
                    style="padding:.5rem .875rem; border-radius:8px; border:1px solid {{ $requested?'var(--le-gold)':'#333' }}; background:{{ $requested?'var(--le-gold)22':'transparent' }}; color:{{ $requested?'var(--le-gold)':'#888' }}; cursor:pointer; font-size:.85rem;">
                    {{ $seat->label }}
                </button>
                @endforeach
                </div>
            </div>
            @endforeach
        </div>
        @endif

        <textarea wire:model="seatNotes" placeholder="Anmerkungen (optional)"
            style="width:100%; background:#111; border:1px solid #333; border-radius:8px; padding:.75rem; color:#fff; font-size:.875rem; outline:none; resize:none; margin-bottom:.75rem;" rows="2"></textarea>

        <button wire:click="submitSeatRequest"
            style="width:100%; background:var(--le-gold); color:#000; font-weight:700; padding:.875rem; border-radius:10px; border:none; font-size:1rem; cursor:pointer;">
            Platz anfragen
        </button>
        @endif
    </div>
    @endif

    {{-- ── BESTÄTIGTE VORSTELLUNG ──────────────────────────────────────── --}}
    @if (in_array($event->status, ['confirmed', 'finished']) && $event->confirmedScreening)
    @php $screening = $event->confirmedScreening; @endphp
    <div style="background:var(--le-surface); border:1px solid var(--le-gold)44; border-radius:16px; padding:1.5rem; margin-bottom:2rem;">
        <div style="font-size:.75rem; color:var(--le-gold); text-transform:uppercase; letter-spacing:.1em; margin-bottom:.75rem;">Bestätigt</div>
        <div style="font-size:1.5rem; font-weight:800; margin-bottom:.25rem;">{{ $screening->movie?->title ?? $event->title }}</div>
        <div style="color:#888; font-size:.95rem;">{{ $screening->starts_at->isoFormat('dddd, D. MMMM YYYY · HH:mm') }} Uhr</div>
    </div>
    @endif

    @endif {{-- step === interact --}}
</div>
