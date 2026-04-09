<div>
@php
$statusFlow = ['draft','polling_date','polling_film','booking_open','confirmed','finished'];
$statusMeta = [
    'draft'        => ['label'=>'Entwurf',         'color'=>'#555'],
    'polling_date' => ['label'=>'Terminumfrage',   'color'=>'#3B82F6'],
    'polling_film' => ['label'=>'Filmumfrage',     'color'=>'#8B5CF6'],
    'booking_open' => ['label'=>'Buchung offen',   'color'=>'#C9A84C'],
    'confirmed'    => ['label'=>'Bestätigt',       'color'=>'#22C55E'],
    'finished'     => ['label'=>'Abgeschlossen',   'color'=>'#555'],
];
$sm = $statusMeta[$event->status] ?? ['label'=>$event->status,'color'=>'#555'];
$screening = $event->screenings->first();
$inp = 'width:100%; background:#0D0D0D; border:1px solid #2a2a2a; border-radius:8px; padding:.625rem .875rem; color:#f5f5f5; font-size:.875rem; outline:none; margin-bottom:.875rem;';
$lbl = 'font-size:.75rem; color:#888; display:block; margin-bottom:.3rem;';
$btnGold = 'background:#C9A84C; color:#000; font-weight:700; border:none; border-radius:9px; padding:.625rem 1.25rem; cursor:pointer; font-size:.875rem;';
$btnGray = 'background:transparent; border:1px solid #2a2a2a; border-radius:9px; color:#888; cursor:pointer; font-size:.875rem; padding:.625rem 1rem;';
@endphp

{{-- ── Breadcrumb + Header ────────────────────────────────────────────── --}}
<div style="display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:1.5rem; flex-wrap:wrap; gap:.75rem;">
    <div>
        <a href="{{ route('admin.events') }}" style="font-size:.8rem; color:#555; text-decoration:none;">← Events</a>
        <h1 style="font-size:1.25rem; font-weight:800; margin-top:.25rem;">{{ $event->title }}</h1>
        <div style="display:flex; gap:.625rem; align-items:center; margin-top:.5rem; flex-wrap:wrap;">
            <span style="background:{{ $sm['color'] }}22; color:{{ $sm['color'] }}; font-size:.75rem; padding:.25rem .75rem; border-radius:99px; font-weight:600;">
                {{ $sm['label'] }}
            </span>
            <span style="font-size:.8rem; color:#555;">{{ $event->type }} · {{ $event->venue?->name }}</span>
        </div>
    </div>
    <div style="display:flex; gap:.5rem; flex-wrap:wrap;">
        {{-- Öffentlicher Link --}}
        <button
            onclick="navigator.clipboard.writeText('{{ $event->public_url }}').then(()=>this.textContent='✅ Kopiert').catch(()=>{}); setTimeout(()=>this.textContent='🔗 Link kopieren',2000)"
            style="{{ $btnGray }} color:#C9A84C; border-color:#C9A84C44;">
            🔗 Link kopieren
        </button>
        <a href="{{ route('admin.events.hub', $event->id) }}"
            style="{{ $btnGold }} text-decoration:none;">
            🎛 Hub
        </a>
        <a href="{{ $event->public_url }}" target="_blank" style="{{ $btnGray }} text-decoration:none;">Vorschau ↗</a>
        @if($screening)
        <a href="{{ route('cinema.checkin', $screening->id) }}" target="_blank" style="{{ $btnGray }} text-decoration:none;">📟 Einlass</a>
        @endif
    </div>
</div>

{{-- ── Status-Stepper ────────────────────────────────────────────────────── --}}
<div style="display:flex; gap:0; margin-bottom:2rem; background:#141414; border:1px solid #1e1e1e; border-radius:12px; overflow:hidden;">
@foreach($statusFlow as $s)
@php $active = $event->status === $s; $past = array_search($s,$statusFlow) < array_search($event->status,$statusFlow); @endphp
<div style="flex:1; padding:.625rem .5rem; text-align:center; font-size:.7rem; font-weight:{{ $active?'700':'400' }};
    background:{{ $active?'#C9A84C22':'transparent' }}; color:{{ $active?'#C9A84C':($past?'#444':'#333') }};
    border-right:1px solid #1e1e1e;">
    {{ $statusMeta[$s]['label'] }}
</div>
@endforeach
</div>

{{-- ════════════════════════════════════════════════════════════════════════
     PHASE 1: TERMIN-ABSTIMMUNG
═════════════════════════════════════════════════════════════════════════ --}}
<div style="background:#141414; border:1px solid #1e1e1e; border-radius:14px; padding:1.5rem; margin-bottom:1.25rem;">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:1rem;">
        <h3 style="font-weight:700;">📅 Termin-Abstimmung</h3>
        @if(in_array($event->status, ['draft','polling_date']))
        <button wire:click="$toggle('showDatePollForm')" style="{{ $btnGold }} font-size:.8rem;">
            {{ $showDatePollForm ? '× Abbrechen' : '+ Umfrage erstellen' }}
        </button>
        @endif
    </div>

    @if($showDatePollForm)
    <div style="background:#0D0D0D; border-radius:10px; padding:1.25rem; margin-bottom:1rem;">
        <label style="{{ $lbl }}">Frage-Titel</label>
        <input wire:model="datePollTitle" type="text" style="{{ $inp }}">

        <div style="font-size:.75rem; color:#888; margin-bottom:.625rem;">Termin-Optionen</div>
        @foreach($dateOptions as $i => $opt)
        <div style="display:flex; gap:.5rem; margin-bottom:.5rem; align-items:center;">
            <input wire:model="dateOptions.{{ $i }}.date" type="date" style="flex:1; {{ $inp }} margin-bottom:0;">
            <input wire:model="dateOptions.{{ $i }}.time" type="time" style="width:100px; {{ $inp }} margin-bottom:0;">
            @if(count($dateOptions) > 1)
            <button wire:click="removeDateOption({{ $i }})" style="background:none;border:none;color:#555;cursor:pointer;font-size:1.1rem;">×</button>
            @endif
        </div>
        @endforeach
        <div style="display:flex; gap:.5rem; margin-top:.5rem;">
            <button wire:click="addDateOption" style="{{ $btnGray }} font-size:.8rem;">+ Termin</button>
            <button wire:click="createDatePoll" style="{{ $btnGold }}">Umfrage starten</button>
        </div>
    </div>
    @endif

    @forelse($event->polls->where('type','date_selection') as $poll)
    <div style="background:#0D0D0D; border-radius:10px; padding:1rem; margin-bottom:.75rem;">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:.875rem; flex-wrap:wrap; gap:.5rem;">
            <div style="font-size:.9rem; font-weight:600;">{{ $poll->title }}</div>
            <div style="display:flex; gap:.5rem;">
                <span style="font-size:.7rem; padding:.2rem .625rem; border-radius:99px;
                    background:{{ $poll->status==='open'?'#22C55E22':'#55555522' }};
                    color:{{ $poll->status==='open'?'#22C55E':'#555' }}; font-weight:600;">
                    {{ $poll->status }}
                </span>
                @if($poll->status === 'open')
                <button wire:click="closeDatePoll({{ $poll->id }})" style="{{ $btnGray }} font-size:.75rem; padding:.25rem .75rem;">Schließen</button>
                @endif
            </div>
        </div>
        @foreach($poll->options->sortByDesc(fn($o) => $o->votes->where('vote_value','yes')->count()) as $opt)
        @php
            $yes    = $opt->votes->where('vote_value','yes')->count();
            $maybe  = $opt->votes->where('vote_value','maybe')->count();
            $no     = $opt->votes->where('vote_value','no')->count();
            $total  = $opt->votes->count();
        @endphp
        <div style="display:flex; justify-content:space-between; align-items:center; padding:.625rem .875rem; border-radius:8px; margin-bottom:.375rem;
            background:{{ $opt->is_winner?'#C9A84C18':'#141414' }}; border:1px solid {{ $opt->is_winner?'#C9A84C44':'#1e1e1e' }};">
            <div>
                <div style="font-size:.875rem; font-weight:{{ $opt->is_winner?'700':'400' }}; color:{{ $opt->is_winner?'#C9A84C':'#f5f5f5' }};">
                    {{ $opt->label }} {{ $opt->is_winner ? '✓' : '' }}
                </div>
                <div style="font-size:.75rem; color:#555; margin-top:.2rem;">
                    ✅ {{ $yes }} · 🤔 {{ $maybe }} · ❌ {{ $no }}
                    @if($total > 0)
                    @foreach($opt->votes->where('vote_value','yes') as $v)
                    <span style="color:#888; margin-left:.25rem;">{{ $v->guest_name }}</span>
                    @endforeach
                    @endif
                </div>
            </div>
            @if(!$opt->is_winner && in_array($poll->status, ['open','closed']))
            <button wire:click="confirmDate({{ $opt->id }})" style="{{ $btnGold }} font-size:.8rem; padding:.375rem .875rem;">
                ✓ Bestätigen
            </button>
            @endif
        </div>
        @endforeach
    </div>
    @empty
    <div style="color:#444; font-size:.875rem; padding:.5rem 0;">Noch keine Termin-Umfrage erstellt.</div>
    @endforelse

    {{-- Termin direkt setzen ohne Poll --}}
    @if($event->status === 'draft')
    <div style="margin-top:.75rem; font-size:.8rem; color:#555;">
        Oder:
        <button wire:click="advanceTo('polling_film')" style="background:none;border:none;color:#C9A84C;cursor:pointer;text-decoration:underline;font-size:.8rem;">
            Termin überspringen → direkt zu Film-Phase
        </button>
    </div>
    @endif
</div>

{{-- ════════════════════════════════════════════════════════════════════════
     PHASE 2: FILM-ABSTIMMUNG
═════════════════════════════════════════════════════════════════════════ --}}
<div style="background:#141414; border:1px solid #1e1e1e; border-radius:14px; padding:1.5rem; margin-bottom:1.25rem;
    opacity:{{ in_array($event->status, ['draft','polling_date'])?'.45':'1' }};">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:1rem;">
        <h3 style="font-weight:700;">🎬 Film-Abstimmung</h3>
        @if(in_array($event->status, ['polling_film']))
        <button wire:click="$toggle('showFilmPollForm')" style="{{ $btnGold }} font-size:.8rem;">
            {{ $showFilmPollForm ? '× Abbrechen' : '+ Umfrage erstellen' }}
        </button>
        @endif
    </div>

    @if($showFilmPollForm)
    <div style="background:#0D0D0D; border-radius:10px; padding:1.25rem; margin-bottom:1rem;">
        <label style="{{ $lbl }}">Frage-Titel</label>
        <input wire:model="filmPollTitle" type="text" style="{{ $inp }}">

        <div style="display:flex; align-items:center; gap:.75rem; margin-bottom:.875rem;">
            <input wire:model="allowWishes" type="checkbox" id="allowWishes">
            <label for="allowWishes" style="font-size:.875rem; color:#888; cursor:pointer;">Gäste können eigene Filme vorschlagen</label>
        </div>

        <div style="font-size:.75rem; color:#888; margin-bottom:.625rem;">Film-Vorschläge (optional)</div>
        @foreach($filmOptions as $i => $opt)
        <div style="display:flex; gap:.5rem; margin-bottom:.5rem; align-items:center;">
            <input wire:model="filmOptions.{{ $i }}.title" type="text" placeholder="Filmtitel" style="flex:2; {{ $inp }} margin-bottom:0;">
            <input wire:model="filmOptions.{{ $i }}.year" type="text" placeholder="Jahr" style="width:80px; {{ $inp }} margin-bottom:0;">
            <button wire:click="removeFilmOption({{ $i }})" style="background:none;border:none;color:#555;cursor:pointer;font-size:1.1rem;">×</button>
        </div>
        @endforeach
        <div style="display:flex; gap:.5rem; margin-top:.5rem;">
            <button wire:click="addFilmOption" style="{{ $btnGray }} font-size:.8rem;">+ Film</button>
            <button wire:click="createFilmPoll" style="{{ $btnGold }}">Umfrage starten</button>
        </div>
    </div>
    @endif

    @forelse($event->polls->where('type','film_vote') as $poll)
    <div style="background:#0D0D0D; border-radius:10px; padding:1rem; margin-bottom:.75rem;">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:.875rem; flex-wrap:wrap; gap:.5rem;">
            <div style="font-size:.9rem; font-weight:600;">{{ $poll->title }}</div>
            <div style="display:flex; gap:.5rem;">
                <span style="font-size:.7rem; padding:.2rem .625rem; border-radius:99px;
                    background:{{ $poll->status==='open'?'#22C55E22':'#55555522' }};
                    color:{{ $poll->status==='open'?'#22C55E':'#555' }}; font-weight:600;">
                    {{ $poll->status }}
                </span>
                @if($poll->status === 'open')
                <button wire:click="closeFilmPoll({{ $poll->id }})" style="{{ $btnGray }} font-size:.75rem; padding:.25rem .75rem;">Schließen</button>
                @endif
            </div>
        </div>
        @foreach($poll->options->sortByDesc(fn($o) => $o->votes->count()) as $opt)
        @php $likes = $opt->votes->where('vote_value','like')->count(); @endphp
        <div style="display:flex; justify-content:space-between; align-items:center; padding:.625rem .875rem; border-radius:8px; margin-bottom:.375rem;
            background:{{ $opt->is_winner?'#C9A84C18':'#141414' }}; border:1px solid {{ $opt->is_winner?'#C9A84C44':'#1e1e1e' }};">
            <div>
                <div style="font-size:.875rem; font-weight:{{ $opt->is_winner?'700':'400' }}; color:{{ $opt->is_winner?'#C9A84C':'#f5f5f5' }};">
                    {{ $opt->movie_title }} {{ $opt->movie_year ? "({$opt->movie_year})" : '' }} {{ $opt->is_winner?'✓':'' }}
                </div>
                <div style="font-size:.75rem; color:#555; margin-top:.2rem;">
                    ❤️ {{ $likes }}
                    @if($opt->suggestedBy) · Vorschlag von {{ $opt->suggestedBy->name }} @endif
                    @foreach($opt->votes->where('vote_value','like') as $v)
                    <span style="color:#888; margin-left:.25rem;">{{ $v->guest_name }}</span>
                    @endforeach
                </div>
            </div>
            @if(!$opt->is_winner && in_array($poll->status, ['open','closed']))
            <button wire:click="confirmFilm({{ $opt->id }})" style="{{ $btnGold }} font-size:.8rem; padding:.375rem .875rem;">
                ✓ Film wählen
            </button>
            @endif
        </div>
        @endforeach
    </div>
    @empty
    <div style="color:#444; font-size:.875rem; padding:.5rem 0;">
        @if($event->status === 'polling_film')
            Noch keine Film-Umfrage. Oder:
            <form style="display:inline-flex; gap:.5rem; align-items:center; margin-top:.5rem;">
                <input type="text" id="directFilm" placeholder="Filmtitel direkt setzen" style="background:#0D0D0D; border:1px solid #333; border-radius:7px; padding:.4rem .75rem; color:#f5f5f5; font-size:.85rem; outline:none;">
                <button type="button"
                    onclick="$wire.setAdminFilm(document.getElementById('directFilm').value)"
                    style="{{ $btnGold }} font-size:.8rem; padding:.4rem .875rem;">
                    Direkt festlegen
                </button>
            </form>
        @else
            Noch nicht in dieser Phase.
        @endif
    </div>
    @endforelse
</div>

{{-- ════════════════════════════════════════════════════════════════════════
     PHASE 3: SITZPLATZ-ANFRAGEN
═════════════════════════════════════════════════════════════════════════ --}}
<div style="background:#141414; border:1px solid #1e1e1e; border-radius:14px; padding:1.5rem; margin-bottom:1.25rem;
    opacity:{{ !in_array($event->status,['booking_open','confirmed'])?'.45':'1' }};">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:1rem; flex-wrap:wrap; gap:.5rem;">
        <h3 style="font-weight:700;">💺 Sitzplatz-Anfragen
            @php $pending = $event->seatRequests->where('status','pending')->count(); @endphp
            @if($pending > 0)
            <span style="background:#C9A84C; color:#000; font-size:.7rem; font-weight:700; padding:.15rem .5rem; border-radius:99px; margin-left:.5rem;">{{ $pending }}</span>
            @endif
        </h3>
        @if($event->seatRequests->where('status','pending')->count() > 0)
        <button wire:click="confirmAllRequests" style="{{ $btnGold }} font-size:.8rem;">
            ✓ Alle bestätigen + Tickets generieren
        </button>
        @endif
    </div>

    @forelse($event->seatRequests as $req)
    @php
        $ticket = $req->booking?->tickets->first() ?? null;
        $statusColors = ['pending'=>'#C9A84C','confirmed'=>'#22C55E','declined'=>'#EF4444','waitlist'=>'#3B82F6'];
    @endphp
    <div style="display:flex; justify-content:space-between; align-items:center; padding:.75rem 1rem; border-radius:10px; margin-bottom:.5rem;
        background:#0D0D0D; border:1px solid {{ ($req->status==='confirmed')?'#22C55E22':'#1e1e1e' }};">
        <div style="flex:1;">
            <div style="display:flex; align-items:center; gap:.625rem; margin-bottom:.25rem;">
                <span style="font-weight:600; font-size:.9rem;">{{ $req->guest_name }}</span>
                <span style="background:{{ ($statusColors[$req->status]??'#555') }}22; color:{{ $statusColors[$req->status]??'#555' }};
                    font-size:.65rem; padding:.15rem .5rem; border-radius:99px; font-weight:600; text-transform:uppercase;">
                    {{ $req->status }}
                </span>
            </div>
            <div style="font-size:.75rem; color:#555;">
                @if($req->requested_seat_ids)
                    Wunsch: {{ collect($req->requested_seat_ids)->map(fn($id) => $event->venue?->seats->find($id)?->label ?? '#'.$id)->join(', ') }}
                @endif
                @if($req->assignedSeat) · Zugewiesen: <strong style="color:#888;">{{ $req->assignedSeat->label }}</strong> @endif
                @if($req->notes) · "{{ $req->notes }}" @endif
            </div>
        </div>
        <div style="display:flex; gap:.375rem; align-items:center;">
            @if($req->status === 'confirmed' && $ticket)
            <button
                onclick="navigator.clipboard.writeText('{{ route('ticket.show', $ticket->ticket_code) }}').then(()=>this.textContent='✅').catch(()=>{}); setTimeout(()=>this.textContent='📋',1500)"
                style="{{ $btnGray }} font-size:.75rem; padding:.3rem .625rem; color:#C9A84C; border-color:#C9A84C44;">
                📋
            </button>
            <a href="{{ route('ticket.show', $ticket->ticket_code) }}" target="_blank"
                style="{{ $btnGold }} font-size:.75rem; padding:.3rem .75rem; text-decoration:none;">
                Ticket
            </a>
            @endif
            @if($req->status === 'pending')
            <button wire:click="openConfirmRequest({{ $req->id }})" style="{{ $btnGold }} font-size:.75rem; padding:.35rem .75rem;">
                ✓
            </button>
            <button wire:click="declineRequest({{ $req->id }})" style="{{ $btnGray }} font-size:.75rem; padding:.35rem .625rem; color:#EF4444; border-color:#EF444433;">
                ✕
            </button>
            @endif
        </div>
    </div>
    @empty
    <div style="color:#444; font-size:.875rem; padding:.5rem 0;">Noch keine Anfragen eingegangen.</div>
    @endforelse
</div>

{{-- ════════════════════════════════════════════════════════════════════════
     BESTÄTIGTE VORSTELLUNG
═════════════════════════════════════════════════════════════════════════ --}}
@if($screening)
<div style="background:#22C55E11; border:1px solid #22C55E33; border-radius:14px; padding:1.25rem 1.5rem; margin-bottom:1.25rem;">
    <div style="font-size:.7rem; color:#22C55E; text-transform:uppercase; letter-spacing:.1em; margin-bottom:.5rem;">Bestätigte Vorstellung</div>
    <div style="font-weight:700; font-size:1rem; margin-bottom:.25rem;">🎬 {{ $screening->movie?->title }}</div>
    <div style="font-size:.85rem; color:#888;">{{ $screening->starts_at->isoFormat('dddd, D. MMMM YYYY · HH:mm') }} Uhr · {{ $screening->venue?->name }}</div>
    <div style="display:flex; gap:.5rem; margin-top:.875rem; flex-wrap:wrap;">
        <a href="{{ route('cinema.checkin', $screening->id) }}" target="_blank" style="{{ $btnGold }} text-decoration:none; font-size:.8rem;">📟 Einlass-Screen</a>
        <a href="/cinema/entrance/{{ $screening->id }}?autoplay=1" target="_blank" style="{{ $btnGray }} text-decoration:none; font-size:.8rem;">📺 Screen 2</a>
        <a href="/screen/main/{{ $screening->id }}?autoplay=1" target="_blank" style="{{ $btnGray }} text-decoration:none; font-size:.8rem;">🎬 Beamer</a>
        <a href="{{ route('cinema.post-event', $screening->id) }}" target="_blank" style="{{ $btnGray }} text-decoration:none; font-size:.8rem;">⭐ Post-Event</a>
    </div>
</div>
@endif

{{-- ── Confirm-Request Modal ───────────────────────────────────────────── --}}
@if($confirmingRequestId !== null)
@php $req = $event->seatRequests->find($confirmingRequestId); @endphp
<div style="position:fixed; inset:0; background:#000b; z-index:50; display:flex; align-items:center; justify-content:center; padding:1rem;">
<div style="background:#141414; border:1px solid #2a2a2a; border-radius:16px; padding:1.5rem; width:100%; max-width:400px;">
    <h3 style="font-weight:700; margin-bottom:1rem;">Sitz zuweisen · {{ $req?->guest_name }}</h3>
    <label style="{{ $lbl }}">Sitzplatz</label>
    <select wire:model="assignSeatId" style="{{ $inp }}">
        <option value="">– Ohne Sitzplatz –</option>
        @foreach($event->venue?->seats->where('is_active',true) ?? [] as $s)
        <option value="{{ $s->id }}" {{ in_array($s->id, $req?->requested_seat_ids??[]) ? 'selected' : '' }}>
            {{ $s->label }} {{ in_array($s->id, $req?->requested_seat_ids??[]) ? '⭐' : '' }}
        </option>
        @endforeach
    </select>
    <div style="display:flex; gap:.75rem; margin-top:.5rem;">
        <button wire:click="confirmRequest" style="flex:1; {{ $btnGold }}">Bestätigen + Ticket generieren</button>
        <button wire:click="$set('confirmingRequestId',null)" style="{{ $btnGray }}">Abbrechen</button>
    </div>
</div>
</div>
@endif
</div>
