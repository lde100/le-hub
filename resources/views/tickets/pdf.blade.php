<!DOCTYPE html>
<html lang="de">
<head>
<meta charset="UTF-8">
<style>
  * { margin: 0; padding: 0; box-sizing: border-box; }

  @font-face { font-family: 'Courier'; }

  body {
    background: #0D0D0D;
    color: #F5F5F5;
    font-family: Courier, monospace;
    width: 595px;
    height: 280px;
    overflow: hidden;
    position: relative;
  }

  /* Ticket-Körper */
  .ticket {
    display: flex;
    width: 595px;
    height: 280px;
    background: #0D0D0D;
    border: 2px solid #C9A84C;
    border-radius: 8px;
    overflow: hidden;
    position: relative;
  }

  /* Perforierungs-Linie */
  .ticket::before {
    content: '';
    position: absolute;
    left: 400px;
    top: 0;
    bottom: 0;
    width: 1px;
    background: repeating-linear-gradient(
      to bottom,
      #C9A84C 0px, #C9A84C 6px,
      transparent 6px, transparent 12px
    );
  }
  .ticket-circle-top {
    position: absolute;
    left: 394px;
    top: -8px;
    width: 16px;
    height: 16px;
    background: #0D0D0D;
    border-radius: 50%;
    border: 2px solid #C9A84C;
  }
  .ticket-circle-bottom {
    position: absolute;
    left: 394px;
    bottom: -8px;
    width: 16px;
    height: 16px;
    background: #0D0D0D;
    border-radius: 50%;
    border: 2px solid #C9A84C;
  }

  /* Dekorative Sterne-Muster */
  .star-pattern {
    position: absolute;
    color: #C9A84C;
    opacity: .07;
    font-size: 60px;
    pointer-events: none;
  }

  /* Linke Seite (Hauptinhalt) */
  .ticket-main {
    width: 400px;
    padding: 22px 24px;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    position: relative;
    z-index: 1;
  }

  .le-brand {
    display: flex;
    align-items: center;
    gap: 8px;
    margin-bottom: 8px;
  }
  .le-logo-box {
    width: 28px;
    height: 28px;
    background: #C9A84C;
    border-radius: 4px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 10px;
    font-weight: 900;
    color: #000;
    letter-spacing: -0.5px;
  }
  .le-name {
    font-size: 9px;
    letter-spacing: .15em;
    color: #C9A84C;
    text-transform: uppercase;
  }

  .movie-title {
    font-size: 26px;
    font-weight: 900;
    color: #FFFFFF;
    line-height: 1.1;
    letter-spacing: -0.5px;
    margin-bottom: 6px;
    max-width: 340px;
  }

  .movie-meta {
    font-size: 10px;
    color: #888;
    letter-spacing: .05em;
    margin-bottom: 14px;
  }

  .ticket-details {
    display: flex;
    gap: 20px;
    margin-bottom: 14px;
  }
  .detail-block { }
  .detail-label {
    font-size: 8px;
    color: #C9A84C;
    letter-spacing: .12em;
    text-transform: uppercase;
    margin-bottom: 3px;
  }
  .detail-value {
    font-size: 13px;
    color: #F5F5F5;
    font-weight: 700;
  }

  .seat-highlight {
    display: inline-block;
    background: #C9A84C;
    color: #000;
    font-weight: 900;
    padding: 3px 10px;
    border-radius: 4px;
    font-size: 14px;
    letter-spacing: 1px;
  }

  .ticket-footer {
    display: flex;
    justify-content: space-between;
    align-items: flex-end;
    border-top: 1px solid #2A2A2A;
    padding-top: 10px;
  }
  .booking-ref {
    font-size: 9px;
    color: #555;
    letter-spacing: .1em;
  }
  .customer-name {
    font-size: 11px;
    color: #888;
  }

  /* Rechte Seite (Abriss) */
  .ticket-stub {
    width: 195px;
    padding: 18px 16px;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: space-between;
    position: relative;
    z-index: 1;
  }

  .stub-title {
    font-size: 8px;
    letter-spacing: .2em;
    color: #C9A84C;
    text-transform: uppercase;
    text-align: center;
    margin-bottom: 8px;
  }

  .qr-wrapper {
    background: #FFF;
    padding: 6px;
    border-radius: 4px;
    display: flex;
    align-items: center;
    justify-content: center;
  }
  .qr-wrapper img {
    width: 100px;
    height: 100px;
    display: block;
  }

  .seat-map-wrapper {
    margin-top: 8px;
    opacity: .9;
  }

  .ticket-code-text {
    font-size: 7px;
    color: #555;
    letter-spacing: .08em;
    text-align: center;
    margin-top: 6px;
    word-break: break-all;
  }

  /* Retro-Ecken */
  .corner { position: absolute; width: 12px; height: 12px; }
  .corner-tl { top: 6px; left: 6px; border-top: 2px solid #C9A84C; border-left: 2px solid #C9A84C; }
  .corner-tr { top: 6px; right: 6px; border-top: 2px solid #C9A84C; border-right: 2px solid #C9A84C; }
  .corner-bl { bottom: 6px; left: 6px; border-bottom: 2px solid #C9A84C; border-left: 2px solid #C9A84C; }
  .corner-br { bottom: 6px; right: 6px; border-bottom: 2px solid #C9A84C; border-right: 2px solid #C9A84C; }
</style>
</head>
<body>
<div class="ticket">
  <div class="corner corner-tl"></div>
  <div class="corner corner-tr"></div>
  <div class="corner corner-bl"></div>
  <div class="corner corner-br"></div>
  <div class="ticket-circle-top"></div>
  <div class="ticket-circle-bottom"></div>

  <!-- Dekorativ -->
  <div class="star-pattern" style="top:30px; right:430px;">★</div>
  <div class="star-pattern" style="top:100px; left:200px; font-size:30px;">✦</div>

  <!-- HAUPTTEIL -->
  <div class="ticket-main">

    <div>
      <div class="le-brand">
        <div class="le-logo-box">LE</div>
        <div class="le-name">Lucas Entertainment · Private Cinema</div>
      </div>

      <div class="movie-title">{{ $ticket->screening->movie?->title ?? 'Event' }}</div>
      <div class="movie-meta">
        {{ implode('  ·  ', array_filter([
            $ticket->screening->movie?->genre,
            $ticket->screening->movie?->duration_formatted,
            $ticket->screening->movie?->rating,
        ])) }}
      </div>
    </div>

    <div class="ticket-details">
      <div class="detail-block">
        <div class="detail-label">Datum</div>
        <div class="detail-value">{{ $ticket->screening->starts_at->format('d.m.Y') }}</div>
      </div>
      <div class="detail-block">
        <div class="detail-label">Einlass</div>
        <div class="detail-value">{{ $ticket->screening->starts_at->format('H:i') }} Uhr</div>
      </div>
      <div class="detail-block">
        <div class="detail-label">Platz</div>
        <div class="detail-value">
          <span class="seat-highlight">{{ $ticket->seat?->label ?? '—' }}</span>
        </div>
      </div>
      @if($ticket->price > 0)
      <div class="detail-block">
        <div class="detail-label">Preis</div>
        <div class="detail-value">{{ number_format($ticket->price, 2) }} €</div>
      </div>
      @endif
    </div>

    <div class="ticket-footer">
      <div>
        <div class="booking-ref">{{ $ticket->booking->booking_ref ?? $ticket->ticket_code }}</div>
        <div class="customer-name">{{ $ticket->booking->customer_name ?? '' }}</div>
      </div>
      <div style="font-size:8px; color:#444; text-align:right;">
        Bitte vorweisen<br>beim Einlass
      </div>
    </div>
  </div>

  <!-- ABRISS -->
  <div class="ticket-stub">
    <div>
      <div class="stub-title">Eintrittskarte</div>
      <div class="qr-wrapper">
        <img src="{{ $qrDataUrl }}" alt="{{ $ticket->ticket_code }}">
      </div>
      <div class="ticket-code-text">{{ $ticket->ticket_code }}</div>
    </div>

    @if($ticket->seat_id)
    <div class="seat-map-wrapper">
      {!! $seatMapSvg !!}
    </div>
    @endif
  </div>

</div>
</body>
</html>
