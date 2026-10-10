---
name: Parse.bot catalog payloads
description: Reliable field mapping and retry behavior for the Parse.bot catalog importer.
---

Category listing responses can already contain product galleries, manufacturer/specification data, stock, and nested current/MSRP prices. Product-detail requests may time out even when category listing requests succeed.

**Why:** The imported listings supplied usable catalog data while detail requests were intermittently unavailable.

**How to apply:** Preserve each raw listing and detail payload in the product snapshot. Extract image URLs from `media_gallery[].sizes[].url`, manufacturers from `moredetails.more_details_text.manufacturer`, and nested prices; enrich descriptions from detail responses when available instead of making the entire listing depend on that endpoint.

When replaying stored snapshots for a catalog of a few thousand products, use `php -d memory_limit=512M artisan parsebot:import-catalog --sync-stored-details`; the default CLI memory limit can be too low.

**Why:** Reprocessing the 2,000+ stored catalog rows exceeded the default 128 MB PHP CLI memory limit.

**How to apply:** Use the higher memory limit for local snapshot-only syncs; this mode does not spend scraper credits or call Parse.bot.
