---
name: HostBuddy Railpack builds
description: HostBuddy Git deployments for this storefront use Railpack and install the repository pnpm workspace before custom build commands.
---

For this storefront, HostBuddy's Git deployment builds the repository with Railpack, not only as a conventional cPanel document-root upload. Railpack runs the root pnpm install before the configured custom build command. A custom Composer build command cannot repair a pnpm lock/config mismatch that stops the earlier install.

**Why:** HostBuddy logs showed pnpm 9.15.9 failing frozen installation because workspace overrides did not match the lockfile, while pnpm 10.26.1 passed the frozen lockfile check locally.

**How to apply:** Keep the root `packageManager` field aligned with the pnpm version that validates the lockfile. After pushing that change, redeploy. Leave Start Command blank for PHP auto-detection unless HostBuddy requires a command; do not set `npm run start` because the root package has no start script.
