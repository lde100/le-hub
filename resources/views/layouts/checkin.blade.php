<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LE · Einlass</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
    <style>
        :root { --gold: #C9A84C; --dark: #0D0D0D; --surface: #141414; }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { background: var(--dark); color: #f5f5f5; font-family: system-ui, sans-serif; height: 100dvh; overflow: hidden; }
    </style>
</head>
<body x-data="checkinApp()" x-init="init()" @ring-bell.window="ringBell()">
    {{ $slot }}
    @livewireScripts

    <script>
    window.checkinApp = function() {
        return {
            init() {
                // BarcodeListener initialisieren
                import('/js/barcode/BarcodeListener.js').then(({ default: BarcodeListener }) => {
                    new BarcodeListener((code) => {
                        Livewire.dispatch('handle-scan', { code });
                    }, { captureInput: false });
                });
            },

            ringBell() {
                // Theater-Glocke via Web Audio API — kein externes File nötig
                const ctx = new (window.AudioContext || window.webkitAudioContext)();
                this._playBell(ctx, 880, 0.0,  0.6, 'sine');
                this._playBell(ctx, 1108, 0.15, 0.5, 'sine');
                this._playBell(ctx, 1318, 0.3,  0.4, 'sine');
            },

            _playBell(ctx, freq, delay, duration, type) {
                const osc  = ctx.createOscillator();
                const gain = ctx.createGain();
                osc.connect(gain);
                gain.connect(ctx.destination);
                osc.type = type;
                osc.frequency.value = freq;
                gain.gain.setValueAtTime(0.3, ctx.currentTime + delay);
                gain.gain.exponentialRampToValueAtTime(0.001, ctx.currentTime + delay + duration);
                osc.start(ctx.currentTime + delay);
                osc.stop(ctx.currentTime + delay + duration);
            }
        }
    }
    </script>
</body>
</html>
