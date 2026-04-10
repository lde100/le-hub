# LE-HUB — DEV_STATUS

**Stand:** 2026-04-10
**Stack:** Laravel 11 + Livewire 4 + MariaDB 11 + Docker + Vite/Tailwind
**Repo:** https://github.com/lde100/le-hub

---

## Schnellstart

```bash
git clone https://github.com/lde100/le-hub.git && cd le-hub
cp .env.example .env
# APP_URL=http://192.168.1.xxx:8080  ← Heimnetz-IP setzen
docker compose up -d --build
docker compose exec app php artisan key:generate
docker compose exec app php artisan migrate --seed
# Passwort setzen:
docker compose exec app php artisan tinker
>>> \App\Models\User::first()->update(['password' => bcrypt('dein-passwort')])
```

Login: `http://IP:8080` → `admin@le-hub.local`
Vollständige Anleitung: **SETUP.md**

---

## Was funktioniert (testbereit)

### 🎟 Event-Workflow (komplett)
```
/events → Event anlegen
  ↓ Termin-Abstimmung (mehrere Optionen)
  ↓ öffentlicher Link → WhatsApp → Gäste stimmen ab
  ↓ Admin bestätigt Termin
  ↓ Film-Abstimmung (Likes + Wünsche)
  ↓ Admin bestätigt Film → Screening angelegt
  ↓ Gäste: Sitzplatz anfragen
  ↓ Admin: bestätigen → Ticket generiert
  ↓ Ticket-Link per WhatsApp
```

### 🎛 Event-Hub (`/events/{id}/hub`)
- Alle Links an einem Ort — kein URL eintippen
- Einladungs-Link + WhatsApp-Button
- Screen-URLs + QR-Codes (Handy draufhalten → öffnet sich)
- Overlay-URLs + QR-Codes für vMix
- Ticket-Links für jeden Gast mit WhatsApp-Button

### 🎬 Abend-Betrieb
| Screen | URL |
|--------|-----|
| Beamer (Infoscreen) | `/screen/main/{id}?autoplay=1` |
| Screen 2 (Einlass) | `/cinema/entrance/{id}?autoplay=1` |
| Scanner-PC | `/cinema/checkin/{id}` |
| Post-Event | `/cinema/post/{id}` |

**State-Control am Scanner-PC:**
`⏳ Countdown` → `✨ Gleich geht's los` → `🔔 Letzter Gong` → `▶ Film läuft`

### 🎭 vMix Overlays (Luma-Key: Schwarz = transparent)
| Overlay | URL |
|---------|-----|
| 5-4-3-2-1 Countdown | `/overlay/countdown/{id}?duration=5` |
| Vorhang öffnet sich | `/overlay/curtain/{id}` |
| Live-Reaktionen | `/overlay/reactions/{id}` |

### 🔊 Audio
- **Theater-Gong** (Web Audio): 3× bei 15min, 2× bei 5min, 1× manuell
- **Einlass-Chime** (togglebar 🔔): Erfolg/Warnung/Fehler
- Testbar aus Browser-Konsole: `window._theaterGong.play(3)` / `window._checkinChime.play()`

### 🎟 Ticket-Seite (`/ticket/{code}`)
- Retro-Kinokarte mit Saalplan-SVG + QR
- PNG speichern (html2canvas, 3×) → Fotos-App
- PDF drucken
- WhatsApp teilen
- Brother 62mm Label: `/ticket/{code}/label`
- Live-Reaktions-Buttons (erscheinen automatisch wenn Film läuft)

### 📟 Checkin-Screen (Admin)
- Barcode/QR scannen (HID) + manueller Check-in
- Abendkasse: Walk-in Ticket sofort erstellen
- Ticket bearbeiten: Name, Platz, Status, Storno
- Sitzplatz spontan hinzufügen
- Film/Zeit spontan ändern
- Ticker auf alle Screens senden (Laufschrift)
- State-Steuerung: Countdown → Ready → Playing

### 📺 Screens
- **Infoscreen** (`/screen`): Slides, Countdown, Menü, PayPal-QR
- **Entrance-Screen**: Countdown + animierter Sitzplan bei Scan
  - Automatisch: `countdown` → `ready` → `playing` je nach State
- **Post-Event**: Danke + Anwesenheitsliste

---

## Architektur

```
le-hub/
├── app/
│   ├── Livewire/
│   │   ├── Admin/        Dashboard, EventIndex, EventDetail, EventHub, CinemaIndex
│   │   ├── Cinema/       SeatMap, TicketScanner, CheckinScreen, EntranceScreen
│   │   ├── Event/        PublicEventPage
│   │   └── Infoscreen/   Screen
│   ├── Models/           (18 Models)
│   └── Services/
│       ├── CheckinBroadcastService   State, Scan, Gong, Ticker, Reaktionen
│       ├── TicketPdfService          Retro-PDF + SVG-Saalplan
│       └── AppleWalletService        (Platzhalter, braucht Apple Dev Cert)
├── public/js/
│   ├── audio/
│   │   ├── TheaterGong.js    Gong-Synthesizer (Web Audio)
│   │   └── CheckinChime.js   Einlass-Chime (Web Audio)
│   └── barcode/
│       └── BarcodeListener.js  HID-Scanner Handler
├── resources/views/
│   ├── layouts/     app, public, infoscreen, checkin
│   ├── overlays/    base, countdown, curtain, reactions
│   ├── tickets/     show, pdf, label
│   ├── cinema/      post-event
│   └── livewire/    (alle Komponenten)
├── docker-compose.yml   (inkl. Cloudflare-Tunnel-Profile)
├── SETUP.md
└── .env.example
```

---

## DB-Schema (18 Tabellen)

**Core:** customers, products, product_categories, orders, order_participants,
order_items, order_item_splits, invoices, payments, infoscreen_slides

**Cinema:** venues, seats, movies, screenings, bookings, tickets

**Events:** events, event_slots, event_polls, poll_options, poll_votes,
seat_requests, attendances

**Guests:** guests, loyalty_transactions

**System:** users, sessions, cache, jobs

---

## Nächste Schritte (nach Testlauf)

- [ ] Testen + Bugs fixen
- [ ] Film-Bewertung nach dem Film (1-5 Sterne, Ergebnis auf Post-Event-Screen)
- [ ] Film-Tagebuch (`/archive`) — alle Abende mit Poster + Wer war dabei
- [ ] Gastro/POS — Bestellaufnahme, Tab, Bill-Split
- [ ] Weitere vMix-Overlays: "Handys aus", Intermission-Timer
- [ ] TMDB-Integration für Filmsuche mit Poster
- [ ] Emby-Integration (Platzhalter ready)
- [ ] Loyalitätspunkte aktivieren
- [ ] IT-Service Modul

---

## Bekannte Offene Punkte

- `gastro.index`, `gastro.orders`, `gastro.menu`, `customers.index`, `infoscreen.admin`
  → Routes vorhanden, Views noch nicht gebaut (Platzhalter)
- Apple Wallet: braucht Apple Developer Zertifikat
- TMDB/Emby: `.env` Keys eintragen wenn ready
- Migration-Konflikt: Laravel-Standard `users`-Migration + eigene — prüfen beim ersten `migrate`

---

## Backlog (während Testphase gesammelt)

### Voting-Audit (Admin)
- [ ] Admin sieht pro Poll: wer hat wann für was gestimmt
- [ ] Änderungshistorie: ursprünglicher Vote + wann geändert + neuer Vote
- [ ] Tabellen-Ansicht in Event-Detail: Spalten = Optionen, Zeilen = Gäste
- [ ] Timestamps der Votes anzeigen (created_at + updated_at auf poll_votes)
- [ ] Export möglich (später)

Technisch: poll_votes hat already created_at/updated_at — Daten sind da,
nur die Admin-UI fehlt noch.
