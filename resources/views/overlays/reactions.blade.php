@extends('overlays.base')

@section('content')
<div id="reactions-stage"
    style="width:1920px; height:1080px; position:relative; overflow:hidden;"
    x-data="reactionsOverlay({{ $screeningId }})"
    x-init="init()"
>
    {{-- Reaktionen werden hier per JS reingerendert --}}
</div>

@push('scripts')
<script>
function reactionsOverlay(screeningId) {
    return {
        lastSeq: 0,

        init() {
            this.poll();
        },

        async poll() {
            try {
                const r = await fetch(`/api/reactions/${screeningId}?since=${this.lastSeq}`);
                const reactions = await r.json();
                reactions.forEach(rx => {
                    if (rx.seq > this.lastSeq) this.lastSeq = rx.seq;
                    this.spawnReaction(rx.emoji, rx.x_pct);
                });
            } catch(e) {}
            setTimeout(() => this.poll(), 800);
        },

        spawnReaction(emoji, xPct) {
            const stage = document.getElementById('reactions-stage');
            const el = document.createElement('div');

            // Zufällige Startposition horizontal (xPct = Gast-Bildschirmbreite, leicht variieren)
            const x = Math.max(5, Math.min(90, (xPct ?? 50) + (Math.random() - 0.5) * 15));
            const size = 80 + Math.random() * 60;  // 80–140px
            const duration = 2200 + Math.random() * 1500;
            const drift = (Math.random() - 0.5) * 120;

            el.textContent = emoji;
            el.style.cssText = `
                position:absolute;
                bottom: 80px;
                left: ${x}%;
                font-size: ${size}px;
                line-height: 1;
                pointer-events: none;
                animation: riseUp ${duration}ms ease-out forwards;
                --drift: ${drift}px;
                filter: drop-shadow(0 4px 12px rgba(0,0,0,.5));
                z-index: 20;
            `;
            stage.appendChild(el);
            setTimeout(() => el.remove(), duration + 100);
        }
    }
}
</script>
<style>
@keyframes riseUp {
    0%   { transform: translateY(0) translateX(0) scale(0.6); opacity:0; }
    10%  { opacity:1; transform: translateY(-60px) translateX(0) scale(1.1); }
    80%  { opacity:0.9; transform: translateY(-750px) translateX(var(--drift)) scale(1); }
    100% { opacity:0; transform: translateY(-920px) translateX(var(--drift)) scale(0.8); }
}
</style>
@endpush
