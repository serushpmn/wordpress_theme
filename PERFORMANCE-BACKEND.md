# PERFORMANCE-BACKEND.md

Backend / database optimization pass for the **Almas Land** theme, implementing the
approved subset of `PERFORMANCE-AUDIT.md`:

`P0.1`, `P0.2`, `P0.3`, `P1.3`, `P1.4`, `P1.5`, `P2.1`, `P2.5`, `P2.6`, `P2.7`

Scope boundary: **no CSS/JS asset architecture changes** were made in this step
(`P1.1`, `P1.2` remain open). No markup, SEO, or visual output was changed.

---

## 1. New shared caching layer

**New file:** `inc/cache.php` (loaded first in `functions.php`, before every other module).

The theme previously had no caching primitives, so each optimization would have
grown its own ad-hoc statics and transients. A single layer was added instead.

### Layer 1 — request cache

| Function | Purpose |
|---|---|
| `almasland_cache_has/get/set/forget()` | Raw access to a per-request static store |
| `almasland_cache_remember( $key, $callback )` | Memoize a callback for the current request |

Free, cleared automatically at the end of the request, and impossible to leak
between visitors. Used as the first line of defence everywhere.

### Layer 2 — persistent cache (transients)

| Function | Purpose |
|---|---|
| `almasland_cache_remember_persistent( $key, $callback, $ttl )` | Transient-backed cache, fronted by the request cache |
| `almasland_cache_version()` | Current namespace version |
| `almasland_cache_flush()` | Bump the version — invalidates every derived key at once |
| `almasland_cache_is_persistent_enabled()` | Kill switch; off inside the Customizer preview |

Design notes:

- **Versioned keys.** Every transient name is prefixed with `almasland_v{N}_`.
  Invalidation increments `N` (a single autoloaded option write) rather than
  deleting rows one by one. Orphaned rows expire on their own TTL and are
  collected by WP's `delete_expired_transients` cron.
- **Payload wrapping.** Values are stored as `array( 'almasland_payload' => $value )`
  so a legitimately empty result (`array()`, `0`, `false`) is a cache *hit*, not a
  perpetual miss. This matters: an empty category tab used to re-run its query on
  every single request.
- **Long keys** (>160 chars) are hashed to stay inside the transient name limit.
- **Escape hatch:** `apply_filters( 'almasland_enable_persistent_cache', true )`.

### Layer 3 — bulk cache priming

| Function | Purpose |
|---|---|
| `almasland_prime_product_caches( $ids )` | Bulk-prime posts, postmeta, term relationships, and card attachments |
| `almasland_prime_product_image_caches( $ids )` | Bulk-prime featured + first gallery attachment |

This converts the dominant N+1 pattern (one lookup per product, then one per
image) into a fixed number of bulk queries. Already-primed IDs are tracked in the
request cache, so calling it repeatedly in one request is free.

### Cache invalidation

Registered on `init` (priority 1) in `almasland_cache_register_invalidation()`:

| Trigger | Hooks |
|---|---|
| Product writes | `save_post_product`, `woocommerce_new_product`, `woocommerce_update_product`, `woocommerce_update_product_variation`, `woocommerce_delete_product`, `woocommerce_trash_product` |
| Stock changes | `woocommerce_product_set_stock`, `woocommerce_variation_set_stock`, `woocommerce_product_set_stock_status`, `woocommerce_variation_set_stock_status` |
| Scheduled sales | `wc_after_products_starting_sales`, `wc_after_products_ending_sales` |
| Taxonomy changes | `created_term`, `edited_term`, `delete_term` — filtered to `product_cat`, `product_tag`, `pa_*` only |
| Permanent deletes | `deleted_post` — filtered to `product`, `product_variation`, `page` |
| Contact page | `save_post_page` |
| Theme panel | `update_option_almasland_theme_panel` |
| Manual | `do_action( 'almasland_flush_cache' )` |

Invalidation is **debounced to one version bump per request**, so a bulk import
of 1,000 products performs one option write, not one thousand.

### What is deliberately *not* cached

Cart contents, session data, checkout fields, customer data, prices resolved
per-customer, and anything derived from the logged-in user. The only cart-adjacent
cache is `almasland_get_cart_item_features()`, which is **request-scoped only** and
derived purely from product/variation attributes (see §9).

---

## 2. P0.1 — Homepage catalog category tree

**Files:** `inc/template-functions.php`

**Before.** `almasland_get_home_catalog_categories()` ran on *every page* of the site
(header at limit 8, footer at limit 6, front page at limit 10 — three independent
calls). Each call did one `get_terms( parent = 0 )`, then per top-level category a
`get_term_children()`, then a `get_term()` per descendant to sum counts.

**After.**

```
almasland_get_home_catalog_categories( $limit )        // thin slice
  └─ almasland_get_home_catalog_category_terms()       // request-memoized
       └─ almasland_build_home_catalog_category_totals()  // transient-cached
```

- `almasland_build_home_catalog_category_totals()` reads the **entire `product_cat`
  taxonomy in one `get_terms()` call**, builds a parent → children map in PHP, and
  walks the tree with `almasland_sum_term_tree_count()` (recursive, with a cycle
  guard). The `get_term_children()` + `get_term()` N+1 is gone entirely.
- The result is a compact `term_id => total_count` map, capped at
  `ALMASLAND_HOME_CATALOG_CATEGORY_POOL` (20), stored in a versioned transient.
- Terms are rehydrated once per request with a single
  `get_terms( include, orderby => include )`, then **sliced** by each caller. Header,
  footer and front page now share one resolution instead of three.

**Behavior preserved.** Same exclusion of `default_product_cat`, same
"own count + all descendants" total, same sort (count DESC, then name ASC), same
`$term->count` override that the templates read.

**Correctness fix included:** the old code mutated `$term->count` on objects that
live in WP's shared term cache, leaking an inflated count into any other code that
later read the same term. The new code **clones** the term before overriding `count`.

---

## 3. P0.2 — Homepage catalog tabs

**Files:** `inc/template-functions.php`, `template-parts/home/section-catalog.php`

**Before.** The template rendered 11 pre-loaded panels (1 "all" + 10 categories).
Each panel called `almasland_get_home_catalog_products()`, which ran a full
`wc_get_products()` object query. That is **11 product queries plus ~88 individual
product/meta/term/attachment reads** on every front page load.

**After.** The tab HTML is untouched — all panels are still server-rendered, so the
tabs keep working exactly as before with no AJAX and no markup change. The
*resolution path* was restructured:

1. New `almasland_get_home_catalog_product_ids( $category_id, $limit )` returns a
   **cached, visibility-filtered ID list** per tab. Visibility is applied at
   cache-build time, so the filtered result is what gets stored.
2. The template resolves all 11 ID lists first (zero queries once warm), merges
   them, and calls `almasland_prime_product_caches()` **once**.
3. Panels are then hydrated via `almasland_get_products_by_ids()`, which reads
   entirely from the primed object cache.

Warm-cache cost drops from 11 product queries + ~88 per-product reads to
**0 product queries + 3 bulk primes**.

`almasland_get_home_catalog_products()` is kept as a thin wrapper so any child
theme or custom code calling it keeps working.

---

## 4. P0.3 — Special offers

**Files:** `inc/template-functions.php`

**Before.** `almasland_get_home_special_offers_products()` called
`wc_get_product_ids_on_sale()` and then ran **`get_post_type()` on every single sale
ID** to filter out variations — one uncached post lookup per sale item — before
running `wc_get_products()`.

**After.**

- The per-ID `get_post_type()` loop is **removed**. `wc_get_products()` already
  constrains the query to `post_type = product`, so variation IDs in the sale list
  are dropped by SQL at no cost. Result set is identical.
- New `almasland_get_home_special_offer_ids( $limit )` caches the final
  visible + on-sale ID list in a transient (1 hour TTL, plus version invalidation
  and explicit `wc_after_products_starting_sales` / `wc_after_products_ending_sales`
  hooks so scheduled sales are picked up).
- Hydration goes through `almasland_get_products_by_ids()` → single bulk prime.

---

## 5. P1.3 — Duplicate related-products query

**Files:** `inc/woocommerce.php` → `almasland_output_related_products()`

**Root cause.** The theme called `wc_get_related_products( $id, 3 )` to decide
whether to render the section, then `woocommerce_related_products()` internally
called `wc_get_related_products( $id, 3, $product->get_upsell_ids() )`. WooCommerce
keys its related-products transient by a hash of `limit` + `exclude_ids`, so the
**differing `exclude_ids` produced two different cache keys and two full queries**.

**Fix.** The theme's call now passes the same `$product->get_upsell_ids()`.
Both calls hit one cache entry; the second is a cache read. The resulting IDs are
also passed through `almasland_prime_product_caches()` so the related loop renders
from primed caches.

Rendering still goes through WooCommerce's `woocommerce_related_products()` and its
`single-product/related.php` template, so all WooCommerce hooks and third-party
filters are untouched.

---

## 6. P1.4 — Product-loop N+1

**Files:** `inc/template-functions.php`, `inc/product-fields.php`, `inc/woocommerce.php`, `inc/cache.php`

| Problem | Fix |
|---|---|
| `almasland_is_used_product()` ran `has_term( 'used', 'product_cat', $id )` several times per card (badge, card class, single-product template) | Memoized per product ID in the request cache |
| `almasland_get_product_card_summary()` and `almasland_get_product_grade_badge()` each did their own `wc_get_product( parent_id )` for variations | Both now use the new `almasland_get_product_meta_owner()`, which memoizes the parent product object per request. `almasland_get_product_meta_source()` in `product-fields.php` delegates to it, so colors/delivery share the same resolution |
| Card images (`featured` + first gallery image for the hover swap) were resolved one attachment at a time | `almasland_prime_product_image_caches()` bulk-primes them; only the *first* gallery ID is primed, since that is all a card renders |
| `wc_get_products()`-driven homepage loops never primed term/attachment caches | `almasland_render_home_product_loop()` now primes its result set |
| Shop/category archive loops primed posts/meta/terms via `WP_Query` but not attachments | New `almasland_prime_shop_loop_caches()` on `woocommerce_before_shop_loop` (priority 5), guarded to skip non-product posts so shortcode loops on regular pages are unaffected |

The hover secondary image was **kept eager**, not lazy-loaded: making it lazy would
have changed markup. Bulk priming delivers the query saving without touching HTML.

---

## 7. P1.5 — Shop brand filter

**Files:** `inc/shop-filters.php` → `almasland_apply_shop_filters()`

**Before.** Any brand selection ran `get_posts( posts_per_page => -1, fields => ids )`
across the whole catalog — once for meta brands, once for taxonomy brands — and
pushed the union into `post__in`. On a large catalog that is an unbounded ID dump
injected into the main query.

**After**, split by case:

| Selection | Handling | Queries |
|---|---|---|
| Meta brands only | Native `meta_query` on `_almas_brand` with `compare => IN` | 0 extra |
| Taxonomy brands only | Native `tax_query` on the brand attribute, `field => slug` | 0 extra |
| Both (rare) | Cached ID union via `almasland_get_shop_brand_post_ids()` | cached |
| Taxonomy brands requested but no brand attribute exists | `post__in = array( 0 )` (same empty result as before) | 0 |

The mixed case genuinely needs an `OR` between a meta clause and a tax clause,
which `WP_Query` cannot express, so it keeps the ID union — but the union is now
transient-cached (1 hour + version invalidation) and the taxonomy side uses
`get_objects_in_term()` instead of a second full `WP_Query`.

The `on_sale` filter still composes correctly: it sets `post__in`, and the native
meta/tax clauses are `AND`-ed against it by WordPress, producing the same result
set the old manual `array_intersect()` produced.

**No raw SQL was introduced.**

Additionally, `almasland_get_shop_brand_options()` (rendered by both the filter
sidebar and the active-chip row) gained a request-level cache, and its transient is
now explicitly deleted on catalog invalidation instead of relying solely on its
1-hour TTL.

---

## 8. P2.1 — Conditional module loading

**Files:** `functions.php`, `inc/theme-panel/bootstrap.php`, `woocommerce/content-single-product.php`

Every request previously parsed and executed all theme modules. Modules are now
loaded only where they can actually be used:

| Module | Before | After |
|---|---|---|
| `inc/used-device-health-report.php` (~33 KB) | Every request | Single product pages only — via `almasland_load_used_device_health_report()`, called on `wp` when `is_product()` **and** defensively at the top of `content-single-product.php` for used products. The module registers **zero hooks**, which is what makes this safe |
| `inc/cart-save-for-later.php` | Every request | Cart screen only — loaded on `wp` (priority 5), well before its `template_redirect` (priority 20) handler needs to fire. `is_cart()` also covers the `[woocommerce_cart]` shortcode |
| `inc/customizer.php` | Every request | `is_admin() \|\| is_customize_preview()` only. It defines nothing the front end uses; `almasland_get_option()` reads `get_theme_mod()` directly |
| `inc/theme-panel/fields.php` + `admin.php` (~36 KB) | Every request | `is_admin()` only. Both contain admin-screen renderers and `admin_menu` / `admin_post_*` handlers exclusively |

`inc/theme-panel/defaults.php` and `settings.php` remain always-loaded — the front
end reads panel settings on every page.

Modules **not** deferred, and why:

- `inc/product-fields.php`, `inc/product-badges.php`, `inc/shop-filters.php`,
  `inc/woocommerce.php` — register hooks that must exist before the query runs.
- `inc/checkout-fields.php` — registers a global `gettext` filter and
  `woocommerce_default_address_fields`, which are also used by My Account address
  forms. Narrowing it is roadmap item **P2.2**, which was not in this scope.

All templates that call into the deferred modules already used `function_exists()`
guards, and those guards were verified rather than assumed.

---

## 9. P2.6 — Cart feature builder

**Files:** `inc/woocommerce.php` → `almasland_get_cart_item_features()`

**Before.** The function walked *every* variation attribute and *every* parent
product attribute, resolved each value (including a `wc_get_product_terms()` query
per taxonomy attribute, plus a `get_term_by()` per variation attribute), collected
them all into an array — and then used only four of them. On a laptop with a dozen
attributes, most of that work was discarded.

**After.** The label is computed first and the attribute is **skipped before its
value is resolved** unless it maps to one of the four labels the function actually
returns (گارانتی / پردازنده / رم / حافظه). The wanted-label set includes both the
literal strings and their `__()` results, so translated installs behave no worse
than before.

The `$attributes` → `$by_label` two-pass structure was collapsed into a single
pass, and the result is memoized per `product_id` + variation signature in the
**request cache only** (the cart template calls this twice — once for cart lines,
once for saved-for-later lines).

**No customer data is cached.** The output depends solely on product and variation
attributes; `$cart_item` is read only for its `variation` array.

Output is byte-identical: the same four labels, same ordering (variation attributes
before parent attributes), same `array_slice(..., 0, 4)` cap, same warranty
fallback string.

---

## 10. P2.7 — Persian digit / price formatting

**Files:** `inc/template-functions.php`, `inc/woocommerce.php`

`almasland_persian_digits()` runs on essentially every number the site renders, and
`almasland_format_wc_price_html()` is hooked to `wc_price` — dozens of calls per
archive page.

| Change | Effect |
|---|---|
| Digit map moved out of the regex callback | It was being **rebuilt on every matched character**; now a function-level `static` |
| Early return when the string contains no ASCII digit | Skips the regex entirely for the common "no numbers" case |
| Fast path via `strtr()` when the string contains no `&` | With no ampersand there is no HTML entity to protect, so a byte map is provably equivalent to the entity-aware regex — and much cheaper |
| `almasland_persian_price()` early return after decoding when no digits remain | Skips the tag-aware `preg_replace_callback`. The return value is still the **decoded** string, so entity decoding behavior is unchanged |
| `almasland_format_wc_price_html()` memoizes by input string (bounded at 500 entries) | The transform is a pure function of its input; archives repeat a small set of distinct price strings |

**Equivalence was verified, not assumed.** The old and new implementations were run
side by side over a corpus of 24 hand-written cases (numeric entities, hex
entities, named entities, `&nbsp;`, nested tags, empty tags, bare ampersands,
mixed Persian/Latin digits, multiline) plus **20,000 randomized fuzz strings** built
from a digit/entity/tag alphabet. Result: **0 mismatches** on both functions.

---

## 11. P2.5 — Contact URL and category navigation

**Files:** `inc/template-functions.php`, `inc/shop-filters.php`

| Item | Before | After |
|---|---|---|
| `almasland_get_contact_url()` | Called from `wp_enqueue_scripts`, the nav walker, the footer, the categories section and the single-product template. Without a configured page ID it ran `get_page_by_path()` **plus** a `WP_Query` title lookup — every call, every page | Request-memoized. The resolved page ID from the slug/title fallback is persisted for 24 h and invalidated on `save_post_page` / page deletion |
| `almasland_get_product_category_image()` | Re-resolved term meta, image URL, image src and srcset for each render — the shop category nav strip calls it once per category | Request-memoized per term ID; resolution logic moved to `almasland_build_product_category_image()` |
| `almasland_get_shop_nav_categories()` | `get_terms()` on each call | Request-memoized |

Category images use the **request cache only**, not transients — image URLs and
srcsets change when thumbnails are regenerated, which is not something the theme
can reliably hook, and the repeated-call cost was the actual problem.

---

## 12. Review, verification and compatibility

### Files changed

| File | Change |
|---|---|
| `inc/cache.php` | **New** — caching layer, invalidation, cache priming |
| `functions.php` | Cache bootstrap; conditional module loading |
| `inc/template-functions.php` | P0.1, P0.2, P0.3, P1.4, P2.5, P2.7 |
| `inc/woocommerce.php` | P1.3, P1.4, P2.6, P2.7 |
| `inc/shop-filters.php` | P1.5, P2.5 |
| `inc/product-fields.php` | P1.4 (delegates to the memoized meta-owner helper) |
| `inc/theme-panel/bootstrap.php` | P2.1 |
| `template-parts/home/section-catalog.php` | P0.2 |
| `woocommerce/content-single-product.php` | P2.1 (module loader call) |

### PHP syntax

`php -l` was run against **every `.php` file in the theme** (PHP 8.2.29):
`FILES_WITH_ERRORS=0`. No linter diagnostics reported on any modified file.

### WooCommerce compatibility

- No WooCommerce template was replaced or bypassed. Related products still render
  through `woocommerce_related_products()` → `single-product/related.php`.
- No WooCommerce hook was removed. Two were added
  (`woocommerce_before_shop_loop` for cache priming, plus the invalidation hooks),
  and one existing call gained an argument it should always have had.
- Filters the theme owns (`loop_shop_per_page`, `loop_shop_columns`,
  `woocommerce_output_related_products_args`, `wc_price`,
  `woocommerce_available_variation`, `woocommerce_product_query`) keep their
  existing priorities and signatures.
- Variable products, variations, upsells, stock status and visibility all resolve
  through the same WooCommerce APIs as before — priming only warms caches those
  APIs already read from.
- No raw SQL was added. The one pre-existing `$wpdb` query
  (`almasland_get_shop_brand_options()`) was left as-is and only had its caching
  improved.

### Templates checked

`header.php`, `footer.php`, `template-parts/home/section-catalog.php`,
`section-special-offers.php`, `section-categories.php`,
`section-product-categories.php`, `woocommerce/content-product.php`,
`woocommerce/content-single-product.php`, `woocommerce/cart/cart.php`,
and the shop category nav renderer — all confirmed to receive the same data
shapes as before (`WP_Term[]`, `WC_Product[]`, feature arrays).

### Cache invalidation checked

- Product create / update / delete / trash / bulk edit → version bump.
- Variation update and every stock-change hook → version bump.
- Scheduled sale start/end → version bump (special offers).
- `product_cat`, `product_tag` and `pa_*` term create/edit/delete → version bump;
  unrelated taxonomies deliberately do not trigger one.
- Contact page save or deletion → version bump.
- Theme panel save → version bump.
- Customizer preview bypasses persistent caching entirely, so previews are live.
- Manual escape hatch: `do_action( 'almasland_flush_cache' )`.

### AJAX checked

The theme registers **no `wp_ajax_*` handlers of its own**; it consumes
WooCommerce's `wc-ajax` endpoints (add to cart, refreshed fragments, variation
forms). Verified:

- `wc_price` memoization is a pure function of its input and is safe inside
  fragment rendering.
- `almasland_get_cart_item_features()` is request-scoped, so nothing persists
  across AJAX requests or between customers.
- Conditionally loaded modules are not needed on any `wc-ajax` or `admin-ajax`
  path — no AJAX code path calls into save-for-later or the health report.
- `almasland_prime_shop_loop_caches()` is guarded against non-product posts, so a
  shortcode-driven product loop on a regular page cannot mis-prime.
- Save-for-later still loads on `wp` (priority 5), which runs before
  `template_redirect` — where both its own handler and WooCommerce's AJAX
  dispatcher live.

---

## 13. Expected impact

Warm-cache, anonymous visitor, relative to the audited baseline:

| Page | Main saving |
|---|---|
| **Every page** | Catalog category tree collapses from three independent resolutions with an N+1 term walk to one cached map + one term query |
| **Front page** | 11 product queries + ~88 per-product reads → 0 product queries + 3 bulk primes; special offers no longer run one `get_post_type()` per sale item |
| **Shop / category** | Brand filtering no longer materializes catalog-wide ID lists; card attachments primed in bulk |
| **Single product** | Related products resolved once instead of twice; ~33 KB health-report module only parsed on used-product pages |
| **Cart** | Attribute values (and their term queries) resolved only for the four labels actually displayed |
| **All rendering** | Persian digit/price conversion short-circuits for digit-free strings and avoids rebuilding its lookup map per character |

### Recommended verification

1. Query Monitor on home, shop, a product category, a single product (new + used),
   cart and checkout — compare query count and time against the audit baseline.
2. Confirm the second run of each page is materially cheaper (cold vs warm cache).
3. Save a product in wp-admin and reload the front page — updated data must appear
   immediately (version bump), confirming invalidation works.
4. Exercise shop filters: brand (meta only, taxonomy only, and both together),
   in-stock, fast shipping, on-sale, price range, category — plus combinations.
5. Add to cart, update quantity, save for later, restore, and complete a checkout.
6. Test a variable product: variation switching, price display, stock messaging.
7. Open the Customizer and confirm changes preview live (persistent caching is
   bypassed there).

---

## 14. Not implemented in this pass

Still open from the audit roadmap:

`P1.1`, `P1.2` (CSS/JS splitting — explicitly out of scope for this step),
`P2.2` (scope the `gettext` filter to checkout), `P2.3`, `P2.4`, `P2.9`,
and all of `P3`.

Note that `P2.8` (invalidate the brand options transient on save) was effectively
delivered as a side effect of the new invalidation layer.
