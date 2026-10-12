---
name: Storefront redesign expectations
description: The user's expectations for substantial, screenshot-driven changes to the Visermart customer storefront.
---

For Visermart UI requests, the user expects a cohesive, visibly substantial redesign across the named shopper-facing screens, not a few isolated color or spacing adjustments. Treat supplied screenshots as acceptance criteria, including mobile header, category browsing/drawer, product lists, checkout/address flow, and fixed bottom navigation when shown.

The shopper header should be one compact fixed bar with the hamburger beside the logo, a bounded search/notification/account area, and a compact orange cart button. In the left drawer, let the category tree scroll while Home, About, and Contact remain visible in a bottom footer row with icons. Homepage order: hero banners, active flash-sale section/countdown immediately afterward, category carousel (omit only the exact “Daily Offers” category), other promotion banners, latest products, daily-offer popup after latest products, then recently viewed. Do not hide other categories such as Daily Deals; do not add country-specific copy the user has explicitly excluded.

**Why:** The user repeatedly said prior UI work had not addressed the screenshots, asked for a complete modern e-commerce experience, and clarified that the CMS flash-sale section must move from the bottom to immediately after the hero banners while only the Daily Offers category is excluded.

**How to apply:** Trace the CMS-driven section loop as well as manually included Blade sections; move active CMS flash offers above category browsing and prevent them from rendering a second time in the bottom section loop. Keep the drawer footer outside the scrolling tree, and verify visually against the supplied screenshots.
