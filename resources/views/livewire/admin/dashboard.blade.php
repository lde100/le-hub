<div>
@php
$statusColors = ['draft'=>'#555','polling_date'=>'#3B82F6','polling_film'=>'#8B5CF6','booking_open'=>'#C9A84C','confirmed'=>'#22C55E'];
$statusLabels = ['draft'=>'Entwurf','polling_date'=>'Terminumfrage','polling_film'=>'Filmumfrage','booking_open'=>'Buchung offen','confirmed'=>'Bestätigt'];
@endphp

{{-- Stat-Kacheln --}}
<div style="display:grid; grid-template-columns:repeat(auto-fit,minmax(140px,1fr)); gap:.75rem; margin-bottom:1.5rem;">
    @foreach([
        ['label'=>'Aktive Events','value'=>$activeEvents->count(),'icon'=>'🎟'],
        ['label'=>'Heute','value'=>$todayScreenings->count(),'icon'=>'📅'],
        ['label'=>'Offene Anfragen','value'=>$pendingRequests,'icon'=>'💺','highlight'=>$pendingRequests>0],
    ] as $stat)
    <div style="background:#141414; border:1px solid {{ ($stat['highlight']??false)?'#C9A84C44':'#1e1e1e' }}; border-radius:12px; padding:1rem 1.25rem;">
        <div style="font-size:1.5rem; margin-bottom:.375rem;">{{ $stat['icon'] }}</div>
        <div style="font-size:1.75rem; font-weight:900; color:{{ ($stat['highlight']??false)?'#C9A84C':'#f5f5f5' }};">{{ $stat['value'] }}</div>
        <div style="font-size:.75rem; color:#555; margin-top:.25rem;">{{ $stat['label'] }}</div>
    </div>
    @endforeach
</div>

{{-- Heutige Vorstellungen --}}
@if($todayScreenings->count())
<div style="background:#C9A84C11; border:1px solid #C9A84C33; border-radius:14px; padding:1.25rem 1.5rem; margin-bottom:1.25rem;">
    <div style="font-size:.7rem; color:#C9A84C; text-transform:uppercase; letter-spacing:.1em; margin-bottom:.875rem;">📅 Heute</div>
    @foreach($todayScreenings as $s)
    <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:.5rem;">
        <div>
            <div style="font-weight:700;">{{ $s->movie?->title ?? '—' }}</div>
            <div style="font-size:.8rem; color:#888;">{{ $s->starts_at->format('H:i') }} Uhr · {{ $s->tickets->where('status','used')->count() }}/{{ $s->tickets->whereIn('status',['valid','used'])->count() }} eingecheckt</div>
        </div>
        <div style="display:flex; gap:.5rem;">
            <a href="{{ route('cinema.checkin', $s->id) }}" target="_blank"
                style="background:#C9A84C; color:#000; font-weight:700; border-radius:8px; padding:.4rem .875rem; font-size:.8rem; text-decoration:none;">
                📟 Einlass
            </a>
        </div>
    </div>
    @endforeach
</div>
@endif

{{-- Aktive Events --}}
<div style="background:#141414; border:1px solid #1e1e1e; border-radius:14px; padding:1.5rem; margin-bottom:1.25rem;">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:1rem;">
        <h3 style="font-weight:700;">Events</h3>
        <a href="{{ route('admin.events') }}" style="font-size:.8rem; color:#C9A84C; text-decoration:none;">Alle →</a>
    </div>

    @forelse($activeEvents as $e)
    @php $sc = $statusColors[$e->status]??'#555'; $sl = $statusLabels[$e->status]??$e->status; @endphp
    <div style="display:flex; justify-content:space-between; align-items:center; padding:.75rem .875rem; background:#0D0D0D; border-radius:10px; margin-bottom:.5rem; flex-wrap:wrap; gap:.5rem;">
        <div>
            <div style="display:flex; align-items:center; gap:.625rem; margin-bottom:.25rem;">
                <span style="font-weight:600; font-size:.9rem;">{{ $e->title }}</span>
                <span style="background:{{ $sc }}22; color:{{ $sc }}; font-size:.65rem; padding:.15rem .5rem; border-radius:99px; font-weight:600;">{{ $sl }}</span>
            </div>
            @if($e->screenings->first())
            <div style="font-size:.75rem; color:#555;">
                🎬 {{ $e->screenings->first()->movie?->title }} · {{ $e->screenings->first()->starts_at->format('d.m.Y H:i') }} Uhr
            </div>
            @endif
        </div>
        <a href="{{ route('admin.events.detail', $e->id) }}"
            style="background:#1e1e1e; border:1px solid #2a2a2a; border-radius:8px; color:#f5f5f5; padding:.375rem .875rem; font-size:.8rem; text-decoration:none;">
            Verwalten →
        </a>
    </div>
    @empty
    <div style="color:#444; font-size:.875rem;">
        Noch kein Event. <a href="{{ route('admin.events') }}" style="color:#C9A84C; text-decoration:none;">Jetzt anlegen →</a>
    </div>
    @endforelse
</div>

{{-- Letzte Tickets --}}
@if($recentTickets->count())
<div style="background:#141414; border:1px solid #1e1e1e; border-radius:14px; padding:1.5rem;">
    <h3 style="font-weight:700; margin-bottom:1rem;">Letzte Tickets</h3>
    @foreach($recentTickets as $t)
    <div style="display:flex; justify-content:space-between; align-items:center; padding:.5rem .75rem; background:#0D0D0D; border-radius:8px; margin-bottom:.375rem; flex-wrap:wrap; gap:.5rem;">
        <div>
            <span style="font-size:.875rem; font-weight:600;">{{ $t->booking->customer_name ?? '—' }}</span>
            <span style="font-size:.75rem; color:#555; margin-left:.5rem;">{{ $t->screening?->movie?->title }}</span>
        </div>
        <div style="display:flex; gap:.375rem; align-items:center;">
            <span style="font-size:.7rem; color:{{ $t->status==='used'?'#22C55E':($t->status==='valid'?'#C9A84C':'#555') }};">● {{ $t->status }}</span>
            <button
                onclick="navigator.clipboard.writeText('{{ route('ticket.show', $t->ticket_code) }}').then(()=>this.textContent='✅').catch(()=>{}); setTimeout(()=>this.textContent='📋',1500)"
                style="background:none; border:none; color:#555; cursor:pointer; font-size:.85rem; padding:.2rem .375rem;">
                📋
            </button>
        </div>
    </div>
    @endforeach
</div>
@endif
</div>
