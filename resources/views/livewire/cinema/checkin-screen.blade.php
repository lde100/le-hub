<div
    class="checkin-root"
    style="display:grid; grid-template-columns:1fr 340px; height:100dvh; gap:0;"
    x-data="{ showWelcome: @entangle('showWelcome') }"
    @handle-scan.window="$wire.handleScan($event.detail.code)"
>

    {{-- ── LINKE SEITE: Saalplan ──────────────────────────────────────── --}}
    <div style="background:var(--surface); border-right:1px solid #222; display:flex; flex-direction:column; overflow:hidden;">

        {{-- Header --}}
        <div style="padding:1.25rem 1.5rem; border-bottom:1px solid #1e1e1e; display:flex; justify-content:space-between; align-items:center;">
            <div style="display:flex; align-items:center; gap:.75rem;">
                <div style="background:var(--gold); width:32px; height:32px; border-radius:6px; display:flex; align-items:center; justify-content:center; font-weight:900; color:#000; font-size:12px;">LE</div>
                <div>
                    <div style="font-weight:700; font-size:1rem;">{{ $screening->movie?->title ?? 'Vorstellung' }}</div>
                    <div style="font-size:.8rem; color:#666;">{{ $screening->starts_at->format('d.m.Y · H:i') }} Uhr</div>
                </div>
            </div>

            {{-- Check-in Fortschritt --}}
            <div style="text-align:right;">
                <div style="font-size:2rem; font-weight:900; color:{{ $all_done ? '#22C55E' : 'var(--gold)' }};">
                    {{ $checked_in_count }}<span style="font-size:1rem; color:#555;">/{{ $total_tickets }}</span>
                </div>
                <div style="font-size:.75rem; color:{{ $all_done ? '#22C55E' : '#888' }};">
                    {{ $all_done ? '✅ Alle eingecheckt' : 'eingecheckt' }}
                </div>
            </div>
        </div>

        {{-- Saalplan --}}
        <div style="flex:1; display:flex; flex-direction:column; align-items:center; justify-content:center; padding:2rem;">
            <div style="font-size:.7rem; color:#555; letter-spacing:.15em; text-transform:uppercase; margin-bottom:1.5rem;">LEINWAND</div>
            <div style="width:100%; max-width:600px; background:#1A1A1A; border-radius:6px; height:6px; margin-bottom:2.5rem; opacity:.4;"></div>

            @foreach ($screening->venue->seats->groupBy('row') as $row => $seats)
            <div style="margin-bottom:1.25rem; width:100%; max-width:600px;">
                <div style="font-size:.65rem; color:#444; letter-spacing:.1em; text-transform:uppercase; margin-bottom:.5rem;">{{ $row }}</div>
                <div style="display:flex; gap:.625rem; flex-wrap:wrap;">
                    @foreach ($seats as $seat)
                    @php
                        $isCheckedIn = in_array($seat->id, $checked_in_seat_ids);
                        $isHighlighted = isset($lastScan) && ($lastScan['seat_id'] ?? null) === $seat->id && $lastScan['status'] === 'success';
                    @endphp
                    <div
                        style="
                            padding:.5rem .875rem;
                            border-radius:8px;
                            border:2px solid {{ $isHighlighted ? 'var(--gold)' : ($isCheckedIn ? '#22C55E' : '#2a2a2a') }};
                            background:{{ $isHighlighted ? 'var(--gold)22' : ($isCheckedIn ? '#22C55E18' : 'transparent') }};
                            color:{{ $isHighlighted ? 'var(--gold)' : ($isCheckedIn ? '#22C55E' : '#555') }};
                            font-size:.875rem;
                            font-weight:{{ $isCheckedIn ? '700' : '400' }};
                            transition:all .3s;
                            position:relative;
                        "
                        wire:key="seat-{{ $seat->id }}"
                    >
                        {{ $seat->label }}
                        @if($isCheckedIn) <span style="margin-left:4px; font-size:.7rem;">✓</span> @endif
                    </div>
                    @endforeach
                </div>
            </div>
            @endforeach

            {{-- Legende --}}
            <div style="display:flex; gap:1.5rem; margin-top:1.5rem; font-size:.75rem; color:#555;">
                <span><span style="color:#22C55E;">■</span> Eingecheckt</span>
                <span><span style="color:var(--gold);">■</span> Gerade gescannt</span>
                <span><span style="color:#333;">■</span> Noch ausstehend</span>
            </div>
        </div>
    </div>

    {{-- ── RECHTE SEITE: Scanner-Status ───────────────────────────────── --}}
    <div style="display:flex; flex-direction:column; background:var(--dark);">

        {{-- Scanner bereit --}}
        <div style="flex:1; display:flex; flex-direction:column; align-items:center; justify-content:center; padding:2rem; text-align:center;">

            @if (!$lastScan)
            <div style="font-size:3rem; margin-bottom:1rem; animation:pulse 2s infinite;">📟</div>
            <div style="font-size:1rem; font-weight:600; color:#888;">Bereit zum Scannen</div>
            <div style="font-size:.8rem; color:#444; margin-top:.5rem;">QR-Code oder Barcode scannen</div>

            @elseif ($lastScan['status'] === 'success')
            {{-- Erfolg --}}
            <div style="font-size:3.5rem; margin-bottom:1rem; animation:bounceIn .4s ease;">✅</div>
            <div style="font-size:1.5rem; font-weight:800; color:#22C55E; margin-bottom:.5rem;">Willkommen!</div>
            <div style="font-size:1.25rem; font-weight:700; color:#fff; margin-bottom:.5rem;">{{ $lastScan['name'] }}</div>
            <div style="background:var(--gold); color:#000; font-weight:900; padding:.5rem 1.25rem; border-radius:8px; font-size:1.1rem; letter-spacing:1px; margin-bottom:1.5rem;">
                💺 {{ $lastScan['seat'] }}
            </div>

            @elseif ($lastScan['status'] === 'warning')
            <div style="font-size:3rem; margin-bottom:1rem;">⚠️</div>
            <div style="font-size:1.1rem; font-weight:700; color:#F59E0B; margin-bottom:.5rem;">{{ $lastScan['message'] }}</div>
            <div style="font-size:.9rem; color:#888;">{{ $lastScan['name'] }} · Platz {{ $lastScan['seat'] }}</div>
            @if(isset($lastScan['used_at']))
                <div style="font-size:.8rem; color:#555; margin-top:.5rem;">Entwertet: {{ $lastScan['used_at'] }} Uhr</div>
            @endif

            @elseif ($lastScan['status'] === 'error')
            <div style="font-size:3rem; margin-bottom:1rem;">❌</div>
            <div style="font-size:1.1rem; font-weight:700; color:#EF4444; margin-bottom:.5rem;">{{ $lastScan['message'] }}</div>
            <div style="font-size:.75rem; color:#444; font-family:monospace; margin-top:.5rem; word-break:break-all;">{{ $lastScan['code'] }}</div>
            @endif

        </div>

        {{-- Fortschritts-Bar unten --}}
        <div style="padding:1.25rem 1.5rem; border-top:1px solid #1e1e1e;">
            @php $pct = $total_tickets > 0 ? round($checked_in_count / $total_tickets * 100) : 0; @endphp
            <div style="display:flex; justify-content:space-between; font-size:.8rem; color:#666; margin-bottom:.5rem;">
                <span>Check-in Fortschritt</span>
                <span>{{ $pct }}%</span>
            </div>
            <div style="background:#1e1e1e; border-radius:99px; height:6px; overflow:hidden;">
                <div style="background:{{ $all_done ? '#22C55E' : 'var(--gold)' }}; height:6px; width:{{ $pct }}%; border-radius:99px; transition:width .5s ease;"></div>
            </div>
            @if($all_done)
            <div style="text-align:center; margin-top:.75rem; font-size:.9rem; font-weight:700; color:#22C55E; animation:pulse 1.5s infinite;">
                🎬 Alle da — Film kann starten!
            </div>
            @endif
        </div>
    </div>

    {{-- ── WELCOME OVERLAY (Vollbild für 4 Sekunden) ──────────────────── --}}
    <div
        x-show="showWelcome"
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0 scale-95"
        x-transition:enter-end="opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-500"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        x-init="$watch('showWelcome', val => { if(val) setTimeout(() => $wire.dismissWelcome(), 4000) })"
        style="position:fixed; inset:0; background:#0D0D0Dee; backdrop-filter:blur(8px); z-index:50; display:flex; flex-direction:column; align-items:center; justify-content:center; text-align:center;"
    >
        @if($lastScan && $lastScan['status'] === 'success')
        <div style="font-size:5rem; margin-bottom:1.5rem; animation:bounceIn .5s ease;">🎬</div>
        <div style="font-size:.9rem; color:var(--gold); letter-spacing:.2em; text-transform:uppercase; margin-bottom:.75rem;">Willkommen</div>
        <div style="font-size:3.5rem; font-weight:900; color:#fff; margin-bottom:1rem; line-height:1.1;">{{ $lastScan['name'] ?? '' }}</div>
        <div style="background:var(--gold); color:#000; font-weight:900; font-size:2rem; padding:.75rem 2rem; border-radius:12px; letter-spacing:2px;">
            💺 {{ $lastScan['seat'] ?? '' }}
        </div>
        <div style="margin-top:2rem; font-size:.9rem; color:#555;">Viel Vergnügen! 🍿</div>
        @endif
    </div>

</div>

<style>
@keyframes pulse { 0%,100%{opacity:1} 50%{opacity:.5} }
@keyframes bounceIn {
    0%   { transform:scale(.3); opacity:0; }
    60%  { transform:scale(1.05); opacity:1; }
    100% { transform:scale(1); }
}
</style>
