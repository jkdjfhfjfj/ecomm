#!/usr/bin/env sh
set -eu

ROOT="$(CDPATH= cd -- "$(dirname -- "$0")/.." && pwd)"
APP="$ROOT/core"

if [ "$(php -r 'echo PHP_VERSION_ID;')" -lt 80300 ]; then
    echo "PHP 8.3 or newer is required." >&2
    exit 1
fi

cd "$APP"

if command -v composer >/dev/null 2>&1; then
    composer install --no-dev --prefer-dist --no-interaction --optimize-autoloader
else
    COMPOSER_TMP="$(mktemp -d)"
    trap 'rm -rf "$COMPOSER_TMP"' EXIT HUP INT TERM
    COMPOSER_INSTALLER="$COMPOSER_TMP/installer.php"
    COMPOSER_PHAR="$COMPOSER_TMP/composer.phar"

    php -r '
        $signature = file_get_contents("https://composer.github.io/installer.sig");
        $installer = file_get_contents("https://getcomposer.org/installer");
        if ($signature === false || $installer === false ||
            !hash_equals(trim($signature), hash("sha384", $installer))) {
            fwrite(STDERR, "Composer installer download or signature verification failed.\n");
            exit(1);
        }
        if (file_put_contents($argv[1], $installer) === false) {
            fwrite(STDERR, "Could not save the verified Composer installer.\n");
            exit(1);
        }
    ' "$COMPOSER_INSTALLER"

    php "$COMPOSER_INSTALLER" --quiet --install-dir="$COMPOSER_TMP" --filename=composer.phar
    php "$COMPOSER_PHAR" install --no-dev --prefer-dist --no-interaction --optimize-autoloader
fi

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

echo "Laravel production dependencies and public assets are prepared."
