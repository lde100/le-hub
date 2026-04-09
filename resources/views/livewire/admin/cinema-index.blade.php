<div>

{{-- ── TOPBAR ACTIONS ──────────────────────────────────────────────────── --}}
<div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:1.5rem; flex-wrap:wrap; gap:.75rem;">
    <div style="font-size:.9rem; color:#888;">{{ $screenings->count() }} Vorstellung(en)</div>
    <button wire:click="openForm()" style="background:#C9A84C; color:#000; font-weight:700; padding:.6rem 1.25rem; border-radius:10px; border:none; cursor:pointer; font-size:.875rem;">
        + Neue Vorstellung
    </button>
</div>

{{-- ── SCREENING-LISTE ──────────────────────────────────────────────────── --}}
<div style="display:flex; flex-direction:column; gap:.75rem;">
@forelse ($screenings as $s)
@php
    $checked = $s->tickets->where('status','used')->count();
    $total   = $s->tickets->whereIn('status',['valid','used'])->count();
    $statusColors = ['scheduled'=>'#555','open'=>'#C9A84C','sold_out'=>'#EF4444','confirmed'=>'#22C55E','finished'=>'#555','cancelled'=>'#EF4444'];
@endphp
<div style="background:#141414; border:1px solid #1e1e1e; border-radius:14px; padding:1.25rem 1.5rem;">
    <div style="display:flex; justify-content:space-between; align-items:flex-start; gap:1rem; flex-wrap:wrap;">

        {{-- Info --}}
        <div style="flex:1; min-width:200px;">
            <div style="font-size:1.05rem; font-weight:700; margin-bottom:.25rem;">{{ $s->movie?->title ?? '—' }}</div>
            <div style="font-size:.8rem; color:#888; margin-bottom:.5rem;">
                {{ $s->starts_at->isoFormat('dddd, D. MMMM YYYY · HH:mm') }} Uhr
                @if($s->venue) · {{ $s->venue->name }} @endif
            </div>
            <div style="display:flex; gap:.5rem; flex-wrap:wrap; align-items:center;">
                <span style="background:{{ ($statusColors[$s->status] ?? '#555') }}22; color:{{ $statusColors[$s->status] ?? '#555' }}; font-size:.7rem; padding:.2rem .6rem; border-radius:99px; font-weight:600; text-transform:uppercase; letter-spacing:.05em;">
                    {{ $s->status }}
                </span>
                <span style="font-size:.8rem; color:#666;">{{ $s->seating_mode }}</span>
                @if($total > 0)
                <span style="font-size:.8rem; color:#666;">{{ $checked }}/{{ $total }} eingecheckt</span>
                @endif
                @if($s->base_price > 0)
                <span style="font-size:.8rem; color:#666;">{{ number_format($s->base_price,2) }} €</span>
                @endif
            </div>
        </div>

        {{-- Actions --}}
        <div style="display:flex; gap:.5rem; flex-wrap:wrap; align-items:center;">
            {{-- Status ändern --}}
            <select wire:change="updateStatus({{ $s->id }}, $event.target.value)"
                style="background:#0D0D0D; border:1px solid #2a2a2a; border-radius:8px; color:#888; padding:.4rem .625rem; font-size:.8rem; cursor:pointer;">
                @foreach(['scheduled','open','sold_out','confirmed','finished','cancelled'] as $st)
                <option value="{{ $st }}" {{ $s->status===$st?'selected':'' }}>{{ $st }}</option>
                @endforeach
            </select>

            <button wire:click="openTicketManager({{ $s->id }})"
                style="background:#1e1e1e; border:1px solid #333; border-radius:8px; color:#f5f5f5; padding:.4rem .875rem; font-size:.8rem; cursor:pointer;">
                🎟 Tickets
            </button>

            <a href="{{ route('cinema.checkin', $s->id) }}" target="_blank"
                style="background:#1e1e1e; border:1px solid #333; border-radius:8px; color:#f5f5f5; padding:.4rem .875rem; font-size:.8rem; cursor:pointer; text-decoration:none;">
                📟 Einlass
            </a>

            <a href="/cinema/entrance/{{ $s->id }}?autoplay=1" target="_blank"
                style="background:#1e1e1e; border:1px solid #333; border-radius:8px; color:#f5f5f5; padding:.4rem .875rem; font-size:.8rem; cursor:pointer; text-decoration:none;">
                📺 Screen 2
            </a>

            <a href="/screen/main/{{ $s->id }}?autoplay=1" target="_blank"
                style="background:#1e1e1e; border:1px solid #333; border-radius:8px; color:#f5f5f5; padding:.4rem .875rem; font-size:.8rem; cursor:pointer; text-decoration:none;">
                🎬 Beamer
            </a>

            <button wire:click="openForm({{ $s->id }})"
                style="background:transparent; border:1px solid #2a2a2a; border-radius:8px; color:#666; padding:.4rem .625rem; font-size:.8rem; cursor:pointer;">
                ✏️
            </button>
        </div>
    </div>
</div>
@empty
<div style="text-align:center; padding:3rem; color:#444;">
    Noch keine Vorstellungen. <button wire:click="openForm()" style="background:none; border:none; color:#C9A84C; cursor:pointer; text-decoration:underline;">Erste anlegen</button>
</div>
@endforelse
</div>

{{-- ── FORMULAR MODAL ────────────────────────────────────────────────────── --}}
@if($showForm)
<div style="position:fixed; inset:0; background:#000b; z-index:50; display:flex; align-items:center; justify-content:center; padding:1rem;">
<div style="background:#141414; border:1px solid #2a2a2a; border-radius:20px; padding:2rem; width:100%; max-width:520px; max-height:90dvh; overflow-y:auto;">

    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:1.5rem;">
        <h2 style="font-size:1.1rem; font-weight:700;">{{ $editScreeningId ? 'Vorstellung bearbeiten' : 'Neue Vorstellung' }}</h2>
        <button wire:click="$set('showForm',false)" style="background:none; border:none; color:#666; font-size:1.5rem; cursor:pointer; line-height:1;">×</button>
    </div>

    @php $inp = 'width:100%; background:#0D0D0D; border:1px solid #2a2a2a; border-radius:8px; padding:.75rem; color:#f5f5f5; font-size:.9rem; outline:none; margin-bottom:1rem;'; @endphp
    @php $lbl = 'font-size:.75rem; color:#888; display:block; margin-bottom:.3rem;'; @endphp

    <label style="{{ $lbl }}">Filmtitel *</label>
    <input wire:model="movieTitle" type="text" placeholder="Interstellar" style="{{ $inp }}">
    @error('movieTitle') <div style="color:#EF4444; font-size:.8rem; margin-top:-.75rem; margin-bottom:.75rem;">{{ $message }}</div> @enderror

    <div style="display:grid; grid-template-columns:1fr 1fr; gap:.75rem;">
        <div>
            <label style="{{ $lbl }}">Genre</label>
            <input wire:model="movieGenre" type="text" placeholder="Sci-Fi" style="{{ $inp }}">
        </div>
        <div>
            <label style="{{ $lbl }}">Laufzeit (Min)</label>
            <input wire:model="movieDuration" type="number" placeholder="169" style="{{ $inp }}">
        </div>
    </div>

    <div style="display:grid; grid-template-columns:1fr 1fr; gap:.75rem;">
        <div>
            <label style="{{ $lbl }}">FSK / Rating</label>
            <input wire:model="movieRating" type="text" placeholder="FSK 12" style="{{ $inp }}">
        </div>
        <div>
            <label style="{{ $lbl }}">Eintritt (€)</label>
            <input wire:model="basePrice" type="number" step="0.50" placeholder="0.00" style="{{ $inp }}">
        </div>
    </div>

    <label style="{{ $lbl }}">Datum & Uhrzeit *</label>
    <input wire:model="startsAt" type="datetime-local" style="{{ $inp }}">
    @error('startsAt') <div style="color:#EF4444; font-size:.8rem; margin-top:-.75rem; margin-bottom:.75rem;">{{ $message }}</div> @enderror

    <label style="{{ $lbl }}">Sitz-Modus</label>
    <select wire:model="seatingMode" style="{{ $inp }}">
        <option value="seated">Mit Sitzplan</option>
        <option value="open">Freie Platzwahl</option>
        <option value="mixed">Gemischt</option>
    </select>

    @if($seatingMode !== 'seated')
    <label style="{{ $lbl }}">Max. Kapazität</label>
    <input wire:model="maxCapacity" type="number" placeholder="20" style="{{ $inp }}">
    @endif

    <label style="{{ $lbl }}">Notizen (intern)</label>
    <textarea wire:model="screeningNotes" rows="2" style="{{ $inp }} resize:none;" placeholder="z.B. HDMI-Kabel mitbringen"></textarea>

    <div style="display:flex; gap:.75rem; margin-top:.5rem;">
        <button wire:click="saveScreening" style="flex:1; background:#C9A84C; color:#000; font-weight:700; padding:.875rem; border-radius:10px; border:none; cursor:pointer;">
            {{ $editScreeningId ? 'Speichern' : 'Vorstellung anlegen' }}
        </button>
        <button wire:click="$set('showForm',false)" style="padding:.875rem 1.25rem; background:transparent; border:1px solid #333; border-radius:10px; color:#888; cursor:pointer;">
            Abbrechen
        </button>
    </div>
</div>
</div>
@endif

{{-- ── TICKET-MANAGER MODAL ──────────────────────────────────────────────── --}}
@if($showTicketMgr && $activeScreeningId)
@php $s = $screenings->find($activeScreeningId); @endphp
<div style="position:fixed; inset:0; background:#000b; z-index:50; display:flex; align-items:center; justify-content:center; padding:1rem;">
<div style="background:#141414; border:1px solid #2a2a2a; border-radius:20px; padding:2rem; width:100%; max-width:640px; max-height:90dvh; overflow-y:auto;">

    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:1.5rem;">
        <div>
            <h2 style="font-size:1.1rem; font-weight:700; margin-bottom:.25rem;">🎟 Tickets · {{ $s?->movie?->title }}</h2>
            <div style="font-size:.8rem; color:#888;">{{ $s?->starts_at->format('d.m.Y H:i') }} Uhr</div>
        </div>
        <button wire:click="$set('showTicketMgr',false)" style="background:none; border:none; color:#666; font-size:1.5rem; cursor:pointer;">×</button>
    </div>

    {{-- Bestehende Tickets --}}
    @if($s?->tickets->count())
    <div style="margin-bottom:1.5rem;">
        <div style="font-size:.75rem; color:#888; margin-bottom:.75rem; text-transform:uppercase; letter-spacing:.1em;">Bestehende Tickets</div>
        @foreach($s->tickets as $t)
        <div style="display:flex; justify-content:space-between; align-items:center; padding:.625rem .875rem; background:#0D0D0D; border-radius:8px; margin-bottom:.5rem; gap:.75rem;">
            <div>
                <div style="font-size:.875rem; font-weight:600;">{{ $t->booking->customer_name ?? '—' }}</div>
                <div style="font-size:.75rem; color:#666;">{{ $t->seat?->label ?? 'Freier Platz' }} · {{ $t->ticket_code }}</div>
            </div>
            <div style="display:flex; gap:.5rem; align-items:center;">
                <span style="font-size:.7rem; color:{{ $t->status==='used'?'#22C55E':($t->status==='valid'?'#C9A84C':'#EF4444') }};">
                    ● {{ $t->status }}
                </span>
                <a href="{{ route('ticket.show', $t->ticket_code) }}" target="_blank"
                    style="font-size:.75rem; color:#C9A84C; text-decoration:none; background:#C9A84C18; border:1px solid #C9A84C33; padding:.25rem .625rem; border-radius:6px;"
                    onclick="navigator.clipboard?.writeText('{{ route('ticket.show', $t->ticket_code) }}')">
                    🔗 Link
                </a>
            </div>
        </div>
        @endforeach
    </div>
    @endif

    {{-- Neue Tickets anlegen --}}
    <div style="font-size:.75rem; color:#888; margin-bottom:.75rem; text-transform:uppercase; letter-spacing:.1em;">Neue Tickets</div>

    @foreach($manualGuests as $i => $guest)
    <div style="background:#0D0D0D; border:1px solid #1e1e1e; border-radius:10px; padding:.875rem; margin-bottom:.625rem;">

        @if(isset($guest['generated']))
        {{-- Ticket erzeugt — Link anzeigen --}}
        <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:.5rem;">
            <div>
                <div style="font-size:.9rem; font-weight:600; color:#22C55E;">✅ {{ $guest['name'] }}</div>
                <div style="font-size:.75rem; color:#666; font-family:monospace;">{{ $guest['ticket_code'] }}</div>
            </div>
            <div style="display:flex; gap:.5rem;">
                <a href="{{ $guest['ticket_url'] }}" target="_blank"
                    style="font-size:.8rem; background:#C9A84C; color:#000; font-weight:700; padding:.375rem .875rem; border-radius:7px; text-decoration:none;">
                    Ticket öffnen
                </a>
                <button
                    onclick="navigator.clipboard.writeText('{{ $guest['ticket_url'] }}').then(()=>this.textContent='✅ Kopiert')"
                    style="font-size:.8rem; background:#1e1e1e; border:1px solid #333; color:#888; padding:.375rem .875rem; border-radius:7px; cursor:pointer;">
                    📋 Kopieren
                </button>
            </div>
        </div>
        @else
        {{-- Eingabe-Zeile --}}
        <div style="display:grid; grid-template-columns:1fr 1fr auto; gap:.5rem; align-items:end;">
            <div>
                <div style="font-size:.7rem; color:#666; margin-bottom:.25rem;">Name *</div>
                <input wire:model="manualGuests.{{ $i }}.name" type="text" placeholder="Max Mustermann"
                    style="width:100%; background:#141414; border:1px solid #2a2a2a; border-radius:7px; padding:.5rem .75rem; color:#f5f5f5; font-size:.875rem; outline:none;">
            </div>
            @if($s?->seating_mode === 'seated' && $s?->venue)
            <div>
                <div style="font-size:.7rem; color:#666; margin-bottom:.25rem;">Platz</div>
                <select wire:model="manualGuests.{{ $i }}.seat_id"
                    style="width:100%; background:#141414; border:1px solid #2a2a2a; border-radius:7px; padding:.5rem .75rem; color:#f5f5f5; font-size:.875rem; outline:none;">
                    <option value="">– Freier Platz –</option>
                    @foreach($s->venue->seats->where('is_active',true) as $seat)
                    <option value="{{ $seat->id }}">{{ $seat->label }}</option>
                    @endforeach
                </select>
            </div>
            @else
            <div></div>
            @endif
            <button wire:click="removeGuestRow({{ $i }})" style="background:none; border:none; color:#555; font-size:1.1rem; cursor:pointer; padding:.5rem;">×</button>
        </div>
        <div style="margin-top:.5rem;">
            <div style="font-size:.7rem; color:#666; margin-bottom:.25rem;">E-Mail (optional — für Ticket-Versand)</div>
            <input wire:model="manualGuests.{{ $i }}.email" type="email" placeholder="max@beispiel.de"
                style="width:100%; background:#141414; border:1px solid #2a2a2a; border-radius:7px; padding:.5rem .75rem; color:#f5f5f5; font-size:.875rem; outline:none;">
        </div>
        @endif
    </div>
    @endforeach

    <div style="display:flex; gap:.75rem; margin-top:.75rem;">
        <button wire:click="addGuestRow"
            style="flex:1; background:transparent; border:1px dashed #333; border-radius:10px; color:#666; padding:.75rem; cursor:pointer; font-size:.875rem;">
            + Gast hinzufügen
        </button>
        @if(count($manualGuests) > 0 && collect($manualGuests)->where('generated', null)->count() > 0)
        <button wire:click="generateTickets"
            style="flex:1; background:#C9A84C; color:#000; font-weight:700; padding:.75rem; border-radius:10px; border:none; cursor:pointer; font-size:.875rem;">
            🎟 Tickets generieren
        </button>
        @endif
    </div>

</div>
</div>
@endif

</div>
