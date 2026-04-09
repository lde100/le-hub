<div
    class="infoscreen-root"
    style="width:100vw; height:100vh; background:#0a0a0a; color:#fff; overflow:hidden; position:relative; font-family:system-ui,sans-serif;"
    x-data="infoscreen(@js($slides), @js($lastScan))"
    x-init="start()"
    @show-welcome.window="showWelcomeOverlay($event.detail.scan)"
>

    {{-- ── LE Header ─────────────────────────────────────────────────── --}}
    <div style="position:absolute; top:0; left:0; right:0; padding:1.25rem 2rem; display:flex; justify-content:space-between; align-items:center; z-index:10; background:linear-gradient(to bottom,#000a,transparent);">
        <div style="display:flex; align-items:center; gap:.75rem;">
            <div style="width:34px;height:34px;background:#C9A84C;border-radius:7px;display:flex;align-items:center;justify-content:center;font-weight:900;color:#000;font-size:13px;letter-spacing:-.5px;">LE</div>
            <div>
                <div style="font-size:.95rem; font-weight:700;">Lucas Entertainment</div>
                <div style="font-size:.65rem; color:#666; letter-spacing:.12em; text-transform:uppercase;">Private Cinema</div>
            </div>
        </div>
        <div style="font-size:1.1rem; font-weight:300; color:#888; font-variant-numeric:tabular-nums;" x-text="clock"></div>
    </div>

    {{-- ── Slide Content ─────────────────────────────────────────────── --}}
    <div style="position:absolute; inset:0;">

        {{-- now_playing --}}
        <template x-if="currentSlide?.type === 'now_playing'">
            <div style="position:absolute;inset:0;display:flex;align-items:center;justify-content:center;padding:5rem 4rem 3rem;">
                <template x-if="currentSlide.data?.empty">
                    <div style="color:#333; font-size:1.5rem;">Keine laufende Vorstellung</div>
                </template>
                <template x-if="!currentSlide.data?.empty">
                    <div style="display:flex; gap:3rem; align-items:center; max-width:900px;">
                        <div style="font-size:5rem; flex-shrink:0;">🎬</div>
                        <div>
                            <div style="font-size:.75rem; color:#C9A84C; letter-spacing:.2em; text-transform:uppercase; margin-bottom:.75rem;">Jetzt läuft</div>
                            <div style="font-size:3.5rem; font-weight:900; line-height:1.1; margin-bottom:1rem;" x-text="currentSlide.data.title"></div>
                            <div style="display:flex; gap:1.5rem; color:#666; font-size:1rem;">
                                <span x-text="'⏱ ' + currentSlide.data.duration"></span>
                                <span x-text="currentSlide.data.rating"></span>
                                <span x-text="'Seit ' + currentSlide.data.started + ' Uhr'"></span>
                            </div>
                        </div>
                    </div>
                </template>
            </div>
        </template>

        {{-- upcoming --}}
        <template x-if="currentSlide?.type === 'upcoming'">
            <div style="position:absolute;inset:0;display:flex;flex-direction:column;justify-content:center;padding:5rem 4rem 3rem;">
                <div style="font-size:.75rem; color:#C9A84C; letter-spacing:.2em; text-transform:uppercase; margin-bottom:1.5rem;">Demnächst</div>
                <div style="display:flex; gap:1.5rem;">
                    <template x-for="item in currentSlide.data" :key="item.title">
                        <div style="flex:1; border:1px solid #1e1e1e; border-radius:16px; padding:1.5rem;">
                            <div style="font-size:2.5rem; margin-bottom:.75rem;">🎥</div>
                            <div style="font-size:1.25rem; font-weight:700; margin-bottom:.5rem;" x-text="item.title"></div>
                            <div style="color:#666; font-size:.9rem;" x-text="item.date + ' · ' + item.time + ' Uhr'"></div>
                        </div>
                    </template>
                </div>
            </div>
        </template>

        {{-- countdown --}}
        <template x-if="currentSlide?.type === 'countdown'">
            <div style="position:absolute;inset:0;display:flex;flex-direction:column;align-items:center;justify-content:center;padding:5rem 4rem 3rem; text-align:center;">
                <template x-if="!currentSlide.data?.empty">
                    <div>
                        <div style="font-size:.75rem; color:#C9A84C; letter-spacing:.2em; text-transform:uppercase; margin-bottom:1rem;">Film beginnt in</div>
                        <div style="font-size:6rem; font-weight:900; letter-spacing:.05em; font-variant-numeric:tabular-nums; margin-bottom:1.5rem;" x-text="getCountdownText(currentSlide.data.starts_at)"></div>
                        <div style="font-size:2rem; font-weight:700; margin-bottom:.5rem;" x-text="currentSlide.data.title"></div>
                        <div style="color:#666; font-size:1rem;" x-text="currentSlide.data.date_label + ' · ' + currentSlide.data.time_label + ' Uhr'"></div>
                    </div>
                </template>
            </div>
        </template>

        {{-- menu_category --}}
        <template x-if="currentSlide?.type === 'menu_category'">
            <div style="position:absolute;inset:0;display:flex;flex-direction:column;justify-content:center;padding:5rem 4rem 3rem;">
                <div style="display:flex; align-items:center; gap:1rem; margin-bottom:1.5rem;">
                    <span style="font-size:3rem;" x-text="currentSlide.data.icon"></span>
                    <span style="font-size:2.5rem; font-weight:700;" x-text="currentSlide.data.name"></span>
                </div>
                <div style="display:grid; grid-template-columns:repeat(3,1fr); gap:.75rem;">
                    <template x-for="p in currentSlide.data.products" :key="p.name">
                        <div style="border:1px solid #1e1e1e; border-radius:12px; padding:1rem 1.25rem; display:flex; justify-content:space-between; align-items:center;">
                            <span style="font-size:1rem;" x-text="p.name"></span>
                            <span style="color:#666; font-size:.85rem;" x-text="p.price || 'inkl.'"></span>
                        </div>
                    </template>
                </div>
            </div>
        </template>

        {{-- paypal_qr --}}
        <template x-if="currentSlide?.type === 'paypal_qr'">
            <div style="position:absolute;inset:0;display:flex;flex-direction:column;align-items:center;justify-content:center;gap:1.5rem;">
                <div style="font-size:.75rem; color:#666; letter-spacing:.2em; text-transform:uppercase;">Bezahlen via PayPal</div>
                <div style="background:#fff; padding:12px; border-radius:16px;">
                    <img :src="'https://api.qrserver.com/v1/create-qr-code/?size=280x280&data=' + encodeURIComponent('https://paypal.me/' + currentSlide.data.paypal_me)" width="280" height="280">
                </div>
                <div style="font-size:1.25rem; font-weight:600;" x-text="'paypal.me/' + currentSlide.data.paypal_me"></div>
            </div>
        </template>

    </div>

    {{-- ── Progress Bar ──────────────────────────────────────────────── --}}
    <div style="position:absolute; bottom:0; left:0; right:0; padding:.75rem 2rem;">
        <div style="display:flex; gap:.375rem;">
            <template x-for="(slide, i) in slides" :key="i">
                <div style="height:3px; border-radius:99px; transition:all .3s;"
                    :style="i === currentIndex ? 'background:#C9A84C; flex:1;' : 'background:#ffffff18; width:20px;'">
                </div>
            </template>
        </div>
    </div>

    {{-- ══ WELCOME OVERLAY (3 Sek bei Check-in) ════════════════════════ --}}
    <div
        id="welcome-overlay"
        style="position:fixed; inset:0; background:#0D0D0Df0; backdrop-filter:blur(12px); z-index:100; display:none; flex-direction:column; align-items:center; justify-content:center; text-align:center;"
    >
        <div style="font-size:4rem; margin-bottom:1rem; animation:bounceIn .5s ease;" id="wo-icon">🎬</div>
        <div style="font-size:.8rem; color:#C9A84C; letter-spacing:.2em; text-transform:uppercase; margin-bottom:.75rem;">Willkommen</div>
        <div style="font-size:4rem; font-weight:900; color:#fff; line-height:1.1; margin-bottom:.5rem;" id="wo-name"></div>
        <div style="background:#C9A84C; color:#000; font-weight:900; font-size:1.5rem; padding:.5rem 1.5rem; border-radius:10px; letter-spacing:1px; margin-top:1rem;" id="wo-seat" style="display:none;"></div>
        <div style="margin-top:1.5rem; color:#555; font-size:.9rem;">Viel Vergnügen! 🍿</div>
    </div>

</div>

@push('scripts')
<script>
window.infoscreen = function(slides, initialScan) {
    return {
        slides,
        currentIndex: 0,
        currentSlide: slides[0] ?? null,
        clock: '',
        timer: null,

        start() {
            this.updateClock();
            setInterval(() => this.updateClock(), 1000);
            this.scheduleNext();
        },

        scheduleNext() {
            clearTimeout(this.timer);
            const dur = (this.currentSlide?.duration ?? 10) * 1000;
            this.timer = setTimeout(() => {
                this.currentIndex = (this.currentIndex + 1) % this.slides.length;
                this.currentSlide = this.slides[this.currentIndex];
                this.scheduleNext();
            }, dur);
        },

        updateClock() {
            this.clock = new Date().toLocaleTimeString('de-DE', { hour: '2-digit', minute: '2-digit' });
        },

        getCountdownText(startsAt) {
            const diff = Math.max(0, Math.floor((new Date(startsAt) - Date.now()) / 1000));
            const h = Math.floor(diff / 3600);
            const m = Math.floor((diff % 3600) / 60);
            const s = diff % 60;
            return (h > 0 ? String(h).padStart(2,'0') + ':' : '') +
                String(m).padStart(2,'0') + ':' + String(s).padStart(2,'0');
        },

        showWelcomeOverlay(scan) {
            const overlay = document.getElementById('welcome-overlay');
            document.getElementById('wo-name').textContent = scan.guest_name ?? '';
            const seatEl = document.getElementById('wo-seat');
            if (scan.seat_label) {
                seatEl.textContent = '💺 ' + scan.seat_label;
                seatEl.style.display = 'inline-block';
            } else {
                seatEl.style.display = 'none';
            }
            overlay.style.display = 'flex';
            setTimeout(() => { overlay.style.display = 'none'; }, 3000);
        },
    }
}
</script>
<style>
@keyframes bounceIn {
    0%   { transform:scale(.3); opacity:0; }
    60%  { transform:scale(1.1); }
    100% { transform:scale(1); opacity:1; }
}
</style>
@endpush
