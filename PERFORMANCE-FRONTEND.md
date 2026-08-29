# PERFORMANCE-FRONTEND.md

Frontend asset optimization for the **Almas Land** theme, implementing
`P1.1` (F4 — monolithic CSS) and `P1.2` (F5 — monolithic `main.js`) from
`PERFORMANCE-AUDIT.md`.

No markup, design, SEO output or PHP behaviour was changed. Backend work from
`PERFORMANCE-BACKEND.md` is untouched.

---

## 1. What was wrong

Every page — including a plain WordPress page, a 404, or the cart — downloaded
and parsed the entire theme frontend:

| Asset | Size | Loaded on |
|---|---:|---|
| `assets/css/style.css` | 278.7 KB | every page |
| `assets/js/main.js` | 46.0 KB | every page |

The stylesheet carried shop filters, the single-product buy card, the used-device
health report, cart, checkout, my-account and blog styles regardless of template.
`main.js` initialised shop filters, the product gallery, variation handling, cart
quantity controls and the checkout sticky bar on every request; the DOM guards
prevented the work, but the browser still parsed and compiled all of it.

---

## 2. Result

Per-page theme payload (uncompressed, excluding Swiper which is unchanged):

| Page type | CSS before | CSS after | JS before | JS after | Total before | Total after | Saved |
|---|---:|---:|---:|---:|---:|---:|---:|
| Front page | 319.4 | 142.9 | 46.0 | 34.0 | 365.4 | 176.9 | **−52%** |
| Shop / category / product search | 278.7 | 121.9 | 46.0 | 27.8 | 324.7 | 149.7 | **−54%** |
| Single product | 278.7 | 156.8 | 46.0 | 31.6 | 324.7 | 188.4 | **−42%** |
| Cart | 278.7 | 172.4 | 46.0 | 27.8 | 324.7 | 200.2 | **−38%** |
| Checkout | 278.7 | 172.4 | 46.0 | 26.2 | 324.7 | 198.6 | **−39%** |
| My account | 278.7 | 128.5 | 46.0 | 24.7 | 324.7 | 153.2 | **−53%** |
| Blog / post / archive | 278.7 | 119.1 | 46.0 | 24.7 | 324.7 | 143.8 | **−56%** |
| Page / 404 | 278.7 | 102.2 | 46.0 | 24.7 | 324.7 | 126.9 | **−61%** |

CSS is render-blocking, so the stylesheet reduction lands directly on FCP/LCP.

---

## 3. Files changed

### New — generated CSS modules

| File | Size | Loaded when |
|---|---:|---|
| `assets/css/base.css` | 102.2 KB | always |
| `assets/css/shop.css` | 19.7 KB | `almasland_is_shop_context()` |
| `assets/css/product.css` | 54.6 KB | `is_product()` |
| `assets/css/cart-checkout.css` | 70.2 KB | `is_cart() \|\| is_checkout()` |
| `assets/css/account.css` | 26.3 KB | `is_account_page()` |
| `assets/css/blog.css` | 16.9 KB | `almasland_is_blog_context()` |

### New — JS modules

| File | Size | Loaded when | Dependencies |
|---|---:|---|---|
| `assets/js/core.js` | 24.7 KB | always | — |
| `assets/js/shop.js` | 3.1 KB | `almasland_is_shop_context()` | core |
| `assets/js/product.js` | 6.9 KB | `is_product()` | core, jquery, `wc-add-to-cart-variation` |
| `assets/js/cart.js` | 3.1 KB | `is_cart()` | core |
| `assets/js/checkout.js` | 1.5 KB | `is_checkout() && ! is_order_received_page()` | core, jquery |
| `assets/js/home.js` | 9.3 KB | `is_front_page()` | core, `almasland-swiper` |

All six carry `wp_script_add_data( …, 'strategy', 'defer' )`, as `main.js` did.

### Moved

| From | To | Why |
|---|---|---|
| `assets/css/style.css` | `assets/src/style.css` | now the build input, not a served asset |
| `assets/js/main.js` | `assets/src/main.js` | kept as the reference the JS split is verified against |

Neither is enqueued on the front end. `assets/src/style.css` is still used by
`add_editor_style()` — the block editor renders arbitrary content with no page
context to select modules from, and it is admin-only.

### Modified

| File | Change |
|---|---|
| `functions.php` | Rewrote `almasland_enqueue_assets()`; added `almasland_is_shop_context()` and `almasland_is_blog_context()`; editor stylesheet repointed to `assets/src/style.css` |

### New — build and verification tooling

| File | Purpose |
|---|---|
| `tools/css-parse.mjs` | Dependency-free CSS block parser shared by the other scripts |
| `tools/split-css.mjs` | Generates the CSS modules; also runs the cascade and round-trip checks |
| `tools/verify-css-coverage.mjs` | Per-page-type check that no template loses its styles |
| `tools/verify-js-split.mjs` | Line coverage and global-collision check for the JS split |

```bash
node tools/split-css.mjs            # report only
node tools/split-css.mjs --write    # regenerate assets/css/*.css
node tools/verify-css-coverage.mjs
node tools/verify-js-split.mjs
```

### Handle renames

| Before | After |
|---|---|
| `almasland-main` (style) | `almasland-base` |
| `almasland-main` (script) | `almasland-core` |

`almasland-theme`, `almasland-front-page` and `almasland-swiper` are unchanged.
The localized object is still `almasLandTheme` with an identical payload; it now
attaches to `almasland-core`.

---

## 4. How the CSS was split

The source stylesheet is not organised by page — its sections interleave, so
"Design System" contains checkout and account rules, and "Shop archive
responsive" contains blog and modal rules. Splitting by section comment would
have dropped rules. The split is therefore done **per rule**, by selector.

### Classification

1. **Page scope wins.** If a selector contains a body/page class
   (`.woocommerce-cart`, `.checkout-page`, `.product-detail-page`,
   `.category-page`, `.woocommerce-account`, `.blog-page`, …) that decides the
   module outright — the element cannot exist on another template.
2. **Otherwise, component block.** BEM block names map to modules, matching
   `.buy-card`, `.buy-card__price` and `.buy-card--used` but not `.buy-cards`.
3. **Everything else stays in base.** Unrecognised selectors are never moved.
   Base therefore keeps the reset, custom properties, typography, layout, header,
   footer, product cards, dark theme, global responsive rules, the design-system
   components, forms, modals, toasts and the page-loading indicator.
4. **Mixed rules.** A rule listing selectors from two page modules is emitted
   into **both** — those modules never load together, so the duplicate can never
   both apply, and each copy keeps its position after base. A rule that mixes a
   page selector with an unscoped one goes to base.
5. `@media` / `@supports` blocks are split per inner rule and re-wrapped in the
   same condition, so a media block spanning four contexts contributes only its
   relevant rules to each module.

Product cards stay in `base.css` deliberately: they render on the front page, in
shop loops, in related products, in cart cross-sells, and can appear anywhere via
a `[products]` shortcode.

### Why `cart` and `checkout` are one module

`almasland_get_main_class()` puts `cart-page` on **both** the cart and the
checkout, so the two share a large body of rules. Splitting them would have
required duplicating that shared set and would have introduced cascade ordering
risk between the copies for no real saving, since `cart-checkout.css` loads on
exactly the two screens that need it.

### Correctness guarantees

`tools/split-css.mjs` fails loudly on any of these, and all currently pass:

| Check | Result |
|---|---|
| **Round trip** — every `(at-rule context, selector, declarations)` triple in the source exists in exactly one module, and no module invents one | `missing=0 extra=0` over **1832 rules** |
| **Cascade order** — for every selector present in two modules that can load together, the module holding the earlier source rule must be enqueued first | `0 conflicts` |
| **Block accounting** — source blocks vs emitted blocks | 1571 → 1651 (the 80 extra are intentional `@media` re-wraps and cross-module duplicates) |

Module enqueue order (`base → shop → product → cart-checkout → account → blog`)
follows first appearance in the source stylesheet, which is what makes the
cascade check pass.

### Per-page coverage check

`tools/verify-css-coverage.mjs` extracts the class names each template renders,
then asserts that any class with rules in the theme has them in a module that
page actually loads:

```
global (page/404/header/footer)  [base]                 classes used=60   UNCOVERED=0
singular with comments           [base + blog]          classes used=6    UNCOVERED=0
front page                       [base + front-page]    classes used=136  UNCOVERED=0
shop / product archive           [base + shop]          classes used=60   UNCOVERED=0
single product                   [base + product]       classes used=151  UNCOVERED=0
cart                             [base + cart-checkout] classes used=79   UNCOVERED=0
checkout                         [base + cart-checkout] classes used=128  UNCOVERED=0
my account                       [base + account]       classes used=138  UNCOVERED=0
blog                             [base + blog]          classes used=65   UNCOVERED=0
TOTAL_UNCOVERED=0
```

This check caught one real bug during development: `page.php` calls
`comments_template()`, and `comments.php` uses the `.blog-comments` styles, so a
page with comments enabled would have rendered an unstyled comment list.
`almasland_is_blog_context()` now returns true for any singular screen with
comments open.

Three findings were reviewed and dismissed (recorded in the script so future runs
stay clean):

| Flag | Why it is not a regression |
|---|---|
| `.alt` on checkout | The only rule is `.buy-card form.cart button.button.alt`, which needs a `.buy-card` ancestor |
| `.required` on my-account | The only rule is `.woocommerce-checkout .required`, scoped to the checkout body class |
| `.order-received` on my-account | Not a class — `order-received` appears in `order-details.php` as a WooCommerce endpoint name |

---

## 5. How the JS was split

| Original lines | Behaviour | Module |
|---|---|---|
| 1–240 | Config, dark-mode toggle, floating contact, mobile nav, header categories, nav submenus | core |
| 242–332 | Filter drawer, view switcher, filter form | **shop** |
| 334–374 | Product gallery thumbnails | **product** |
| 376–421 | Generic `.quantity-control`, cart count helpers | core |
| 423–603 | WC AJAX add-to-cart plumbing, cart choice modal | core |
| 605–715 | Single-product add to cart (buy card + mobile bar) | **product** |
| 717–812 | Variable product price/stock/image sync | **product** |
| 814–917 | Cart quantity controls, update button | **cart** |
| 919–1052 | Mega menu, modals, accordions, tabs, toasts, notification popup | core |
| 1054–1146 | Front page trust tooltips | **home** |
| 1148–1220 | Product colour tooltips | core |
| 1222–1290 | Offer card add to cart | **home** |
| 1292–1446 | Hero + special offers Swipers, catalog tabs | **home** |
| 1455–1579 | Page-loading indicator | core |
| 1581–1631 | Checkout sticky bar | **checkout** |

Product colour tooltips stayed in core because the swatches are rendered by
`almasland_render_product_color_swatch()`, which runs inside product cards on the
front page, shop loops and cart cross-sells as well as on the product page. The
generic `.quantity-control` handler stayed in core for the same reason: the code
is a dozen lines and moving it would have narrowed where it applies.

### Sharing state between modules

`core.js` publishes the helpers the page modules need on `window.AlmasLand`:

```
config, ADD_TO_CART_BUTTON_SELECTOR, addProductToCartAjax, buildAddToCartFormData,
closeCartChoiceModal, closeMegaMenus, findSingleProductCartButton,
getSingleProductCartForm, getWcAjaxUrl, openCartChoiceModal, parseCartCount,
setModalState, showCartValidationMessage, showToast, toPersianDigits,
updateCartFragments
```

The page modules read them through the namespace (`window.AlmasLand.getWcAjaxUrl(…)`)
rather than destructuring. This is not stylistic: these are classic scripts
sharing one global lexical scope, so a top-level `const getWcAjaxUrl` in
`product.js` would collide with the same binding in `core.js` and throw
`SyntaxError: Identifier … has already been declared`, killing the module.

Every page module declares `almasland-core` as a WordPress dependency, which both
orders the `<script>` tags and keeps `defer` execution order deterministic.

### Verification

`tools/verify-js-split.mjs` reports:

- **Coverage** — 825 meaningful source lines; 8 do not appear verbatim in the
  modules. All 8 are the lines rewritten to call through `window.AlmasLand.*`,
  and the script prints the matching replacement for each.
- **Global collisions** — `0` across every pair of modules that can load
  together (core+shop, core+product, core+cart, core+checkout, core+home,
  shop+home, cart+checkout, shop+product).
- `node --check` passes on all six modules.

`php -l` passes on every `.php` file in the theme (`FILES_WITH_ERRORS=0`).

---

## 6. Loading conditions

```php
base.css     always
shop.css     is_shop() || is_product_taxonomy() || ( is_search() && post_type=product )
product.css  is_product()
cart-checkout.css  is_cart() || is_checkout()          // includes order-received
account.css  is_account_page()
blog.css     is_home() || is_singular('post') || is_category() || is_tag()
             || is_author() || is_date() || is_search()
             || ( is_singular() && comments are open )
front-page.css  is_front_page()                        // unchanged

core.js      always
shop.js      same condition as shop.css
product.js   is_product()
cart.js      is_cart()
checkout.js  is_checkout() && ! is_order_received_page()
home.js      is_front_page()                           // after Swiper
```

Notes on the edge cases:

- **Order received / thank you.** `is_checkout()` is true, so
  `cart-checkout.css` — which holds the `.order-received` and `.order-details`
  styles — loads. `checkout.js` does not, because the sticky bar needs
  `#place_order`, which that page has no equivalent of. This matches the previous
  behaviour, where the same guard already existed inside the function.
- **Order details in my account.** `woocommerce/order/order-details.php` renders
  on both the thank-you page and *View order*, so `.order-details`,
  `.order-addresses`, `.order-card` and `.mini-order-list` are emitted into both
  `cart-checkout.css` and `account.css`. Those two never load together.
- **Product search.** `?post_type=product&s=…` is served by
  `woocommerce/archive-product.php`, so it gets `shop.css` and `shop.js`.
  `blog.css` also loads (`is_search()`), which the cascade check explicitly
  covers as a co-loaded pair.
- **Panel / customizer CSS.** `wp_add_inline_style()` now attaches to the *last*
  enqueued style module rather than a fixed handle, reproducing its old position
  immediately after all theme CSS and before `front-page.css`.

---

## 7. Functionality preserved

| Area | Status |
|---|---|
| WooCommerce AJAX add to cart, fragments, cart count | core — every page, unchanged |
| Variable products (`found_variation`, `reset_data`, price/stock/image sync) | product.js, still depends on `wc-add-to-cart-variation` + jQuery |
| Shop filters, filter drawer, grid/list view, price inputs | shop.js |
| Cart quantity +/− controls, remove-link swap, update button | cart.js |
| Checkout sticky bar, `updated_checkout` resync | checkout.js, still depends on jQuery |
| Mobile navigation, header categories, submenu open/close | core |
| Product gallery thumbnails | product.js |
| Catalog tabs, hero + offers sliders | home.js, after Swiper |
| Trust tooltips, product colour tooltips | home.js / core |
| Notification bar + popup, toasts, modals, accordions, tabs | core |
| Dark mode toggle and persistence | core (the inline `<head>` script in `header.php` is untouched) |
| Page-loading indicator | core |
| `defer` on all theme scripts | preserved |

Nothing was deleted. Handlers with no matching markup in the current templates
(`[data-view-switcher]`, `[data-mega-toggle]`, `[data-offer-add-to-cart]`,
`[data-add-to-cart]`, `.shop-filter-toggle`, `[data-modal-open]`, `[data-tabs]`,
`[data-accordion]`, `[data-toast]`) were kept and placed in the module they
belong to, so re-adding that markup still works.

---

## 8. Risks

| Risk | Likelihood | Mitigation |
|---|---|---|
| A rule needed on a page ends up in a module that page does not load | Low | `verify-css-coverage.mjs` reports 0 uncovered classes across 9 page contexts; unrecognised selectors default to base |
| Cascade order changes between two co-loaded modules | Low | `split-css.mjs` reports 0 conflicts; enqueue order follows source order |
| A plugin or child theme enqueues against `almasland-main` | Low | Handle renamed to `almasland-base` / `almasland-core`. Search the site for that handle before deploying |
| Page-specific markup injected into an unrelated template (a `[products]` shortcode on a plain page, a product card in a widget) | Medium | Product cards, forms, modals, toasts, pagination and the design-system components are all in `base.css`. Genuinely page-scoped markup on a foreign template is the one case that needs a manual check |
| More HTTP requests (2 → up to 4 CSS, 1 → 2 JS) | Low | Each is far smaller than what it replaces; negligible over HTTP/2, and the modules cache independently across navigation |
| Someone edits `assets/css/base.css` directly and it is later regenerated | Medium | Every generated file carries a header pointing at `assets/src/style.css` and the regeneration command |
| A future edit to `assets/src/style.css` is not propagated | Medium | Re-run `node tools/split-css.mjs --write`; the checks fail loudly on a bad split |

`assets/css/style.css` and `assets/js/main.js` no longer exist at those paths.
Anything hard-coding them (a caching or optimisation plugin's exclusion list,
a critical-CSS tool, a CDN rule) must be updated.

---

## 9. Testing checklist

CSS was verified structurally rather than visually. Before deploying, load each
page type in both light and dark mode:

1. **Front page** — hero slider, special offers slider, catalog tabs, trust
   tooltips, product cards and hover image swap.
2. **Shop and a product category** — filter drawer on mobile, grid/list switch,
   price range submit, sort pills, active filter chips, pagination.
3. **Single product** (new and used) — gallery thumbnails, colour tooltips,
   variation switching with price/stock/image updates, add to cart from the buy
   card and the mobile bar, cart choice modal, related products, and the
   used-device health report.
4. **Cart** — quantity +/−, remove-link swap at quantity 1, coupon, update
   button, cross-sells, sticky bar.
5. **Checkout** — field layout, payment boxes, trust badges, sticky bar total and
   button sync, then place an order.
6. **Order received** — confirm `.order-received` and `.order-details` are styled.
7. **My account** — dashboard, orders list, view order, addresses, login and
   register forms.
8. **Blog** — index, category archive, single post, comments, sidebar widgets.
9. **A plain page with comments enabled**, and a **404**.
10. **Search** — both a normal search and `?post_type=product`.
11. Confirm in DevTools that only the expected stylesheets load per page and that
    no console error mentions `AlmasLand` or a redeclared identifier.

---

## 10. Not done here

- Minification and concatenation — left to a caching/optimisation plugin or a
  build step, so the served files stay readable and debuggable.
- Critical CSS inlining and `preload` hints for the module stylesheets.
- Trimming `base.css` further. The header (18 KB), global responsive rules
  (11 KB), product cards (16 KB) and custom properties (9 KB) dominate it and are
  genuinely global.
- `assets/vendor/swiper/swiper-bundle.min.js` (148 KB) still loads in full on the
  front page. A custom Swiper build with only the modules used (navigation,
  pagination, autoplay) would cut most of it, but that changes a vendor asset and
  was out of scope.
- The `almasland-theme` handle still requests the root `style.css`, which
  contains only the theme header. It is kept because `get_stylesheet_uri()` is
  how a child theme's stylesheet loads.
- Slimming the `almasLandTheme` localize payload per page type, as the audit
  suggested. The payload is unchanged so that no behaviour depends on a field
  that silently disappeared.
