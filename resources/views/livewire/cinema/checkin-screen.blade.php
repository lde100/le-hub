<div
    style="display:grid; grid-template-columns:1fr 340px; height:100dvh; gap:0; font-family:system-ui,sans-serif;"
    x-data="checkinApp()"
    @handle-scan.window="$wire.handleScan($event.detail.code)"
    @scan-result.window="playChime($event.detail.status); setTimeout(() => document.getElementById('scan-input')?.focus(), 100)"
    @ticket-created.window="onTicketCreated($event.detail)"
>

{{-- ══ LINKS: Saalplan + Gästeliste ══════════════════════════════════════ --}}
<div style="background:#141414; border-right:1px solid #1e1e1e; display:flex; flex-direction:column; overflow:hidden;">

    {{-- Header --}}
    <div style="padding:1rem 1.5rem; border-bottom:1px solid #1e1e1e; display:flex; justify-content:space-between; align-items:center; gap:.75rem; flex-wrap:wrap;">
        <div style="display:flex; align-items:center; gap:.75rem;">
            <div style="background:#C9A84C; width:30px; height:30px; border-radius:6px; display:flex; align-items:center; justify-content:center; font-weight:900; color:#000; font-size:11px; flex-shrink:0;">LE</div>
            <div>
                <div style="font-weight:700; font-size:.95rem;">{{ $screening->movie?->title ?? 'Vorstellung' }}</div>
                <div style="font-size:.75rem; color:#666;">{{ $screening->starts_at->format('d.m.Y · H:i') }} Uhr</div>
            </div>
        </div>
        <div style="display:flex; gap:.5rem; flex-wrap:wrap; align-items:center;">
            <button wire:click="openBoxOffice" style="background:#C9A84C22; border:1px solid #C9A84C44; border-radius:7px; color:#C9A84C; padding:.375rem .75rem; font-size:.8rem; cursor:pointer;">🎟 Kasse</button>
            <button wire:click="openAddSeat" style="background:#1e1e1e; border:1px solid #2a2a2a; border-radius:7px; color:#888; padding:.375rem .75rem; font-size:.8rem; cursor:pointer;">+ Sitz</button>
            <button wire:click="openEditScreening" style="background:#1e1e1e; border:1px solid #2a2a2a; border-radius:7px; color:#888; padding:.375rem .75rem; font-size:.8rem; cursor:pointer;">✏️</button>
            <button wire:click="$toggle('showTickerForm')" style="background:#1e1e1e; border:1px solid #2a2a2a; border-radius:7px; color:#888; padding:.375rem .75rem; font-size:.8rem; cursor:pointer;">📢</button>
            <button @click="toggleChime()"
                :style="chimeEnabled ? 'background:#C9A84C22; border:1px solid #C9A84C44; border-radius:7px; color:#C9A84C; padding:.375rem .625rem; font-size:.85rem; cursor:pointer;' : 'background:#1e1e1e; border:1px solid #2a2a2a; border-radius:7px; color:#444; padding:.375rem .625rem; font-size:.85rem; cursor:pointer;'">
                🔔
            </button>
            <div style="font-size:1.25rem; font-weight:900; color:{{ $all_done ? '#22C55E' : '#C9A84C' }}; min-width:50px; text-align:right;">
                {{ $checked_in_count }}<span style="font-size:.75rem; color:#444;">/{{ $total_tickets }}</span>
            </div>
        </div>
    </div>

    {{-- State-Control-Bar --}}
    <div style="padding:.625rem 1.5rem; background:#0a0a0a; border-bottom:1px solid #1e1e1e; display:flex; gap:.5rem; align-items:center; flex-wrap:wrap;">
        <span style="font-size:.7rem; color:#444; text-transform:uppercase; letter-spacing:.1em; margin-right:.25rem;">Zustand:</span>
        @if($screeningState === 'countdown')
            <span style="font-size:.75rem; color:#555; background:#1e1e1e; padding:.3rem .75rem; border-radius:99px;">⏳ Countdown</span>
            <button wire:click="setStateReady" style="background:#C9A84C22; border:1px solid #C9A84C44; border-radius:7px; color:#C9A84C; padding:.35rem .875rem; font-size:.8rem; cursor:pointer; margin-left:auto;">→ Gleich geht's los</button>
        @elseif($screeningState === 'ready')
            <span style="font-size:.75rem; color:#C9A84C; background:#C9A84C18; padding:.3rem .75rem; border-radius:99px; font-weight:600;">✨ Gleich geht's los</span>
            <button wire:click="triggerLastGong" style="background:#1e1e1e; border:1px solid #333; border-radius:7px; color:#f5f5f5; padding:.35rem .875rem; font-size:.8rem; cursor:pointer;">🔔 Letzter Gong</button>
            <button wire:click="setStatePlaying" style="background:#22C55E; color:#000; font-weight:700; border:none; border-radius:7px; padding:.35rem .875rem; font-size:.8rem; cursor:pointer; margin-left:auto;">▶ Film läuft</button>
        @elseif($screeningState === 'playing')
            <span style="font-size:.75rem; color:#22C55E; background:#22C55E18; padding:.3rem .75rem; border-radius:99px; font-weight:600;">🎬 Film läuft</span>
            <button wire:click="resetState" style="background:transparent; border:1px solid #2a2a2a; border-radius:7px; color:#555; padding:.35rem .75rem; font-size:.75rem; cursor:pointer; margin-left:auto;">↩ Reset</button>
        @endif
    </div>

    {{-- Ticker-Form --}}
    @if($showTickerForm)
    <div style="padding:.75rem 1.5rem; background:#0D0D0D; border-bottom:1px solid #1e1e1e; display:flex; gap:.5rem;">
        <input wire:model="tickerText" type="text" placeholder="Nachricht auf alle Screens..."
            style="flex:1; background:#141414; border:1px solid #333; border-radius:7px; padding:.5rem .75rem; color:#f5f5f5; font-size:.875rem; outline:none;">
        <button wire:click="sendTicker" style="background:#C9A84C; color:#000; font-weight:700; border:none; border-radius:7px; padding:.5rem 1rem; cursor:pointer; font-size:.875rem;">Senden</button>
        <button wire:click="clearTicker" style="background:#1e1e1e; border:1px solid #333; border-radius:7px; color:#666; padding:.5rem .75rem; cursor:pointer; font-size:.8rem;">✕</button>
    </div>
    @endif

    {{-- Saalplan --}}
    <div style="padding:1.25rem 1.5rem; border-bottom:1px solid #1e1e1e;">
        <div style="font-size:.6rem; color:#444; letter-spacing:.15em; text-transform:uppercase; text-align:center; margin-bottom:.75rem;">LEINWAND</div>
        <div style="background:#1e1e1e; height:5px; border-radius:3px; margin-bottom:1.25rem;"></div>
        @foreach ($screening->venue->seats->groupBy('row') as $row => $seats)
        <div style="margin-bottom:.875rem;">
            <div style="font-size:.65rem; color:#333; letter-spacing:.1em; text-transform:uppercase; margin-bottom:.375rem;">{{ $row }}</div>
            <div style="display:flex; gap:.5rem; flex-wrap:wrap;">
                @foreach ($seats as $seat)
                @php
                    $isCheckedIn  = in_array($seat->id, $checked_in_seat_ids);
                    $isHighlighted = ($lastScan['seat_id'] ?? null) === $seat->id && ($lastScan['status'] ?? '') === 'success';
                    $ticket = $screening->tickets->where('seat_id', $seat->id)->whereIn('status',['valid','used'])->first();
                @endphp
                <button
                    @if($ticket) wire:click="openEdit({{ $ticket->id }})" title="{{ $ticket->booking->customer_name }}" @endif
                    style="padding:.375rem .75rem; border-radius:7px; font-size:.8rem; cursor:{{ $ticket ? 'pointer' : 'default' }};
                        border:1.5px solid {{ $isHighlighted ? '#C9A84C' : ($isCheckedIn ? '#22C55E' : ($ticket ? '#333' : '#1e1e1e')) }};
                        background:{{ $isHighlighted ? '#C9A84C22' : ($isCheckedIn ? '#22C55E18' : 'transparent') }};
                        color:{{ $isHighlighted ? '#C9A84C' : ($isCheckedIn ? '#22C55E' : ($ticket ? '#888' : '#2a2a2a')) }};">
                    {{ $seat->label }}
                    @if($isCheckedIn) <span style="font-size:.65rem;">✓</span> @endif
                </button>
                @endforeach
            </div>
        </div>
        @endforeach
    </div>

    {{-- Gästeliste --}}
    <div style="flex:1; overflow-y:auto; padding:.75rem 1.5rem;">
        <div style="font-size:.65rem; color:#444; letter-spacing:.1em; text-transform:uppercase; margin-bottom:.625rem;">Noch ausstehend ({{ $pending_tickets->count() }})</div>
        @forelse($pending_tickets as $t)
        <div style="display:flex; justify-content:space-between; align-items:center; padding:.5rem .625rem; border-radius:8px; margin-bottom:.375rem; background:#0D0D0D;">
            <div>
                <div style="font-size:.85rem; font-weight:600;">{{ $t->booking->customer_name }}</div>
                <div style="font-size:.7rem; color:#555;">{{ $t->seat?->label ?? 'Freier Platz' }}</div>
            </div>
            <div style="display:flex; gap:.375rem;">
                <button wire:click="checkInManually({{ $t->id }})" style="background:#22C55E22; border:1px solid #22C55E33; border-radius:6px; color:#22C55E; padding:.3rem .625rem; font-size:.75rem; cursor:pointer;">✓</button>
                <button wire:click="openEdit({{ $t->id }})" style="background:#1e1e1e; border:1px solid #2a2a2a; border-radius:6px; color:#666; padding:.3rem .5rem; font-size:.75rem; cursor:pointer;">✏</button>
                <a href="{{ route('ticket.label', $t->ticket_code) }}" target="_blank" style="background:#1e1e1e; border:1px solid #2a2a2a; border-radius:6px; color:#666; padding:.3rem .5rem; font-size:.75rem; cursor:pointer; text-decoration:none;">🏷</a>
            </div>
        </div>
        @empty
        @if($all_done && $total_tickets > 0)
        <div style="text-align:center; padding:1rem; color:#22C55E; font-size:.875rem;">🎬 Alle eingecheckt!</div>
        @else
        <div style="text-align:center; padding:1rem; color:#333; font-size:.8rem;">Keine offenen Tickets</div>
        @endif
        @endforelse
    </div>

    {{-- Fortschritt --}}
    <div style="padding:.875rem 1.5rem; border-top:1px solid #1e1e1e;">
        @php $pct = $total_tickets > 0 ? round($checked_in_count / $total_tickets * 100) : 0; @endphp
        <div style="display:flex; justify-content:space-between; font-size:.75rem; color:#555; margin-bottom:.375rem;">
            <span>Einlass</span><span>{{ $pct }}%</span>
        </div>
        <div style="background:#1e1e1e; border-radius:99px; height:5px;">
            <div style="background:{{ $all_done ? '#22C55E' : '#C9A84C' }}; height:5px; width:{{ $pct }}%; border-radius:99px; transition:width .5s;"></div>
        </div>
        @if($all_done && $total_tickets > 0)
        <div style="text-align:center; margin-top:.5rem; font-size:.8rem; color:#22C55E; font-weight:700;">🎬 Film kann starten!</div>
        @endif
    </div>
</div>

{{-- ══ RECHTS: Scanner-Status ══════════════════════════════════════════════ --}}
<div style="background:#0D0D0D; display:flex; flex-direction:column;">
    <div style="flex:1; display:flex; flex-direction:column; align-items:center; justify-content:center; padding:1.5rem; text-align:center;">

        @if(!$lastScan)
        <div style="font-size:2.5rem; margin-bottom:.75rem; opacity:.4;">📟</div>
        <div style="font-size:.95rem; color:#444; margin-bottom:1.25rem;">Bereit zum Scannen</div>
        <div x-data="{ code: '' }" style="width:100%; padding:0 .5rem;">
            <input
                x-model="code"
                x-ref="scanInput"
                id="scan-input"
                type="text"
                placeholder="Code eingeben + Enter..."
                @keydown.enter="if(code.trim()){ Livewire.dispatch('handle-scan',{code:code.trim()}); code=''; }"
                x-init="$el.focus()"
                style="width:100%; background:#141414; border:1px solid #2a2a2a; border-radius:10px; padding:.75rem 1rem; color:#f5f5f5; font-size:.875rem; outline:none; text-align:center;">
            <div style="font-size:.7rem; color:#444; margin-top:.375rem;">Manuelle Eingabe oder HID-Scanner</div>
        </div>

        @elseif($lastScan['status'] === 'success')
        <div style="font-size:3rem; margin-bottom:.75rem;">✅</div>
        <div style="font-size:1.25rem; font-weight:700; color:#22C55E; margin-bottom:.375rem;">Willkommen!</div>
        <div style="font-size:1.1rem; font-weight:700; margin-bottom:.5rem;">{{ $lastScan['name'] }}</div>
        @if($lastScan['seat'] ?? null)
        <div style="background:#C9A84C; color:#000; font-weight:900; padding:.375rem 1rem; border-radius:7px; font-size:1rem; letter-spacing:1px;">
            💺 {{ $lastScan['seat'] }}
        </div>
        @endif

        @elseif($lastScan['status'] === 'warning')
        <div style="font-size:3rem; margin-bottom:.75rem;">⚠️</div>
        <div style="font-size:1.1rem; color:#F59E0B; font-weight:700; margin-bottom:.5rem;">{{ $lastScan['message'] }}</div>
        <div style="font-size:.9rem; color:#888; margin-bottom:.25rem;">{{ $lastScan['name'] ?? '' }}</div>
        @if(isset($lastScan['used_at']))
        <div style="font-size:.8rem; color:#555;">Entwertet: {{ $lastScan['used_at'] }} Uhr</div>
        @endif
        <div style="margin-top:1rem; background:#F59E0B18; border:1px solid #F59E0B33; border-radius:8px; padding:.625rem 1rem; font-size:.8rem; color:#F59E0B;">
            Bereits eingecheckt
        </div>

        @elseif($lastScan['status'] === 'error')
        <div style="font-size:3rem; margin-bottom:.75rem;">❌</div>
        <div style="font-size:1.1rem; color:#EF4444; font-weight:700; margin-bottom:.5rem;">{{ $lastScan['message'] }}</div>
        <div style="font-size:.75rem; color:#444; font-family:monospace; word-break:break-all; margin-top:.375rem; max-width:200px;">{{ $lastScan['code'] ?? '' }}</div>
        <div style="margin-top:1rem; background:#EF444418; border:1px solid #EF444433; border-radius:8px; padding:.625rem 1rem; font-size:.8rem; color:#EF4444;">
            Ungültiges Ticket
        </div>
        @endif
    </div>
</div>

{{-- ══ MODALS ═══════════════════════════════════════════════════════════════ --}}
@php
    $modalStyle = 'position:fixed; inset:0; background:#000c; z-index:60; display:flex; align-items:center; justify-content:center; padding:1rem;';
    $cardStyle  = 'background:#141414; border:1px solid #2a2a2a; border-radius:16px; padding:1.5rem; width:100%; max-width:420px; max-height:85dvh; overflow-y:auto;';
    $inp        = 'width:100%; background:#0D0D0D; border:1px solid #2a2a2a; border-radius:8px; padding:.625rem .875rem; color:#f5f5f5; font-size:.9rem; outline:none; margin-bottom:.875rem;';
    $btnGold    = 'flex:1; background:#C9A84C; color:#000; font-weight:700; padding:.75rem; border-radius:9px; border:none; cursor:pointer; font-size:.9rem;';
    $btnGray    = 'padding:.75rem 1.25rem; background:transparent; border:1px solid #2a2a2a; border-radius:9px; color:#666; cursor:pointer; font-size:.875rem;';
@endphp

@if($showBoxOffice)
<div style="{{ $modalStyle }}">
<div style="{{ $cardStyle }}">
    <div style="display:flex; justify-content:space-between; margin-bottom:1.25rem;">
        <h3 style="font-weight:700;">🎟 Abendkasse</h3>
        <button wire:click="$set('showBoxOffice',false)" style="background:none; border:none; color:#555; font-size:1.25rem; cursor:pointer;">×</button>
    </div>
    <label style="font-size:.75rem; color:#888; display:block; margin-bottom:.3rem;">Name *</label>
    <input wire:model="boxName" type="text" placeholder="Gast-Name" style="{{ $inp }}" autofocus>
    @error('boxName') <div style="color:#EF4444; font-size:.75rem; margin-top:-.625rem; margin-bottom:.625rem;">{{ $message }}</div> @enderror
    @if($screening->venue && $screening->seating_mode === 'seated')
    <label style="font-size:.75rem; color:#888; display:block; margin-bottom:.3rem;">Platz</label>
    <select wire:model="boxSeatId" style="{{ $inp }}">
        <option value="">– Kein Sitzplatz –</option>
        @foreach($screening->venue->seats->where('is_active',true) as $s)
        @php $taken = $screening->tickets->where('seat_id',$s->id)->whereIn('status',['valid','used'])->count() > 0; @endphp
        <option value="{{ $s->id }}" {{ $taken ? 'disabled' : '' }}>{{ $s->label }}{{ $taken ? ' (belegt)' : '' }}</option>
        @endforeach
    </select>
    @endif
    @error('boxSeatId') <div style="color:#EF4444; font-size:.8rem; margin-top:-.625rem; margin-bottom:.625rem;">{{ $message }}</div> @enderror
    <label style="font-size:.75rem; color:#888; display:block; margin-bottom:.3rem;">E-Mail (optional)</label>
    <input wire:model="boxEmail" type="email" placeholder="gast@beispiel.de" style="{{ $inp }}">
    <div style="display:flex; gap:.625rem;">
        <button wire:click="createWalkIn" style="{{ $btnGold }}">Ticket erstellen</button>
        <button wire:click="$set('showBoxOffice',false)" style="{{ $btnGray }}">Abbrechen</button>
    </div>
</div>
</div>
@endif

@if($showEdit)
<div style="{{ $modalStyle }}">
<div style="{{ $cardStyle }}">
    <div style="display:flex; justify-content:space-between; margin-bottom:1.25rem;">
        <h3 style="font-weight:700;">✏️ Ticket bearbeiten</h3>
        <button wire:click="$set('showEdit',false)" style="background:none; border:none; color:#555; font-size:1.25rem; cursor:pointer;">×</button>
    </div>
    <label style="font-size:.75rem; color:#888; display:block; margin-bottom:.3rem;">Name</label>
    <input wire:model="editName" type="text" style="{{ $inp }}">
    @if($screening->venue && $screening->seating_mode === 'seated')
    <label style="font-size:.75rem; color:#888; display:block; margin-bottom:.3rem;">Platz</label>
    <select wire:model="editSeatId" style="{{ $inp }}">
        <option value="">– Kein Sitzplatz –</option>
        @foreach($screening->venue->seats->where('is_active',true) as $s)
        <option value="{{ $s->id }}">{{ $s->label }}</option>
        @endforeach
    </select>
    @endif
    <label style="font-size:.75rem; color:#888; display:block; margin-bottom:.3rem;">Status</label>
    <select wire:model="editStatus" style="{{ $inp }}">
        <option value="valid">Gültig</option>
        <option value="used">Entwertet</option>
        <option value="cancelled">Storniert</option>
    </select>
    <div style="display:flex; gap:.625rem;">
        <button wire:click="saveTicketEdit" style="{{ $btnGold }}">Speichern</button>
        @if($editTicketId)
        <button wire:click="cancelTicket({{ $editTicketId }}); $set('showEdit',false)" style="padding:.75rem; background:#EF444418; border:1px solid #EF444433; border-radius:9px; color:#EF4444; cursor:pointer; font-size:.85rem;">Storno</button>
        @endif
        <button wire:click="$set('showEdit',false)" style="{{ $btnGray }}">Abbrechen</button>
    </div>
</div>
</div>
@endif

@if($showAddSeat)
<div style="{{ $modalStyle }}">
<div style="{{ $cardStyle }}">
    <div style="display:flex; justify-content:space-between; margin-bottom:1.25rem;">
        <h3 style="font-weight:700;">+ Sitz hinzufügen</h3>
        <button wire:click="$set('showAddSeat',false)" style="background:none; border:none; color:#555; font-size:1.25rem; cursor:pointer;">×</button>
    </div>
    <label style="font-size:.75rem; color:#888; display:block; margin-bottom:.3rem;">Bezeichnung *</label>
    <input wire:model="newSeatLabel" type="text" placeholder='z.B. "Extra 1"' style="{{ $inp }}" autofocus>
    <label style="font-size:.75rem; color:#888; display:block; margin-bottom:.3rem;">Reihe</label>
    <input wire:model="newSeatRow" type="text" placeholder="Extra" style="{{ $inp }}">
    <label style="font-size:.75rem; color:#888; display:block; margin-bottom:.3rem;">Typ</label>
    <select wire:model="newSeatType" style="{{ $inp }}">
        <option value="standard">Standard</option>
        <option value="couch">Couch</option>
        <option value="recliner">Sessel</option>
    </select>
    <div style="display:flex; gap:.625rem;">
        <button wire:click="saveNewSeat" style="{{ $btnGold }}">Hinzufügen</button>
        <button wire:click="$set('showAddSeat',false)" style="{{ $btnGray }}">Abbrechen</button>
    </div>
</div>
</div>
@endif

@if($showEditScreening)
<div style="{{ $modalStyle }}">
<div style="{{ $cardStyle }}">
    <div style="display:flex; justify-content:space-between; margin-bottom:1.25rem;">
        <h3 style="font-weight:700;">✏️ Film / Zeit ändern</h3>
        <button wire:click="$set('showEditScreening',false)" style="background:none; border:none; color:#555; font-size:1.25rem; cursor:pointer;">×</button>
    </div>
    <label style="font-size:.75rem; color:#888; display:block; margin-bottom:.3rem;">Filmtitel</label>
    <input wire:model="editMovieTitle" type="text" style="{{ $inp }}">
    <label style="font-size:.75rem; color:#888; display:block; margin-bottom:.3rem;">Datum & Uhrzeit</label>
    <input wire:model="editStartsAt" type="datetime-local" style="{{ $inp }}">
    <div style="display:flex; gap:.625rem;">
        <button wire:click="saveScreeningEdit" style="{{ $btnGold }}">Speichern</button>
        <button wire:click="$set('showEditScreening',false)" style="{{ $btnGray }}">Abbrechen</button>
    </div>
</div>
</div>
@endif

{{-- Kein Vollbild-Overlay auf Scanner-PC — Welcome läuft auf Infoscreen/EntranceScreen --}}

</div>

@push('scripts')
<script>
window.checkinApp = function() {
    return {
        chimeEnabled: localStorage.getItem('le_chime_enabled') !== 'false',

        init() {
            import('/js/barcode/BarcodeListener.js').then(({ default: BL }) => {
                new BL(code => Livewire.dispatch('handle-scan', { code }), { captureInput: false });
            });
        },

        playChime(type) {
            if (window.playCheckinChime) window.playCheckinChime(type || 'success');
        },

        toggleChime() {
            this.chimeEnabled = !this.chimeEnabled;
            if (window.setChimeEnabled) window.setChimeEnabled(this.chimeEnabled);
        },
    }
}
</script>
@endpush
