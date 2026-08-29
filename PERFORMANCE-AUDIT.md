# Almas-Theme Performance Audit

**Date:** 2026-08-23  
**Scope:** Custom WordPress + WooCommerce theme (`Almas-Theme`) only — plugins/host/object-cache not audited unless they interact with theme code.  
**Constraint:** No code was changed. This document is diagnostic only. Optimizations must wait for roadmap approval.

---

## Executive summary

The theme is feature-rich and WooCommerce-aware, but several patterns create **high TTFB / PHP cost** and **query inflation**, especially on:

| Surface | Main bottlenecks |
|--------|-------------------|
| **Every page** | Uncached category tree in header + footer; monolithic CSS/JS; full WC include bootstrap; logo attachment queries |
| **Homepage** | Up to **11×** `wc_get_products` for catalog tabs; sale-ID scan with per-ID `get_post_type`; duplicate category lookups |
| **Shop archive** | Brand filter via `posts_per_page => -1` ID lists; stock-sort `posts_clauses`; category nav image N+1 |
| **Single product** | Full cart scan for “in cart”; related products queried twice; attribute/spec loops; variation price HTML for all variations |
| **Cart** | Per-line attribute term lookups across all product attributes |

Largest static assets: `assets/css/style.css` (~279 KB / ~11k lines), `assets/js/main.js` (~46 KB), `inc/template-functions.php` (~76 KB PHP helpers loaded globally).

---

## Methodology

Static review of:

- `functions.php`, all of `inc/`, `template-parts/`, WooCommerce overrides, `header.php` / `footer.php`, front templates
- Hooks (`add_action` / `add_filter`), queries (`WP_Query`, `wc_get_products`, `$wpdb`, meta/option access), asset enqueue, AJAX usage in `main.js`

No Query Monitor / New Relic numbers were captured on a live request. **Estimated impact** is engineering judgment based on call frequency × cost.

---

## Architecture notes (relevant to performance)

1. **Bootstrap is eager** — When WooCommerce is active, `functions.php` requires product fields, badges, shop filters, cart-save-for-later, used-device health report, WooCommerce integration, and checkout fields on **every** front/admin request that loads the theme.
2. **Theme panel settings** — `almasland_get_panel_settings()` is request-static and merges a large defaults tree with `get_option( 'almasland_theme_panel' )` (saved with `autoload = false`). First call per request does a deep merge; subsequent calls are cheap.
3. **Legacy option bridge** — `almasland_get_option()` may call panel + `get_theme_mod()`; still fine per call, but used heavily in header/footer/enqueue.
4. **Good patterns already present** — WC default styles dequeued; brand options use a 1-hour transient; some ID queries set `fields => ids` and disable meta/term caches; Swiper only on front page; `defer` on main script.

---

## Findings

### F1 — Homepage catalog preloads every category panel with separate product queries

| Field | Detail |
|-------|--------|
| **File** | `template-parts/home/section-catalog.php` |
| **Function** | Template body (lines ~12–26) + `almasland_get_home_catalog_products()` |
| **Lines** | `section-catalog.php` 12–26; `inc/template-functions.php` ~1699–1734 |
| **Problem** | Loads “all” products once, then **for each category tab** runs another `wc_get_products()` and renders full card HTML into hidden panels. |
| **Why slow** | Up to **1 + N** product object queries (N ≤ 10). Each product then runs card helpers (meta, terms, gallery image). HTML size and PHP time grow linearly with tabs × products. |
| **Impact** | **Critical** (homepage TTFB + DB) |
| **Recommended solution** | Render only the active “all” panel server-side; load other tabs via AJAX/REST (or a single batched query + JS filter). Cache panel HTML or product ID lists in transients keyed by category + version. |
| **Changes functionality?** | No if UX (tabs) preserved |
| **DB changes?** | No (optional transient keys) |
| **Testing?** | Yes — tab switching, empty categories, mobile |

---

### F2 — `almasland_get_home_catalog_categories()` N+1 term tree, called on every page (header + footer)

| Field | Detail |
|-------|--------|
| **File** | `inc/template-functions.php`, `header.php`, `footer.php` |
| **Function** | `almasland_get_home_catalog_categories()` |
| **Lines** | Helper ~1632–1689; `header.php` ~100; `footer.php` ~29–31 |
| **Problem** | Fetches **all** top-level `product_cat` terms, then for **each** term calls `get_term_children()` and for **each child** `get_term()` to sum counts. No request-level or object cache. Header and footer each call it again. Homepage catalog calls it a third time. |
| **Why slow** | Classic N+1 taxonomy queries on **every** front request between `wp_body_open` and `wp_footer`. |
| **Impact** | **Critical** (global) |
| **Recommended solution** | (1) Static/`wp_cache` memoization per request. (2) Transient (e.g. 15–60 min) invalidated on `edited_product_cat` / product save. (3) Prefer `get_terms` with counts or a single SQL aggregate instead of per-child `get_term`. (4) Pass shared result from a header context into footer, or one helper used once. |
| **Changes functionality?** | No |
| **DB changes?** | No |
| **Testing?** | Yes — category counts, Uncategorized exclude, empty cats |

---

### F3 — Special offers: sale IDs + per-ID `get_post_type()`

| Field | Detail |
|-------|--------|
| **File** | `inc/template-functions.php` |
| **Function** | `almasland_get_home_special_offers_products()` |
| **Lines** | ~1579–1622 |
| **Problem** | Uses `wc_get_product_ids_on_sale()` then `array_filter` with `get_post_type( $id )` for **every** sale ID before `wc_get_products( include )`. |
| **Why slow** | On catalogs with large sale sets, hundreds of post lookups; sale ID list is already expensive and often uncached without object cache. |
| **Impact** | **High** (homepage) |
| **Recommended solution** | Filter with one `get_posts( fields=ids, post_type=product, post__in=$sale_ids, posts_per_page=$limit )` or tax/meta-free `wc_get_products` with `include` limited after intersecting with a single ID query. Cache result transient `almasland_home_offers_{limit}`. |
| **Changes functionality?** | No |
| **DB changes?** | No |
| **Testing?** | Yes — sale/variable parents, visibility |

---

### F4 — Monolithic CSS loaded on every page (~279 KB)

| Field | Detail |
|-------|--------|
| **File** | `functions.php`, `assets/css/style.css` |
| **Function** | `almasland_enqueue_assets()` |
| **Lines** | `functions.php` ~134–185; CSS ~11k lines |
| **Problem** | Full theme CSS (shop, cart, checkout, account, blog, used-product report, etc.) loads on all templates. Front-page CSS is correctly conditional; main bundle is not. |
| **Why slow** | Parse/download cost hurts FCP/LCP; unused CSS still blocks rendering. |
| **Impact** | **High** (frontend) |
| **Recommended solution** | Split critical CSS by context: `base.css`, `shop.css`, `single-product.css`, `cart-checkout.css`, `account.css`, `blog.css`. Enqueue by `is_shop` / `is_product` / `is_cart` / etc. Keep visual parity; do not redesign. |
| **Changes functionality?** | No (visual parity required) |
| **DB changes?** | No |
| **Testing?** | Yes — every template type + dark mode |

---

### F5 — Monolithic `main.js` (~46 KB) on every page

| Field | Detail |
|-------|--------|
| **File** | `functions.php`, `assets/js/main.js` |
| **Function** | `almasland_enqueue_assets()` |
| **Lines** | Enqueue ~159–174; JS ~1370 lines |
| **Problem** | Single script initializes shop filters, gallery, cart qty, checkout sticky, catalog tabs, tooltips, AJAX add-to-cart, etc. Guarded by DOM queries but still **parsed** everywhere. Localize always builds WC AJAX URLs and notify config. |
| **Why slow** | Main-thread parse/compile; larger transfer; unnecessary listeners setup cost on simple pages. |
| **Impact** | **High** (frontend) / **Medium** (PHP localize) |
| **Recommended solution** | Split into `theme-core.js` + conditional modules (`shop.js`, `product.js`, `cart.js`, `checkout.js`, `home.js`). Keep `defer`. Slim `wp_localize_script` payload by page. |
| **Changes functionality?** | No if modules wired correctly |
| **DB changes?** | No |
| **Testing?** | Yes — AJAX cart, variations, filters, mobile nav |

---

### F6 — Duplicate / conflicting shop loop filters

| Field | Detail |
|-------|--------|
| **File** | `functions.php`, `inc/woocommerce.php` |
| **Function** | `almasland_shop_per_page`, `almasland_shop_columns`, `almasland_products_per_page`, `almasland_loop_columns` |
| **Lines** | `functions.php` 263–276; `woocommerce.php` 200–213 |
| **Problem** | Two handlers each for `loop_shop_per_page` and `loop_shop_columns`. Hardcoded `12` / `4` vs panel settings. Higher priority (20) wins, but both run and confuse maintainers. |
| **Why slow** | Minor CPU; risk of wrong pagination if priorities change. |
| **Impact** | **Medium** (correctness + small cost) |
| **Recommended solution** | Keep a single source of truth (panel). Remove hardcoded filters in `woocommerce.php`. |
| **Changes functionality?** | Only if current effective values differ from intended panel values — verify live |
| **DB changes?** | No |
| **Testing?** | Yes — shop pagination/columns |

---

### F7 — Related products: double resolution

| Field | Detail |
|-------|--------|
| **File** | `inc/woocommerce.php` |
| **Function** | `almasland_output_related_products()` |
| **Lines** | ~232–262 |
| **Problem** | Calls `wc_get_related_products( $id, 3 )` only to check emptiness, then `woocommerce_related_products()` which resolves related IDs again and runs the loop. |
| **Why slow** | Duplicate related-product queries on every single product. |
| **Impact** | **High** (single product) |
| **Recommended solution** | Use one path: either early exit with cached IDs passed into `woocommerce_related_products`, or skip the preliminary `wc_get_related_products` and let WC output handle empty. |
| **Changes functionality?** | No |
| **DB changes?** | No |
| **Testing?** | Yes — products with/without related |

---

### F8 — Single product: full cart iteration for “already in cart”

| Field | Detail |
|-------|--------|
| **File** | `woocommerce/content-single-product.php` |
| **Function** | Template bootstrap |
| **Lines** | ~51–61 |
| **Problem** | Loops `WC()->cart->get_cart()` comparing IDs on every product page view. |
| **Why slow** | Cost grows with cart size; usually small but unnecessary if using cart session helpers / product cart hash. |
| **Impact** | **Low–Medium** |
| **Recommended solution** | Use `WC()->cart->find_product_in_cart( WC()->cart->generate_cart_id( ... ) )` or a small helper without full scan semantics duplication. |
| **Changes functionality?** | No |
| **DB changes?** | No |
| **Testing?** | Yes — simple/variable, already-in-cart UI |

---

### F9 — Product card secondary image + term checks in loops (N+1 patterns)

| Field | Detail |
|-------|--------|
| **File** | `woocommerce/content-product.php`, `inc/template-functions.php` |
| **Function** | `almasland_render_product_card_media()`, `almasland_is_used_product()`, color/grade helpers |
| **Lines** | Card template; media ~1268+; `is_used` ~1465–1472 |
| **Problem** | Per card: gallery secondary ID → attachment image; `has_term( 'used', ... )`; grade/color meta; rating. In catalog with 12–48 products this multiplies queries if term/meta caches incomplete. |
| **Why slow** | Loop amplification; second image doubles image markup/bandwidth. |
| **Impact** | **High** (shop/home loops) |
| **Recommended solution** | Prime caches: `update_object_term_cache` / ensure WC product query primes meta. Batch `has_term` via preloaded term relationships. Optional: lazy-load secondary image `src` only on hover via `data-src`. |
| **Changes functionality?** | Lazy hover may delay first hover paint slightly |
| **DB changes?** | No |
| **Testing?** | Yes — used badge, hover swap, shop/list views |

---

### F10 — Shop brand filter builds unbounded ID lists

| Field | Detail |
|-------|--------|
| **File** | `inc/shop-filters.php` |
| **Function** | Inside `almasland_apply_shop_filters()` |
| **Lines** | ~364–436 |
| **Problem** | For brand filters, `get_posts( posts_per_page => -1, fields => ids )` for meta and/or taxonomy, then `post__in`. |
| **Why slow** | Large catalogs → huge ID arrays, slow SQL `IN (...)`, high memory. Runs on main product query when filters active. |
| **Impact** | **High** (filtered shop) |
| **Recommended solution** | Prefer `tax_query` / `meta_query` on the main query instead of materializing all IDs. If IDs needed, cap or use lookup tables. |
| **Changes functionality?** | No if query equivalence preserved |
| **DB changes?** | No |
| **Testing?** | Yes — multi-brand, brand+sale+price combo |

---

### F11 — `gettext` filter registered globally for checkout translations

| Field | Detail |
|-------|--------|
| **File** | `inc/checkout-fields.php` |
| **Function** | `almasland_checkout_gettext()` |
| **Lines** | ~199–238 |
| **Problem** | Hooked to `gettext` always. Callback early-returns unless checkout, but still invoked for **every** translated string site-wide. |
| **Why slow** | High call volume on any page (admin + front). `is_checkout()` itself has cost when conditional tags ready. |
| **Impact** | **Medium** (global PHP) / higher on checkout |
| **Recommended solution** | Add filter only on checkout via `template_redirect` / `wp` when `is_checkout()`, or use WooCommerce locale/MO overrides / `woocommerce_*` specific filters. |
| **Changes functionality?** | No if strings still translated on checkout |
| **DB changes?** | No |
| **Testing?** | Yes — checkout labels, cart (should stay unaffected) |

---

### F12 — Global price HTML filters on every `wc_price` / `get_price_html`

| Field | Detail |
|-------|--------|
| **File** | `inc/woocommerce.php` |
| **Function** | `almasland_format_wc_price_html`, `almasland_hide_outofstock_price_html` |
| **Lines** | ~33–36, ~896–902 |
| **Problem** | Persian digit conversion on all price HTML; stock check on all price HTML. |
| **Why slow** | Called many times per page (cards, fragments, widgets). Regex/string work adds up. |
| **Impact** | **Medium** |
| **Recommended solution** | Keep behavior; optimize `almasland_persian_digits` (strtr map), avoid double-processing; consider formatting once per product in card helpers. |
| **Changes functionality?** | No if digits/out-of-stock rules unchanged |
| **DB changes?** | No |
| **Testing?** | Yes — prices, out-of-stock cards, AJAX fragments |

---

### F13 — Eager require of all WooCommerce theme modules

| Field | Detail |
|-------|--------|
| **File** | `functions.php` |
| **Function** | Bootstrap |
| **Lines** | ~21–28 |
| **Problem** | Shop filters, APSB health report, checkout field maps, cart-save-for-later load even on blog/pages where unused. |
| **Why slow** | PHP parse/opcache memory + hook registration on every request. |
| **Impact** | **Medium** (global) |
| **Recommended solution** | Conditional `require`: checkout fields on checkout/account; shop-filters on shop/taxonomy/AJAX product query; health report on `is_product` / used templates; save-for-later on cart. Keep shared helpers always loaded. |
| **Changes functionality?** | Risk if hooks must register early — needs careful boot order |
| **DB changes?** | No |
| **Testing?** | Yes — all WC endpoints + filters still apply |

---

### F14 — Logo: up to three `full` size attachment images every page

| Field | Detail |
|-------|--------|
| **File** | `inc/template-functions.php` |
| **Function** | `almasland_site_logo()`, `almasland_get_logo_image_html()` |
| **Lines** | ~201–265 |
| **Problem** | Desktop light, dark, and mobile logos each call `wp_get_attachment_image( ..., 'full' )`. |
| **Why slow** | Extra attachment meta/file queries; oversized images in HTML. |
| **Impact** | **Medium** (every page) |
| **Recommended solution** | Use a dedicated small size (e.g. 440×160); one `<img>` + `srcset` / CSS for dark if possible; cache HTML string statically per request. |
| **Changes functionality?** | No if visuals match |
| **DB changes?** | No (optional regenerate thumbnails) |
| **Testing?** | Yes — light/dark/mobile header |

---

### F15 — Footer repeats expensive category helper + contact URL resolution

| Field | Detail |
|-------|--------|
| **File** | `footer.php`, `inc/template-functions.php` |
| **Function** | Footer template; `almasland_get_contact_url()` |
| **Lines** | `footer.php` 29–57; contact URL ~314–333 |
| **Problem** | Categories via F2 again. Contact URL may fall through to `get_page_by_path` and `almasland_get_page_by_title()` → `WP_Query`. Also called from `wp_localize_script`. |
| **Why slow** | Extra queries when panel contact ID unset. |
| **Impact** | **Medium** |
| **Recommended solution** | Persist `contact_page_id` in panel; static-cache URL; share categories with header. |
| **Changes functionality?** | No |
| **DB changes?** | No |
| **Testing?** | Yes — footer links, localize contactUrl |

---

### F16 — Shop category nav: image lookup per term

| Field | Detail |
|-------|--------|
| **File** | `inc/shop-filters.php` |
| **Function** | `almasland_shop_category_nav()`, `almasland_get_product_category_image()` |
| **Lines** | Nav ~712–742; image ~1002–1040 |
| **Problem** | For each top-level category: term meta thumbnail + attachment URL/srcset probes across sizes. |
| **Why slow** | N image meta queries on shop/taxonomy. |
| **Impact** | **Medium** (shop) |
| **Recommended solution** | Cache nav HTML or image map transient; use fixed size once. |
| **Changes functionality?** | No |
| **DB changes?** | No |
| **Testing?** | Yes — active state, lazy images |

---

### F17 — Cart line features: all attributes + taxonomy terms per item

| Field | Detail |
|-------|--------|
| **File** | `inc/woocommerce.php`, `woocommerce/cart/cart.php` |
| **Function** | `almasland_get_cart_item_features()` |
| **Lines** | ~399–461 |
| **Problem** | For each cart line, iterates **all** parent attributes; taxonomy attributes call `wc_get_product_terms`; variation attrs call `get_term_by`. |
| **Why slow** | Cart with many lines × many attributes → query spike. |
| **Impact** | **Medium** (cart) |
| **Recommended solution** | Prefer `_almas_card_specs` / known keys only; or cache features on product meta at save time. |
| **Changes functionality?** | Possibly fewer feature chips if attribute set incomplete — define product rules |
| **DB changes?** | Optional denormalized meta |
| **Testing?** | Yes — simple/variable cart lines |

---

### F18 — Variation data: custom price HTML for every available variation

| Field | Detail |
|-------|--------|
| **File** | `inc/woocommerce.php` |
| **Function** | `almasland_available_variation_price_html()` |
| **Lines** | ~879–887 |
| **Problem** | Filter builds `almasland_get_buy_price_html( $variation )` for each variation WooCommerce exposes to the frontend. |
| **Why slow** | Variable products with many variations inflate JSON payload and PHP time on single product. |
| **Impact** | **Medium** (complex variables) |
| **Recommended solution** | Keep for UX; ensure buy-price helper is cheap; avoid nested queries inside it. |
| **Changes functionality?** | No |
| **DB changes?** | No |
| **Testing?** | Yes — variation switcher prices |

---

### F19 — `almasland_remove_default_wc_single_hooks` on every `wp`

| Field | Detail |
|-------|--------|
| **File** | `inc/woocommerce.php` |
| **Function** | `almasland_remove_default_wc_single_hooks()` |
| **Lines** | ~81–95 |
| **Problem** | Runs on all front `wp` loads, not only `is_product()`. |
| **Why slow** | Low absolute cost; unnecessary work off product pages. |
| **Impact** | **Low** |
| **Recommended solution** | Guard with `is_product()`. |
| **Changes functionality?** | No |
| **DB changes?** | No |
| **Testing?** | Yes — single product layout |

---

### F20 — Duplicate `sanitize_shop_price_query_vars` hooks

| Field | Detail |
|-------|--------|
| **File** | `inc/shop-filters.php` |
| **Function** | `almasland_sanitize_shop_price_query_vars` |
| **Lines** | ~594–595 |
| **Problem** | Hooked to both `parse_request` and `init`. |
| **Why slow** | Runs twice per request (cheap). |
| **Impact** | **Low** |
| **Recommended solution** | Keep one hook (`parse_request` is enough). |
| **Changes functionality?** | No |
| **DB changes?** | No |
| **Testing?** | Yes — invalid min/max price URLs |

---

### F21 — Brand options transient never cleared on product/brand save

| Field | Detail |
|-------|--------|
| **File** | `inc/shop-filters.php` |
| **Function** | `almasland_get_shop_brand_options()` |
| **Lines** | ~199–262 |
| **Problem** | 1-hour transient is good for reads, but missing invalidation can show stale brands (correctness) or force waiting (ops). Called twice in filter UI paths (mitigated by transient). |
| **Why slow** | Cold cache: heavy `$wpdb` GROUP BY on `postmeta`. |
| **Impact** | **Low–Medium** (shop cold cache) |
| **Recommended solution** | `delete_transient` on `save_post_product`, attribute term edits. Optionally longer TTL with invalidation. |
| **Changes functionality?** | Fresher filter list |
| **DB changes?** | No |
| **Testing?** | Yes — add/remove brand |

---

### F22 — Stock-last sorting joins lookup/meta on catalog queries

| Field | Detail |
|-------|--------|
| **File** | `inc/shop-filters.php` |
| **Function** | `almasland_sort_out_of_stock_last_clauses()` |
| **Lines** | ~533–570 |
| **Problem** | Adds JOIN + ORDER BY on product catalog queries (intentional). |
| **Why slow** | Extra join cost on every shop listing; acceptable if indexed (`wc_product_meta_lookup`). Fallback postmeta join is worse. |
| **Impact** | **Medium** (shop) if lookup missing |
| **Recommended solution** | Ensure WC lookup table exists; avoid postmeta fallback when possible. |
| **Changes functionality?** | No |
| **DB changes?** | No (use WC tools to regenerate lookup) |
| **Testing?** | Yes — OOS products sort last |

---

### F23 — Multiple custom image sizes

| Field | Detail |
|-------|--------|
| **File** | `functions.php` |
| **Function** | `almasland_setup()` |
| **Lines** | ~92–96 |
| **Problem** | Registers `almasland-card`, `almasland-hero`, tablet, mobile, single. |
| **Why slow** | Upload/regenerate cost; disk; some pages may still request `full`. |
| **Impact** | **Low** (runtime) / Medium (media ops) |
| **Recommended solution** | Audit unused sizes; serve appropriately sized logos/heroes. |
| **Changes functionality?** | No if sizes still used |
| **DB changes?** | No |
| **Testing?** | Yes — regenerates |

---

### F24 — Notification popup always printed when enabled

| Field | Detail |
|-------|--------|
| **File** | `functions.php` |
| **Function** | `almasland_render_notification_popup` |
| **Lines** | ~224–256 |
| **Problem** | Markup in `wp_footer` on all pages when enabled (JS may hide). |
| **Why slow** | Extra HTML; attachment URL lookup. |
| **Impact** | **Low** |
| **Recommended solution** | Acceptable; ensure image size `medium`; once-per-session already in JS config. |
| **Changes functionality?** | No |
| **DB changes?** | No |
| **Testing?** | Optional |

---

### F25 — Homepage / shop between `wp_body_open` and `wp_footer` (hot path map)

| Region | Cost drivers |
|--------|----------------|
| Header | Notify bar panel read; topbar options; **catalog categories**; nav menu; logo ×3; search form; cart count |
| Front main | Hero; trust; product categories (more term/image work); special offers products; why-us; **catalog N queries**; features |
| Shop main | Category nav images; filter form (brands); product loop cards |
| Footer | **catalog categories again**; contact URL; menus; badges images |
| Scripts | Full `main.js` + localize |

This window is where most user-visible latency accumulates for anonymous catalog traffic.

---

## What is already relatively healthy

- Request-static panel settings merge
- Front-page-only Swiper CSS/JS
- Dequeue of WC classic + block styles
- Brand options transient (when warm)
- Shop filter ID queries sometimes disable meta/term caches
- `defer` on main theme script
- Font preload is local (no external font CDN)

---

## Prioritized optimization roadmap

### P0 — Critical (do first)

| ID | Item | Finding |
|----|------|---------|
| P0.1 | Cache / memoize `almasland_get_home_catalog_categories()`; call once per request; share header↔footer | F2 |
| P0.2 | Stop preloading all homepage catalog tab product grids; AJAX or single-panel SSR | F1 |
| P0.3 | Fix special-offers sale ID filtering (no per-ID `get_post_type`); add short transient | F3 |

### P1 — High

| ID | Item | Finding |
|----|------|---------|
| P1.1 | Split `style.css` by template context | F4 |
| P1.2 | Split `main.js` + conditional enqueue | F5 |
| P1.3 | Remove duplicate related-product query | F7 |
| P1.4 | Reduce product-loop N+1 (terms/meta prime; optional lazy secondary image) | F9 |
| P1.5 | Replace brand `post__in` ID dumps with native tax/meta query | F10 |

### P2 — Medium

| ID | Item | Finding |
|----|------|---------|
| P2.1 | Conditional-load WC theme includes | F13 |
| P2.2 | Register `gettext` only on checkout | F11 |
| P2.3 | Deduplicate loop columns/per_page filters | F6 |
| P2.4 | Optimize logo image sizes / single request-cache | F14 |
| P2.5 | Persist + cache contact URL; shop nav image cache | F15, F16 |
| P2.6 | Slim cart feature builder | F17 |
| P2.7 | Speed up Persian price formatting path | F12 |
| P2.8 | Invalidate brand options transient on save | F21 |
| P2.9 | Verify WC product meta lookup for stock sort | F22 |

### P3 — Low

| ID | Item | Finding |
|----|------|---------|
| P3.1 | Guard single-product hook removals with `is_product()` | F19 |
| P3.2 | Drop duplicate price sanitize hook | F20 |
| P3.3 | Faster “in cart” check on single product | F8 |
| P3.4 | Review custom image size set | F23 |
| P3.5 | Popup markup micro-opts | F24 |

---

## Suggested implementation order (after approval)

1. **P0.1** (categories cache) — largest win for *all* pages, low risk.  
2. **P0.2** (catalog tabs) — largest homepage win.  
3. **P0.3** (offers).  
4. **P1.3** then **P1.4** / **P1.5** for product/shop.  
5. **P1.1 / P1.2** asset splits (more QA surface).  
6. **P2+** cleanup and conditional loading.

---

## Compatibility / non-goals (per brief)

- Do **not** change visual design or UX without explicit approval.
- Do **not** remove features (tabs, hover images, filters, APSB report, save-for-later).
- Preserve WooCommerce compatibility and SEO (titles, breadcrumbs, semantic headings, crawlable product links).
- Prefer caching and conditional loading over deleting behavior.
- Measure before/after with Query Monitor (queries, time) + Lighthouse (LCP/CSS) on: home, shop, single product, cart, checkout.

---

## Approval gate

**No code changes have been made.**  
Please approve the roadmap (or select a subset of P0/P1 items) before implementation begins.
