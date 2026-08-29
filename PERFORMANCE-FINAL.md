# PERFORMANCE-FINAL.md

Final regression and performance audit of the **Almas Land** theme, after the
backend pass (`PERFORMANCE-BACKEND.md`) and the frontend asset pass
(`PERFORMANCE-FRONTEND.md`).

This pass re-read the whole theme, verified the previous two passes actually did
what they claimed, and fixed seven remaining low-risk issues. No design, markup,
SEO output or functionality was changed.

---

## 0. Verification of the previous passes

Every claim in the two earlier reports was checked against the current code
rather than taken on trust.

| Claim | Verdict |
|---|---|
| Category tree memoized + transient-cached, shared header↔footer, `$term->count` cloned | **Confirmed.** One build per request, two slices |
| Homepage catalog tabs resolve cached ID lists + one bulk prime | **Confirmed** |
| Special offers no longer run `get_post_type()` per sale ID | **Confirmed** |
| Related products resolved once (matching `exclude_ids` → one WC transient) | **Confirmed.** Theme and core both pass `$product->get_upsell_ids()` |
| Brand filter uses native `meta_query` / `tax_query` | **Confirmed** for meta-only and taxonomy-only; the mixed case still builds a cached ID union (documented, unavoidable in `WP_Query`) |
| `almasland_is_used_product()` memoized | **Confirmed** |
| `almasland_get_product_meta_owner()` memoizes variation→parent | **Confirmed**, but one caller still bypassed it — **fixed below** |
| `almasland_prime_shop_loop_caches()` on `woocommerce_before_shop_loop` | **Confirmed**, but only effective for the main query — see §2 |
| Cart features resolve only the 4 displayed labels, request-memoized | **Confirmed.** Cache key is product + variation only; no customer data |
| `wc_price` output memoized | **Confirmed**, bounded at 500 entries |
| Save-for-later loads on cart only | **Confirmed** |
| Health report loads on product pages only | **Confirmed but over-broad** — loaded on *all* product pages — **fixed below** |
| CSS/JS split, conditional enqueue, `defer` preserved | **Confirmed.** No dangling reference to `almasland-main`, `assets/css/style.css` or `assets/js/main.js` anywhere in the theme |

The caching layer in `inc/cache.php` was reviewed for correctness: the request
store, the payload wrapping that makes an empty result a cache *hit*, the key
hashing, the one-bump-per-request debounce and the invalidation hook list are all
sound. **No cache-poisoning risk was found** — nothing user, cart, session or
checkout scoped is written to the persistent layer.

---

## 1. Problems fixed in this pass

Seven changes across six files. All are behaviour-preserving.

### 1.1 Used-device health report resolved three times per page — **High**

`inc/used-device-health-report.php`

`almasland_get_used_device_health_report_data()` calls the APSB plugin's
`apsb_get_product_specs()` and then maps its whole field payload. On a used
product page it ran **three times**: once for the availability check
(`content-single-product.php` line 40), once inside the gallery panel, and once
inside the report renderer itself.

It is now memoized per product for the request, with the builder split out:

```php
function almasland_get_used_device_health_report_data( $product ) {
	// ...
	return almasland_cache_remember(
		'used_health_report:' . $product_id,
		static function () use ( $product_id ) {
			return almasland_build_used_device_health_report_data( $product_id );
		}
	);
}
```

`almasland_cache_remember()` uses `array_key_exists`, so a `null` result (product
with no report) is a cache **hit** rather than a permanent miss — otherwise the
memo would have done nothing for exactly the products that call it most.

**Effect:** 3 plugin calls + 3 payload mappings → 1, per used product page.

### 1.2 Health report module parsed on every product page — **Medium**

`functions.php`

The ~33 KB module was `require`d for every `is_product()` request, though only
used products can render a report. Narrowed to used products:

```php
if ( function_exists( 'is_product' ) && is_product() ) {
	$queried = wc_get_product( get_queried_object_id() );

	if ( $queried && almasland_is_used_product( $queried ) ) {
		almasland_load_used_device_health_report();
	}
}
```

This is safe because it is not the only guard: `content-single-product.php`
already loads the module itself for used products before calling into it, and
both render calls sit behind `$has_used_health_report`, which is false whenever
the product is not used. The added `almasland_is_used_product()` call is free —
it is memoized, and the template calls it anyway a few lines later.

**Effect:** ~33 KB of PHP no longer parsed on new-product pages.

### 1.3 Account dashboard loaded the customer's entire order history twice — **High**

`inc/woocommerce.php`

`almasland_get_account_order_stats()` ran two `wc_get_orders()` queries with
`limit => -1` purely to `count()` the returned arrays. For a customer with 800
orders that is 800 rows materialized twice to display two numbers.

```php
function almasland_count_customer_orders( $user_id, array $statuses ) {
	$results = wc_get_orders(
		array(
			'customer' => $user_id,
			'limit'    => 1,
			'paginate' => true,
			'return'   => 'ids',
			'status'   => $statuses,
		)
	);

	return isset( $results->total ) ? (int) $results->total : 0;
}
```

`paginate => true` makes WooCommerce return the true total alongside one row, so
**the displayed numbers are identical** — only the row transfer disappears.

**Effect:** two unbounded result sets → two counted queries. The saving scales
with the customer's order history.

### 1.4 `gettext` filter ran on every translated string, site-wide — **Medium** (roadmap P2.2)

`inc/checkout-fields.php`

`almasland_checkout_gettext()` was registered globally and called `is_checkout()`
as its first statement. `gettext` fires for **every translated string on every
request** — menus, widgets, WooCommerce, admin screens — so the theme was running
a WooCommerce conditional tag thousands of times per page to answer "no" almost
every time. Calling a conditional tag from `gettext` is also fragile, since
`gettext` can fire before the query is set up.

The condition moved from the callback to the registration:

```php
function almasland_register_checkout_gettext() {
	if ( ! function_exists( 'is_checkout' ) || ! is_checkout() || is_wc_endpoint_url( 'order-received' ) ) {
		return;
	}

	add_filter( 'gettext', 'almasland_checkout_gettext', 20, 3 );
}
add_action( 'wp', 'almasland_register_checkout_gettext' );
```

The callback keeps its domain check and map lookup unchanged. `wp` resolves the
page before `template_redirect`, where checkout markup is rendered, so no string
is missed. Behaviour on `wc-ajax` requests is unchanged too: `is_checkout()`
returned false there under the old code as well, so the same strings were already
untranslated by this filter.

**Effect:** the filter is now absent from the `gettext` chain on every page
except the checkout.

### 1.5 Twelve `remove_action()` calls on every request — **Low/Medium** (roadmap P3.1)

`inc/woocommerce.php`

`almasland_remove_default_wc_single_hooks()` was hooked to `wp` with no guard, so
it stripped single-product hooks on the blog, cart, checkout and every other
screen. Those hooks only ever fire while rendering a single product:

```php
function almasland_remove_default_wc_single_hooks() {
	if ( ! is_product() ) {
		return;
	}
	// ...
}
```

### 1.6 Dead duplicate shop loop filters — **Low** (roadmap P2.3)

`inc/woocommerce.php`

`loop_shop_per_page` and `loop_shop_columns` each had **two** handlers: hardcoded
`12` / `4` at priority 10 here, and the theme-panel values at priority 20 in
`functions.php`. The priority-20 handlers ignore the value passed to them, so the
hardcoded pair could never affect output — they just ran and were overwritten.
Removed, with a comment recording where the filters now live.

**No behaviour change:** the effective values were already the panel's.

### 1.7 Card title bypassed the memoized meta owner — **Low**

`inc/product-fields.php`

`almasland_get_product_card_title()` did its own `wc_get_product( $parent_id )`
for variations, while every sibling helper went through the memoized
`almasland_get_product_meta_owner()`. Routed through the same helper, so a loop of
variation cards resolves each parent once instead of once per card.

---

## 2. Problems remaining

Nothing here is a regression; these are pre-existing costs that were out of scope
for a "no architectural changes" pass, or that cannot be fixed without altering
functionality or design.

### High

| Issue | File | Why it is not fixed here |
|---|---|---|
| **Variation JSON bloat.** `almasland_available_variation_price_html()` adds `almas_price_html` to every available variation, on top of WooCommerce's own `price_html`. A 50-variation product carries roughly 8–20 KB of extra HTML in the `data-product_variations` attribute | `inc/woocommerce.php` | It adds no DB queries (variations are already bulk-primed), but removing it would change the variation price UI. Fixing it properly means fetching price HTML on selection instead of up front — an architectural change |

### Medium

| Issue | File | Notes |
|---|---|---|
| **Secondary loops are not cache-primed.** `almasland_prime_shop_loop_caches()` keys off the global `$wp_query`, so it primes the main archive loop but does nothing for cart cross-sells or a `[products]` shortcode on a regular page. Related products and the homepage loops prime explicitly, so they are covered | `inc/woocommerce.php` | Fix is a per-loop prime at each call site |
| **`on_sale` filter loads every sale ID.** `wc_get_product_ids_on_sale()` feeds the whole list into `post__in` on each filtered view | `inc/shop-filters.php` | WooCommerce caches this list, but it is unbounded on large catalogs |
| **Mixed meta+tax brand filter** still uses `posts_per_page => -1`. This is now the only `-1` query left in the theme, and it is transient-cached | `inc/shop-filters.php` | `WP_Query` cannot express `OR` between a meta clause and a tax clause |
| **Saved-for-later cart loop** calls `wc_get_product()` per saved item — a genuine N+1, though the list is normally short | `woocommerce/cart/cart.php` | One `almasland_prime_product_caches()` before the loop would fix it |
| **Oversized card images.** `almasland-card` is 640×520 and renders into roughly 250–300 px slots. The hover secondary image roughly doubles image bytes per card | `functions.php`, `inc/template-functions.php` | Both are lazy + async-decoded already. Changing the size is a visual-risk change and needs thumbnail regeneration |
| **Header logos at `full` size**, rendered up to three times (desktop light, desktop dark, mobile) | `inc/template-functions.php` | Roadmap P2.4. A registered logo size would help, but picking one risks changing how the logo renders — a design decision |
| **Attribute specs.** `almasland_get_product_attribute_specs()` can issue a term query per taxonomy attribute when `get_attribute()` comes back empty | `inc/product-fields.php` | Scales with attribute count on every product view |

### Low

| Issue | File |
|---|---|
| "Already in cart" check iterates the whole cart (roadmap P3.3). In-memory only — no queries — so the cost is negligible for realistic carts | `woocommerce/content-single-product.php` |
| `almasland_related_products_args()` is hooked to `woocommerce_output_related_products_args`, but the theme calls `woocommerce_related_products()` directly with hardcoded args, so the filter never fires. Dead, and misleading to a maintainer | `inc/woocommerce.php` |
| Dead functions: `almasland_social_links()`, `almasland_shop_result_count()`, `almasland_get_shop_category_options()` | `inc/template-functions.php`, `inc/shop-filters.php` |
| Shop filters are skipped on product search (`is_shop() \|\| is_product_taxonomy()`) while the out-of-stock sort does apply there. An inconsistency, not a performance issue | `inc/shop-filters.php` |
| Footer falls back to an uncached `get_terms()` when the catalog helper returns empty. The footer renders once per request, so there is no duplication — it is one extra query on stores with no counted categories | `footer.php` |
| Brand options transient uses a fixed key outside the versioned namespace. It is explicitly deleted on invalidation, so this is a consistency wart rather than a bug | `inc/shop-filters.php` |
| `save_post_page` bumps the whole catalog cache version, so editing an unrelated page invalidates catalog transients | `inc/cache.php` |
| Contact URL can go stale if the contact page is **trashed** rather than deleted, since trashing does not fire `deleted_post` | `inc/template-functions.php` |
| Swiper ships as the full 148 KB bundle on the front page though only navigation, pagination and autoplay are used | `assets/vendor/swiper/` |
| The `almasland-theme` handle still requests the root `style.css`, which contains only the theme header. Kept because `get_stylesheet_uri()` is how a child theme's stylesheet loads | `functions.php` |

**Deliberately left alone:** `almasland_sanitize_shop_price_query_vars()` is
registered on both `init` and `parse_request` (roadmap P3.2). The function is
idempotent and costs two `isset()` checks; `init` fires first and is what
actually protects early consumers of `$_GET`, so dropping either registration
buys nothing measurable and only risks an ordering change.

---

## 3. Before / after expected impact

### This pass

| Surface | Change |
|---|---|
| **Every page** | The `gettext` filter leaves the translation chain entirely; twelve `remove_action()` calls and two dead shop filters no longer run |
| **Single product (new)** | ~33 KB of PHP no longer parsed |
| **Single product (used)** | Health report resolved once instead of three times — three fewer APSB calls and payload mappings |
| **My account dashboard** | Two unbounded order-history queries become two counted queries; scales with the customer's order count |
| **Product loops with variations** | Parent product resolved once per parent instead of once per card |

### Cumulative across all three passes

Relative to the audited baseline, warm cache, anonymous visitor:

| Surface | Backend | Frontend assets |
|---|---|---|
| **Every page** | Category tree: three N+1 term walks → one cached map + one query. Contact URL memoized. Persian digit conversion short-circuits | 325 KB → 127 KB on a plain page |
| **Front page** | 11 product queries + ~88 per-product reads → 0 queries + 3 bulk primes; sale-ID `get_post_type()` loop removed | 365 KB → 177 KB (−52%) |
| **Shop / category** | Brand filtering no longer materializes catalog-wide ID lists; card attachments primed in bulk | 325 KB → 150 KB (−54%) |
| **Single product** | Related products resolved once instead of twice; health report once instead of three times; module parsing scoped to used products | 325 KB → 188 KB (−42%) |
| **Cart** | Attribute values resolved only for the four displayed labels | 325 KB → 200 KB (−38%) |
| **Checkout** | `gettext` filter no longer taxes every other page | 325 KB → 199 KB (−39%) |
| **My account** | Order counts no longer load full history | 325 KB → 153 KB (−53%) |

**These are structural expectations, not measurements.** The asset numbers are
exact byte counts; the query and PHP-time numbers are reasoned from call counts
in the code. See §5 for what has to be measured on a live site.

---

## 4. Potential bottlenecks outside the theme

The theme is now a small share of a typical request. These dominate what is left,
and none of them can be fixed from inside the theme.

| Area | Why it matters | How to check |
|---|---|---|
| **No persistent object cache** | Every transient the new caching layer writes goes to the `wp_options` table. With Redis or Memcached the same reads are in-memory, and WooCommerce's own product/related/sale-ID transients get much cheaper too. This is probably the single highest-leverage change available | `wp_using_ext_object_cache()`; Query Monitor's Object Cache panel |
| **APSB plugin** (`apsb_get_product_specs`) | The theme now calls it once per request instead of three times, but its own cost is unmeasured. If it queries per call, it is still on the used-product critical path | Query Monitor, grouped by component |
| **WooCommerce order storage (HPOS vs posts)** | The account dashboard counts are far cheaper against HPOS order tables than against `wp_posts` + `wp_postmeta` | WooCommerce → Settings → Advanced → Features |
| **`wc_product_meta_lookup` table** | The out-of-stock sort joins it; without it the code falls back to a `wp_postmeta` join on every catalog query, which is much worse (roadmap P2.9) | WooCommerce → Status → Tools → regenerate lookup table |
| **Other plugins' assets** | The CSS/JS split only controls *theme* assets. A plugin enqueueing globally can easily exceed what the split saved | Lighthouse "Reduce unused CSS/JS"; Query Monitor Scripts/Styles panels |
| **WooCommerce cart fragments** | WooCommerce's `wc-ajax=get_refreshed_fragments` fires on page load and is uncacheable, often the slowest request on an otherwise cached page | Network tab, filter `wc-ajax` |
| **No page cache / CDN** | All the PHP work above is skipped entirely for anonymous visitors behind a full-page cache | Response headers |
| **Host: PHP version, OPcache, MySQL** | OPcache off makes every "parse fewer KB of PHP" saving much larger than estimated, and signals a bigger problem | Query Monitor Environment panel |
| **Images** | No WebP/AVIF conversion or CDN. Product images are the bulk of catalog page bytes | Lighthouse |
| **Search** | Product search runs the default `LIKE` query. On a large catalog this is slow regardless of theme code | Query Monitor on a search URL |

---

## 5. What cannot be verified by static analysis

Everything below needs a running site. I want to be explicit that **none of the
work in these three passes has been executed** — it has been verified
structurally (PHP linting, CSS round-trip and cascade checks, per-page CSS class
coverage, JS line coverage and collision checks), which is a different and weaker
guarantee than "it works".

### Needs Query Monitor

1. **Actual query count and time** on home, shop, a product category, a single
   product (new **and** used), cart, checkout and the account dashboard — the
   numbers to compare against the audit baseline.
2. **Cold vs warm cache.** Load each page twice. The second load should be
   materially cheaper; if it is not, the transients are not persisting and the
   object-cache question in §4 is the real story.
3. **Cache invalidation.** Save a product in wp-admin, reload the front page, and
   confirm the change appears immediately.
4. **Whether the remaining unbounded queries actually hurt** — the sale-ID list
   and the mixed-brand union depend entirely on catalog size.
5. **Slow query log** for the brand `GROUP BY` on `wp_postmeta` on a cold cache.
6. **Component attribution** — how much of the remaining time is theme versus
   plugins versus WooCommerce core.
7. **PHP error log** on every page type. Static analysis cannot catch a
   `_doing_it_wrong` notice, a deprecation, or an undefined index that only
   triggers with real data.

### Needs Lighthouse / WebPageTest

8. **FCP and LCP before and after the CSS split.** The byte reduction is certain;
   the effect on paint timing is not, and depends on the network and the LCP
   element.
9. **Whether `base.css` at 102 KB is still the render-blocking bottleneck**, and
   whether critical-CSS inlining would be worth it.
10. **Total unused CSS/JS including plugins.**
11. **CLS**, particularly around the lazy-loaded product card images and sliders.

### Needs manual browser testing

12. **Visual parity of the CSS split.** The coverage checker proves every class a
    template renders has rules in a loaded module. It does **not** prove the
    rendering is pixel-identical. Every page type must be compared in light and
    dark mode, and in RTL.
13. **The JS modules actually run.** Collision checking and line coverage are
    static. Confirm no console error mentions `AlmasLand` or a redeclared
    identifier, and exercise: mobile navigation, product gallery, variation
    switching, AJAX add to cart, cart quantity controls, checkout sticky bar,
    catalog tabs, tooltips, notifications.
14. **Checkout string translations**, specifically after moving the `gettext`
    registration — check the checkout page, then change shipping or address to
    trigger `update_order_review`, and confirm nothing reverted to English.
15. **Used vs new product pages** after narrowing the health-report module load —
    the report must still render on used products, and new products must show the
    standard spec list.
16. **The account dashboard counts** — verify the two numbers match the real order
    count for a customer with a substantial history.
17. **Shop filter combinations**: brand (meta only, taxonomy only, and both
    together), in-stock, fast shipping, on-sale, price range, category, and
    combinations, plus pagination and sorting.
18. **A complete order**, end to end.
19. **Plugin interactions with the renamed handles.** `almasland-main` no longer
    exists; anything enqueueing against it will silently fail.
20. **Caching/optimisation plugin config.** `assets/css/style.css` and
    `assets/js/main.js` no longer exist at those paths.

---

## 6. Recommended next investigation

In priority order.

1. **Confirm whether a persistent object cache is running.** Everything else is
   secondary. If transients are hitting the options table, installing Redis is a
   larger win than any remaining theme change, and it changes the value of every
   caching decision made in these three passes.
2. **Capture real Query Monitor numbers** for the seven page types and compare
   against the audit's estimates. The remaining items in §2 are ranked by
   reasoning, not measurement, and measurement will very likely reorder them.
3. **Profile the used-product page**, now that the health report resolves once.
   If `apsb_get_product_specs()` is still expensive, caching its payload in the
   theme's persistent layer is the obvious next step — but only with a clear
   invalidation hook from the plugin.
4. **Measure a worst-case variable product** (30+ variations). If the variation
   JSON dominates TTFB or HTML size, moving `almas_price_html` to an on-selection
   fetch is the biggest remaining structural win — and the only one that needs a
   real design decision.
5. **Check `wc_product_meta_lookup`** exists and is populated (P2.9). Cheap to
   verify, and the fallback path is significantly worse.
6. **Then, if still needed**, revisit the deferred items: prime the cross-sell and
   shortcode loops, prime the saved-for-later loop, right-size the card and logo
   images, and consider critical-CSS inlining.

Do **not** start on item 6 before items 1 and 2. The theme is now well inside the
range where infrastructure and plugins dominate, and further micro-optimization
without measurement is likely to cost more in risk than it returns in time.

---

## 7. Files changed in this pass

| File | Change |
|---|---|
| `functions.php` | Health report module load narrowed to used products |
| `inc/used-device-health-report.php` | Report data memoized per product; builder split out |
| `inc/woocommerce.php` | `almasland_count_customer_orders()` added; dashboard stats no longer load full history; `is_product()` guard on the single-product hook removal; dead duplicate loop filters removed |
| `inc/checkout-fields.php` | `gettext` filter registered on checkout only |
| `inc/product-fields.php` | Card title routed through the memoized meta owner |
| `PERFORMANCE-FINAL.md` | This report |

### Verification run

| Check | Result |
|---|---|
| `php -l` on every `.php` file in the theme (PHP 8.2.29) | `FILES_WITH_ERRORS=0` |
| Linter diagnostics on all modified files | None |
| CSS round trip (`tools/split-css.mjs`) | `missing=0 extra=0` over 1832 rules |
| CSS cascade conflicts | 0 |
| Per-page CSS class coverage (`tools/verify-css-coverage.mjs`) | `TOTAL_UNCOVERED=0` across 9 page contexts |
| JS line coverage / global collisions (`tools/verify-js-split.mjs`) | 8 intentional rewrites, `GLOBAL_COLLISIONS=0` |
| References to removed asset handles or paths | None |
| References to removed functions | None |
