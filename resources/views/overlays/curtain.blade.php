@extends('overlays.base')

@section('content')
<div id="stage"
    style="width:1920px; height:1080px; position:relative; overflow:hidden;"
    x-data="curtain()"
    x-init="init()"
>
    {{-- Linker Vorhang --}}
    <div id="curtain-l"
        style="
            position:absolute; top:0; left:0;
            width:960px; height:1080px;
            background: linear-gradient(to right,
                #5C0A0A 0%, #8B1010 30%, #A01515 55%, #7A0D0D 80%, #5C0A0A 100%);
            transform-origin: left center;
            box-shadow: inset -20px 0 40px rgba(0,0,0,.4);
        "
    >
        {{-- Falten --}}
        @for($i = 0; $i < 7; $i++)
        <div style="
            position:absolute; top:0; bottom:0;
            width:{{ 8 + $i * 2 }}px;
            left:{{ 80 + $i * 130 }}px;
            background:rgba(0,0,0,.15);
            border-radius:50%;
        "></div>
        @endfor

        {{-- Goldener Saum --}}
        <div style="position:absolute; top:0; right:0; bottom:0; width:12px;
            background:linear-gradient(to bottom, #C9A84C, #8B6914, #C9A84C, #8B6914, #C9A84C);"></div>

        {{-- Quaste oben --}}
        <div style="position:absolute; top:0; right:6px; width:24px; height:60px;
            background:linear-gradient(to bottom, #C9A84C, #8B6914);
            border-radius:0 0 12px 12px;"></div>
    </div>

    {{-- Rechter Vorhang --}}
    <div id="curtain-r"
        style="
            position:absolute; top:0; right:0;
            width:960px; height:1080px;
            background: linear-gradient(to left,
                #5C0A0A 0%, #8B1010 30%, #A01515 55%, #7A0D0D 80%, #5C0A0A 100%);
            transform-origin: right center;
            box-shadow: inset 20px 0 40px rgba(0,0,0,.4);
        "
    >
        @for($i = 0; $i < 7; $i++)
        <div style="
            position:absolute; top:0; bottom:0;
            width:{{ 8 + $i * 2 }}px;
            right:{{ 80 + $i * 130 }}px;
            background:rgba(0,0,0,.15);
            border-radius:50%;
        "></div>
        @endfor
        <div style="position:absolute; top:0; left:0; bottom:0; width:12px;
            background:linear-gradient(to bottom, #C9A84C, #8B6914, #C9A84C, #8B6914, #C9A84C);"></div>
        <div style="position:absolute; top:0; left:6px; width:24px; height:60px;
            background:linear-gradient(to bottom, #C9A84C, #8B6914);
            border-radius:0 0 12px 12px;"></div>
    </div>

    {{-- Obere Blende --}}
    <div style="position:absolute; top:0; left:0; right:0; height:80px;
        background:linear-gradient(to bottom, #3a0808, #5C0A0A);
        border-bottom:6px solid #C9A84C; z-index:10;"></div>

</div>

@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/alpinejs/3.13.3/cdn.min.js" defer></script>
<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('curtain', () => ({
        init() {
            const delay = {{ $delay ?? 500 }};
            const dur   = {{ $duration ?? 2000 }};

            setTimeout(() => {
                const l = document.getElementById('curtain-l');
                const r = document.getElementById('curtain-r');
                const easing = `cubic-bezier(0.4, 0, 0.2, 1)`;

                l.style.transition = `transform ${dur}ms ${easing}`;
                r.style.transition = `transform ${dur}ms ${easing}`;
                l.style.transform  = 'translateX(-100%)';
                r.style.transform  = 'translateX(100%)';

                // Nach Öffnen: Fade-out des gesamten Overlays
                setTimeout(() => {
                    document.getElementById('stage').style.transition = 'opacity 0.6s';
                    document.getElementById('stage').style.opacity = '0';
                }, dur + 500);
            }, delay);
        }
    }));
});
</script>
@endpush
