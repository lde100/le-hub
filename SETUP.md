# LE-Hub — Setup-Anleitung

## Voraussetzungen
- Docker + Docker Compose
- Git

## Erster Start (lokal)

```bash
git clone https://github.com/lde100/le-hub.git
cd le-hub

# .env anlegen
cp .env.example .env

# Wichtig: APP_URL auf deine lokale IP setzen (für QR-Codes im Heimnetz)
# z.B. APP_URL=http://192.168.1.100:8080
nano .env

# Container bauen + starten
docker compose up -d --build

# App-Key generieren
docker compose exec app php artisan key:generate

# Datenbank einrichten
docker compose exec app php artisan migrate --seed

# Passwort ändern (Standard: change-me-123)
docker compose exec app php artisan tinker
# >>> \App\Models\User::first()->update(['password' => bcrypt('dein-passwort')])
# >>> exit
```

**Login:** http://localhost:8080 → admin@le-hub.local

---

## Cloudflare Tunnel (für externe Gäste)

### Einmalig einrichten:

```bash
# 1. cloudflared installieren
#    https://developers.cloudflare.com/cloudflare-one/connections/connect-networks/downloads/

# 2. Einloggen
cloudflared tunnel login

# 3. Tunnel erstellen
cloudflared tunnel create le-hub

# 4. Token holen (aus Ausgabe oder Cloudflare Dashboard)
#    Dashboard → Zero Trust → Networks → Tunnels → le-hub → Configure → Token

# 5. Token in .env eintragen
echo "CLOUDFLARE_TUNNEL_TOKEN=eyJ..." >> .env

# 6. APP_URL auf deine Domain setzen
# APP_URL=https://kino.lucas-entertainment.de
```

### In Cloudflare Dashboard:
```
Zero Trust → Networks → Tunnels → le-hub → Public Hostname
  Hostname: kino.lucas-entertainment.de
  Service:  http://nginx:80
```

### Tunnel starten:
```bash
docker compose --profile tunnel up -d
```

### Tunnel stoppen:
```bash
docker compose --profile tunnel down cloudflared
# oder einfach:
docker compose down  # stoppt alles
```

---

## Heimnetz-Zugang (ohne Cloudflare, nur lokal)

```bash
# APP_URL auf Heimnetz-IP setzen
APP_URL=http://192.168.1.100:8080

# Dann sind alle QR-Codes auf dem Event-Hub auch im Heimnetz scannbar
```

---

## Täglicher Betrieb

```bash
# Starten
docker compose up -d

# Mit Cloudflare Tunnel
docker compose --profile tunnel up -d

# Stoppen
docker compose down

# Logs
docker compose logs -f app
docker compose logs -f cloudflared

# Nach Code-Updates
docker compose exec app php artisan optimize:clear
```

---

## Erster Kinoabend — Checkliste

1. `docker compose --profile tunnel up -d`
2. `APP_URL` auf externe Domain gesetzt? → QR-Codes stimmen
3. `/events` → Event anlegen → Hub öffnen
4. Einladungs-Link per WhatsApp teilen (📱 Button im Hub)
5. Gäste stimmen ab → Admin bestätigt Termin + Film
6. Sitzplatz-Anfragen bestätigen → Ticket-Links per WhatsApp
7. Am Abend:
   - Beamer-URL im Hub kopieren → in vMix als Browser-Input
   - Screen-2-URL per QR scannen → auf zweitem Monitor öffnen
   - Einlass-Screen auf Checkin-PC öffnen
8. Scannen, Gong, Film!
