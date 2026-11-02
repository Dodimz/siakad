#!/bin/bash
set -e

if [ -z "$APP_KEY" ]; then
    echo "ERROR: APP_KEY is empty. Isi dari k8s Secret dulu." >&2
    exit 1
fi

chown -R www-data:www-data storage bootstrap/cache 2>/dev/null || true
chmod -R 775 storage bootstrap/cache 2>/dev/null || true
mkdir -p storage/app/public storage/app/private 2>/dev/null || true
php artisan storage:link 2>/dev/null || true

if [ "${RUN_MIGRATIONS:-false}" = "true" ]; then
    echo "Waiting for database ${DB_HOST}:${DB_PORT}..."
    for i in $(seq 1 30); do
        if php artisan db:show --no-ansi >/dev/null 2>&1; then
            echo "Database reachable."
            break
        fi
        echo "Retry $i/30..."
        sleep 2
        if [ "$i" = "30" ]; then
            echo "ERROR: database tidak terjangkau." >&2
            exit 1
        fi
    done
    php artisan migrate --force --no-ansi
fi

php artisan optimize --no-ansi 2>/dev/null || php artisan config:cache --no-ansi 2>/dev/null || true

exec "$@"
