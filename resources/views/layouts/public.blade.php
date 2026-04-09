<!DOCTYPE html>
<html lang="de" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <title>Lucas Entertainment</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
    <style>
        :root {
            --le-gold:   #C9A84C;
            --le-dark:   #0D0D0D;
            --le-surface:#1A1A1A;
            --le-border: #2A2A2A;
        }
        * { box-sizing: border-box; }
        body {
            background: var(--le-dark);
            color: #F5F5F5;
            font-family: system-ui, -apple-system, sans-serif;
            min-height: 100dvh;
        }
    </style>
</head>
<body>
    {{-- LE Header --}}
    <header style="background: #111; border-bottom: 1px solid var(--le-gold)33; padding: 1rem 1.5rem;">
        <div style="max-width: 640px; margin: 0 auto; display: flex; align-items: center; gap: .75rem;">
            <div style="width:36px; height:36px; background: var(--le-gold); border-radius:6px; display:flex; align-items:center; justify-content:center; font-weight:900; color:#000; font-size:14px; letter-spacing:-1px;">LE</div>
            <div>
                <div style="font-weight:700; font-size:15px; color:#fff;">Lucas Entertainment</div>
                <div style="font-size:11px; color:#888; letter-spacing:.05em;">PRIVATE CINEMA</div>
            </div>
        </div>
    </header>

    <main style="max-width: 640px; margin: 0 auto; padding: 1.5rem 1rem 4rem;">
        {{ $slot }}
    </main>

    @livewireScripts
    @stack('scripts')
</body>
</html>
