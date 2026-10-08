#!/bin/sh
set -e

cd /var/www/html

log() { echo "[entrypoint] $*"; }

if [ ! -f .env ]; then
    cp .env.example .env
    log ".env dibuat dari .env.example"
fi

if [ ! -f vendor/autoload.php ] || [ composer.lock -nt vendor/autoload.php ]; then
    log "composer install ..."
    composer install --no-interaction --prefer-dist --optimize-autoloader
fi

if [ ! -d node_modules ] || [ ! -f node_modules/.package-lock.json ] \
    || [ package-lock.json -nt node_modules/.package-lock.json ]; then
    log "npm ci ..."
    npm ci --no-interaction
fi

input_stamp() {
    cat package.json package-lock.json vite.config.js 2>/dev/null
    find resources -type f -print0 2>/dev/null | sort -z | xargs -0 cat 2>/dev/null
}

STAMP=$(input_stamp | md5sum | cut -d' ' -f1)
if [ ! -f public/build/manifest.json ] || [ ! -f public/build/.stamp ] \
    || [ "$(cat public/build/.stamp)" != "$STAMP" ]; then
    log "npm run build ..."
    if npm run build; then
        printf '%s' "$STAMP" > public/build/.stamp
    else
        log "WARNING: npm run build gagal, aset lama tetap dipakai"
    fi
fi

mkdir -p storage/app/public \
         storage/framework/cache/data \
         storage/framework/sessions \
         storage/framework/testing \
         storage/framework/views \
         storage/logs \
         bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache 2>/dev/null || true
chmod -R ug+rwX storage bootstrap/cache || true

if [ "${DB_CONNECTION:-mysql}" = "mysql" ] && [ -n "${DB_HOST:-}" ]; then
    log "menunggu MySQL di ${DB_HOST}:${DB_PORT:-3306} ..."
    attempts=0
    until php docker/wait-db.php; do
        attempts=$((attempts + 1))
        if [ "$attempts" -ge 60 ]; then
            echo "[entrypoint] ERROR: MySQL tidak siap setelah 120 detik" >&2
            exit 1
        fi
        sleep 2
    done
    log "MySQL siap"

    log "impor backup database (jika ada)"
    php docker/import-backup.php
fi

php artisan storage:link >/dev/null 2>&1 || true
php artisan package:discover --ansi || true

if [ "${AUTO_MIGRATE:-true}" = "true" ]; then
    log "php artisan migrate --force"
    php artisan migrate --force
fi

log "menjalankan: $*"
exec "$@"
