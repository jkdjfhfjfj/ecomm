---
name: Geoapify key priority
description: The storefront's Geoapify credential source and safe administration rule.
---

Use the Replit environment secret as the preferred Geoapify credential. Use the admin-managed database value only when no environment key is configured, and keep that fallback encrypted and masked in the admin UI.

**Why:** the user requested a database fallback while keeping environment-based setup available; the key must not be exposed in rendered settings or logs.

**How to apply:** when changing address autocomplete or reverse-geocoding configuration, preserve environment-first resolution and never prefill or print the credential.
