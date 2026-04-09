# LE-HUB — DEV_STATUS

**Stand:** 2026-04-10
**Stack:** Laravel 11 + Livewire 4 + MariaDB + Docker + Vite/Tailwind

---

## Architektur-Überblick

```
le-hub/
├── app/
│   ├── Livewire/Cinema/     # SeatMap, TicketScanner
│   └── Models/              # Venue, Seat, Movie, Screening, Booking, Ticket
├── database/
│   ├── migrations/          # cinema_tables, users_and_roles
│   └── seeders/             # HomecinemaSeeder (10 Plätze, 4 Zonen)
├── resources/
│   ├── js/barcode/          # BarcodeListener.js (HID + Camera)
│   └── views/livewire/      # seat-map, ticket-scanner
├── docker/                  # php/Dockerfile, nginx/default.conf
├── docker-compose.yml
└── .env.example
```

---

## Module-Status

### 🎬 Cinema (in Arbeit)
- [x] DB-Schema: venues, seats, movies, screenings, bookings, tickets
- [x] Seeder: Heimkino-Layout (Couch 2x, Sessel 2x, Tisch 6x = 10 Plätze)
- [x] Model: Venue, Seat, Movie, Screening, Booking, Ticket
- [x] Livewire: SeatMap (Auswahl, Status, Preis)
- [x] Livewire: TicketScanner (HID + iPhone Kamera via jsQR)
- [ ] Views: Layouts, App-Shell, Tailwind-Styling
- [ ] Buchungsflow: SeatMap → Kundendaten → Bestätigung → Ticket-PDF
- [ ] Ticket-PDF Generation (DomPDF oder ähnlich)
- [ ] Info-Screen (öffentliche URL, Auto-Refresh, nächste Vorstellung)
- [ ] Admin: Vorstellungen anlegen, Saalplan bearbeiten

### 🔒 Auth (geplant)
- [ ] Login (Laravel Auth + Rolle: admin / staff / scanner)
- [ ] Cloudflare Tunnel für Kunden-Portal

### 🛒 POS (geplant)
- [ ] Barcode-getriebener Kassenfluss
- [ ] Coupons / Kundenkarten

### 🎟️ Ticketing / RFID (geplant)
- [ ] RFID-Support (HID, gleiche Architektur wie Barcode)
- [ ] Zutritts- / Zeitsystem (Thermen-Logik)

---

## Heimkino-Layout

```
        [ LEINWAND ]

  [Couch L] [Couch R]    [Sessel] [Stuhl]

  [Tisch L1] [Tisch M1] [Tisch R1]
  [Tisch L2] [Tisch M2] [Tisch R2]
```

Raum: 3,80 × 10 m — 10 Plätze total

---

## Docker-Start (lokal)

```bash
cp .env.example .env
# APP_KEY setzen:
docker compose run --rm app php artisan key:generate
docker compose up -d
docker compose exec app php artisan migrate --seed
```

---

## Offene Fragen / Entscheidungen

- Ticket-PDF: DomPDF (Laravel-nativ) vs. externe Library?
- Infoscreen: eigene Route `/screen` oder separater Docker-Service?
- Emby-Integration: WebSocket oder Polling für "gerade läuft"?
- Kundenkarten-Format: intern generierter Barcode oder EAN-13?
