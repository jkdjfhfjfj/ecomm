---
name: Laravel Preview proxy
description: Proxy-origin handling for Laravel apps served through custom Replit webview workflows.
---

For Laravel behind a custom Replit webview, do not pin `APP_URL` to localhost or assume the workflow inherits `REPLIT_DEV_DOMAIN`. Laravel may otherwise generate redirects to the internal host. Normalize only recognized Replit forwarding hosts and the external HTTPS scheme before Laravel captures the request; leave direct loopback requests local. Preserve any webview path prefix in redirects and HTML URLs, and serve static assets from the app root when that prefix is present.

In path-routed workspaces, a healthy standalone webview workflow does not automatically own the shared development domain. Requests go only to registered artifact service paths. To expose an imported Laravel app at the domain root, register a web artifact with a `/` service path and run the existing PHP startup script on that artifact's assigned `PORT`; do not add a Vite proxy to another local service.

**Why:** The server and database can be healthy while the shared domain shows `Backend Not Configured`, a redirect points at `127.0.0.1`, or prefixed asset requests fall through to the app route. The registered artifact path, workflow origin, and mount path all affect what the browser reaches.

**How to apply:** Check artifact path registration before debugging Laravel or MySQL when the dev domain cannot reach the app. Bind the PHP server to the artifact-assigned `PORT`, normalize trusted forwarded host/scheme/prefix values before Laravel captures the request, and verify redirects and assets through the shared preview route.

When the artifact-assigned port returns HTTP 200 locally but the shared preview returns 502 and the managed artifact workflow fails its health check, treat these as separate hops. Avoid adding `.replit` port mappings or changing the preview path as guesses; restore the intended route and investigate artifact gateway/registration status.

**Why:** In this workspace, Laravel served the storefront successfully on ports 5000 and 25501, while the shared preview still returned 502. Port-map and subpath experiments did not fix the gateway failure.

**How to apply:** Once direct HTTP is verified, stop changing Laravel/DB code. Check managed artifact routing and preview-gateway status, then only restart after a justified route/config change.

An artifact service must open its own configured `localPort`; a managed workflow that only waits on the main app's already-open port does not satisfy the artifact workflow's startup check.

**Why:** Replit marked the storefront artifact workflow failed when its command waited on port 5000 instead of opening the declared artifact port.

**How to apply:** Keep the artifact's assigned port and its server startup command aligned; don't try to make a second managed service borrow the main workflow's port.
