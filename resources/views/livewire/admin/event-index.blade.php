<div>
<div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:1.5rem;">
    <div style="font-size:.9rem; color:#888;">{{ $events->count() }} Event(s)</div>
    <button wire:click="openForm" style="background:#C9A84C; color:#000; font-weight:700; padding:.6rem 1.25rem; border-radius:10px; border:none; cursor:pointer; font-size:.875rem;">
        + Neues Event
    </button>
</div>

@php
$statusMeta = [
    'draft'        => ['label'=>'Entwurf',           'color'=>'#555'],
    'polling_date' => ['label'=>'Terminabstimmung',   'color'=>'#3B82F6'],
    'polling_film' => ['label'=>'Filmabstimmung',     'color'=>'#8B5CF6'],
    'booking_open' => ['label'=>'Buchung offen',      'color'=>'#C9A84C'],
    'confirmed'    => ['label'=>'Bestätigt',          'color'=>'#22C55E'],
    'finished'     => ['label'=>'Abgeschlossen',      'color'=>'#555'],
    'cancelled'    => ['label'=>'Abgesagt',           'color'=>'#EF4444'],
];
@endphp

<div style="display:flex; flex-direction:column; gap:.75rem;">
@forelse($events as $e)
@php $sm = $statusMeta[$e->status] ?? ['label'=>$e->status,'color'=>'#555']; @endphp
<div style="background:#141414; border:1px solid #1e1e1e; border-radius:14px; padding:1.25rem 1.5rem; display:flex; justify-content:space-between; align-items:center; gap:1rem; flex-wrap:wrap;">
    <div style="flex:1; min-width:200px;">
        <div style="display:flex; align-items:center; gap:.75rem; margin-bottom:.375rem;">
            <span style="font-weight:700; font-size:1rem;">{{ $e->title }}</span>
            <span style="background:{{ $sm['color'] }}22; color:{{ $sm['color'] }}; font-size:.7rem; padding:.2rem .625rem; border-radius:99px; font-weight:600;">{{ $sm['label'] }}</span>
        </div>
        <div style="font-size:.8rem; color:#555;">
            {{ $e->type }} ·
            {{ $e->venue?->name ?? 'Kein Saal' }} ·
            {{ $e->polls->count() }} Poll(s) ·
            {{ $e->screenings->count() }} Vorstellung(en)
        </div>
        @if($e->screenings->first()?->movie)
        <div style="font-size:.8rem; color:#888; margin-top:.25rem;">
            🎬 {{ $e->screenings->first()->movie->title }} ·
            {{ $e->screenings->first()->starts_at->format('d.m.Y H:i') }} Uhr
        </div>
        @endif
    </div>
    <div style="display:flex; gap:.5rem; flex-wrap:wrap;">
        <a href="{{ $e->public_url }}" target="_blank"
            style="background:#1e1e1e; border:1px solid #2a2a2a; border-radius:8px; color:#C9A84C; padding:.4rem .875rem; font-size:.8rem; text-decoration:none;"
            onclick="navigator.clipboard?.writeText('{{ $e->public_url }}')">
            🔗 Link
        </a>
        <a href="{{ route('admin.events.hub', $e->id) }}"
            style="background:#C9A84C; color:#000; font-weight:700; border-radius:8px; padding:.4rem .875rem; font-size:.8rem; text-decoration:none;">
            🎛 Hub
        </a>
        <a href="{{ route('admin.events.detail', $e->id) }}"
            style="background:#1e1e1e; border:1px solid #2a2a2a; border-radius:8px; color:#f5f5f5; padding:.4rem .875rem; font-size:.8rem; text-decoration:none;">
            Verwalten
        </a>
        <button wire:click="deleteEvent({{ $e->id }})"
            wire:confirm="Event '{{ $e->title }}' wirklich löschen?"
            style="background:transparent; border:1px solid #2a2a2a; border-radius:8px; color:#555; padding:.4rem .625rem; font-size:.8rem; cursor:pointer;">
            🗑
        </button>
    </div>
</div>
@empty
<div style="text-align:center; padding:3rem; color:#444;">
    Noch kein Event. <button wire:click="openForm" style="background:none; border:none; color:#C9A84C; cursor:pointer; text-decoration:underline;">Erstes anlegen</button>
</div>
@endforelse
</div>

@if($showForm)
<div style="position:fixed; inset:0; background:#000b; z-index:50; display:flex; align-items:center; justify-content:center; padding:1rem;">
<div style="background:#141414; border:1px solid #2a2a2a; border-radius:20px; padding:2rem; width:100%; max-width:460px;">
    <div style="display:flex; justify-content:space-between; margin-bottom:1.5rem;">
        <h2 style="font-weight:700;">Neues Event</h2>
        <button wire:click="$set('showForm',false)" style="background:none;border:none;color:#555;font-size:1.25rem;cursor:pointer;">×</button>
    </div>

    @php $inp = 'width:100%; background:#0D0D0D; border:1px solid #2a2a2a; border-radius:8px; padding:.75rem; color:#f5f5f5; font-size:.9rem; outline:none; margin-bottom:1rem;'; @endphp
    @php $lbl = 'font-size:.75rem; color:#888; display:block; margin-bottom:.3rem;'; @endphp

    <label style="{{ $lbl }}">Titel *</label>
    <input wire:model="title" type="text" placeholder='z.B. "Kinoabend April"' style="{{ $inp }}">
    @error('title') <div style="color:#EF4444; font-size:.8rem; margin-top:-.75rem; margin-bottom:.75rem;">{{ $message }}</div> @enderror

    <label style="{{ $lbl }}">Beschreibung</label>
    <textarea wire:model="description" rows="2" style="{{ $inp }} resize:none;" placeholder="Optional..."></textarea>

    <div style="display:grid; grid-template-columns:1fr 1fr; gap:.75rem;">
        <div>
            <label style="{{ $lbl }}">Typ</label>
            <select wire:model="type" style="{{ $inp }}">
                <option value="cinema">Kino</option>
                <option value="live">Live-Event</option>
                <option value="party">Party</option>
                <option value="custom">Sonstiges</option>
            </select>
        </div>
        <div>
            <label style="{{ $lbl }}">Sitz-Modus</label>
            <select wire:model="seatingMode" style="{{ $inp }}">
                <option value="seated">Mit Sitzplan</option>
                <option value="open">Freie Plätze</option>
            </select>
        </div>
    </div>

    @if($venues->count() > 1)
    <label style="{{ $lbl }}">Saal</label>
    <select wire:model="venueId" style="{{ $inp }}">
        <option value="">Standard-Saal</option>
        @foreach($venues as $v)
        <option value="{{ $v->id }}">{{ $v->name }}</option>
        @endforeach
    </select>
    @endif

    <div style="display:flex; gap:.75rem; margin-top:.25rem;">
        <button wire:click="createEvent" style="flex:1; background:#C9A84C; color:#000; font-weight:700; padding:.875rem; border-radius:10px; border:none; cursor:pointer;">
            Event anlegen
        </button>
        <button wire:click="$set('showForm',false)" style="padding:.875rem 1.25rem; background:transparent; border:1px solid #2a2a2a; border-radius:10px; color:#666; cursor:pointer;">
            Abbrechen
        </button>
    </div>
</div>
</div>
@endif
</div>
