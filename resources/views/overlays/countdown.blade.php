@extends('overlays.base')

@section('content')
<div id="root" style="width:1920px; height:1080px; display:flex; align-items:center; justify-content:center; position:relative;">

    {{-- Filmkorn-Overlay (generiert per Canvas) --}}
    <canvas id="grain" style="position:absolute; inset:0; opacity:.035; pointer-events:none;"></canvas>

    {{-- Countdown-Kreis --}}
    <div id="countdown-wrap" style="position:relative; width:340px; height:340px; display:flex; align-items:center; justify-content:center;">

        {{-- SVG-Ring --}}
        <svg style="position:absolute; inset:0; transform:rotate(-90deg);" width="340" height="340">
            <circle cx="170" cy="170" r="155"
                fill="none" stroke="#ffffff18" stroke-width="3"/>
            <circle id="ring" cx="170" cy="170" r="155"
                fill="none" stroke="#ffffff" stroke-width="3"
                stroke-dasharray="{{ 2 * pi() * 155 }}"
                stroke-dashoffset="0"
                stroke-linecap="round"/>
        </svg>

        {{-- Zahl --}}
        <div id="num"
            style="font-size:220px; font-weight:900; color:#fff; line-height:1;
                   font-variant-numeric:tabular-nums; letter-spacing:-8px;
                   text-shadow:0 0 60px rgba(255,255,255,.3);">
            5
        </div>
    </div>

    {{-- Scratchy Line (horizontal) --}}
    <div id="scratch-h" style="position:absolute; height:1px; background:#fff; opacity:0; pointer-events:none;"></div>
    <div id="scratch-v" style="position:absolute; width:1px; background:#fff; opacity:0; pointer-events:none;"></div>

</div>

@push('scripts')
<script>
(function() {
    const DURATION = {{ $duration ?? 5 }};   // Sekunden
    const circumference = 2 * Math.PI * 155;
    let remaining = DURATION;
    let startTime = null;
    let done = false;

    // Filmkorn
    const canvas = document.getElementById('grain');
    canvas.width  = 1920;
    canvas.height = 1080;
    const ctx = canvas.getContext('2d');
    function drawGrain() {
        const img = ctx.createImageData(1920, 1080);
        for (let i = 0; i < img.data.length; i += 4) {
            const v = Math.random() * 255 | 0;
            img.data[i] = img.data[i+1] = img.data[i+2] = v;
            img.data[i+3] = 255;
        }
        ctx.putImageData(img, 0, 0);
    }
    setInterval(drawGrain, 80);

    // Kratzer
    function showScratch() {
        if (Math.random() > 0.3) return;
        const h = document.getElementById('scratch-h');
        const v = document.getElementById('scratch-v');
        const which = Math.random() > 0.5 ? h : v;
        const pos = Math.random() * 100;
        if (which === h) {
            which.style.top  = pos + '%';
            which.style.left = '0'; which.style.right = '0';
        } else {
            which.style.left = pos + '%';
            which.style.top  = '0'; which.style.bottom = '0';
        }
        which.style.opacity = (Math.random() * 0.5 + 0.1).toFixed(2);
        setTimeout(() => { which.style.opacity = 0; }, 40 + Math.random() * 80);
    }
    setInterval(showScratch, 200);

    // Countdown-Schleife
    function tick(ts) {
        if (!startTime) startTime = ts;
        const elapsed  = (ts - startTime) / 1000;
        remaining = Math.max(0, DURATION - elapsed);

        const display = Math.ceil(remaining);
        const frac    = remaining - Math.floor(remaining);  // Fortschritt innerhalb dieser Sekunde
        const offset  = circumference * (1 - (DURATION - remaining) / DURATION);

        document.getElementById('num').textContent  = display > 0 ? display : '';
        document.getElementById('ring').style.strokeDashoffset = offset;

        // Puls wenn Zahl wechselt
        const numEl = document.getElementById('num');
        numEl.style.transform = `scale(${1 + (1 - frac) * 0.08})`;
        numEl.style.opacity   = frac < 0.15 ? (frac / 0.15).toFixed(2) : '1';

        if (remaining <= 0 && !done) {
            done = true;
            // Fade out
            document.getElementById('root').style.transition = 'opacity .5s';
            document.getElementById('root').style.opacity = '0';
            return;
        }
        requestAnimationFrame(tick);
    }
    requestAnimationFrame(tick);
})();
</script>
@endpush
