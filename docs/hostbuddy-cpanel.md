# HostBuddy cPanel deployment

This guide targets the Laravel storefront in `core/`, not the separate Vite preview artifact.

## Build and document root

Configure the Git builder to run this from the repository root:

```sh
sh scripts/cpanel-build.sh
```

The build environment must provide PHP 8.3+, Composer, and the PHP extensions required by `core/composer.lock`. Set the domain's document root to the repository's `core/public` directory. Do not expose the repository root as the public document root.

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
