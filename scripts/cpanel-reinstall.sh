#!/usr/bin/env sh
set -eu

ROOT="$(CDPATH= cd -- "$(dirname -- "$0")/.." && pwd)"
APP="$ROOT/core"

if [ ! -f "$APP/artisan" ] || [ ! -f "$APP/composer.json" ] || [ ! -f "$APP/composer.lock" ]; then
    echo "Code-only reinstall stopped: Laravel source or the locked dependency files are missing." >&2
    exit 1
fi

if [ "${DB_CONNECTION:-}" != "mysql" ]; then
    echo "Code-only reinstall stopped: set DB_CONNECTION=mysql in HostBuddy's build environment." >&2
    exit 1
fi

for required_var in DB_HOST DB_PORT DB_DATABASE DB_USERNAME DB_PASSWORD; do
    eval "required_value=\${$required_var:-}"
    if [ -z "$required_value" ]; then
        echo "Code-only reinstall stopped: required HostBuddy setting $required_var is missing." >&2
        exit 1
    fi
done

if [ "${CPANEL_PERSISTENT_STORAGE_CONFIRMED:-}" != "1" ]; then
    echo "Code-only reinstall stopped: confirm HostBuddy preserves core/storage/app/public across builds." >&2
    echo "Set CPANEL_PERSISTENT_STORAGE_CONFIRMED=1 only after verifying persistent storage for uploaded files." >&2
    exit 1
fi

if [ "${CPANEL_REINSTALL_DRY_RUN:-0}" = "1" ]; then
    echo "Preflight passed. Dry run: no build, cache, storage, or database files were changed."
    exit 0
fi

export CPANEL_CODE_ONLY_REINSTALL=1
exec sh "$ROOT/scripts/cpanel-build.sh"
