<div x-data="{ copiedUrl: '' }">

{{-- ── Header ────────────────────────────────────────────────────────────── --}}
<div style="display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:1.5rem; flex-wrap:wrap; gap:.75rem;">
    <div>
        <a href="{{ route('admin.events.detail', $event->id) }}" style="font-size:.8rem; color:#555; text-decoration:none;">← Event-Detail</a>
        <h1 style="font-size:1.25rem; font-weight:800; margin-top:.25rem;">🎛 Hub · {{ $event->title }}</h1>
        @if($screening)
        <div style="font-size:.85rem; color:#888; margin-top:.375rem;">
            🎬 {{ $screening->movie?->title }} ·
            {{ $screening->starts_at->isoFormat('D. MMMM · HH:mm') }} Uhr
        </div>
        @endif
    </div>
    <a href="{{ route('admin.events.detail', $event->id) }}"
        style="background:#1e1e1e; border:1px solid #2a2a2a; border-radius:9px; color:#888; padding:.5rem 1rem; font-size:.85rem; text-decoration:none;">
        Verwalten ↗
    </a>
</div>

{{-- ── LINK-KARTEN ──────────────────────────────────────────────────────────── --}}
@foreach($links as $groupKey => $group)
<div style="margin-bottom:1.5rem;">
    <div style="font-size:.7rem; color:#555; text-transform:uppercase; letter-spacing:.12em; margin-bottom:.75rem;">
        {{ $group['label'] }}
    </div>

    <div style="display:flex; flex-direction:column; gap:.625rem;">
    @foreach($group['items'] as $link)
    <div style="background:#141414; border:1px solid {{ isset($link['primary'])?'#C9A84C44':'#1e1e1e' }}; border-radius:14px; padding:1rem 1.25rem; display:flex; gap:1rem; align-items:center; flex-wrap:wrap;">

        {{-- QR-Code --}}
        @if(!empty($link['qr']))
        <div style="flex-shrink:0; background:#fff; padding:5px; border-radius:8px; width:80px; height:80px; display:flex; align-items:center; justify-content:center;">
            <img
                src="https://api.qrserver.com/v1/create-qr-code/?size=140x140&margin=0&data={{ urlencode($link['url']) }}"
                width="70" height="70"
                style="display:block; border-radius:2px;"
                alt="QR"
                loading="lazy"
            >
        </div>
        @endif

        {{-- Info + Buttons --}}
        <div style="flex:1; min-width:200px;">
            <div style="display:flex; align-items:center; gap:.5rem; margin-bottom:.25rem; flex-wrap:wrap;">
                <span style="font-weight:700; font-size:.95rem;">{{ $link['label'] }}</span>
                @if(!empty($link['auth']))
                <span style="background:#3B82F622; color:#3B82F6; font-size:.65rem; padding:.15rem .5rem; border-radius:99px; font-weight:600;">Login nötig</span>
                @endif
                @if(!empty($link['primary']))
                <span style="background:#C9A84C22; color:#C9A84C; font-size:.65rem; padding:.15rem .5rem; border-radius:99px; font-weight:600;">WhatsApp</span>
                @endif
            </div>
            @if(!empty($link['hint']))
            <div style="font-size:.775rem; color:#555; margin-bottom:.625rem;">{{ $link['hint'] }}</div>
            @endif

            {{-- URL-Anzeige --}}
            <div style="background:#0D0D0D; border-radius:7px; padding:.4rem .75rem; font-size:.72rem; font-family:monospace; color:#666; margin-bottom:.625rem; word-break:break-all; line-height:1.4;">
                {{ $link['url'] }}
            </div>

            {{-- Action-Buttons --}}
            <div style="display:flex; gap:.5rem; flex-wrap:wrap;">
                {{-- Kopieren --}}
                <button
                    @click="
                        navigator.clipboard.writeText('{{ $link['url'] }}')
                            .then(() => { copiedUrl = '{{ $link['url'] }}'; setTimeout(() => copiedUrl = '', 2000); })
                            .catch(() => {});
                    "
                    style="background:#1e1e1e; border:1px solid #2a2a2a; border-radius:8px; color:#888; padding:.4rem .875rem; font-size:.8rem; cursor:pointer;"
                    :style="copiedUrl === '{{ $link['url'] }}' ? 'border-color:#22C55E44; color:#22C55E; background:#22C55E11;' : ''"
                >
                    <span x-text="copiedUrl === '{{ $link['url'] }}' ? '✅ Kopiert' : '📋 Kopieren'"></span>
                </button>

                {{-- Öffnen --}}
                <a href="{{ $link['url'] }}" target="_blank"
                    style="background:{{ isset($link['primary'])?'#C9A84C':'#1e1e1e' }}; color:{{ isset($link['primary'])?'#000':'#f5f5f5' }}; font-weight:{{ isset($link['primary'])?'700':'400' }}; border:1px solid {{ isset($link['primary'])?'#C9A84C':'#2a2a2a' }}; border-radius:8px; padding:.4rem .875rem; font-size:.8rem; text-decoration:none;">
                    Öffnen ↗
                </a>

                {{-- WhatsApp teilen --}}
                @if(!empty($link['share']))
                @php
                    $movie = $screening?->movie?->title ?? $event->title;
                    $date  = $screening?->starts_at->format('d.m.Y') ?? '';
                    $msg   = "🎬 Kinoabend: {$movie}" . ($date ? " · {$date}" : "") . "\n\n" . $link['url'];
                @endphp
                <a href="https://wa.me/?text={{ urlencode($msg) }}" target="_blank"
                    style="background:#25D36622; border:1px solid #25D36644; border-radius:8px; color:#25D366; font-weight:700; padding:.4rem .875rem; font-size:.8rem; text-decoration:none;">
                    📱 WhatsApp
                </a>
                @endif
            </div>
        </div>
    </div>
    @endforeach
    </div>
</div>
@endforeach

{{-- ── TICKET-LINKS ─────────────────────────────────────────────────────────── --}}
@if($tickets->count())
<div style="margin-bottom:1.5rem;">
    <div style="font-size:.7rem; color:#555; text-transform:uppercase; letter-spacing:.12em; margin-bottom:.75rem;">
        Tickets ({{ $tickets->count() }})
    </div>

    <div style="background:#141414; border:1px solid #1e1e1e; border-radius:14px; overflow:hidden;">
        @foreach($tickets as $ticket)
        @php $url = route('ticket.show', $ticket->ticket_code); @endphp
        <div style="display:flex; justify-content:space-between; align-items:center; padding:.875rem 1.25rem; border-bottom:1px solid #1e1e1e; flex-wrap:wrap; gap:.5rem;"
            style="{{ $loop->last ? 'border-bottom:none;' : '' }}">
            <div>
                <div style="display:flex; align-items:center; gap:.625rem; margin-bottom:.25rem;">
                    <span style="font-weight:600; font-size:.9rem;">{{ $ticket->booking->customer_name }}</span>
                    @if($ticket->seat)
                    <span style="background:#C9A84C22; color:#C9A84C; font-size:.7rem; padding:.15rem .5rem; border-radius:6px; font-weight:700;">
                        {{ $ticket->seat->label }}
                    </span>
                    @endif
                    <span style="font-size:.7rem; color:{{ $ticket->status==='used'?'#22C55E':($ticket->status==='valid'?'#888':'#EF4444') }};">
                        ● {{ $ticket->status }}
                    </span>
                </div>
                <div style="font-size:.7rem; font-family:monospace; color:#444;">{{ $ticket->ticket_code }}</div>
            </div>
            <div style="display:flex; gap:.375rem; flex-wrap:wrap;">
                <button
                    @click="navigator.clipboard.writeText('{{ $url }}').then(()=>{ copiedUrl='{{ $url }}'; setTimeout(()=>copiedUrl='',2000); }).catch(()=>{})"
                    style="background:#1e1e1e; border:1px solid #2a2a2a; border-radius:7px; padding:.35rem .75rem; font-size:.775rem; cursor:pointer;"
                    :style="copiedUrl === '{{ $url }}' ? 'border-color:#22C55E44; color:#22C55E;' : 'color:#888;'"
                >
                    <span x-text="copiedUrl === '{{ $url }}' ? '✅' : '📋'"></span>
                </button>
                <a href="{{ $url }}" target="_blank"
                    style="background:#1e1e1e; border:1px solid #2a2a2a; border-radius:7px; color:#f5f5f5; padding:.35rem .75rem; font-size:.775rem; text-decoration:none;">
                    Ticket ↗
                </a>
                @php $waMsg = "🎟 Dein Ticket für " . ($screening?->movie?->title ?? $event->title) . ":\n" . $url; @endphp
                <a href="https://wa.me/?text={{ urlencode($waMsg) }}" target="_blank"
                    style="background:#25D36618; border:1px solid #25D36633; border-radius:7px; color:#25D366; padding:.35rem .75rem; font-size:.775rem; text-decoration:none;">
                    📱
                </a>
            </div>
        </div>
        @endforeach
    </div>
</div>
@elseif($screening)
<div style="background:#141414; border:1px solid #1e1e1e; border-radius:14px; padding:1.25rem; text-align:center; color:#444; font-size:.875rem;">
    Noch keine Tickets generiert. <a href="{{ route('admin.events.detail', $event->id) }}" style="color:#C9A84C; text-decoration:none;">Sitzplatz-Anfragen bestätigen →</a>
</div>
@endif

{{-- ── CLOUDFLARE INFO-BOX ──────────────────────────────────────────────────── --}}
@if(!str_starts_with(config('app.url'), 'https'))
<div style="background:#F59E0B11; border:1px solid #F59E0B33; border-radius:12px; padding:1rem 1.25rem; margin-top:1rem; font-size:.8rem; color:#F59E0B; line-height:1.6;">
    <strong>💡 Cloudflare Tunnel aktiv?</strong><br>
    Wenn ja, setze <code style="background:#0D0D0D; padding:.1rem .4rem; border-radius:4px;">APP_URL=https://deine-domain.de</code> in <code>.env</code> — dann stimmen alle Links und QR-Codes auch für externe Gäste.
</div>
@endif

</div>
