#!/usr/bin/env sh
set -eu

ROOT="$(CDPATH= cd -- "$(dirname -- "$0")/.." && pwd)"
APP="$ROOT/core"

if [ "$(php -r 'echo PHP_VERSION_ID;')" -lt 80300 ]; then
    echo "PHP 8.3 or newer is required." >&2
    exit 1
fi

if ! command -v composer >/dev/null 2>&1; then
    echo "Composer must be available to the HostBuddy build environment." >&2
    exit 1
fi

cd "$APP"
composer install --no-dev --prefer-dist --no-interaction --optimize-autoloader

mkdir -p \
    "$APP/storage/framework/cache/data" \
    "$APP/storage/framework/sessions" \
    "$APP/storage/framework/views" \
    "$APP/storage/logs" \
    "$APP/bootstrap/cache" \
    "$APP/public/assets"

if [ -d "$ROOT/assets" ]; then
    cp -R "$ROOT/assets/." "$APP/public/assets/"
fi

if [ ! -e "$APP/public/storage" ] && [ ! -L "$APP/public/storage" ]; then
    ln -s ../storage/app/public "$APP/public/storage"
fi

chmod -R u+rwX "$APP/storage" "$APP/bootstrap/cache"

echo "cPanel build prepared. Set the domain document root to core/public."
