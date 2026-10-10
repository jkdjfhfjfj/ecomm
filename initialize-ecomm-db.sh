#!/usr/bin/env bash
set -Eeuo pipefail

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
MYSQL_HOME="$ROOT_DIR/.mysql-data"
MYSQL_SOCKET="$MYSQL_HOME/mysql.sock"
APP_DB=ecomm_replit
APP_DB_USER=ecomm_local
EXPECTED_TABLES=59

if [[ -f "$MYSQL_HOME/ready" ]]; then
  exit 0
fi

if [[ -f "$MYSQL_HOME/importing" ]]; then
  echo "An earlier database import was interrupted. Existing data has been preserved."
  exit 1
fi

printf '%s\n' "Import in progress" >"$MYSQL_HOME/importing"

on_error() {
  status=$?
  printf '%s\n' "Database setup failed. Existing MySQL files were preserved; inspect .mysql-data/import.log." \
    >"$MYSQL_HOME/import-error"
  exit "$status"
}

on_signal() {
  printf '%s\n' "Database setup was interrupted. Existing MySQL files were preserved; inspect .mysql-data/import.log." \
    >"$MYSQL_HOME/import-error"
  exit 1
}

trap on_error ERR
trap on_signal INT TERM HUP

TABLE_COUNT="$(
  mysql --no-defaults --protocol=SOCKET --socket="$MYSQL_SOCKET" --user=root \
    --batch --skip-column-names \
    --execute="SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = '$APP_DB'"
)"

if [[ "$TABLE_COUNT" == "0" ]]; then
  echo "Importing install/database.sql into $APP_DB..."
  mysql --no-defaults --protocol=SOCKET --socket="$MYSQL_SOCKET" --user=root \
    "$APP_DB" <"$ROOT_DIR/install/database.sql"
elif [[ "$TABLE_COUNT" != "$EXPECTED_TABLES" ]]; then
  echo "Found $TABLE_COUNT tables in $APP_DB; expected $EXPECTED_TABLES. No existing data was removed."
  exit 1
else
  echo "$APP_DB already has the expected table count; continuing setup."
fi

TABLE_COUNT="$(
  mysql --no-defaults --protocol=SOCKET --socket="$MYSQL_SOCKET" --user=root \
    --batch --skip-column-names \
    --execute="SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = '$APP_DB'"
)"
if [[ "$TABLE_COUNT" != "$EXPECTED_TABLES" ]]; then
  echo "Import produced $TABLE_COUNT tables; expected $EXPECTED_TABLES."
  exit 1
fi

cd "$ROOT_DIR/core"
php artisan key:generate --force --no-ansi
php artisan config:clear --no-ansi
php artisan package:discover --ansi

touch "$MYSQL_HOME/ready"
rm -f "$MYSQL_HOME/importing" "$MYSQL_HOME/import-error"
echo "Database setup is complete."
