# HostBuddy cPanel deployment

This guide targets the Laravel storefront in `core/`, not the separate Vite preview artifact. The supplied HostBuddy Git deployment log shows a Railpack/Docker deployment using FrankenPHP, rather than a conventional shared-cPanel Apache deployment.

## Build and document root

For a normal Git deployment that applies pending Laravel migrations, configure the Git builder to run this from the repository root:

```sh
sh scripts/cpanel-build.sh
```

The build environment must provide PHP 8.3+. The build script checks for the required `bcmath`, `gd`, `gmp`, and `zip` extensions and attempts to install them using the PHP image's extension installer (or the official PHP Docker helpers plus their system libraries). If Composer is absent, the script downloads its official installer and verifies its published SHA-384 signature before installing production dependencies. Railpack installs the root pnpm workspace before it runs this custom build command, so `package.json` pins the package-manager version used to validate `pnpm-lock.yaml`.

For the Railpack deployment shown in the logs, leave Start Command blank to use PHP auto-detection unless HostBuddy specifically requires a custom command. The root package has no `npm run start` script. Ensure the deployed PHP server serves Laravel's `core/public` directory; do not expose the repository root. If using HostBuddy's separate classic cPanel/Apache hosting instead, set that domain's document root to `core/public`.

The normal build script installs the production dependencies pinned by `core/composer.lock`, copies the tracked storefront assets into `core/public/assets`, prepares Laravel's storage link and writable directories, clears caches, and applies pending migrations. It does not reset or seed the database.

## Code-only reinstall (keep the existing database)

Use this command when you need to rebuild the application code without changing any database tables or records:

```sh
sh scripts/cpanel-reinstall.sh
```

The code-only command checks that the Laravel source and Composer lockfile are present. It does not need database credentials at build time and does not connect to MySQL. It installs the locked production dependencies without running Composer scripts, copies tracked storefront assets, and clears only generated config, route, and compiled-view files. It skips Laravel boot commands, migrations, seeders, imports, resets, and application-cache clearing; it does not read or write database records.

Keep the existing database settings in HostBuddy's private runtime environment (or in `core/.env`); the running application still needs them to connect to the database. The build command does not create, overwrite, or delete `.env` or files under `core/storage/app/public`. It does not change license/activation source files or behavior. Composer dependencies are regenerated from the committed lockfile. `core/vendor` is generated and ignored by Git, so manual edits made only inside that directory are not included in a Git reinstall.

This project redirects to `/install` if `core/.env` is missing and its `SystemInstalled` cache flag is absent. Restore the non-empty production `core/.env` from a secure backup before starting the app; it is gitignored and is not recreated by this build. Do not run the installer against an existing database.

The build script itself does not delete `core/storage/app/public`, but HostBuddy may replace the deployment directory. Back up that directory or configure persistent storage before deploying if local uploads must be retained. Product image URLs stored in the existing database are unaffected by this code-only build.

For a no-change test of the preflight, set `CPANEL_REINSTALL_DRY_RUN=1`; the script checks the source files and exits before installing dependencies or clearing caches. After a real build, verify the app connects to the same database and existing uploads remain available. Do not use `migrate:fresh`, `db:wipe`, or database seed commands for a code-only reinstall.

## Database copy

The configured workspace database is `ecomm_install`. The current copy contains 5,737 products, 1 user, no orders, and 28,672 reviews. The reviews are copied with their current approved status, as requested.

1. In cPanel, create a MySQL database and a database user; grant that user privileges on the database.
2. Import `generated-artifacts/ecomm_install-cpanel.sql.gz` into the new database using phpMyAdmin.
3. Enter the cPanel database host, database name, username, and password in the application's server-side environment configuration. Do not commit these values.

The database export is deliberately stored outside Git and deployment bundles. Delete the temporary cPanel copy after importing it.

## Production environment

Set these values in HostBuddy's server-side environment settings, or in `core/.env` if their builder documents that method:

```dotenv
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-domain.example
DB_CONNECTION=mysql
DB_HOST=your-cpanel-mysql-host
DB_PORT=3306
DB_DATABASE=your_cpanel_database
DB_USERNAME=your_cpanel_database_user
DB_PASSWORD=your_cpanel_database_password
```

Preserve the existing `APP_KEY` from the current private environment because database settings may be encrypted with it. Never put `.env` or credentials in Git. Add any required service keys through HostBuddy's private environment settings.

Enable HTTPS before accepting customer logins or orders. Verify that `core/storage` and `core/bootstrap/cache` are writable by the PHP process. Take a backup of the cPanel database before future imports or schema changes.

## Current limitation

The exact HostBuddy builder configuration format has not been confirmed. If it cannot run a custom command, ask HostBuddy how to configure Composer for a repository whose `composer.json` is in `core/`; the script above is the required build command.
