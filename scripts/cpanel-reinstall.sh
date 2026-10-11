#!/usr/bin/env sh
set -eu

ROOT="$(CDPATH= cd -- "$(dirname -- "$0")/.." && pwd)"
APP="$ROOT/core"

if [ ! -f "$APP/artisan" ] || [ ! -f "$APP/composer.json" ] || [ ! -f "$APP/composer.lock" ]; then
    echo "Code-only reinstall stopped: Laravel source or the locked dependency files are missing." >&2
    exit 1
fi

if [ "${CPANEL_REINSTALL_DRY_RUN:-0}" = "1" ]; then
    echo "Preflight passed. Dry run: no build, cache, storage, or database files were changed."
    exit 0
fi

echo "Warning: this build does not access MySQL. Ensure HostBuddy preserves or restores core/storage/app/public uploads."
export CPANEL_CODE_ONLY_REINSTALL=1
exec sh "$ROOT/scripts/cpanel-build.sh"
