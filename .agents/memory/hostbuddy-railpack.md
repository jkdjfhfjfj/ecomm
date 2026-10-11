---
name: HostBuddy Railpack builds
description: HostBuddy Git deployments for this storefront use Railpack and install the repository pnpm workspace before custom build commands.
---

For this storefront, HostBuddy's Git deployment builds the repository with Railpack, not only as a conventional cPanel document-root upload. Railpack runs the root pnpm install before the configured custom build command. A custom Composer build command cannot repair a pnpm lock/config mismatch that stops the earlier install. Its PHP image may also omit Composer and required PHP extensions when `composer.json` is nested under `core/`.

**Why:** HostBuddy logs showed pnpm 9.15.9 failing frozen installation because workspace overrides did not match the lockfile, while pnpm 10.26.1 passed the frozen lockfile check locally. Subsequent builds passed pnpm but lacked Composer and the `bcmath`, `gd`, `gmp`, and `zip` extensions.

**How to apply:** Keep the root `packageManager` field aligned with the pnpm version that validates the lockfile. If Composer is absent, download its official installer and verify its published SHA-384 signature before installing dependencies. Install missing extensions with the image's PHP extension tooling; never suppress Composer platform checks for required extensions. After pushing changes, redeploy. Leave Start Command blank for PHP auto-detection unless HostBuddy requires a command; do not set `npm run start` because the root package has no start script.
