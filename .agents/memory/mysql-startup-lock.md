---
name: MySQL socket lock ownership
description: Avoid treating a reused process ID as proof that the local MySQL socket lock is still owned by mysqld.
---

When recovering local MySQL startup, validate both the PID file and socket-lock PID against the actual `mysqld` command line and expected socket. A live PID alone is not sufficient: Linux can reuse a stale PID for an unrelated process, which can block database startup indefinitely.

**Why:** The storefront returned 502 because a stale MySQL lock referred to a live Node/Vite process after PID reuse.

**How to apply:** Startup checks for `.mysql-data/mysql.pid` and `.mysql-data/mysql.sock.lock` should verify the process command line and socket path before treating either PID as a live server.

Avoid restarting the two storefront workflows while a Parse.bot catalog import is writing to MySQL; the restart can interrupt the importer with a connection-refused error.

**Why:** Restarting the storefront during a long listing import caused the importer to fail while attaching category links.

**How to apply:** Let catalog imports finish before restarting either workflow that runs `start-ecomm.sh`, then confirm MySQL is stable before resuming imports.

The Replit preview uses the preserved local MySQL snapshot by default. The cPanel database connection was refused from this workspace; opt into the remote preview only with `REPLIT_USE_REMOTE_DATABASE=1` after the host permits the connection. Local schema repair migrations must stay local unless production migration is explicitly requested.

**Why:** Keeping the preview on the local snapshot restores a usable storefront without changing the production database while its network access is blocked.

**How to apply:** Use the local preview for UI verification; separately resolve cPanel remote access and apply schema changes under the explicit production migration process.
