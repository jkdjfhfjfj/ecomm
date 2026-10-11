#!/usr/bin/env sh
set -eu

ROOT="$(CDPATH= cd -- "$(dirname -- "$0")/.." && pwd)"
APP="$ROOT/core"

if [ "$(php -r 'echo PHP_VERSION_ID;')" -lt 80300 ]; then
    echo "PHP 8.3 or newer is required." >&2
    exit 1
fi

cd "$APP"

MISSING_EXTENSIONS=0
for extension in bcmath gd gmp zip; do
    if ! php -m | grep -Eiq "^${extension}$"; then
        MISSING_EXTENSIONS=1
    fi
done

if [ "$MISSING_EXTENSIONS" -eq 1 ]; then
    if command -v install-php-extensions >/dev/null 2>&1; then
        install-php-extensions bcmath gd gmp zip
    elif command -v docker-php-ext-install >/dev/null 2>&1 && \
         command -v docker-php-ext-configure >/dev/null 2>&1 && \
         command -v apt-get >/dev/null 2>&1; then
        apt-get update
        apt-get install -y --no-install-recommends \
            libfreetype6-dev \
            libgmp-dev \
            libjpeg62-turbo-dev \
            libzip-dev \
            libpng-dev \
            libwebp-dev
        docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp
        docker-php-ext-install -j"$(nproc)" bcmath gd gmp zip
        rm -rf /var/lib/apt/lists/*
    else
        echo "PHP extensions bcmath, gd, gmp, and zip are required; HostBuddy's PHP image cannot install them from this build script." >&2
        exit 1
    fi
fi

for extension in bcmath gd gmp zip; do
    if ! php -m | grep -Eiq "^${extension}$"; then
        echo "Required PHP extension ${extension} is still unavailable after installation." >&2
        exit 1
    fi
done

if command -v composer >/dev/null 2>&1; then
    if [ "${CPANEL_CODE_ONLY_REINSTALL:-0}" = "1" ]; then
        composer install --no-dev --prefer-dist --no-interaction --optimize-autoloader --no-scripts
    else
        composer install --no-dev --prefer-dist --no-interaction --optimize-autoloader
    fi
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
    if [ "${CPANEL_CODE_ONLY_REINSTALL:-0}" = "1" ]; then
        php "$COMPOSER_PHAR" install --no-dev --prefer-dist --no-interaction --optimize-autoloader --no-scripts
    else
        php "$COMPOSER_PHAR" install --no-dev --prefer-dist --no-interaction --optimize-autoloader
    fi
fi

if [ "${CPANEL_CODE_ONLY_REINSTALL:-0}" = "1" ]; then
    echo "Code-only reinstall: database operations and application boot scripts are skipped."
else
    php artisan optimize:clear
    php artisan migrate --force --no-interaction
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

if [ "${CPANEL_CODE_ONLY_REINSTALL:-0}" = "1" ]; then
    # Clear only generated filesystem caches; do not boot Laravel or flush the
    # application cache store, which may itself be backed by MySQL.
    find "$APP/bootstrap/cache" -maxdepth 1 -type f \
        \( -name 'config.php' -o -name 'routes-*.php' \) -delete
    find "$APP/storage/framework/views" -maxdepth 1 -type f -name '*.php' -delete
    echo "Code-only reinstall complete: migrations, seeders, imports, and application cache were left untouched."
fi

echo "Laravel production dependencies and public assets are prepared."
