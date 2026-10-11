# HostBuddy cPanel deployment

This guide targets the Laravel storefront in `core/`, not the separate Vite preview artifact. The supplied HostBuddy Git deployment log shows a Railpack/Docker deployment using FrankenPHP, rather than a conventional shared-cPanel Apache deployment.

## Build and document root

Configure the Git builder to run this from the repository root:

```sh
sh scripts/cpanel-build.sh
```

The build environment must provide PHP 8.3+. The build script checks for the required `bcmath`, `gd`, and `gmp` extensions and attempts to install them using the PHP image's extension installer (or the official PHP Docker helpers plus their system libraries). If Composer is absent, the script downloads its official installer and verifies its published SHA-384 signature before installing production dependencies. Railpack installs the root pnpm workspace before it runs this custom build command, so `package.json` pins the package-manager version used to validate `pnpm-lock.yaml`.

For the Railpack deployment shown in the logs, leave Start Command blank to use PHP auto-detection unless HostBuddy specifically requires a custom command. The root package has no `npm run start` script. Ensure the deployed PHP server serves Laravel's `core/public` directory; do not expose the repository root. If using HostBuddy's separate classic cPanel/Apache hosting instead, set that domain's document root to `core/public`.

The build script installs production Composer dependencies, copies the tracked storefront assets into `core/public/assets`, and prepares Laravel's storage link and writable directories. It does not run database migrations or alter the database.

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
