---
name: Storefront redesign expectations
description: The user's expectations for substantial, screenshot-driven changes to the Visermart customer storefront.
---

For Visermart UI requests, the user expects a cohesive, visibly substantial redesign across the named shopper-facing screens, not a few isolated color or spacing adjustments. Treat supplied screenshots as acceptance criteria, including mobile header, category browsing/drawer, product lists, checkout/address flow, and fixed bottom navigation when shown.

The shopper header should be one compact fixed bar with the hamburger beside the logo, a bounded search/notification/account area, and a compact orange cart button. The left drawer is the category tree. On home, put flash promotions first, then latest products, daily offers, and recently viewed products. Do not add country-specific copy the user has explicitly excluded.

**Why:** The user repeatedly said prior UI work had not addressed the screenshots and asked for a complete modern e-commerce experience.

**How to apply:** Trace and update the real Laravel Blade views and styles that render each screenshot. Verify the storefront visually when the database is available; never claim a UI fix based only on CSS edits when the rendered page could not be checked.
