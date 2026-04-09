<!DOCTYPE html>
<html lang="de" class="bg-black text-white h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="refresh" content="300"> {{-- Hard-Refresh alle 5min falls Slides sich geändert haben --}}
    <title>Lucas Entertainment — Infoscreen</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
    <style>
        * { box-sizing: border-box; }
        body { margin: 0; font-family: 'Inter', system-ui, sans-serif; background: #0a0a0a; }
        /* Verhindert Cursor-Anzeige auf TV/Kiosk */
        .kiosk-mode { cursor: none; user-select: none; }
    </style>
</head>
<body class="kiosk-mode h-screen overflow-hidden">
    {{ $slot }}
    @livewireScripts
</body>
</html>
