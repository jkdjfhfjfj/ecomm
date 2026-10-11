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

Treat Parse.bot HTTP 429 as a hard pause for the current import attempt; do not immediately repeat the request. Resume listing pagination from the stored per-category page checkpoint once the provider accepts requests again.

**Why:** A catalog request returned HTTP 429 during a resumed run, and repeated attempts would not recover service availability or help preserve credits.

The Parse.bot key has a 250-request daily free-credit allowance.

**Why:** The user confirmed the current Parse.bot allowance is 250 free requests per day.

**How to apply:** Keep listing and detail calls combined under 250 attempts per local day; account for failed attempts too. Stop after 429 or repeated timeouts, inspect the latest importer result, and resume from saved category checkpoints once the provider responds successfully.

Never build product descriptions from specification or merchandising facts. Store source descriptions only in the description field; keep returned specifications in their own field and map structured scalar facts to product attributes. Leave unavailable source fields empty rather than generating substitute copy.

**Why:** The earlier importer repeated manufacturer, department, and subtype facts as product descriptions and extra descriptions, so the catalog showed specifications as prose.

**How to apply:** When correcting imports, compare existing fields with saved source snapshots, remove only values proven to have been generated from specification facts, and replay the local snapshots without spending API requests.

Treat the literal `ComplexTextValue` returned in saved description arrays as a serialization placeholder, not product copy.

**Why:** The current stored detail snapshots contained this marker instead of readable description text; importing it would create false descriptions.

**How to apply:** Filter the marker from detail payloads and leave the product description empty unless a readable source string is present.
