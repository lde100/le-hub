<!DOCTYPE html>
<html lang="de" class="bg-black text-white h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="refresh" content="300">
    <title>Lucas Entertainment — Screen</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
    <style>
        * { box-sizing: border-box; }
        body { margin: 0; font-family: system-ui, sans-serif; background: #0a0a0a; }
        .kiosk-mode { cursor: none; user-select: none; }
    </style>
</head>
<body class="kiosk-mode h-screen overflow-hidden">
    {{ $slot }}
    @livewireScripts
    @stack('scripts')

    {{-- Theater-Gong: wird aktiviert sobald ein Countdown-Slide mit startsAt-Daten vorliegt --}}
    <script type="module">
    import TheaterGong from '/js/audio/TheaterGong.js';

    const gong = new TheaterGong();
    window._theaterGong = gong;

    // Wird vom Infoscreen-JS aufgerufen sobald ein Countdown-Slide geladen ist
    window.scheduleTheaterGong = function(startsAt, mode = 'classic') {
        gong.scheduleFor(startsAt, mode);
        console.log('🎭 Theater-Gong scheduliert für', startsAt, '(' + mode + ')');
    };

    // Einlass-Gong (bei Check-in, Checkin-Screen ruft das auf)
    window.playCheckinGong = function() {
        gong.play(1);
    };

    // Für schnelles Testen aus der Browser-Konsole:
    // window._theaterGong.play(3)
    </script>
</body>
</html>
