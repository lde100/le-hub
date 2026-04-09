<div
    class="entrance-root"
    style="width:100vw; height:100vh; background:#0D0D0D; color:#f5f5f5; overflow:hidden; position:relative; font-family:system-ui,sans-serif;"
    x-data="entranceScreen({{ $screening->id }}, {{ $seconds_until_start }}, @js($currentScan), {{ $scanModeUntil }})"
    x-init="init()"
    @new-scan.window="onNewScan($event.detail.scan)"
>

    {{-- ══ MODUS A: COUNTDOWN ══════════════════════════════════════════ --}}
    <div
        x-show="!scanMode"
        x-transition:enter="transition ease-out duration-700"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        style="position:absolute; inset:0; display:flex; flex-direction:column;"
    >
        {{-- LE-Header --}}
        <div style="padding:1.5rem 2rem; border-bottom:1px solid #C9A84C22; display:flex; justify-content:space-between; align-items:center;">
            <div style="display:flex; align-items:center; gap:.75rem;">
                <div style="width:36px;height:36px;background:#C9A84C;border-radius:7px;display:flex;align-items:center;justify-content:center;font-weight:900;color:#000;font-size:13px;">LE</div>
                <div>
                    <div style="font-weight:700; font-size:1rem;">Lucas Entertainment</div>
                    <div style="font-size:.75rem; color:#666; letter-spacing:.1em; text-transform:uppercase;">Private Cinema · Einlass</div>
                </div>
            </div>
            <div style="text-align:right;">
                <div style="font-size:.8rem; color:#555;">Eingecheckt</div>
                <div style="font-size:1.25rem; font-weight:700; color:#C9A84C;">{{ $checked_in_count }}<span style="font-size:.8rem; color:#444;">/{{ $total }}</span></div>
            </div>
        </div>

        {{-- Film-Info --}}
        <div style="flex:1; display:flex; flex-direction:column; align-items:center; justify-content:center; padding:2rem; text-align:center;">

            <div style="font-size:.75rem; color:#555; letter-spacing:.2em; text-transform:uppercase; margin-bottom:1rem;">Demnächst</div>
            <div style="font-size:3.5rem; font-weight:900; line-height:1.1; margin-bottom:.75rem; max-width:800px;">
                {{ $screening->movie?->title ?? 'Vorstellung' }}
            </div>
            <div style="color:#888; font-size:1rem; margin-bottom:3rem;">
                {{ $screening->starts_at->format('d.m.Y') }} · Einlass {{ $screening->starts_at->format('H:i') }} Uhr
            </div>

            {{-- Countdown --}}
            <div style="background:#141414; border:1px solid #C9A84C33; border-radius:20px; padding:1.5rem 3rem; text-align:center;">
                <div style="font-size:.7rem; color:#C9A84C; letter-spacing:.2em; text-transform:uppercase; margin-bottom:.5rem;">Film beginnt in</div>
                <div style="font-size:4rem; font-weight:900; letter-spacing:.05em; font-variant-numeric:tabular-nums;" x-text="countdownText">
                    {{ gmdate('H:i:s', $seconds_until_start) }}
                </div>
            </div>

            {{-- Fortschritts-Punkte --}}
            <div style="display:flex; gap:.5rem; margin-top:2.5rem;">
                @foreach($screening->venue->seats->where('is_active', true) as $seat)
                <div style="
                    width:10px; height:10px; border-radius:50%;
                    background:{{ in_array($seat->id, $checked_in_seat_ids) ? '#22C55E' : '#2a2a2a' }};
                    transition:background .5s;
                " title="{{ $seat->label }}"></div>
                @endforeach
            </div>
            @if($checked_in_count === $total && $total > 0)
            <div style="margin-top:1rem; color:#22C55E; font-size:.9rem; font-weight:700;">✅ Alle eingecheckt</div>
            @endif

        </div>
    </div>

    {{-- ══ MODUS B: SITZPLAN (30 Sek nach Scan) ════════════════════════ --}}
    <div
        x-show="scanMode"
        x-transition:enter="transition ease-out duration-500"
        x-transition:enter-start="opacity-0 scale-95"
        x-transition:enter-end="opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-500"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        style="position:absolute; inset:0; display:flex; flex-direction:column; align-items:center; justify-content:center; background:#0D0D0D;"
    >
        {{-- Willkommens-Banner --}}
        <div style="text-align:center; margin-bottom:2.5rem;" x-show="scanMode">
            <div style="font-size:.8rem; color:#C9A84C; letter-spacing:.2em; text-transform:uppercase; margin-bottom:.5rem;">Willkommen</div>
            <div style="font-size:3rem; font-weight:900;" x-text="currentScan?.guest_name ?? ''"></div>
        </div>

        {{-- Animierter Sitzplan --}}
        <div style="position:relative; width:100%; max-width:700px; padding:0 2rem;">

            {{-- Leinwand --}}
            <div style="background:#C9A84C22; border:1px solid #C9A84C44; border-radius:6px; height:10px; margin-bottom:2rem; text-align:center; line-height:0; position:relative;">
                <span style="position:absolute; top:50%; left:50%; transform:translate(-50%,-50%); font-size:.65rem; color:#C9A84C; letter-spacing:.2em; text-transform:uppercase; white-space:nowrap;">LEINWAND</span>
            </div>

            @foreach($screening->venue->seats->where('is_active', true)->groupBy('row') as $row => $seats)
            <div style="margin-bottom:1rem;">
                <div style="font-size:.6rem; color:#444; letter-spacing:.1em; text-transform:uppercase; margin-bottom:.4rem;">{{ $row }}</div>
                <div style="display:flex; gap:.625rem; flex-wrap:wrap;">
                    @foreach($seats as $seat)
                    <div
                        style="
                            padding:.625rem 1.125rem;
                            border-radius:10px;
                            font-size:.9rem;
                            font-weight:600;
                            transition:all .4s;
                            position:relative;
                        "
                        :style="getSeatStyle({{ $seat->id }}, {{ $seat->id === ($currentScan['seat_id'] ?? -1) ? 'true' : 'false' }}, {{ in_array($seat->id, $checked_in_seat_ids) ? 'true' : 'false' }})"
                    >
                        {{ $seat->label }}
                        @if(in_array($seat->id, $checked_in_seat_ids) && $seat->id !== ($currentScan['seat_id'] ?? -1))
                            <span style="margin-left:4px; font-size:.7rem; opacity:.7;">✓</span>
                        @endif
                    </div>
                    @endforeach
                </div>
            </div>
            @endforeach
        </div>

        {{-- Sitz-Label gross --}}
        <div style="margin-top:2rem; text-align:center;" x-show="currentScan?.seat_label">
            <div style="font-size:.75rem; color:#888; letter-spacing:.15em; text-transform:uppercase; margin-bottom:.5rem;">Dein Platz</div>
            <div
                style="background:#C9A84C; color:#000; font-weight:900; font-size:2.5rem; padding:.625rem 2rem; border-radius:12px; letter-spacing:2px; display:inline-block;"
                x-text="currentScan?.seat_label ?? ''"
            ></div>
        </div>

        {{-- Countdown zurück --}}
        <div style="position:absolute; bottom:1.5rem; right:2rem; font-size:.75rem; color:#333;">
            Zurück in <span x-text="backInSeconds"></span>s
        </div>
    </div>

</div>

@push('scripts')
<script>
window.entranceScreen = function(screeningId, initialSeconds, initialScan, scanModeUntil) {
    return {
        countdownSeconds: initialSeconds,
        scanMode: !!initialScan,
        currentScan: initialScan,
        scanModeUntil: scanModeUntil,
        backInSeconds: 30,
        backTimer: null,
        countdownText: '',

        init() {
            this.updateCountdown();
            setInterval(() => this.updateCountdown(), 1000);

            if (this.scanMode) {
                this.startBackTimer();
            }
        },

        updateCountdown() {
            if (this.countdownSeconds > 0) this.countdownSeconds--;
            const h = Math.floor(this.countdownSeconds / 3600);
            const m = Math.floor((this.countdownSeconds % 3600) / 60);
            const s = this.countdownSeconds % 60;
            this.countdownText = (h > 0 ? String(h).padStart(2,'0') + ':' : '') +
                String(m).padStart(2,'0') + ':' + String(s).padStart(2,'0');

            // Zurückschalten prüfen
            if (this.scanModeUntil > 0 && Math.floor(Date.now()/1000) > this.scanModeUntil) {
                this.scanMode = false;
                this.currentScan = null;
                this.scanModeUntil = 0;
            }
            this.backInSeconds = Math.max(0, this.scanModeUntil - Math.floor(Date.now()/1000));
        },

        onNewScan(scan) {
            this.currentScan   = scan;
            this.scanMode      = true;
            this.scanModeUntil = Math.floor(Date.now()/1000) + 30;
            this.backInSeconds = 30;
        },

        getSeatStyle(seatId, isHighlighted, isCheckedIn) {
            if (isHighlighted) {
                return 'border:2px solid #C9A84C; background:#C9A84C22; color:#C9A84C; box-shadow:0 0 20px #C9A84C55, 0 0 40px #C9A84C22; animation:seatPulse 1s ease-in-out infinite;';
            }
            if (isCheckedIn) {
                return 'border:2px solid #22C55E44; background:#22C55E11; color:#22C55E;';
            }
            return 'border:2px solid #1e1e1e; background:transparent; color:#444;';
        },

        startBackTimer() {
            this.backInSeconds = Math.max(0, this.scanModeUntil - Math.floor(Date.now()/1000));
        }
    }
}
</script>
<style>
@keyframes seatPulse {
    0%,100% { box-shadow: 0 0 15px #C9A84C55, 0 0 30px #C9A84C22; transform: scale(1); }
    50%      { box-shadow: 0 0 30px #C9A84C88, 0 0 60px #C9A84C44; transform: scale(1.05); }
}
</style>
@endpush
