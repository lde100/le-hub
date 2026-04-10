<div>

{{-- ── AUTH: Login / Registrierung ──────────────────────────────────────── --}}
@if ($step === 'identify')
<div style="background:#141414; border:1px solid #2a2a2a; border-radius:16px; padding:2rem; margin-bottom:1.5rem;">

    {{-- Tab-Switch --}}
    <div style="display:flex; gap:0; margin-bottom:1.5rem; background:#0D0D0D; border-radius:10px; padding:3px;">
        <button wire:click="$set('authMode','login'); $set('guestPin',''); $set('guestPin2',''); $set('authError','')"
            style="flex:1; padding:.625rem; border-radius:8px; border:none; cursor:pointer; font-size:.9rem; font-weight:600;
                background:{{ $authMode==='login' ? '#C9A84C' : 'transparent' }};
                color:{{ $authMode==='login' ? '#000' : '#666' }};">
            Einloggen
        </button>
        <button wire:click="$set('authMode','register'); $set('guestPin',''); $set('guestPin2',''); $set('authError','')"
            style="flex:1; padding:.625rem; border-radius:8px; border:none; cursor:pointer; font-size:.9rem; font-weight:600;
                background:{{ $authMode==='register' ? '#C9A84C' : 'transparent' }};
                color:{{ $authMode==='register' ? '#000' : '#666' }};">
            Neu registrieren
        </button>
    </div>

    @if($authError)
    <div style="background:#EF444411; border:1px solid #EF444433; border-radius:8px; padding:.75rem 1rem; margin-bottom:1rem; color:#EF4444; font-size:.875rem;">
        {{ $authError }}
    </div>
    @endif

    @php $inp = 'width:100%; background:#0D0D0D; border:1px solid #2a2a2a; border-radius:10px; padding:.875rem 1rem; color:#f5f5f5; font-size:1rem; outline:none; margin-bottom:.875rem;'; @endphp

    @if($authMode === 'login')
    {{-- LOGIN --}}
    <h2 style="font-size:1.1rem; font-weight:700; margin-bottom:.375rem;">Willkommen zurück 👋</h2>
    <p style="font-size:.875rem; color:#666; margin-bottom:1.25rem;">Name + PIN eingeben</p>

    <label style="font-size:.8rem; color:#888; display:block; margin-bottom:.375rem;">Name</label>
    <input wire:model="guestName" type="text" placeholder="Dein Name"
        style="{{ $inp }}" autocomplete="username">
    @error('guestName') <div style="color:#EF4444; font-size:.8rem; margin-top:-.625rem; margin-bottom:.625rem;">{{ $message }}</div> @enderror

    <label style="font-size:.8rem; color:#888; display:block; margin-bottom:.375rem;">PIN</label>
    <input wire:model="guestPin" type="password" inputmode="numeric" placeholder="••••"
        style="{{ $inp }}" autocomplete="current-password"
        wire:keydown.enter="login">
    @error('guestPin') <div style="color:#EF4444; font-size:.8rem; margin-top:-.625rem; margin-bottom:.625rem;">{{ $message }}</div> @enderror

    <button wire:click="login"
        style="width:100%; background:#C9A84C; color:#000; font-weight:700; padding:.9rem; border-radius:10px; border:none; font-size:1rem; cursor:pointer;">
        Einloggen →
    </button>

    @else
    {{-- REGISTRIERUNG --}}
    <h2 style="font-size:1.1rem; font-weight:700; margin-bottom:.375rem;">Neu dabei? 🎬</h2>
    <p style="font-size:.875rem; color:#666; margin-bottom:1.25rem;">Name wählen + PIN festlegen (4–8 Stellen)</p>

    <label style="font-size:.8rem; color:#888; display:block; margin-bottom:.375rem;">Name</label>
    <input wire:model="guestName" type="text" placeholder="z.B. Max"
        style="{{ $inp }}" autocomplete="username">
    @error('guestName') <div style="color:#EF4444; font-size:.8rem; margin-top:-.625rem; margin-bottom:.625rem;">{{ $message }}</div> @enderror

    <label style="font-size:.8rem; color:#888; display:block; margin-bottom:.375rem;">PIN wählen</label>
    <input wire:model="guestPin" type="password" inputmode="numeric" placeholder="z.B. 1234"
        style="{{ $inp }}" autocomplete="new-password">
    @error('guestPin') <div style="color:#EF4444; font-size:.8rem; margin-top:-.625rem; margin-bottom:.625rem;">{{ $message }}</div> @enderror

    <label style="font-size:.8rem; color:#888; display:block; margin-bottom:.375rem;">PIN wiederholen</label>
    <input wire:model="guestPin2" type="password" inputmode="numeric" placeholder="••••"
        style="{{ $inp }}" autocomplete="off"
        wire:keydown.enter="register">
    @error('guestPin2') <div style="color:#EF4444; font-size:.8rem; margin-top:-.625rem; margin-bottom:.625rem;">{{ $message }}</div> @enderror

    <button wire:click="register"
        style="width:100%; background:#C9A84C; color:#000; font-weight:700; padding:.9rem; border-radius:10px; border:none; font-size:1rem; cursor:pointer;">
        Account anlegen →
    </button>
    @endif

</div>
@endif

{{-- ── EINGELOGGT ──────────────────────────────────────────────────────────── --}}
@if ($step === 'interact')

{{-- Event Header --}}
<div style="margin-bottom:1.75rem;">
    <div style="font-size:.75rem; color:#C9A84C; letter-spacing:.1em; text-transform:uppercase; margin-bottom:.375rem;">
        {{ \App\Models\Event::STATUSES[$event->status] ?? $event->status }}
    </div>
    <h1 style="font-size:1.75rem; font-weight:800; margin:0 0 .5rem; line-height:1.2;">{{ $event->title }}</h1>
    @if($event->description)
        <p style="color:#888; margin:0 0 .875rem; font-size:.95rem;">{{ $event->description }}</p>
    @endif

    {{-- Gast-Badge + Logout --}}
    <div style="display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:.5rem;">
        <div style="display:inline-flex; align-items:center; gap:.5rem; background:{{ $guest->avatar_color }}22; border:1px solid {{ $guest->avatar_color }}44; border-radius:20px; padding:.375rem .875rem;">
            <div style="width:24px;height:24px;border-radius:50%;background:{{ $guest->avatar_color }};display:flex;align-items:center;justify-content:center;font-size:10px;font-weight:700;color:#000;">{{ $guest->initials }}</div>
            <span style="font-size:.875rem; font-weight:600;">{{ $guest->name }}</span>
        </div>
        <button wire:click="logout" style="background:none; border:none; color:#555; cursor:pointer; font-size:.8rem; padding:.375rem .625rem;">
            Abmelden
        </button>
    </div>
</div>

{{-- ── TERMIN-ABSTIMMUNG ───────────────────────────────────────────────────── --}}
@if ($event->status === 'polling_date' && $event->activeDatePoll)
@php $poll = $event->activeDatePoll; @endphp
<div style="margin-bottom:2rem;">
    <h3 style="font-size:1.05rem; font-weight:700; margin:0 0 .875rem;">📅 {{ $poll->title }}</h3>
    <div style="display:flex; flex-direction:column; gap:.75rem;">
    @foreach ($poll->options as $option)
    @php
        $myVote   = $selectedOptionIds[$option->id] ?? null;
        $yesCount = $option->votes->where('vote_value','yes')->count();
        $maybeCount = $option->votes->where('vote_value','maybe')->count();
        $noCount  = $option->votes->where('vote_value','no')->count();
    @endphp
    <div style="background:#1a1a1a; border:1px solid {{ $myVote ? '#C9A84C44' : '#222' }}; border-radius:12px; padding:1rem 1.25rem;
        {{ $myVote ? 'border-color:#C9A84C44;' : '' }}">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:.75rem; flex-wrap:wrap; gap:.5rem;">
            <div>
                <div style="font-weight:600; font-size:.95rem;">{{ $option->date_value?->isoFormat('dddd, D. MMMM YYYY') }}</div>
                <div style="color:#666; font-size:.8rem;">{{ $option->date_value?->format('H:i') }} Uhr</div>
            </div>
            <div style="display:flex; gap:.75rem; font-size:.8rem;">
                @if($yesCount) <span style="color:#22C55E;">✅ {{ $yesCount }}</span> @endif
                @if($maybeCount) <span style="color:#F59E0B;">🤔 {{ $maybeCount }}</span> @endif
                @if($noCount) <span style="color:#EF4444;">❌ {{ $noCount }}</span> @endif
            </div>
        </div>
        <div style="display:flex; gap:.5rem;">
            @foreach(['yes'=>['✅ Passt','#22C55E'], 'maybe'=>['🤔 Vielleicht','#F59E0B'], 'no'=>['❌ Kann nicht','#EF4444']] as $val=>[$label,$color])
            <button wire:click="voteDate({{ $option->id }}, '{{ $val }}')"
                style="flex:1; padding:.5rem .25rem; border-radius:8px; cursor:pointer; font-size:.8rem; transition:all .15s;
                    border:1px solid {{ $myVote===$val ? $color : '#2a2a2a' }};
                    background:{{ $myVote===$val ? $color.'22' : 'transparent' }};
                    color:{{ $myVote===$val ? $color : '#666' }};
                    font-weight:{{ $myVote===$val ? '700' : '400' }};">
                {{ $label }}
            </button>
            @endforeach
        </div>
    </div>
    @endforeach
    </div>
</div>
@endif

{{-- ── FILM-VOTING ─────────────────────────────────────────────────────────── --}}
@if (in_array($event->status, ['polling_film','booking_open','confirmed']) && $event->activeFilmPoll)
@php $filmPoll = $event->activeFilmPoll; @endphp
<div style="margin-bottom:2rem;">
    <h3 style="font-size:1.05rem; font-weight:700; margin:0 0 .875rem;">🎬 {{ $filmPoll->title }}</h3>
    <div style="display:flex; flex-direction:column; gap:.75rem;">
    @foreach ($filmPoll->options->sortByDesc(fn($o) => $o->votes->count()) as $option)
    @php
        $liked     = isset($selectedOptionIds[$option->id]);
        $likeCount = $option->votes->where('vote_value','like')->count();
    @endphp
    <div style="background:#1a1a1a; border:1px solid {{ $liked ? '#C9A84C44' : '#222' }}; border-radius:12px; padding:1rem; display:flex; gap:1rem; align-items:center;">
        @if($option->poster_url)
            <img src="{{ $option->poster_url }}" style="width:48px; height:68px; object-fit:cover; border-radius:5px; flex-shrink:0;" alt="">
        @else
            <div style="width:48px;height:68px;background:#111;border-radius:5px;display:flex;align-items:center;justify-content:center;flex-shrink:0;font-size:1.25rem;">🎬</div>
        @endif
        <div style="flex:1; min-width:0;">
            <div style="font-weight:700; font-size:.9rem; margin-bottom:.2rem;">{{ $option->movie_title }}</div>
            <div style="color:#555; font-size:.75rem;">
                {{ implode(' · ', array_filter([$option->movie_year, $option->movie_genre])) }}
            </div>
            @if($option->suggestedBy)
            <div style="font-size:.7rem; color:#444; margin-top:.25rem;">von {{ $option->suggestedBy->name }}</div>
            @endif
        </div>
        <button wire:click="toggleFilmLike({{ $option->id }})"
            style="flex-shrink:0; border-radius:10px; padding:.5rem .75rem; cursor:pointer; transition:all .15s;
                border:1px solid {{ $liked ? '#C9A84C' : '#2a2a2a' }};
                background:{{ $liked ? '#C9A84C22' : 'transparent' }};
                color:{{ $liked ? '#C9A84C' : '#555' }};
                font-weight:{{ $liked ? '700' : '400' }};
                font-size:.85rem; white-space:nowrap;">
            {{ $liked ? '❤️' : '🤍' }} {{ $likeCount }}
        </button>
    </div>
    @endforeach
    </div>

    @if($filmPoll->allow_new_options)
    @if(!$showWishForm)
    <button wire:click="$set('showWishForm', true)"
        style="width:100%; margin-top:.875rem; padding:.875rem; background:transparent; border:1px dashed #2a2a2a; border-radius:12px; color:#555; font-size:.875rem; cursor:pointer;">
        + Anderen Film vorschlagen
    </button>
    @else
    <div style="background:#141414; border:1px solid #2a2a2a; border-radius:12px; padding:1.25rem; margin-top:.875rem;">
        <div style="font-weight:600; margin-bottom:.875rem; font-size:.95rem;">🎬 Film vorschlagen</div>
        <input wire:model="wishTitle" type="text" placeholder="Filmtitel"
            style="width:100%; background:#0D0D0D; border:1px solid #2a2a2a; border-radius:8px; padding:.75rem; color:#f5f5f5; font-size:.9rem; margin-bottom:.625rem; outline:none;">
        <input wire:model="wishYear" type="text" placeholder="Jahr (optional)"
            style="width:100%; background:#0D0D0D; border:1px solid #2a2a2a; border-radius:8px; padding:.75rem; color:#f5f5f5; font-size:.9rem; margin-bottom:.75rem; outline:none;">
        @error('wishTitle') <div style="color:#EF4444; font-size:.8rem; margin-bottom:.5rem;">{{ $message }}</div> @enderror
        <div style="display:flex; gap:.75rem;">
            <button wire:click="submitFilmWish"
                style="flex:1; background:#C9A84C; color:#000; font-weight:700; padding:.75rem; border-radius:8px; border:none; cursor:pointer;">
                Vorschlagen
            </button>
            <button wire:click="$set('showWishForm', false)"
                style="padding:.75rem 1.25rem; background:transparent; border:1px solid #2a2a2a; border-radius:8px; color:#666; cursor:pointer;">
                Abbrechen
            </button>
        </div>
    </div>
    @endif
    @endif
</div>
@endif

{{-- ── SITZPLATZ-ANFRAGE ───────────────────────────────────────────────────── --}}
@if ($event->status === 'booking_open' && $event->allow_seat_requests && $event->seating_mode !== 'open')
@php $existingRequest = $event->seatRequests->where('guest_id', $guest?->id)->first(); @endphp
<div style="margin-bottom:2rem;">
    <h3 style="font-size:1.05rem; font-weight:700; margin:0 0 .5rem;">💺 Platz anfragen</h3>
    <p style="color:#666; font-size:.875rem; margin:0 0 1rem;">Wähle deinen Wunschplatz.</p>

    @if($existingRequest)
    <div style="background:{{ $existingRequest->status==='confirmed' ? '#22C55E11' : '#C9A84C11' }};
        border:1px solid {{ $existingRequest->status==='confirmed' ? '#22C55E33' : '#C9A84C33' }};
        border-radius:12px; padding:1rem 1.25rem; color:{{ $existingRequest->status==='confirmed' ? '#22C55E' : '#C9A84C' }}; font-size:.9rem;">
        @if($existingRequest->status === 'confirmed')
            ✅ Bestätigt — Platz: <strong>{{ $existingRequest->assignedSeat?->label ?? '—' }}</strong>
        @elseif($seatRequestSent)
            ✅ Anfrage gesendet!
        @else
            ⏳ Anfrage eingegangen — wird bestätigt
        @endif
    </div>
    @else
    @if($event->seating_mode === 'open')
    {{-- Freie Platzwahl: nur "Ich bin dabei" --}}
    <p style="color:#666; font-size:.875rem; margin:0 0 1rem;">Freie Platzwahl — melde dich einfach an.</p>
    @else
    @if($event->venue)
    @php $seatsByRow = $event->venue->seats->where('is_active', true)->groupBy('row'); @endphp
    <div style="margin-bottom:1rem;">
        @foreach($seatsByRow as $row => $seats)
        <div style="margin-bottom:.75rem;">
            <div style="font-size:.7rem; color:#555; margin-bottom:.375rem; text-transform:uppercase; letter-spacing:.05em;">{{ $row }}</div>
            <div style="display:flex; gap:.5rem; flex-wrap:wrap;">
            @foreach($seats as $seat)
            @php $requested = in_array($seat->id, $requestedSeatIds); @endphp
            <button wire:click="toggleSeatRequest({{ $seat->id }})"
                style="padding:.5rem .875rem; border-radius:8px; cursor:pointer; font-size:.85rem; transition:all .15s;
                    border:1px solid {{ $requested ? '#C9A84C' : '#2a2a2a' }};
                    background:{{ $requested ? '#C9A84C22' : 'transparent' }};
                    color:{{ $requested ? '#C9A84C' : '#666' }};
                    font-weight:{{ $requested ? '700' : '400' }};">
                {{ $seat->label }}
            </button>
            @endforeach
            </div>
        </div>
        @endforeach
    </div>
    @endif
    @endif
    <textarea wire:model="seatNotes" placeholder="Anmerkungen (optional)"
        style="width:100%; background:#111; border:1px solid #2a2a2a; border-radius:8px; padding:.75rem; color:#f5f5f5; font-size:.875rem; outline:none; resize:none; margin-bottom:.75rem;" rows="2"></textarea>
    <button wire:click="submitSeatRequest"
        style="width:100%; background:#C9A84C; color:#000; font-weight:700; padding:.875rem; border-radius:10px; border:none; font-size:1rem; cursor:pointer;">
        {{ $event->seating_mode === 'open' ? '✅ Ich bin dabei' : 'Platz anfragen' }}
    </button>
    @endif
</div>
@endif

{{-- ── BESTÄTIGTE VORSTELLUNG ──────────────────────────────────────────────── --}}
@if (in_array($event->status, ['confirmed','finished']) && $event->confirmedScreening)
<div style="background:#1a1a1a; border:1px solid #C9A84C44; border-radius:16px; padding:1.5rem; margin-bottom:1.5rem;">
    <div style="font-size:.7rem; color:#C9A84C; text-transform:uppercase; letter-spacing:.1em; margin-bottom:.625rem;">
        {{ $event->status === 'booking_open' ? 'Buchung offen' : 'Bestätigt' }}
    </div>
    @if($event->confirmedScreening)
    <div style="font-size:1.5rem; font-weight:800; margin-bottom:.25rem;">{{ $event->confirmedScreening->movie?->title ?? $event->title }}</div>
    <div style="color:#666; font-size:.9rem;">{{ $event->confirmedScreening->starts_at->isoFormat('dddd, D. MMMM YYYY · HH:mm') }} Uhr</div>
    @else
    <div style="font-size:1.5rem; font-weight:800; margin-bottom:.25rem;">{{ $event->title }}</div>
    <div style="color:#666; font-size:.875rem;">Termin & Film werden noch bekanntgegeben.</div>
    @endif
</div>
@endif

@endif {{-- step === interact --}}
</div>
