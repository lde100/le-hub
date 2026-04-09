<!DOCTYPE html>
<html lang="de">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
<title>Dein Ticket · Lucas Entertainment</title>
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
<style>
:root { --gold: #C9A84C; --dark: #0D0D0D; --surface: #141414; }
* { box-sizing: border-box; margin: 0; padding: 0; }

body {
    background: #111;
    font-family: system-ui, -apple-system, sans-serif;
    min-height: 100dvh;
    display: flex;
    flex-direction: column;
    align-items: center;
    padding: 1.5rem 1rem 3rem;
    color: #f5f5f5;
}

/* LE-Header */
.le-header {
    display: flex;
    align-items: center;
    gap: .625rem;
    margin-bottom: 2rem;
    align-self: flex-start;
}
.le-badge {
    width: 32px; height: 32px;
    background: var(--gold);
    border-radius: 6px;
    display: flex; align-items: center; justify-content: center;
    font-weight: 900; font-size: 12px; color: #000; letter-spacing: -.5px;
}
.le-name { font-size: 11px; letter-spacing: .15em; color: var(--gold); text-transform: uppercase; }

/* ── DAS TICKET ─────────────────────────────────────────────────── */
#ticket-card {
    width: 100%;
    max-width: 420px;
    background: var(--dark);
    border: 2px solid var(--gold);
    border-radius: 16px;
    overflow: hidden;
    position: relative;
    font-family: 'Courier New', Courier, monospace;
}

/* Oberer Bereich */
.ticket-top {
    padding: 1.5rem 1.5rem 1.25rem;
    position: relative;
    background: linear-gradient(135deg, #0D0D0D 0%, #1a1410 100%);
}

/* Sterne-Deko */
.deco-star {
    position: absolute;
    color: var(--gold);
    opacity: .08;
    font-size: 80px;
    pointer-events: none;
    user-select: none;
}

.ticket-brand {
    display: flex;
    align-items: center;
    gap: .5rem;
    margin-bottom: 1.25rem;
}
.ticket-badge {
    width: 24px; height: 24px;
    background: var(--gold);
    border-radius: 4px;
    display: flex; align-items: center; justify-content: center;
    font-size: 9px; font-weight: 900; color: #000;
}
.ticket-brand-name {
    font-size: 9px;
    letter-spacing: .2em;
    color: var(--gold);
    text-transform: uppercase;
}

.movie-title {
    font-size: 1.6rem;
    font-weight: 900;
    color: #fff;
    line-height: 1.1;
    margin-bottom: .375rem;
}
.movie-meta {
    font-size: .75rem;
    color: #666;
    letter-spacing: .05em;
    margin-bottom: 1.25rem;
}

.ticket-fields {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: .75rem;
    margin-bottom: 1.25rem;
}
.field-label {
    font-size: .6rem;
    color: var(--gold);
    letter-spacing: .12em;
    text-transform: uppercase;
    margin-bottom: .25rem;
}
.field-value {
    font-size: .9rem;
    font-weight: 700;
    color: #fff;
}
.seat-badge {
    display: inline-block;
    background: var(--gold);
    color: #000;
    font-weight: 900;
    padding: .2rem .625rem;
    border-radius: 4px;
    font-size: .9rem;
    letter-spacing: 1px;
}

/* Perforations-Linie */
.perforation {
    display: flex;
    align-items: center;
    padding: 0 -2px;
    position: relative;
    margin: 0;
}
.perf-circle {
    width: 20px; height: 20px;
    background: #111;
    border-radius: 50%;
    flex-shrink: 0;
    margin: 0 -10px;
    position: relative;
    z-index: 2;
    border: 2px solid var(--gold);
}
.perf-line {
    flex: 1;
    height: 0;
    border-top: 2px dashed #C9A84C55;
}

/* Unterer Bereich (Stub) */
.ticket-bottom {
    padding: 1.25rem 1.5rem;
    display: flex;
    gap: 1rem;
    align-items: center;
    background: #0a0a0a;
}

.qr-area {
    flex-shrink: 0;
}
.qr-box {
    background: #fff;
    padding: 6px;
    border-radius: 8px;
    display: inline-flex;
}
.qr-box img { width: 90px; height: 90px; display: block; border-radius: 2px; }

.ticket-info {
    flex: 1;
    min-width: 0;
}
.customer-name {
    font-size: 1rem;
    font-weight: 700;
    color: #fff;
    margin-bottom: .25rem;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
.booking-ref {
    font-size: .7rem;
    color: #555;
    letter-spacing: .1em;
    margin-bottom: .625rem;
}

/* SVG Saalplan */
.seatmap-area {
    margin-top: .625rem;
}

/* Tickets-Code unten */
.ticket-code-bar {
    background: #080808;
    padding: .5rem 1.5rem;
    text-align: center;
    font-size: .65rem;
    color: #333;
    letter-spacing: .12em;
    font-family: 'Courier New', monospace;
    border-top: 1px solid #1a1a1a;
}

/* ── BUTTONS ─────────────────────────────────────────────────────── */
.action-buttons {
    margin-top: 1.5rem;
    display: flex;
    flex-direction: column;
    gap: .75rem;
    width: 100%;
    max-width: 420px;
}
.btn {
    width: 100%;
    padding: .875rem 1rem;
    border-radius: 12px;
    border: none;
    font-size: .95rem;
    font-weight: 700;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: .5rem;
    transition: opacity .15s;
}
.btn:active { opacity: .8; }
.btn-primary { background: var(--gold); color: #000; }
.btn-secondary { background: #1e1e1e; color: #f5f5f5; border: 1px solid #333; }

/* iOS Hinweis */
.ios-hint {
    margin-top: 1rem;
    font-size: .8rem;
    color: #555;
    text-align: center;
    line-height: 1.5;
    max-width: 320px;
}

/* Loading-Overlay */
#loading {
    display: none;
    position: fixed;
    inset: 0;
    background: #000a;
    z-index: 99;
    align-items: center;
    justify-content: center;
    flex-direction: column;
    gap: 1rem;
    color: #fff;
    font-size: 1rem;
}
#loading.active { display: flex; }
</style>
</head>
<body>

<div class="le-header">
    <div class="le-badge">LE</div>
    <div class="le-name">Lucas Entertainment</div>
</div>

<!-- DAS TICKET (wird zu PNG gerendert) -->
<div id="ticket-card">

    <div class="ticket-top">
        <span class="deco-star" style="top:10px; right:20px;">★</span>
        <span class="deco-star" style="bottom:-10px; left:120px; font-size:40px;">✦</span>

        <div class="ticket-brand">
            <div class="ticket-badge">LE</div>
            <div class="ticket-brand-name">Lucas Entertainment · Private Cinema</div>
        </div>

        <div class="movie-title">{{ $ticket->screening->movie?->title ?? 'Event' }}</div>
        <div class="movie-meta">
            {{ implode('  ·  ', array_filter([
                $ticket->screening->movie?->genre,
                $ticket->screening->movie?->duration_formatted,
                $ticket->screening->movie?->rating,
            ])) }}
        </div>

        <div class="ticket-fields">
            <div>
                <div class="field-label">Datum</div>
                <div class="field-value">{{ $ticket->screening->starts_at->format('d.m.Y') }}</div>
            </div>
            <div>
                <div class="field-label">Einlass</div>
                <div class="field-value">{{ $ticket->screening->starts_at->format('H:i') }} Uhr</div>
            </div>
            <div>
                <div class="field-label">Platz</div>
                <div class="field-value">
                    @if($ticket->seat)
                        <span class="seat-badge">{{ $ticket->seat->label }}</span>
                    @else
                        <span style="color:#888;">Freier Platz</span>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Perforations-Linie -->
    <div class="perforation">
        <div class="perf-circle"></div>
        <div class="perf-line"></div>
        <div class="perf-circle"></div>
    </div>

    <!-- Stub -->
    <div class="ticket-bottom">
        <div class="qr-area">
            <div class="qr-box">
                <img
                    src="https://api.qrserver.com/v1/create-qr-code/?size=180x180&format=png&data={{ urlencode($ticket->ticket_code) }}"
                    alt="QR Code"
                    crossorigin="anonymous"
                >
            </div>
        </div>

        <div class="ticket-info">
            <div class="customer-name">{{ $ticket->booking->customer_name ?? 'Gast' }}</div>
            <div class="booking-ref">{{ $ticket->booking->booking_ref ?? $ticket->ticket_code }}</div>

            @if($ticket->seat_id)
            <div class="seatmap-area">
                {!! $seatMapSvg !!}
            </div>
            @endif
        </div>
    </div>

    <div class="ticket-code-bar">{{ $ticket->ticket_code }}</div>
</div>

<!-- BUTTONS -->
<div class="action-buttons">
    <button class="btn btn-primary" onclick="saveAsPng()">
        📸 Als Bild speichern
    </button>
    <button class="btn btn-secondary" onclick="window.print()">
        🖨️ PDF / Drucken
    </button>
    <button class="btn btn-secondary" onclick="shareTicket()">
        📤 Teilen
    </button>
</div>

<div class="ios-hint" id="ios-hint" style="display:none;">
    👆 Bild lange drücken → <strong>"Zu Fotos hinzufügen"</strong>
</div>

<!-- Loading -->
<div id="loading">
    <div style="font-size:2rem;">🎬</div>
    <div>Ticket wird erstellt…</div>
</div>

<!-- Generiertes PNG anzeigen (nach html2canvas) -->
<div id="png-result" style="display:none; margin-top:1.5rem; width:100%; max-width:420px; text-align:center;">
    <img id="png-img" style="width:100%; border-radius:16px; border:2px solid var(--gold);" alt="Ticket">
    <div class="ios-hint" style="display:block; margin-top:.75rem;">
        👆 Bild lange drücken → <strong>"Zu Fotos hinzufügen"</strong>
    </div>
</div>

<script>
const isIOS = /iPad|iPhone|iPod/.test(navigator.userAgent);
const isAndroid = /Android/.test(navigator.userAgent);

async function saveAsPng() {
    const loading = document.getElementById('loading');
    loading.classList.add('active');

    // QR-Bild vorladen damit html2canvas es erwischt
    await new Promise(r => setTimeout(r, 300));

    try {
        const canvas = await html2canvas(document.getElementById('ticket-card'), {
            backgroundColor: '#0D0D0D',
            scale: 3,            // 3x für scharfe Darstellung auf Retina
            useCORS: true,
            allowTaint: false,
            logging: false,
        });

        loading.classList.remove('active');

        if (isIOS) {
            // iOS: Bild anzeigen, User tippt "Zu Fotos hinzufügen"
            const img = document.getElementById('png-img');
            const result = document.getElementById('png-result');
            img.src = canvas.toDataURL('image/png');
            result.style.display = 'block';
            result.scrollIntoView({ behavior: 'smooth' });
        } else {
            // Android + Desktop: direkter Download
            const link = document.createElement('a');
            link.download = 'ticket-{{ $ticket->ticket_code }}.png';
            link.href = canvas.toDataURL('image/png');
            link.click();
        }
    } catch(e) {
        loading.classList.remove('active');
        alert('Fehler beim Erstellen: ' + e.message);
    }
}

async function shareTicket() {
    const url = window.location.href;
    const title = '🎬 Mein Ticket · Lucas Entertainment';
    const text = '{{ $ticket->screening->movie?->title ?? "Event" }} · {{ $ticket->screening->starts_at->format("d.m.Y H:i") }} Uhr';

    if (navigator.share) {
        // Web Share API (iOS Safari, Android Chrome)
        await navigator.share({ title, text, url });
    } else {
        // Fallback: in Zwischenablage
        await navigator.clipboard.writeText(url);
        alert('Link kopiert: ' + url);
    }
}

// iOS Hint direkt anzeigen
if (isIOS) {
    document.getElementById('ios-hint').style.display = 'block';
}
</script>

@push('scripts')
<style>
@media print {
    .action-buttons, .ios-hint, #ios-hint, #png-result, .le-header { display: none !important; }
    body { background: white; padding: 0; }
    #ticket-card { border-color: #000; max-width: 100%; }
}
</style>
@endpush

</body>
</html>
