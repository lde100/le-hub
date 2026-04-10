#!/bin/sh
set -e

# Vite-Build nachholen falls Volume-Mount public/build weggefegt hat
if [ ! -f /var/www/public/build/manifest.json ]; then
    echo ">>> Vite manifest fehlt — baue Assets..."
    cd /var/www
    npm run build
    echo ">>> Assets gebaut."
fi

# Storage-Permissions sicherstellen
chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache 2>/dev/null || true

exec "$@"
