# LE-HUB — DEV_STATUS

**Stand:** 2026-04-10
**Stack:** Laravel 11 + Livewire 4 + MariaDB 11 + Docker + Vite/Tailwind + DomPDF + Reverb

---

## Architektur

```
le-hub/
├── app/
│   ├── Livewire/
│   │   ├── Cinema/     SeatMap, TicketScanner, CheckinScreen
│   │   ├── Event/      PublicEventPage
│   │   └── Infoscreen/ Screen
│   ├── Models/         (siehe unten)
│   └── Services/
│       ├── TicketPdfService      Retro-PDF, SVG-Saalplan
│       └── AppleWalletService    PKPass (braucht Apple Dev Cert)
├── config/wallet.php
├── resources/views/
│   ├── layouts/
│   │   ├── public.blade.php      Mobile LE-Branding
│   │   ├── infoscreen.blade.php  Kiosk-Mode
│   │   └── checkin.blade.php     Einlass-Screen + Web Audio
│   ├── tickets/pdf.blade.php     Retro-Kinokarte (DIN A5 quer)
│   └── livewire/
│       ├── cinema/    seat-map, ticket-scanner, checkin-screen
│       ├── event/     public-page
│       └── infoscreen/screen
└── routes/web.php
```

---

## Ticket-Flow (vollständig geplant, teilweise gebaut)

```
Event erstellen (Admin)
  ↓
Termin-Abstimmung → Film-Voting → Sitzplatz-Anfrage
  ↓ Admin bestätigt Sitze
Ticket generieren → ticket_code = LExxxxxxxxxx
  ↓
Gast bekommt:
  ├── PDF-Link:    /ticket/{code}/pdf     → Retro-Kinokarte (druckbar)
  └── Wallet-Link: /ticket/{code}/wallet  → Apple Wallet .pkpass

Einlass-Screen: /cinema/checkin/{screening}
  ├── Barcode/QR scannen → Livewire handleScan()
  ├── Welcome-Overlay (4 Sek, animiert, Name + Platz)
  ├── Sitzplan live aktualisiert (grün = eingecheckt, gold = gerade gescannt)
  ├── Theater-Glocke (Web Audio API, A5-Akkord)
  └── Fortschritts-Bar + "Alle da — Film kann starten!" wenn 100%
```

---

## Modul-Status

### 🎬 Cinema + Einlass
- [x] DB, Models, Seeder
- [x] SeatMap Livewire
- [x] TicketScanner (HID + iPhone Kamera)
- [x] **CheckinScreen** — Saalplan live, Welcome-Overlay, Theater-Glocke, Fortschritt
- [x] **Ticket-PDF** — Retro-Kinokarte DIN A5 quer, SVG-Saalplan mit Sitz-Markierung
- [x] **Apple Wallet** — vollständig implementiert, braucht Apple Dev Zertifikat
- [ ] Buchungsflow: Admin Screening anlegen → Tickets generieren
- [ ] Ticket-Link per E-Mail an Gäste schicken

### 🎟️ Event / Voting
- [x] Komplettes DB-Schema + Models
- [x] PublicEventPage (Abstimmung, Film-Voting, Sitzplatz-Anfrage)
- [ ] Admin: Event anlegen, Polls steuern, Anfragen bestätigen

### 📺 Infoscreen
- [x] Screen-Komponente, multi-channel
- [ ] "Jetzt auf Platz"-Overlay bei Einlass (braucht Reverb/Broadcasting)
- [ ] Countdown "Film beginnt in X Minuten"
- [ ] Post-Event Memory-Seite

### 🛒 Gastro / POS
- [x] DB + Models vollständig
- [ ] Livewire UI

### 🔒 Auth
- [ ] Login-Controller + App-Shell

---

## Apple Wallet Setup (einmalig)

1. Apple Developer Account aktiv?
2. Neuen Pass Type Identifier anlegen: `pass.de.lucas-entertainment.ticket`
3. Zertifikat exportieren als `.p12`, dann:
   ```bash
   openssl pkcs12 -in cert.p12 -clcerts -nokeys -out storage/app/wallet/pass.crt
   openssl pkcs12 -in cert.p12 -nocerts -nodes -out storage/app/wallet/pass.key
   ```
4. WWDR G4 von Apple laden → `storage/app/wallet/AppleWWDRCA.pem`
5. In `.env` eintragen

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

## Routen

| URL                          | Auth | Beschreibung                       |
|------------------------------|------|------------------------------------|
| /event/{token}               | Nein | Event-Seite (WhatsApp-Link)        |
| /screen, /screen/{channel}   | Nein | Infoscreen                         |
| /ticket/{code}/pdf           | Nein | Ticket-PDF Download                |
| /ticket/{code}/wallet        | Nein | Apple Wallet .pkpass               |
| /cinema/checkin/{screening}  | Ja   | Einlass-Screen mit Saalplan        |

## Design
Farben: #0D0D0D (Hintergrund) · #C9A84C (Gold/Akzent) · #F5F5F5 (Text)
Font: System-UI + Courier (Retro-Ticket)
