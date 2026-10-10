#!/usr/bin/env bash
set -euo pipefail

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
APP_DIR="$ROOT_DIR/core"
MYSQL_HOME="$ROOT_DIR/.mysql-data"
MYSQL_DATA="$MYSQL_HOME/data"
MYSQL_SOCKET="$MYSQL_HOME/mysql.sock"
MYSQL_SOCKET_LOCK="${MYSQL_SOCKET}.lock"
MYSQL_PID="$MYSQL_HOME/mysql.pid"
MYSQL_LOG="$MYSQL_HOME/mysql.log"
MYSQL_INIT_LOG="$MYSQL_HOME/mysql-init.log"
APP_PORT="${PORT:-5000}"
MYSQL_PORT=3306
APP_DB=ecomm_replit
APP_DB_USER=ecomm_local

if [[ ! "$APP_PORT" =~ ^[0-9]+$ ]]; then
  echo "Invalid application port: $APP_PORT" >&2
  exit 1
fi

cd "$ROOT_DIR"

for command_name in mysqld mysql mysqladmin php flock; do
  if ! command -v "$command_name" >/dev/null 2>&1; then
    echo "Required command not found: $command_name" >&2
    exit 1
  fi
done

MYSQLD="$(command -v mysqld)"
MYSQL_BASE="$(dirname "$(dirname "$(readlink -f "$MYSQLD")")")"
mkdir -p "$MYSQL_HOME"
chmod 700 "$MYSQL_HOME"
exec {MYSQL_STARTUP_LOCK_FD}>"$MYSQL_HOME/startup.lock"
flock "$MYSQL_STARTUP_LOCK_FD"

if [[ ! -d "$MYSQL_DATA/mysql" ]]; then
  mkdir -p "$MYSQL_DATA"
  echo "Initializing the local MySQL data directory..."
  "$MYSQLD" --no-defaults \
    --basedir="$MYSQL_BASE" \
    --datadir="$MYSQL_DATA" \
    --initialize-insecure \
    --user="$(id -un)" \
    >"$MYSQL_INIT_LOG" 2>&1 || {
      tail -60 "$MYSQL_INIT_LOG" >&2 || true
      exit 1
    }
fi

mysql_is_ready() {
  mysqladmin --no-defaults --socket="$MYSQL_SOCKET" --user=root ping --silent \
    >/dev/null 2>&1
}

if ! mysql_is_ready; then
  if [[ -f "$MYSQL_PID" ]] && kill -0 "$(cat "$MYSQL_PID")" 2>/dev/null; then
    echo "Waiting for the existing local MySQL process..."
  else
    rm -f "$MYSQL_SOCKET" "$MYSQL_PID"
    if [[ -f "$MYSQL_SOCKET_LOCK" ]]; then
      SOCKET_LOCK_PID="$(cat "$MYSQL_SOCKET_LOCK" 2>/dev/null || true)"
      SOCKET_LOCK_CMD=""
      if [[ "$SOCKET_LOCK_PID" =~ ^[0-9]+$ && -r "/proc/$SOCKET_LOCK_PID/cmdline" ]]; then
        SOCKET_LOCK_CMD="$(tr '\0' ' ' <"/proc/$SOCKET_LOCK_PID/cmdline" 2>/dev/null || true)"
      fi
      if [[ "$SOCKET_LOCK_CMD" == *mysqld* && "$SOCKET_LOCK_CMD" == *"$MYSQL_SOCKET"* ]]; then
        echo "MySQL socket lock is owned by live server process $SOCKET_LOCK_PID; refusing to remove it." >&2
        exit 1
      fi
      rm -f "$MYSQL_SOCKET_LOCK"
    fi
    echo "Starting local MySQL on 127.0.0.1:$MYSQL_PORT..."
    nohup "$MYSQLD" --no-defaults \
      --basedir="$MYSQL_BASE" \
      --datadir="$MYSQL_DATA" \
      --socket="$MYSQL_SOCKET" \
      --pid-file="$MYSQL_PID" \
      --port="$MYSQL_PORT" \
      --bind-address=127.0.0.1 \
      --mysqlx=0 \
      --user="$(id -un)" \
      --log-error="$MYSQL_LOG" \
      </dev/null >/dev/null 2>&1 &
  fi

  ready=0
  for _ in $(seq 1 60); do
    if mysql_is_ready; then
      ready=1
      break
    fi
    if [[ -f "$MYSQL_PID" ]] && ! kill -0 "$(cat "$MYSQL_PID")" 2>/dev/null; then
      break
    fi
    sleep 1
  done
  if [[ "$ready" != "1" ]]; then
    echo "MySQL did not start. Recent server output:" >&2
    tail -80 "$MYSQL_LOG" >&2 2>/dev/null || tail -80 "$MYSQL_INIT_LOG" >&2 || true
    exit 1
  fi
fi

mysql --no-defaults --protocol=SOCKET --socket="$MYSQL_SOCKET" --user=root <<SQL
CREATE DATABASE IF NOT EXISTS \`$APP_DB\`
  CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER IF NOT EXISTS '$APP_DB_USER'@'localhost' IDENTIFIED BY '';
CREATE USER IF NOT EXISTS '$APP_DB_USER'@'127.0.0.1' IDENTIFIED BY '';
ALTER USER '$APP_DB_USER'@'localhost' IDENTIFIED BY '';
ALTER USER '$APP_DB_USER'@'127.0.0.1' IDENTIFIED BY '';
GRANT ALL PRIVILEGES ON \`$APP_DB\`.* TO '$APP_DB_USER'@'localhost';
GRANT ALL PRIVILEGES ON \`$APP_DB\`.* TO '$APP_DB_USER'@'127.0.0.1';
CREATE DATABASE IF NOT EXISTS \`ecomm_install\`
  CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER IF NOT EXISTS 'ecomm_installer'@'127.0.0.1' IDENTIFIED BY '';
GRANT ALL PRIVILEGES ON \`ecomm_install\`.* TO 'ecomm_installer'@'127.0.0.1';
FLUSH PRIVILEGES;
SQL

if [[ -n "${ECOMM_INSTALL_DB_PASSWORD:-}" && ! -f "$MYSQL_HOME/ecomm-installer-password-configured" ]]; then
  MYSQL_SOCKET="$MYSQL_SOCKET" php "$ROOT_DIR/scripts/configure-ecomm-install-db-password.php"
fi

if [[ -n "${ECOMM_INSTALL_DB_PASSWORD:-}" ]]; then
  export DB_PASSWORD="$ECOMM_INSTALL_DB_PASSWORD"
fi

if [[ ! -f "$APP_DIR/.env" ]]; then
  cat >"$APP_DIR/.env" <<'ENV'
APP_NAME=Ecomm
APP_ENV=local
APP_KEY=
APP_DEBUG=false
APP_URL=
LOG_CHANNEL=stack
LOG_LEVEL=debug
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=ecomm_replit
DB_USERNAME=ecomm_local
DB_PASSWORD=
CACHE_STORE=file
SESSION_DRIVER=file
QUEUE_CONNECTION=sync
MAIL_MAILER=log
ENV
fi

# The router sets APP_URL from the current Preview request so generated redirects
# use the host and scheme the browser actually visited.
unset APP_URL
export APP_ENV=local
export APP_DEBUG=false

# A stale config cache can keep the installer password unset and the old
# environment name after Replit Secrets or the local .env has changed.
(
  cd "$APP_DIR"
  php artisan config:clear --no-ansi
)

if [[ ! -f "$MYSQL_HOME/ready" ]]; then
  if [[ -f "$MYSQL_HOME/import-error" || -f "$MYSQL_HOME/importing" ]]; then
    if [[ -f "$MYSQL_HOME/import.pid" ]] && kill -0 "$(cat "$MYSQL_HOME/import.pid")" 2>/dev/null; then
      echo "MySQL schema import is still running."
    else
      printf '%s\n' "A previous schema import did not finish. Database files were preserved; see .mysql-data/import.log." \
        >"$MYSQL_HOME/import-error"
      echo "Database setup needs attention; existing MySQL data was preserved."
    fi
  elif [[ -f "$MYSQL_HOME/import.pid" ]] && kill -0 "$(cat "$MYSQL_HOME/import.pid")" 2>/dev/null; then
    echo "MySQL schema import is still running."
  else
    rm -f "$MYSQL_HOME/import-error"
    nohup bash "$ROOT_DIR/initialize-ecomm-db.sh" \
      >>"$MYSQL_HOME/import.log" 2>&1 </dev/null &
    echo "$!" >"$MYSQL_HOME/import.pid"
    echo "Importing the app database in the background."
  fi
fi

flock -u "$MYSQL_STARTUP_LOCK_FD"
exec {MYSQL_STARTUP_LOCK_FD}>&-

echo "E-commerce app is listening on 0.0.0.0:$APP_PORT"
exec php -S "0.0.0.0:$APP_PORT" -t "$ROOT_DIR" "$ROOT_DIR/replit-dev-router.php"
