# Ecomm Storefront

Laravel 11 e-commerce application imported from `jkdjfhfjfj/ecomm`. The repository files are at the Project root alongside the existing Replit workspace services.

## Run & Operate

- Start the **Ecomm Storefront** workflow to launch the PHP app and its local MySQL server.
- The workflow runs `bash ./start-ecomm.sh` and serves the app on port `5000`.
- Local MySQL listens only on `127.0.0.1:3306`.
- Development database: `ecomm_replit`; username: `ecomm_local`; password: blank.
- MySQL files persist in `.mysql-data/` and are ignored by Git. The seed import runs once in the background; existing MySQL data is never dropped.
- Laravel's local environment and generated application key live in ignored `core/.env`.
- Existing pnpm workspace checks remain available with `pnpm run typecheck` and `pnpm run build`.

## Stack

- E-commerce app: PHP 8.4, Laravel 11, MySQL 8.0.
- Existing workspace services: pnpm, Node.js 24, TypeScript, Express, PostgreSQL, and Drizzle ORM.

## Where Things Live

- `index.php` — application front controller.
- `core/` — Laravel application, routes, configuration, and dependencies.
- `assets/` — public store assets.
- `install/database.sql` — imported local MySQL schema and seed data.
- `start-ecomm.sh` — starts MySQL and PHP, then launches the one-time database setup.
- `initialize-ecomm-db.sh` — imports the seed schema and completes Laravel setup.
- `replit-dev-router.php` — serves public assets and routes app requests while blocking private project files.

## Gotchas

- The source app's license activation check is retained; it has not been bypassed.
- The blank-password MySQL account is restricted to the local development database and is not suitable for production. Use separately managed production database credentials if deploying.
- Do not delete `.mysql-data/` unless you intend to remove the local database.
