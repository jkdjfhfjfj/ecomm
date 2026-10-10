---
name: Laravel Preview proxy
description: Proxy-origin handling for Laravel apps served through custom Replit webview workflows.
---

For Laravel behind a custom Replit webview, do not pin `APP_URL` to localhost or assume the workflow inherits `REPLIT_DEV_DOMAIN`. Laravel may otherwise generate redirects to the internal host. Normalize only recognized Replit forwarding hosts and the external HTTPS scheme before Laravel captures the request; leave direct loopback requests local. Preserve any webview path prefix in redirects and HTML URLs, and serve static assets from the app root when that prefix is present.

In path-routed workspaces, a healthy standalone webview workflow does not automatically own the shared development domain. Requests go only to registered artifact service paths. To expose an imported Laravel app at the domain root, register a web artifact with a `/` service path and run the existing PHP startup script on that artifact's assigned `PORT`; do not add a Vite proxy to another local service.

**Why:** The server and database can be healthy while the shared domain shows `Backend Not Configured`, a redirect points at `127.0.0.1`, or prefixed asset requests fall through to the app route. The registered artifact path, workflow origin, and mount path all affect what the browser reaches.

**How to apply:** Check artifact path registration before debugging Laravel or MySQL when the dev domain cannot reach the app. Bind the PHP server to the artifact-assigned `PORT`, normalize trusted forwarded host/scheme/prefix values before Laravel captures the request, and verify redirects and assets through the shared preview route.

When an artifact-assigned port returns HTTP 200 locally but the shared preview returns 502, check whether a failed artifact still owns `/`. A `.replit` mapping to the legacy workflow will not help while that root route is claimed; move the artifact to a distinct subpath before mapping the legacy workflow's port to external port 80.

**Why:** In this workspace, port mapping alone did not fix the preview while the failed storefront artifact still owned `/`. Moving the artifact off root and mapping the healthy legacy workflow on port 5000 made the shared URL return HTTP 200.

**How to apply:** Make sure only one service owns the root route. For the legacy Laravel workflow, move the artifact preview to a subpath, map port 5000 to external port 80, then verify the exact shared URL.

An artifact service must open its own configured `localPort`; a managed workflow that only waits on the main app's already-open port does not satisfy the artifact workflow's startup check.

**Why:** Replit marked the storefront artifact workflow failed when its command waited on port 5000 instead of opening the declared artifact port.

**How to apply:** Keep the artifact's assigned port and its server startup command aligned; don't try to make a second managed service borrow the main workflow's port.
