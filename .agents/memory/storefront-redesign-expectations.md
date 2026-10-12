---
name: Storefront redesign expectations
description: The user's expectations for substantial, screenshot-driven changes to the Visermart customer storefront.
---

For Visermart UI requests, the user expects a cohesive, visibly substantial redesign across the named shopper-facing screens, not a few isolated color or spacing adjustments. Treat supplied screenshots as acceptance criteria, including mobile header, category browsing/drawer, product lists, checkout/address flow, and fixed bottom navigation when shown.

The shopper header should be one compact fixed bar with the hamburger beside the logo, a bounded search/notification/account area, and a compact orange cart button. In the left drawer, let the category tree scroll while Home, About, and Contact remain visible in a bottom footer row with icons. Homepage order: hero, active flash sale/countdown, category carousel, other promotion banners, latest products, daily-offer popup after latest products, then recently viewed. Exclude Daily Offers/Deals from category navigation; do not add country-specific copy the user has explicitly excluded.

**Why:** The user repeatedly said prior UI work had not addressed the screenshots, asked for a complete modern e-commerce experience, and corrected the drawer/footer visibility and homepage sequence after earlier placements were still not visible.

**How to apply:** Trace and update the real Laravel Blade views and styles that render each screenshot. Keep the drawer footer outside the scrolling tree, split flash promotions from other promotions, and verify the storefront visually when the database is available; never claim a UI fix based only on CSS edits when the rendered page could not be checked.
