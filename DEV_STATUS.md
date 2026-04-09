# LE-HUB — DEV_STATUS

**Stand:** 2026-04-10
**Stack:** Laravel 11 + Livewire 4 + MariaDB 11 + Docker + Vite/Tailwind

---

## Architektur

```
le-hub/
├── app/
│   ├── Livewire/
│   │   ├── Cinema/           SeatMap, TicketScanner
│   │   ├── Event/            PublicEventPage (öffentlich, kein Login)
│   │   └── Infoscreen/       Screen (multi-channel)
│   └── Models/
│       ├── CORE:    Customer, Product, ProductCategory
│       ├── CORE:    Order, OrderParticipant, OrderItem, OrderItemSplit
│       ├── CORE:    Invoice, Payment, InfoscreenSlide
│       ├── CINEMA:  Venue, Seat, Movie, Screening, Booking, Ticket
│       ├── GUEST:   Guest, LoyaltyTransaction
│       └── EVENT:   Event, EventSlot, EventPoll, PollOption, PollVote,
│                    SeatRequest, Attendance
├── database/migrations/
│   ├── cinema_tables
│   ├── core_tables
│   └── guest_and_event_tables
├── database/seeders/
│   ├── HomecinemaSeeder
│   └── CoreSeeder
├── resources/views/
│   ├── layouts/
│   │   ├── public.blade.php      (LE-Branding, Mobile-optimiert)
│   │   └── infoscreen.blade.php  (Kiosk-Mode, kein Cursor)
│   └── livewire/
│       ├── cinema/    seat-map, ticket-scanner
│       ├── event/     public-page
│       └── infoscreen/screen
└── routes/web.php
```

---

## Event-Lifecycle

```
DRAFT
  ↓  Admin legt Event + Terminvorschläge an
polling_date  ──── /event/{token} → Terminabstimmung (Ja/Vielleicht/Nein)
  ↓  Admin bestätigt Termin
polling_film  ──── /event/{token} → Film-Voting (Like) + Filmwünsche (Freitext)
  ↓  Admin bestätigt Film → Screening wird angelegt
booking_open  ──── /event/{token} → Sitzplatz anfragen + Saalplan
  ↓  Admin bestätigt Sitzplätze → Tickets generieren
confirmed     ──── Gäste sehen bestätigte Vorstellung + Ticket-Link
  ↓  Vorstellung läuft, Einlass via Scanner
finished      ──── Statistik, Punkte, History
```

---

## Modul-Status

### 🎬 Cinema
- [x] DB vollständig
- [x] Seeder: 10 Plätze
- [x] Livewire: SeatMap, TicketScanner (HID + Kamera)
- [ ] **Buchungsflow: SeatMap → Kundendaten → Ticket-PDF** ← NÄCHSTER SCHRITT
- [ ] Admin: Vorstellungen anlegen

### 🎟️ Event / Voting
- [x] DB: events, event_slots, event_polls, poll_options, poll_votes, seat_requests, attendances
- [x] Modelle: Event, EventPoll, PollOption, PollVote, SeatRequest, Attendance
- [x] Livewire: PublicEventPage (alle Lifecycle-Phasen)
- [x] Termin-Abstimmung (Ja/Vielleicht/Nein)
- [x] Film-Voting (Like/Unlike) + eigene Filmwünsche
- [x] Sitzplatz-Anfrage mit Mini-Saalplan
- [ ] Admin: Event anlegen, Polls erstellen, Abstimmungen auswerten, Sitze bestätigen
- [ ] Emby-Integration (Platzhalter, später)
- [ ] TMDB-Suche für externe Filmwünsche

### 👤 Gäste & Loyalty
- [x] DB: guests, loyalty_transactions
- [x] Model: Guest (Magic Token, Avatar-Farbe, Initialen)
- [x] Guest::earnPoints(), Guest::findByToken()
- [x] Niedrigschwellig: Name reicht, E-Mail optional
- [ ] Punkte-Regeln definieren (x Punkte pro Besuch)
- [ ] Loyalty-Dashboard für Gäste
- [ ] Prämien / Einlösungen

### 🛒 Gastro / POS
- [x] DB + Modelle vollständig
- [ ] Livewire: Bestellaufnahme, Tab-View, Bill-Split UI

### 📺 Infoscreen
- [x] DB, Modelle, Livewire-Komponente
- [x] Routes: /screen, /screen/{channel}
- [ ] Admin-UI für Slides

### 🔒 Auth
- [ ] Login-Controller + App-Shell mit LE-Branding

---

## Routen-Übersicht

| URL                    | Auth? | Beschreibung                        |
|------------------------|-------|-------------------------------------|
| /event/{token}         | Nein  | Öffentliche Event-Seite (WhatsApp)  |
| /screen                | Nein  | Infoscreen main-channel             |
| /screen/{channel}      | Nein  | Infoscreen beliebiger Channel       |
| /login                 | Nein  | Admin-Login                         |
| /dashboard             | Ja    | Admin-Backend                       |
| /cinema                | Ja    | Cinema-Verwaltung                   |
| /gastro                | Ja    | Gastro / Bestellungen               |
| /customers             | Ja    | Kundenverwaltung                    |
| /infoscreen            | Ja    | Infoscreen-Admin                    |

---

## Design-System

Farben: Schwarz (#0D0D0D) + Gold (#C9A84C) + Weiß
Font: System-UI
Mobile-first, Touch-optimiert, auch Keyboard-only nutzbar

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

## Offene Architektur-Fragen

- Emby URL + API-Key → später eintragen
- PayPal.me Username → in .env konfigurierbar machen
- Loyalitätspunkte-Regeln: wie viele Punkte pro Besuch?
- Ticket-PDF: mit Sitzplatz-Grafik oder clean/minimal?
