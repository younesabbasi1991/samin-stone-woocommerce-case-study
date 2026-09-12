# Challenges and solutions

## 1. Representing a domain-specific stone catalog

### Challenge

WooCommerce product categories alone could not express both the material of a stone and its intended architectural use without creating an awkward, deeply nested category tree.

### Solution

Two independent hierarchical product taxonomies were introduced: material and usage. Their registration lives in a custom core plugin rather than the theme, which protects catalog data from future theme changes and gives each dimension its own archive, REST representation, admin column, and navigation support.

## 2. Giving custom taxonomy terms visual identities

### Challenge

WordPress does not provide a built-in image field for arbitrary taxonomies, while the homepage design required image-led material and usage cards.

### Solution

A reusable thumbnail module registers integer term metadata and adds Media Library controls to both add and edit screens. It validates nonces, user capabilities, and attachment types before saving and adds a compact thumbnail column to administration tables.

## 3. Combining filters without unintentionally hiding products

### Challenge

The business expected a product matching any selected catalog dimension to appear. WooCommerce's default query composition can easily turn independent taxonomy filters into an overly restrictive `AND` query. A default price range could also submit values even when the visitor had not intentionally filtered by price.

### Solution

Selected material, usage, and product-category clauses are nested inside an `OR` group. Price values are sanitized separately, and blank values or an unchanged full-store range are removed before query construction. This keeps taxonomy discovery broad while making an intentionally changed price range an additional constraint.

## 4. Keeping an AJAX cart drawer synchronized

### Challenge

The mini-cart body, empty state, cart-count badge, notices, and drawer visibility all needed to remain correct after asynchronous add and remove actions.

### Solution

Server-side fragment callbacks return both the complete mini-cart body and count badge. Browser code listens to WooCommerce fragment and cart events, updates accessible labels, opens the drawer after successful additions, and handles removal feedback. Keyboard trapping, Escape dismissal, overlay dismissal, and focus restoration were included as interaction requirements rather than afterthoughts.

## 5. Selling products by different measurement units

### Challenge

Stone products may be priced and ordered by square metre, linear metre, or piece. A label shown only on the product page would be lost later in the purchasing workflow.

### Solution

The selected unit is stored as product metadata with a safe square-metre fallback. It is resolved for variations, shown next to quantity controls, copied into cart item data, displayed during review, and finally written to WooCommerce order item metadata.

## 6. Consistent Persian price presentation

### Challenge

Classic WooCommerce templates, mini-cart fragments, and block-based pages could render currency placement and numeric separators differently in RTL layouts.

### Solution

Price options and runtime WooCommerce price arguments are normalized on the server: comma thousands separators, no fractional digits, and a consistent currency position. Component CSS then isolates mixed-direction price strings where required instead of globally reversing interface direction.

## 7. Preserving plugin compatibility on checkout

### Challenge

Replacing checkout structure can break WooCommerce validation, country/state data, address-field plugins, and shipping calculations.

### Solution

Functional checkout markup and field behavior remain controlled by WooCommerce and compatible field-management plugins. The custom layer is limited to scoped presentation rules and removes visual elements only when they are not required for checkout behavior.

## 8. Avoiding global asset conflicts

### Challenge

Large global stylesheets caused unrelated page rules to affect controls such as the preloader, checkout fields, and account forms.

### Solution

Commerce assets were split by responsibility and conditionally enqueued for archives, product search, single products, cart, checkout, and account screens. Component-specific selectors reduce cascade leakage and make cache/version changes easier to reason about.

