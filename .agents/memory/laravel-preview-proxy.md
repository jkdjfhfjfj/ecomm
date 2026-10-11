---
name: Laravel Preview proxy
description: Proxy-origin handling for Laravel apps served through custom Replit webview workflows.
---

For Laravel behind a custom Replit webview, do not pin `APP_URL` to localhost or assume the workflow inherits `REPLIT_DEV_DOMAIN`. Laravel may otherwise generate redirects to the internal host. Normalize only recognized Replit forwarding hosts and the external HTTPS scheme before Laravel captures the request; leave direct loopback requests local. Preserve any webview path prefix in redirects and HTML URLs, and serve static assets from the app root when that prefix is present.

In this path-routed workspace, the shared development domain root must belong to a registered artifact route; a healthy standalone `Ecomm Storefront` workflow on port 5000 alone returns 404 at the shared URL if the artifact is moved to another path. The legacy Laravel app can be exposed at `/` by a web artifact service on its assigned port that proxies to the running legacy workflow on 5000.

**Why:** Moving the artifact from `/` to `/storefront-artifact` produced a shared-domain 404 even though the legacy app returned 200 on port 5000. Running a second Laravel process on the artifact port returned 500 because it lacked the installer DB password; proxying requests to the already-running legacy process returned the real storefront.

**How to apply:** Keep one artifact service registered at `/`, bind its proxy to injected `PORT`, and forward the original request path and headers to port 5000. Verify the exact shared URL and logo asset, not just localhost.

An artifact service must open its own configured `localPort`; a managed workflow that only waits on the main app's already-open port does not satisfy the artifact workflow's startup check.

**Why:** Replit marked the storefront artifact workflow failed when its command waited on port 5000 instead of opening the declared artifact port. Even after a proxy listened on the assigned port and the exact public URL returned 200, the managed restart reported a failed preview health check.

**How to apply:** Keep the proxy's bind port aligned with the artifact's declared port. If it listens on `0.0.0.0` and the exact URL works but the managed health check still fails, stop restarting and report the discrepancy rather than looping.
