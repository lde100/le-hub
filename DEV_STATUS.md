# LE-HUB — DEV_STATUS

**Stand:** 2026-04-10
**Stack:** Laravel 11 + Livewire 4 + MariaDB 11 + Docker + Vite/Tailwind

---

## Architektur

```
le-hub/
├── app/
│   ├── Livewire/
│   │   ├── Cinema/          # SeatMap, TicketScanner
│   │   └── Infoscreen/      # Screen (multi-channel)
│   └── Models/
│       ├── CORE: Customer, Product, ProductCategory
│       ├── CORE: Order, OrderParticipant, OrderItem, OrderItemSplit
│       ├── CORE: Invoice, Payment, InfoscreenSlide
│       └── CINEMA: Venue, Seat, Movie, Screening, Booking, Ticket
├── database/
│   ├── migrations/
│   │   ├── cinema_tables (venues, seats, movies, screenings, bookings, tickets)
│   │   └── core_tables   (customers, products, orders + splits, invoices, payments, infoscreen_slides)
│   └── seeders/
│       ├── HomecinemaSeeder  — 10 Plätze (Couch 2x, Sessel 2x, Tisch 6x)
│       └── CoreSeeder        — Kategorien, Beispiel-Produkte, Infoscreen-Slides
├── resources/
│   ├── js/barcode/           # BarcodeListener.js
│   └── views/
│       ├── layouts/infoscreen.blade.php
│       └── livewire/
│           ├── cinema/       # seat-map, ticket-scanner
│           └── infoscreen/   # screen (multi-channel)
├── routes/web.php
├── docker-compose.yml
└── .env.example
```

---

## Modul-Status

### 🎬 Cinema
- [x] DB-Schema vollständig
- [x] Seeder: 10 Plätze (echtes Layout)
- [x] Livewire: SeatMap, TicketScanner (HID + iPhone Kamera)
- [ ] Buchungsflow: SeatMap → Kundendaten → Confirm → Ticket-PDF
- [ ] Admin: Vorstellungen/Filme anlegen
- [ ] Ticket-PDF (DomPDF)

### 🛒 Gastro / POS (Core gebaut)
- [x] DB-Schema: customers, products, orders, order_items, splits, invoices, payments
- [x] Split-Logik: assignTo(), splitEqually() auf OrderItem
- [x] Seeder: Kategorien + Beispiel-Produkte
- [ ] Livewire: Bestellaufnahme (Barcode-Scan + Touch-Auswahl)
- [ ] Livewire: Tab-Ansicht mit Per-Person-Zuweisung
- [ ] Livewire: Bill-Split UI (zuweisen, aufteilen, bezahlen)
- [ ] Payment-Flow: bar / PayPal.me QR

### 📺 Infoscreen
- [x] DB-Schema: infoscreen_slides (multi-channel)
- [x] Livewire: Screen-Komponente (alle Slide-Typen)
- [x] Channels: `main` (TV), `menu` (iPad), erweiterbar
- [x] Slide-Typen: now_playing, upcoming, menu_category, paypal_qr, custom_text
- [x] Route: GET /screen, GET /screen/{channel}
- [x] Auto-Advance mit konfigurierbarer Dauer
- [ ] Admin UI: Slides verwalten, Reihenfolge, Zeiten

### 👥 Kunden / CRM
- [x] DB: context-Feld (guest / client / both)
- [x] customer_number: LE-K-0001
- [ ] UI: Kundenliste, Kunden anlegen, Kundenkarte (Barcode)

### 🔒 Auth
- [ ] Login-Controller
- [ ] App-Shell / Layout mit LE-Branding
- [ ] Rollen: admin / staff / scanner

### 🔧 IT-Service (später)
- [ ] Angebote
- [ ] Projekte / Service-Tickets
- [ ] Zeiterfassung

---

## Heimkino-Saalplan

```
        [ LEINWAND ]

  [Couch L] [Couch R]    [Sessel] [Stuhl]

  [Tisch L1] [Tisch M1] [Tisch R1]
  [Tisch L2] [Tisch M2] [Tisch R2]
```
Raum: 3,80 × 10 m — 10 Plätze total

---

## Infoscreen-Channels

| Channel | URL           | Gedacht für         |
|---------|---------------|---------------------|
| main    | /screen       | Heimkino-TV         |
| menu    | /screen/menu  | iPad Menükarte      |
| payment | /screen/payment | Bezahl-QR-Screen  |

---

## Docker-Start

```bash
cp .env.example .env
docker compose run --rm app php artisan key:generate
docker compose up -d
docker compose exec app php artisan migrate
docker compose exec app php artisan db:seed --class=HomecinemaSeeder
docker compose exec app php artisan db:seed --class=CoreSeeder
```

---

## Design-Entscheidungen

- **Order ≠ Invoice**: Order = offener Tab, Invoice = abgerechnetes Dokument
- **OrderItemSplit**: Cent-Reste landen bei Person 1 (deterministisch, kein Rundungsproblem)
- **Infoscreen**: rein client-seitig getaktet (setTimeout), kein WebSocket nötig
- **Barcode-Listener**: Gap-basiert (≤50ms), kein Konflikt mit normalem Tippen
- **PayPal**: QR-Code via api.qrserver.com, kein PayPal-API nötig
- **IT-Service nutzt dieselben Tabellen**: Product(category=it), Order(module=it), Invoice

---

## Offene Fragen

- LE-Branding: Farben? (aktuell: schwarz/weiß/clean — Vorschlag: Dunkelrot/Gold als Akzent)
- Ticket-PDF: mit/ohne Sitzplatz-Graphic?
- PayPal.me Benutzername?
- Emby-Integration: Polling oder WebSocket für "gerade läuft"?
