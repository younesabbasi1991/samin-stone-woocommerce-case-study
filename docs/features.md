# Feature inventory

## Catalog and administration

| Feature | Implementation |
| --- | --- |
| Stone material taxonomy | Hierarchical public product taxonomy with REST and menu support |
| Stone usage taxonomy | Hierarchical public product taxonomy with REST and menu support |
| Taxonomy thumbnails | Registered term metadata, WordPress Media Library selector, nonce and capability checks |
| Admin thumbnail column | Compact visual preview in taxonomy list tables |
| Product unit selector | WooCommerce product-editor select with three business-specific units |

## Storefront discovery

| Feature | Implementation |
| --- | --- |
| Reusable product card | Shared rendering across archives and homepage listings |
| Latest products | Date-ordered visible WooCommerce products in a responsive carousel |
| Latest travertine products | Taxonomy-aware product query with visibility and stock exclusions |
| Stone material listing | Eight taxonomy cards in a four-column desktop and two-column mobile grid |
| Stone usage listing | Visual navigation based on the usage taxonomy |
| Product-only search | Native WordPress search parameters constrained to `post_type=product` |

## Archive and filtering

| Feature | Implementation |
| --- | --- |
| Responsive product archive | Three columns on desktop with smaller responsive layouts |
| Catalog taxonomy filters | Material, usage, and product category grouped with `OR` |
| Hierarchical filter rendering | Recursive term tree with counts and checked-state restoration |
| Price bounds | Minimum and maximum values read from WooCommerce product lookup data |
| Optional range filter | Empty/default bounds removed so they do not exclude products |
| Mobile filter drawer | Off-canvas panel with overlay, Escape handling, focus restoration, and `inert` state |
| Product search integration | Search term retained when applying or resetting archive filters |

## Commerce experience

| Feature | Implementation |
| --- | --- |
| AJAX mini-cart drawer | Opens after `added_to_cart` and refreshes with WooCommerce fragments |
| Cart counter | Server-rendered badge updated by fragments on add and remove operations |
| Accessible drawer | Focus management, keyboard loop, Escape support, labels, and body scroll lock |
| Empty-cart state | Purpose-built message and shop action without unrelated product listings |
| Product measurement units | Unit displayed in product, cart, checkout, and final order line item |
| Price consistency | Fixed thousands separator, currency position, decimal separator, and zero decimals |
| WooCommerce page styling | Product, cart, checkout, login, account, and order interfaces adapted to the visual system |

## Content and responsive UI

- Dynamic WordPress menus with separate desktop and mobile navigation.
- Reusable breadcrumbs for product and editorial content.
- Dynamic article archive, cards, pagination, single article, metadata, sharing, comments, and sidebars.
- Responsive homepage sections split into maintainable template parts.
- Local fonts and icon assets with no front-end CDN requirement.
- Preloader positioning isolated from conflicting page-level styles.

