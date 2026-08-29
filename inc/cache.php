<?php
/**
 * Request-level and persistent caching helpers.
 *
 * Two layers are provided:
 *
 * 1. Request cache — a plain static array. Free, always safe, cleared per request.
 * 2. Persistent cache — transients namespaced by a version counter, so a single
 *    counter bump invalidates every derived key without deleting rows one by one.
 *
 * User, cart, session and checkout data must never be stored here.
 *
 * @package AlmasLand
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Option holding the catalog cache version counter.
 */
const ALMASLAND_CACHE_VERSION_OPTION = 'almasland_cache_version';

/**
 * Default lifetime for catalog transients.
 */
const ALMASLAND_CACHE_TTL = 6 * HOUR_IN_SECONDS;

/**
 * Read/write the per-request cache store.
 *
 * @return array<string, mixed>
 */
function &almasland_cache_store() {
	static $store = array();

	return $store;
}

/**
 * Whether a request-level key exists.
 *
 * @param string $key Cache key.
 * @return bool
 */
function almasland_cache_has( $key ) {
	$store = &almasland_cache_store();

	return array_key_exists( $key, $store );
}

/**
 * Get a request-level cached value.
 *
 * @param string $key     Cache key.
 * @param mixed  $default Fallback when missing.
 * @return mixed
 */
function almasland_cache_get( $key, $default = null ) {
	$store = &almasland_cache_store();

	return array_key_exists( $key, $store ) ? $store[ $key ] : $default;
}

/**
 * Store a request-level value.
 *
 * @param string $key   Cache key.
 * @param mixed  $value Value.
 * @return mixed The stored value.
 */
function almasland_cache_set( $key, $value ) {
	$store         = &almasland_cache_store();
	$store[ $key ] = $value;

	return $value;
}

/**
 * Forget one request-level key, or the whole store when no key is given.
 *
 * @param string|null $key Cache key.
 * @return void
 */
function almasland_cache_forget( $key = null ) {
	$store = &almasland_cache_store();

	if ( null === $key ) {
		$store = array();
		return;
	}

	unset( $store[ $key ] );
}

/**
 * Memoize a callback for the current request.
 *
 * @param string   $key      Cache key.
 * @param callable $callback Producer.
 * @return mixed
 */
function almasland_cache_remember( $key, callable $callback ) {
	if ( almasland_cache_has( $key ) ) {
		return almasland_cache_get( $key );
	}

	return almasland_cache_set( $key, $callback() );
}

/**
 * Current catalog cache version.
 *
 * @return int
 */
function almasland_cache_version() {
	return (int) almasland_cache_remember(
		'cache_version',
		static function () {
			$version = (int) get_option( ALMASLAND_CACHE_VERSION_OPTION, 0 );

			return $version > 0 ? $version : 1;
		}
	);
}

/**
 * Invalidate every versioned transient by bumping the counter.
 *
 * Stale rows expire on their own TTL, so no bulk delete is required.
 *
 * @return void
 */
function almasland_cache_flush() {
	$version = almasland_cache_version() + 1;

	update_option( ALMASLAND_CACHE_VERSION_OPTION, $version, true );

	almasland_cache_forget();
	almasland_cache_set( 'cache_version', $version );
}

/**
 * Whether persistent caching should be used for this request.
 *
 * @return bool
 */
function almasland_cache_is_persistent_enabled() {
	if ( is_customize_preview() ) {
		return false;
	}

	/**
	 * Toggle theme transient caching.
	 *
	 * @param bool $enabled Whether persistent caching is active.
	 */
	return (bool) apply_filters( 'almasland_enable_persistent_cache', true );
}

/**
 * Build a versioned transient key.
 *
 * Transient names are limited to 172 characters, so long keys are hashed.
 *
 * @param string $key Logical key.
 * @return string
 */
function almasland_cache_transient_key( $key ) {
	$name = 'almasland_v' . almasland_cache_version() . '_' . $key;

	if ( strlen( $name ) > 160 ) {
		$name = 'almasland_v' . almasland_cache_version() . '_' . md5( $key );
	}

	return $name;
}

/**
 * Remember a value in the transient cache, backed by the request cache.
 *
 * The callback result is cached even when empty, so repeated misses do not
 * re-run expensive queries. Use `almasland_cache_flush()` to invalidate.
 *
 * @param string   $key      Logical cache key.
 * @param callable $callback Producer.
 * @param int|null $ttl      Lifetime in seconds.
 * @return mixed
 */
function almasland_cache_remember_persistent( $key, callable $callback, $ttl = null ) {
	$request_key = 'persistent:' . $key;

	if ( almasland_cache_has( $request_key ) ) {
		return almasland_cache_get( $request_key );
	}

	if ( ! almasland_cache_is_persistent_enabled() ) {
		return almasland_cache_set( $request_key, $callback() );
	}

	$transient_key = almasland_cache_transient_key( $key );
	$cached        = get_transient( $transient_key );

	// Values are wrapped so a legitimately falsy payload is not treated as a miss.
	if ( is_array( $cached ) && array_key_exists( 'almasland_payload', $cached ) ) {
		return almasland_cache_set( $request_key, $cached['almasland_payload'] );
	}

	$value = $callback();

	set_transient(
		$transient_key,
		array( 'almasland_payload' => $value ),
		null === $ttl ? ALMASLAND_CACHE_TTL : max( 60, (int) $ttl )
	);

	return almasland_cache_set( $request_key, $value );
}

/**
 * Flush caches when catalog content changes.
 *
 * @return void
 */
function almasland_cache_invalidate_catalog() {
	// One bump covers every change made in the same request (e.g. bulk imports).
	if ( almasland_cache_get( 'catalog_flushed' ) ) {
		return;
	}

	almasland_cache_flush();
	almasland_cache_set( 'catalog_flushed', true );

	// Brand options predate the versioned cache and are keyed by a fixed name.
	delete_transient( 'almasland_shop_brand_options' );
}

/**
 * Register cache invalidation hooks.
 *
 * @return void
 */
function almasland_cache_register_invalidation() {
	$post_hooks = array(
		'save_post_product',
		'woocommerce_update_product',
		'woocommerce_new_product',
		'woocommerce_delete_product',
		'woocommerce_trash_product',
		'woocommerce_update_product_variation',
		'woocommerce_product_set_stock',
		'woocommerce_variation_set_stock',
		'woocommerce_product_set_stock_status',
		'woocommerce_variation_set_stock_status',
		'wc_after_products_starting_sales',
		'wc_after_products_ending_sales',
		'save_post_page',
	);

	foreach ( $post_hooks as $hook ) {
		add_action( $hook, 'almasland_cache_invalidate_catalog' );
	}

	$term_hooks = array(
		'created_term',
		'edited_term',
		'delete_term',
	);

	foreach ( $term_hooks as $hook ) {
		add_action( $hook, 'almasland_cache_invalidate_term', 10, 3 );
	}

	add_action( 'deleted_post', 'almasland_cache_invalidate_post', 10, 2 );
	add_action( 'update_option_almasland_theme_panel', 'almasland_cache_invalidate_catalog' );
	add_action( 'almasland_flush_cache', 'almasland_cache_invalidate_catalog' );
}
add_action( 'init', 'almasland_cache_register_invalidation', 1 );

/**
 * Invalidate only when a product-related taxonomy changed.
 *
 * @param int    $term_id  Term ID.
 * @param int    $tt_id    Term taxonomy ID.
 * @param string $taxonomy Taxonomy name.
 * @return void
 */
function almasland_cache_invalidate_term( $term_id, $tt_id, $taxonomy ) {
	unset( $term_id, $tt_id );

	if ( ! is_string( $taxonomy ) ) {
		return;
	}

	if ( 'product_cat' !== $taxonomy && 'product_tag' !== $taxonomy && 0 !== strpos( $taxonomy, 'pa_' ) ) {
		return;
	}

	almasland_cache_invalidate_catalog();
}

/**
 * Invalidate when a product or page is permanently deleted.
 *
 * @param int          $post_id Post ID.
 * @param WP_Post|null $post    Post object.
 * @return void
 */
function almasland_cache_invalidate_post( $post_id, $post = null ) {
	$post_type = $post instanceof WP_Post ? $post->post_type : get_post_type( $post_id );

	if ( 'product' !== $post_type && 'product_variation' !== $post_type && 'page' !== $post_type ) {
		return;
	}

	almasland_cache_invalidate_catalog();
}

/**
 * Prime post, meta and term caches for a set of product IDs.
 *
 * Loading products one by one is the main source of N+1 queries in loops. A
 * single prime turns those into three bulk queries.
 *
 * @param int[] $product_ids Product IDs.
 * @return void
 */
function almasland_prime_product_caches( $product_ids ) {
	$product_ids = array_values( array_unique( array_filter( array_map( 'absint', (array) $product_ids ) ) ) );

	if ( empty( $product_ids ) ) {
		return;
	}

	$pending = array();

	foreach ( $product_ids as $product_id ) {
		if ( ! almasland_cache_has( 'primed_product:' . $product_id ) ) {
			$pending[] = $product_id;
			almasland_cache_set( 'primed_product:' . $product_id, true );
		}
	}

	if ( empty( $pending ) ) {
		return;
	}

	_prime_post_caches( $pending, false, true );
	update_object_term_cache( $pending, 'product' );

	almasland_prime_product_image_caches( $pending );
}

/**
 * Prime the attachment caches used by product cards.
 *
 * Cards render a featured image plus the first gallery image for the hover
 * swap; without this, each one is a separate attachment lookup.
 *
 * @param int[] $product_ids Product IDs with primed meta.
 * @return void
 */
function almasland_prime_product_image_caches( array $product_ids ) {
	$attachment_ids = array();

	foreach ( $product_ids as $product_id ) {
		$thumbnail_id = (int) get_post_meta( $product_id, '_thumbnail_id', true );

		if ( $thumbnail_id > 0 ) {
			$attachment_ids[] = $thumbnail_id;
		}

		$gallery = get_post_meta( $product_id, '_product_image_gallery', true );

		if ( is_string( $gallery ) && '' !== $gallery ) {
			$parts = explode( ',', $gallery, 2 );
			$first = absint( $parts[0] );

			if ( $first > 0 ) {
				$attachment_ids[] = $first;
			}
		}
	}

	$attachment_ids = array_values( array_unique( $attachment_ids ) );

	if ( $attachment_ids ) {
		_prime_post_caches( $attachment_ids, false, true );
	}
}
