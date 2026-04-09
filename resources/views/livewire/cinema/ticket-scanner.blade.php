<div
    class="ticket-scanner"
    x-data="ticketScanner()"
    x-init="init()"
    wire:ignore.self
>
    {{-- Mode Toggle --}}
    <div class="scanner-mode-tabs">
        <button
            class="mode-tab {{ $mode === 'scan' ? 'active' : '' }}"
            wire:click="$set('mode', 'scan')"
        >📟 HID Scanner</button>
        <button
            class="mode-tab {{ $mode === 'camera' ? 'active' : '' }}"
            wire:click="$set('mode', 'camera')"
        >📱 Kamera</button>
    </div>

    {{-- HID-Scanner Modus: einfach Fokus halten, Scanner tippt automatisch --}}
    @if ($mode === 'scan')
        <div class="scanner-hid">
            <div class="scanner-status" x-text="listening ? '🟢 Bereit — Ticket scannen...' : '⚪ Inaktiv'"></div>
            <div class="scanner-hint">USB Scanner verbunden? Einfach scannen.</div>
        </div>
    @endif

    {{-- Kamera-Modus (iPhone / Mobile) --}}
    @if ($mode === 'camera')
        <div class="scanner-camera">
            <video id="scanner-video" autoplay playsinline muted></video>
            <canvas id="scanner-canvas" style="display:none"></canvas>
            <div class="camera-overlay">
                <div class="scan-frame"></div>
            </div>
            <div class="scanner-status" x-text="cameraStatus"></div>
        </div>
    @endif

    {{-- Ergebnis --}}
    @if ($lastScan)
        <div class="scan-result scan-result--{{ $lastScan['status'] }}">
            <div class="result-icon">
                @switch($lastScan['status'])
                    @case('success') ✅ @break
                    @case('warning') ⚠️ @break
                    @case('error')   ❌ @break
                @endswitch
            </div>
            <div class="result-body">
                <strong>{{ $lastScan['message'] }}</strong>
                <code>{{ $lastScan['code'] }}</code>

                @if(isset($lastScan['ticket']))
                    <div class="result-ticket-detail">
                        <span>🎬 {{ $lastScan['ticket']['movie'] }}</span>
                        <span>🕐 {{ $lastScan['ticket']['starts_at'] }}</span>
                        <span>💺 {{ $lastScan['ticket']['seat'] }}</span>
                        <span>👤 {{ $lastScan['ticket']['customer'] }}</span>
                        <span>🔖 {{ $lastScan['ticket']['booking_ref'] }}</span>
                    </div>
                @endif

                @if(isset($lastScan['used_at']))
                    <div class="result-warning-detail">Entwertet: {{ $lastScan['used_at'] }}</div>
                @endif
            </div>
        </div>
    @endif
</div>

@push('scripts')
<script type="module">
import BarcodeListener from '/js/barcode/BarcodeListener.js';

window.ticketScanner = function() {
    return {
        listening: false,
        cameraStatus: 'Kamera wird gestartet...',
        scanner: null,
        videoEl: null,

        init() {
            this.scanner = new BarcodeListener((code) => {
                @this.handleScan(code);
            }, { captureInput: false });
            this.listening = true;

            this.$watch('$wire.mode', (mode) => {
                if (mode === 'camera') this.startCamera();
                else this.stopCamera();
            });
        },

        async startCamera() {
            this.videoEl = document.getElementById('scanner-video');
            const canvas  = document.getElementById('scanner-canvas');

            try {
                const stream = await navigator.mediaDevices.getUserMedia({
                    video: { facingMode: 'environment' }
                });
                this.videoEl.srcObject = stream;
                this.cameraStatus = '🟢 Kamera aktiv — QR-Code vor die Kamera halten';
                this.scanLoop(canvas);
            } catch(e) {
                this.cameraStatus = '❌ Kamera nicht verfügbar: ' + e.message;
            }
        },

        scanLoop(canvas) {
            if (!this.videoEl || this.videoEl.readyState < 2) {
                requestAnimationFrame(() => this.scanLoop(canvas));
                return;
            }
            const ctx = canvas.getContext('2d');
            canvas.width  = this.videoEl.videoWidth;
            canvas.height = this.videoEl.videoHeight;
            ctx.drawImage(this.videoEl, 0, 0);

            // jsQR für QR-Code Dekodierung (wird via CDN geladen)
            if (window.jsQR) {
                const imageData = ctx.getImageData(0, 0, canvas.width, canvas.height);
                const code = jsQR(imageData.data, imageData.width, imageData.height);
                if (code) {
                    @this.handleScan(code.data);
                    setTimeout(() => this.scanLoop(canvas), 2000); // Pause nach Fund
                    return;
                }
            }
            requestAnimationFrame(() => this.scanLoop(canvas));
        },

        stopCamera() {
            if (this.videoEl?.srcObject) {
                this.videoEl.srcObject.getTracks().forEach(t => t.stop());
            }
        }
    }
}
</script>
@endpush
