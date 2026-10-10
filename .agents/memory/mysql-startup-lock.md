---
name: MySQL socket lock ownership
description: Avoid treating a reused process ID as proof that the local MySQL socket lock is still owned by mysqld.
---

When recovering the local MySQL socket lock, check that the referenced PID is actually a `mysqld` process using the expected socket. A live PID alone is not sufficient: Linux can reuse a stale PID for an unrelated process, which can block the database startup indefinitely.

**Why:** The storefront returned 502 because a stale MySQL lock referred to a live Node/Vite process after PID reuse.

**How to apply:** Any startup script that decides whether to remove `.mysql-data/mysql.sock.lock` should verify the process command line and socket path before preserving the lock.

Avoid restarting the two storefront workflows while a Parse.bot catalog import is writing to MySQL; the restart can interrupt the importer with a connection-refused error.

**Why:** Restarting the storefront during a long listing import caused the importer to fail while attaching category links.

**How to apply:** Let catalog imports finish before restarting either workflow that runs `start-ecomm.sh`, then confirm MySQL is stable before resuming imports.
