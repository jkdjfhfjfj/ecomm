---
name: External API key priority
description: Environment keys remain authoritative; encrypted admin database values are fallbacks for Geoapify and Parse.bot.
---

Use the deployment environment secret as the authoritative credential for external APIs such as Geoapify and Parse.bot. Use an admin-managed database value only when no environment key is configured, and keep that fallback encrypted and masked in the admin UI.

**Why:** the user needs credential rotation without exposing API keys in rendered settings or logs, while deployment-managed secrets remain the source of truth.

**How to apply:** preserve environment-first resolution for Parse.bot imports and address autocomplete/reverse geocoding. The admin UI may save a fallback but must never prefill or print it.
