<!DOCTYPE html>
<html lang="de">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width">
<title>Label · {{ $ticket->ticket_code }}</title>
<style>
/* ── Brother QL 62mm Endlosband, Thermaldruck S/W ──────────────── */
* { margin:0; padding:0; box-sizing:border-box; }

@media screen {
    body { background:#f0f0f0; display:flex; flex-direction:column; align-items:center; padding:1rem; font-family:system-ui,sans-serif; }
    .label { background:#fff; box-shadow:0 2px 12px rgba(0,0,0,.15); margin-bottom:1rem; }
    .print-btn { background:#C9A84C; color:#000; font-weight:700; border:none; padding:.75rem 2rem; border-radius:10px; cursor:pointer; font-size:1rem; }
}

@media print {
    body { margin:0; padding:0; }
    .print-btn, .print-hint { display:none !important; }
    @page { margin:0; size: 62mm auto; }
}

/* Label-Körper: 62mm breit, Höhe ergibt sich */
.label {
    width: 62mm;
    padding: 3mm 3mm 4mm;
    font-family: 'Arial Narrow', Arial, sans-serif;
    color: #000;
    background: #fff;
}

/* Marken-Header */
.label-header {
    display: flex;
    align-items: center;
    gap: 2mm;
    border-bottom: 0.5pt solid #000;
    padding-bottom: 2mm;
    margin-bottom: 2.5mm;
}
.label-badge {
    width: 7mm; height: 7mm;
    background: #000;
    border-radius: 1mm;
    display: flex; align-items: center; justify-content: center;
    color: #fff;
    font-size: 5pt;
    font-weight: 900;
    letter-spacing: -0.3pt;
    flex-shrink: 0;
}
.label-brand {
    font-size: 5pt;
    font-weight: 700;
    letter-spacing: .05em;
    text-transform: uppercase;
    line-height: 1.3;
}
.label-brand span {
    display: block;
    font-size: 4pt;
    font-weight: 400;
    color: #555;
    letter-spacing: .08em;
}

/* Filmtitel */
.label-title {
    font-size: 11pt;
    font-weight: 900;
    line-height: 1.15;
    margin-bottom: 2mm;
    /* Zu lang → umbrechen, max 2 Zeilen */
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

/* Infos */
.label-info {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1.5mm 3mm;
    margin-bottom: 3mm;
}
.info-block {}
.info-label {
    font-size: 4pt;
    text-transform: uppercase;
    letter-spacing: .1em;
    color: #666;
    margin-bottom: .5mm;
}
.info-value {
    font-size: 8pt;
    font-weight: 700;
}

/* Sitz-Highlight */
.seat-box {
    background: #000;
    color: #fff;
    font-size: 9pt;
    font-weight: 900;
    text-align: center;
    padding: 1.5mm 0;
    border-radius: 1mm;
    letter-spacing: 1pt;
    margin-bottom: 3mm;
}

/* QR-Code Bereich */
.label-bottom {
    display: flex;
    align-items: center;
    gap: 3mm;
    border-top: 0.5pt solid #000;
    padding-top: 2.5mm;
}
.qr-box {
    flex-shrink: 0;
}
.qr-box img {
    width: 18mm;
    height: 18mm;
    display: block;
    image-rendering: pixelated;
}
.label-code {
    flex: 1;
}
.code-value {
    font-size: 5.5pt;
    font-family: 'Courier New', monospace;
    font-weight: 700;
    letter-spacing: .05em;
    word-break: break-all;
    margin-bottom: 1.5mm;
}
.code-name {
    font-size: 6pt;
    color: #555;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

/* Perforations-Optik (nur Screen) */
@media screen {
    .label {
        border: 1px dashed #ccc;
        border-radius: 2mm;
    }
}
</style>
</head>
<body>

<div class="label" id="label">

    <div class="label-header">
        <div class="label-badge">LE</div>
        <div class="label-brand">
            Lucas Entertainment
            <span>Private Cinema</span>
        </div>
    </div>

    <div class="label-title">{{ $ticket->screening->movie?->title ?? 'Veranstaltung' }}</div>

    <div class="label-info">
        <div class="info-block">
            <div class="info-label">Datum</div>
            <div class="info-value">{{ $ticket->screening->starts_at->format('d.m.Y') }}</div>
        </div>
        <div class="info-block">
            <div class="info-label">Einlass</div>
            <div class="info-value">{{ $ticket->screening->starts_at->format('H:i') }} Uhr</div>
        </div>
        @if($ticket->screening->movie?->genre)
        <div class="info-block">
            <div class="info-label">Genre</div>
            <div class="info-value">{{ $ticket->screening->movie->genre }}</div>
        </div>
        @endif
        @if($ticket->screening->movie?->rating)
        <div class="info-block">
            <div class="info-label">FSK</div>
            <div class="info-value">{{ $ticket->screening->movie->rating }}</div>
        </div>
        @endif
    </div>

    @if($ticket->seat)
    <div class="seat-box">💺 {{ $ticket->seat->label }}</div>
    @endif

    <div class="label-bottom">
        <div class="qr-box">
            <img
                src="https://api.qrserver.com/v1/create-qr-code/?size=200x200&format=png&margin=0&data={{ urlencode($ticket->ticket_code) }}"
                alt="QR"
            >
        </div>
        <div class="label-code">
            <div class="code-value">{{ $ticket->ticket_code }}</div>
            <div class="code-name">{{ $ticket->booking->customer_name ?? '' }}</div>
            @if($ticket->booking->booking_ref)
            <div class="code-name">{{ $ticket->booking->booking_ref }}</div>
            @endif
        </div>
    </div>

</div>

<button class="print-btn" onclick="window.print()">🖨️ Drucken (62mm Label)</button>
<div class="print-hint" style="margin-top:.75rem; font-size:.8rem; color:#888; text-align:center; max-width:200px;">
    Im Druckdialog:<br>Papierformat = 62mm × Endlos<br>Ränder = Keine
</div>

</body>
</html>
