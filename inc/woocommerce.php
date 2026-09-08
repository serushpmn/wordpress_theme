<?php
/**
 * WooCommerce integration.
 *
 * @package AlmasLand
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Use theme styles instead of default WooCommerce CSS.
 *
 * @param array $styles Registered styles.
 * @return array
 */
function almasland_dequeue_woocommerce_styles( $styles ) {
	unset( $styles['woocommerce-general'] );
	unset( $styles['woocommerce-layout'] );
	unset( $styles['woocommerce-smallscreen'] );

	return $styles;
}
add_filter( 'woocommerce_enqueue_styles', 'almasland_dequeue_woocommerce_styles' );

/**
 * Render WooCommerce prices with Persian digits and readable currency text.
 *
 * @param string $price_html Formatted price HTML.
 * @return string
 */
function almasland_format_wc_price_html( $price_html ) {
	if ( ! is_string( $price_html ) || '' === $price_html ) {
		return almasland_persian_price( $price_html );
	}

	// wc_price() fires dozens of times per archive with a small set of distinct
	// strings, and the transform depends only on its input.
	static $memo = array();

	if ( ! isset( $memo[ $price_html ] ) ) {
		if ( count( $memo ) > 500 ) {
			$memo = array();
		}

		$memo[ $price_html ] = almasland_persian_price( $price_html );
	}

	return $memo[ $price_html ];
}
add_filter( 'wc_price', 'almasland_format_wc_price_html', 20 );

/**
 * Lowercase text safely on hosts without mbstring.
 *
 * @param string $value Text to normalize.
 * @return string
 */
function almasland_normalize_search_text( $value ) {
	$value = wp_strip_all_tags( (string) $value );

	return function_exists( 'mb_strtolower' ) ? mb_strtolower( $value, 'UTF-8' ) : strtolower( $value );
}

/**
 * UTF-8 aware contains check with a non-mbstring fallback.
 *
 * @param string $haystack Text to search in.
 * @param string $needle   Text to find.
 * @return bool
 */
function almasland_search_text_contains( $haystack, $needle ) {
	if ( '' === $needle ) {
		return true;
	}

	return function_exists( 'mb_strpos' ) ? false !== mb_strpos( $haystack, $needle, 0, 'UTF-8' ) : false !== strpos( $haystack, $needle );
}

/**
 * Dequeue WooCommerce block styles on the front end.
 */
function almasland_dequeue_wc_block_styles() {
	if ( is_admin() ) {
		return;
	}

	wp_dequeue_style( 'wc-blocks-style' );
	wp_dequeue_style( 'wc-blocks-vendors-style' );
}
add_action( 'wp_enqueue_scripts', 'almasland_dequeue_wc_block_styles', 100 );

/**
 * Remove default single-product hooks replaced by the theme template.
 *
 * These hooks only ever fire while rendering a single product, so there is
 * nothing to remove anywhere else.
 */
function almasland_remove_default_wc_single_hooks() {
	if ( ! is_product() ) {
		return;
	}

	remove_action( 'woocommerce_before_single_product_summary', 'woocommerce_show_product_sale_flash', 10 );
	remove_action( 'woocommerce_before_single_product_summary', 'woocommerce_show_product_images', 20 );
	remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_title', 5 );
	remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_rating', 10 );
	remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_price', 10 );
	remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_excerpt', 20 );
	remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_add_to_cart', 30 );
	remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_meta', 40 );
	remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_sharing', 50 );
	remove_action( 'woocommerce_after_single_product_summary', 'woocommerce_output_product_data_tabs', 10 );
	remove_action( 'woocommerce_after_single_product_summary', 'woocommerce_upsell_display', 15 );
	remove_action( 'woocommerce_after_single_product_summary', 'woocommerce_output_related_products', 20 );
}
add_action( 'wp', 'almasland_remove_default_wc_single_hooks' );

/**
 * Product loop wrapper classes aligned with HTML prototypes.
 *
 * @param string $html Default loop start markup.
 * @return string
 */
function almasland_product_loop_start( $html ) {
	$classes = array( 'products', 'product-grid' );

	if ( is_shop() || is_product_taxonomy() ) {
		$classes[] = 'category-product-grid';
	}

	if ( is_front_page() ) {
		$classes[] = 'product-grid--home';
	}

	if ( wc_get_loop_prop( 'is_related' ) ) {
		$classes[] = 'product-grid--related';
	}

	return '<ul class="' . esc_attr( implode( ' ', array_unique( $classes ) ) ) . '">';
}
add_filter( 'woocommerce_product_loop_start', 'almasland_product_loop_start' );

/**
 * Compact card class for related products.
 *
 * @param string[]   $classes CSS classes.
 * @param WC_Product $product Product object.
 * @return string[]
 */
function almasland_related_product_post_class( $classes, $product ) {
	if ( wc_get_loop_prop( 'is_related' ) ) {
		$classes[] = 'product-card--compact';
	}

	return $classes;
}
add_filter( 'woocommerce_post_class', 'almasland_related_product_post_class', 10, 2 );

/**
 * Replace loop add-to-cart with product view link like the HTML prototype.
 *
 * @param string     $html    Button HTML.
 * @param WC_Product $product Product object.
 * @param array      $args    Arguments.
 * @return string
 */
function almasland_loop_add_to_cart_link( $html, $product, $args ) {
	if ( ! $product->is_purchasable() || ! $product->is_in_stock() ) {
		return sprintf(
			'<a class="btn btn--ghost btn--small" href="%s">%s</a>',
			esc_url( $product->get_permalink() ),
			esc_html__( 'مشاهده', 'almas-land' )
		);
	}

	return sprintf(
		'<a class="btn btn--primary btn--small" href="%s">%s</a>',
		esc_url( $product->get_permalink() ),
		esc_html__( 'مشاهده و خرید', 'almas-land' )
	);
}
add_filter( 'woocommerce_loop_add_to_cart_link', 'almasland_loop_add_to_cart_link', 10, 3 );

/**
 * Style single add-to-cart button text.
 *
 * @return string
 */
function almasland_single_add_to_cart_text() {
	return __( 'افزودن به سبد خرید', 'almas-land' );
}
add_filter( 'woocommerce_product_single_add_to_cart_text', 'almasland_single_add_to_cart_text' );
add_filter( 'woocommerce_product_add_to_cart_text', 'almasland_single_add_to_cart_text' );

/**
 * Header cart fragment.
 *
 * @param array $fragments Fragments.
 * @return array
 */
function almasland_cart_fragments( $fragments ) {
	if ( ! WC()->cart ) {
		return $fragments;
	}

	ob_start();
	?>
	<span class="cart-count" data-cart-count><?php echo esc_html( almasland_persian_digits( WC()->cart->get_cart_contents_count() ) ); ?></span>
	<?php
	$fragments['span.cart-count'] = ob_get_clean();

	return $fragments;
}
add_filter( 'woocommerce_add_to_cart_fragments', 'almasland_cart_fragments' );

/*
 * `loop_shop_columns` and `loop_shop_per_page` are owned by
 * almasland_shop_columns() / almasland_shop_per_page() in functions.php, which
 * read the theme panel at priority 20. The hardcoded 4 / 12 handlers that used
 * to live here ran at priority 10 and were overwritten every time.
 */

/**
 * Prime post/term/attachment caches for the archive product loop.
 *
 * WP_Query primes posts, meta and terms, but not the attachments each card
 * renders, which would otherwise be one lookup per image.
 *
 * @return void
 */
function almasland_prime_shop_loop_caches() {
	global $wp_query;

	if ( ! $wp_query instanceof WP_Query || empty( $wp_query->posts ) ) {
		return;
	}

	$ids = array();

	foreach ( $wp_query->posts as $loop_post ) {
		if ( ! is_object( $loop_post ) ) {
			$ids[] = (int) $loop_post;
			continue;
		}

		// Shortcode loops can run against a non-product query.
		if ( isset( $loop_post->post_type ) && 'product' !== $loop_post->post_type ) {
			continue;
		}

		$ids[] = (int) $loop_post->ID;
	}

	almasland_prime_product_caches( $ids );
}
add_action( 'woocommerce_before_shop_loop', 'almasland_prime_shop_loop_caches', 5 );

/**
 * How many related products to show on the single product page.
 *
 * @return int
 */
function almasland_related_products_limit() {
	return (int) apply_filters( 'almasland_related_products_limit', 8 );
}

/**
 * Related products args (kept for any core / plugin path that still uses it).
 *
 * @param array $args Args.
 * @return array
 */
function almasland_related_products_args( $args ) {
	$limit = almasland_related_products_limit();

	$args['posts_per_page'] = $limit;
	$args['columns']        = 4;

	return $args;
}
add_filter( 'woocommerce_output_related_products_args', 'almasland_related_products_args' );

/**
 * Replace WooCommerce's category/tag related list with the theme's ranking.
 *
 * Priority: same brand + same category, then similar price (in stock only).
 *
 * @param int[] $related_posts Related product IDs.
 * @param int   $product_id    Product ID.
 * @param array $args          Query args (limit, excluded_ids, …).
 * @return int[]
 */
function almasland_filter_related_product_ids( $related_posts, $product_id, $args ) {
	unset( $related_posts );

	$product = wc_get_product( $product_id );
	if ( ! $product instanceof WC_Product ) {
		return array();
	}

	$limit   = isset( $args['limit'] ) ? max( 1, (int) $args['limit'] ) : almasland_related_products_limit();
	$exclude = isset( $args['excluded_ids'] ) ? array_map( 'absint', (array) $args['excluded_ids'] ) : array();

	return almasland_get_related_product_ids( $product, $limit, $exclude );
}
add_filter( 'woocommerce_related_products', 'almasland_filter_related_product_ids', 10, 3 );

/**
 * Build related product IDs with the theme ranking rules.
 *
 * 1. Same brand and same category (in stock preferred).
 * 2. Fill remaining slots with a similar price band, in stock only.
 *
 * @param WC_Product $product Product.
 * @param int        $limit   Max IDs to return.
 * @param int[]      $exclude Extra IDs to exclude (upsells, etc.).
 * @return int[]
 */
function almasland_get_related_product_ids( $product, $limit = 8, $exclude = array() ) {
	if ( ! $product instanceof WC_Product ) {
		return array();
	}

	$limit      = max( 1, (int) $limit );
	$product_id = (int) $product->get_id();
	$exclude    = array_values(
		array_unique(
			array_filter(
				array_merge(
					array( $product_id ),
					array_map( 'absint', (array) $exclude ),
					array_map( 'absint', (array) $product->get_upsell_ids() ),
					array_map( 'absint', (array) $product->get_children() )
				)
			)
		)
	);

	$cache_key = 'related_product_ids:' . $product_id . ':' . $limit . ':' . md5( (string) wp_json_encode( $exclude ) );

	return (array) almasland_cache_remember(
		$cache_key,
		static function () use ( $product, $limit, $exclude ) {
			$ids = almasland_query_related_by_brand_and_category( $product, $limit, $exclude );

			if ( count( $ids ) < $limit ) {
				$price_ids = almasland_query_related_by_price(
					$product,
					$limit - count( $ids ),
					array_merge( $exclude, $ids )
				);
				$ids = array_merge( $ids, $price_ids );
			}

			return array_values( array_map( 'absint', $ids ) );
		}
	);
}

/**
 * Category term IDs used for related matching (excludes Uncategorized).
 *
 * Prefers leaf categories when the product has both parent and child terms.
 *
 * @param WC_Product $product Product.
 * @return int[]
 */
function almasland_get_related_category_ids( $product ) {
	$cat_ids = array_map( 'absint', (array) $product->get_category_ids() );
	$default = (int) get_option( 'default_product_cat', 0 );

	if ( $default ) {
		$cat_ids = array_values( array_diff( $cat_ids, array( $default ) ) );
	}

	if ( empty( $cat_ids ) ) {
		return array();
	}

	$leaves = array();

	foreach ( $cat_ids as $cat_id ) {
		$is_leaf = true;

		foreach ( $cat_ids as $other_id ) {
			if ( $other_id !== $cat_id && term_is_ancestor_of( $cat_id, $other_id, 'product_cat' ) ) {
				$is_leaf = false;
				break;
			}
		}

		if ( $is_leaf ) {
			$leaves[] = $cat_id;
		}
	}

	return ! empty( $leaves ) ? $leaves : $cat_ids;
}

/**
 * Brand query clauses for the current product (taxonomy and/or meta).
 *
 * @param WC_Product $product Product.
 * @return array{tax_query:array,meta_query:array}
 */
function almasland_get_related_brand_clauses( $product ) {
	$tax_query  = array();
	$meta_query = array();
	$source     = function_exists( 'almasland_get_product_meta_owner' ) ? almasland_get_product_meta_owner( $product ) : $product;
	$source     = $source instanceof WC_Product ? $source : $product;

	if ( function_exists( 'almasland_get_brand_attribute_taxonomy' ) ) {
		$taxonomy = almasland_get_brand_attribute_taxonomy();

		if ( $taxonomy ) {
			$term_ids = wc_get_product_term_ids( $source->get_id(), $taxonomy );

			if ( ! empty( $term_ids ) ) {
				$tax_query[] = array(
					'taxonomy' => $taxonomy,
					'field'    => 'term_id',
					'terms'    => $term_ids,
					'operator' => 'IN',
				);
			}
		}
	}

	$brand_meta = trim( (string) $source->get_meta( '_almas_brand' ) );

	if ( '' !== $brand_meta ) {
		$meta_query[] = array(
			'key'   => '_almas_brand',
			'value' => $brand_meta,
		);
	}

	return array(
		'tax_query'  => $tax_query,
		'meta_query' => $meta_query,
	);
}

/**
 * Shared wc_get_products defaults for related lookups.
 *
 * @param int   $limit   Limit.
 * @param int[] $exclude Exclude IDs.
 * @return array<string, mixed>
 */
function almasland_related_products_query_defaults( $limit, array $exclude ) {
	return array(
		'status'     => 'publish',
		'type'       => array( 'simple', 'variable', 'external', 'grouped' ),
		'limit'      => max( 1, (int) $limit ),
		'exclude'    => $exclude,
		'visibility' => 'catalog',
		'return'     => 'ids',
		'orderby'    => 'date',
		'order'      => 'DESC',
		'paginate'   => false,
	);
}

/**
 * Attach a product_cat tax clause to a query args array.
 *
 * @param array $args         Query args.
 * @param int[] $category_ids Category term IDs.
 * @return array
 */
function almasland_related_with_categories( array $args, array $category_ids ) {
	if ( empty( $category_ids ) ) {
		return $args;
	}

	if ( empty( $args['tax_query'] ) || ! is_array( $args['tax_query'] ) ) {
		$args['tax_query'] = array();
	}

	$args['tax_query'][] = array(
		'taxonomy' => 'product_cat',
		'field'    => 'term_id',
		'terms'    => array_map( 'absint', $category_ids ),
		'operator' => 'IN',
	);

	return $args;
}

/**
 * Phase 1: same brand and same category.
 *
 * @param WC_Product $product Product.
 * @param int        $limit   Limit.
 * @param int[]      $exclude Exclude IDs.
 * @return int[]
 */
function almasland_query_related_by_brand_and_category( $product, $limit, array $exclude ) {
	$category_ids = almasland_get_related_category_ids( $product );
	$brand        = almasland_get_related_brand_clauses( $product );

	if ( empty( $category_ids ) || ( empty( $brand['tax_query'] ) && empty( $brand['meta_query'] ) ) ) {
		return array();
	}

	$base                 = almasland_related_products_query_defaults( $limit, $exclude );
	$base['stock_status'] = 'instock';
	$base                 = almasland_related_with_categories( $base, $category_ids );

	// Brand may live in taxonomy OR meta — either match is enough.
	if ( ! empty( $brand['tax_query'] ) && ! empty( $brand['meta_query'] ) ) {
		$tax_args                = $base;
		$meta_args               = $base;
		$tax_args['tax_query']   = array_merge( $tax_args['tax_query'], $brand['tax_query'] );
		$meta_args['meta_query'] = $brand['meta_query'];

		$ids = array_map( 'absint', (array) wc_get_products( $tax_args ) );
		if ( count( $ids ) < $limit ) {
			$meta_args['limit']   = $limit - count( $ids );
			$meta_args['exclude'] = array_merge( $exclude, $ids );
			$ids                  = array_merge( $ids, array_map( 'absint', (array) wc_get_products( $meta_args ) ) );
		}

		return array_values( array_unique( $ids ) );
	}

	if ( ! empty( $brand['tax_query'] ) ) {
		$base['tax_query'] = array_merge( $base['tax_query'], $brand['tax_query'] );
	}

	if ( ! empty( $brand['meta_query'] ) ) {
		$base['meta_query'] = $brand['meta_query'];
	}

	return array_values( array_map( 'absint', (array) wc_get_products( $base ) ) );
}

/**
 * Phase 2: similar price band, in stock only.
 *
 * Price window is ±30% of the product's display price (min variation for
 * variable products). Products with no price skip this phase.
 *
 * @param WC_Product $product Product.
 * @param int        $limit   Limit.
 * @param int[]      $exclude Exclude IDs.
 * @return int[]
 */
function almasland_query_related_by_price( $product, $limit, array $exclude ) {
	$limit = max( 1, (int) $limit );
	$price = almasland_get_related_reference_price( $product );

	if ( $price <= 0 ) {
		return array();
	}

	$min = max( 0, $price * 0.7 );
	$max = $price * 1.3;

	$args                 = almasland_related_products_query_defaults( $limit, $exclude );
	$args['stock_status'] = 'instock';
	$args['meta_query']   = array(
		array(
			'key'     => '_price',
			'value'   => array( $min, $max ),
			'type'    => 'DECIMAL',
			'compare' => 'BETWEEN',
		),
	);

	// Prefer filling from the same category when possible, then widen.
	$category_ids = almasland_get_related_category_ids( $product );
	$ids          = array();

	if ( ! empty( $category_ids ) ) {
		$same_cat = almasland_related_with_categories( $args, $category_ids );
		$ids      = array_map( 'absint', (array) wc_get_products( $same_cat ) );
	}

	if ( count( $ids ) < $limit ) {
		$args['limit']   = $limit - count( $ids );
		$args['exclude'] = array_merge( $exclude, $ids );
		$ids             = array_merge( $ids, array_map( 'absint', (array) wc_get_products( $args ) ) );
	}

	return array_values( array_unique( $ids ) );
}

/**
 * Reference price used for the related price band.
 *
 * @param WC_Product $product Product.
 * @return float
 */
function almasland_get_related_reference_price( $product ) {
	if ( ! $product instanceof WC_Product ) {
		return 0.0;
	}

	if ( $product->is_type( 'variable' ) ) {
		$min = $product->get_variation_price( 'min', true );

		return is_numeric( $min ) ? (float) $min : 0.0;
	}

	$price = $product->get_price();

	return is_numeric( $price ) ? (float) $price : 0.0;
}

/**
 * Output related products with HTML prototype markup.
 */
function almasland_output_related_products() {
	global $product;

	if ( ! $product instanceof WC_Product ) {
		return;
	}

	$limit   = almasland_related_products_limit();
	$related = almasland_get_related_product_ids( $product, $limit );

	if ( empty( $related ) ) {
		return;
	}

	almasland_prime_product_caches( $related );

	$category_ids = almasland_get_related_category_ids( $product );
	$term         = null;

	if ( ! empty( $category_ids ) ) {
		$term = get_term( (int) $category_ids[0], 'product_cat' );
		if ( ! $term || is_wp_error( $term ) ) {
			$term = null;
		}
	}

	wc_set_loop_prop( 'is_related', true );
	wc_set_loop_prop( 'name', 'related' );
	wc_set_loop_prop( 'columns', 4 );

	?>
	<section class="related-products" aria-labelledby="related-title">
		<div class="section-heading">
			<h2 id="related-title"><?php esc_html_e( 'کالاهای مشابه', 'almas-land' ); ?></h2>
			<?php if ( $term ) : ?>
				<a href="<?php echo esc_url( get_term_link( $term ) ); ?>"><?php esc_html_e( 'مشاهده همه', 'almas-land' ); ?></a>
			<?php endif; ?>
		</div>
		<?php
		woocommerce_product_loop_start();

		foreach ( $related as $related_id ) {
			$related_product = wc_get_product( $related_id );
			if ( ! $related_product ) {
				continue;
			}

			$post_object = get_post( $related_product->get_id() );
			if ( ! $post_object ) {
				continue;
			}

			$GLOBALS['post'] = $post_object; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
			setup_postdata( $GLOBALS['post'] );
			wc_get_template_part( 'content', 'product' );
		}

		woocommerce_product_loop_end();
		wp_reset_postdata();
		?>
	</section>
	<?php

	wc_set_loop_prop( 'is_related', false );
}
add_action( 'woocommerce_after_single_product_summary', 'almasland_output_related_products', 20 );

/**
 * Cart page header.
 */
function almasland_cart_header() {
	$is_saved_view = function_exists( 'almasland_is_saved_cart_view' ) && almasland_is_saved_cart_view();
	$cart_count    = WC()->cart ? WC()->cart->get_cart_contents_count() : 0;
	$saved_count   = function_exists( 'almasland_get_saved_cart_count' ) ? almasland_get_saved_cart_count() : 0;
	$cart_url      = wc_get_cart_url();
	$saved_url     = function_exists( 'almasland_get_saved_cart_url' ) ? almasland_get_saved_cart_url() : $cart_url;
	?>
	<div class="cart-header cart-header--compact">
		<div class="cart-header__title<?php echo $is_saved_view ? '' : ' is-active'; ?>">
			<a href="<?php echo esc_url( $cart_url ); ?>">
				<h1><?php esc_html_e( 'سبد خرید', 'almas-land' ); ?></h1>
				<span><?php echo esc_html( almasland_persian_digits( $cart_count ) ); ?></span>
			</a>
		</div>
		<div class="cart-header__future">
			<span aria-hidden="true">/</span>
			<a class="<?php echo $is_saved_view ? 'is-active' : ''; ?>" href="<?php echo esc_url( $saved_url ); ?>">
				<?php esc_html_e( 'سبد خرید آینده', 'almas-land' ); ?>
				<small><?php echo esc_html( '(' . almasland_persian_digits( $saved_count ) . ')' ); ?></small>
			</a>
		</div>
	</div>
	<?php
}
add_action( 'woocommerce_before_cart', 'almasland_cart_header', 5 );

/**
 * Handle the cart page "remove all" action.
 */
function almasland_handle_empty_cart_action() {
	if ( ! isset( $_GET['almas_empty_cart'] ) || ! class_exists( 'WooCommerce' ) || ! WC()->cart ) {
		return;
	}

	$empty_cart = sanitize_text_field( wp_unslash( $_GET['almas_empty_cart'] ) );
	if ( '1' !== $empty_cart ) {
		return;
	}

	if ( ! isset( $_GET['_wpnonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_GET['_wpnonce'] ) ), 'almas-empty-cart' ) ) {
		return;
	}

	WC()->cart->empty_cart();
	wp_safe_redirect( wc_get_cart_url() );
	exit;
}
add_action( 'template_redirect', 'almasland_handle_empty_cart_action' );

/**
 * Normalize cart attribute labels for a Persian-only UI.
 *
 * @param string $label Attribute label.
 * @return string
 */
function almasland_cart_attribute_label( $label ) {
	$normalized = almasland_normalize_search_text( $label );

	if ( almasland_search_text_contains( $normalized, 'گارانتی' ) || almasland_search_text_contains( $normalized, 'guarantee' ) || almasland_search_text_contains( $normalized, 'warranty' ) ) {
		return __( 'گارانتی', 'almas-land' );
	}

	if ( almasland_search_text_contains( $normalized, 'پردازنده' ) || almasland_search_text_contains( $normalized, 'cpu' ) || almasland_search_text_contains( $normalized, 'processor' ) ) {
		return __( 'پردازنده', 'almas-land' );
	}

	if ( almasland_search_text_contains( $normalized, 'ram' ) || almasland_search_text_contains( $normalized, 'رم' ) ) {
		return __( 'رم', 'almas-land' );
	}

	if ( almasland_search_text_contains( $normalized, 'حافظه' ) || almasland_search_text_contains( $normalized, 'storage' ) || almasland_search_text_contains( $normalized, 'ssd' ) || almasland_search_text_contains( $normalized, 'hdd' ) ) {
		return __( 'حافظه', 'almas-land' );
	}

	if ( almasland_search_text_contains( $normalized, 'رنگ' ) || almasland_search_text_contains( $normalized, 'color' ) ) {
		return __( 'رنگ', 'almas-land' );
	}

	return $label;
}

/**
 * Make technical cart values fit the Persian UI.
 *
 * @param string $value Attribute value.
 * @return string
 */
function almasland_cart_attribute_value( $value ) {
	$value = wp_strip_all_tags( html_entity_decode( (string) $value, ENT_QUOTES, get_bloginfo( 'charset' ) ) );
	$value = preg_replace( '/\bGB\b/i', 'گیگابایت', $value );
	$value = preg_replace( '/\bTB\b/i', 'ترابایت', $value );
	$value = preg_replace( '/\bMB\b/i', 'مگابایت', $value );
	$value = str_replace( array( ' - ', '-' ), ' ', $value );

	return almasland_persian_digits( trim( preg_replace( '/\s+/', ' ', $value ) ) );
}

/**
 * Render delivery notice for a cart line item.
 *
 * @param WC_Product|null $product Product.
 * @return void
 */
function almasland_render_cart_item_delivery( $product ) {
	$delivery = function_exists( 'almasland_get_product_delivery_text' )
		? almasland_get_product_delivery_text( $product )
		: '';

	if ( '' === $delivery ) {
		return;
	}
	?>
	<p class="cart-item__delivery">
		<span class="cart-item__delivery-icon" aria-hidden="true">
			<svg viewBox="0 0 24 24" fill="none"><path d="M3 7h11v10H3V7Z" stroke="currentColor" stroke-width="1.6"/><path d="M14 10h4l3 3v4h-7v-7Z" stroke="currentColor" stroke-width="1.6" stroke-linejoin="round"/><circle cx="7" cy="18" r="1.7" stroke="currentColor" stroke-width="1.5"/><circle cx="17" cy="18" r="1.7" stroke="currentColor" stroke-width="1.5"/></svg>
		</span>
		<span class="cart-item__delivery-text">
			<?php esc_html_e( 'زمان ارسال این کالا:', 'almas-land' ); ?>
			<strong><?php echo esc_html( $delivery ); ?></strong>
		</span>
	</p>
	<?php
}

/**
 * Read product attributes for the cart card, with Persian labels.
 *
 * @param array      $cart_item Cart item data.
 * @param WC_Product $product   Product instance.
 * @return array
 */
function almasland_get_cart_item_features( $cart_item, $product ) {
	if ( ! $product instanceof WC_Product ) {
		return array();
	}

	// Derived purely from product/variation attributes — no customer data.
	$cache_key = 'cart_item_features:' . $product->get_id() . ':' . md5( (string) wp_json_encode( isset( $cart_item['variation'] ) ? $cart_item['variation'] : array() ) );

	if ( almasland_cache_has( $cache_key ) ) {
		return almasland_cache_get( $cache_key );
	}

	$source_product = function_exists( 'almasland_get_product_meta_owner' )
		? almasland_get_product_meta_owner( $product )
		: ( $product->is_type( 'variation' ) && $product->get_parent_id() ? wc_get_product( $product->get_parent_id() ) : $product );

	/*
	 * Only these four labels end up in the returned feature list, so attributes
	 * that map to anything else are skipped before their values (and taxonomy
	 * term queries) are resolved.
	 */
	$wanted = array_fill_keys(
		array(
			'گارانتی',
			'پردازنده',
			'رم',
			'حافظه',
			__( 'گارانتی', 'almas-land' ),
			__( 'پردازنده', 'almas-land' ),
			__( 'رم', 'almas-land' ),
			__( 'حافظه', 'almas-land' ),
		),
		true
	);

	$by_label = array();

	if ( ! empty( $cart_item['variation'] ) ) {
		foreach ( $cart_item['variation'] as $name => $value ) {
			if ( '' === $value ) {
				continue;
			}

			$taxonomy = str_replace( 'attribute_', '', $name );
			$label    = almasland_cart_attribute_label( wc_attribute_label( $taxonomy, $source_product ) );

			if ( ! isset( $wanted[ $label ] ) ) {
				continue;
			}

			if ( taxonomy_exists( $taxonomy ) ) {
				$term  = get_term_by( 'slug', $value, $taxonomy );
				$value = $term && ! is_wp_error( $term ) ? $term->name : $value;
			}

			$value = almasland_cart_attribute_value( $value );

			if ( ! empty( $value ) ) {
				$by_label[ $label ][] = $value;
			}
		}
	}

	if ( $source_product instanceof WC_Product ) {
		foreach ( $source_product->get_attributes() as $attribute ) {
			$label = almasland_cart_attribute_label( wc_attribute_label( $attribute->get_name(), $source_product ) );

			if ( ! isset( $wanted[ $label ] ) ) {
				continue;
			}

			if ( $attribute->is_taxonomy() ) {
				$terms  = wc_get_product_terms( $source_product->get_id(), $attribute->get_name(), array( 'fields' => 'names' ) );
				$values = is_wp_error( $terms ) ? array() : $terms;
			} else {
				$values = $attribute->get_options();
			}

			if ( empty( $values ) ) {
				continue;
			}

			$value = almasland_cart_attribute_value( implode( '، ', array_slice( $values, 0, 2 ) ) );

			if ( ! empty( $value ) ) {
				$by_label[ $label ][] = $value;
			}
		}
	}

	$features = array();
	$warranty = ! empty( $by_label['گارانتی'] ) ? reset( $by_label['گارانتی'] ) : __( 'ضمانت ۷ روزه الماس لند - ضمانت اصالت کالا', 'almas-land' );
	$features[] = array(
		'label' => __( 'گارانتی', 'almas-land' ),
		'value' => $warranty,
	);

	if ( ! empty( $by_label['پردازنده'] ) ) {
		$features[] = array(
			'label' => __( 'پردازنده', 'almas-land' ),
			'value' => reset( $by_label['پردازنده'] ),
		);
	}

	if ( ! empty( $by_label['رم'] ) ) {
		$features[] = array(
			'label' => __( 'رم', 'almas-land' ),
			'value' => implode( ' ', array_slice( array_unique( $by_label['رم'] ), 0, 2 ) ),
		);
	}

	if ( ! empty( $by_label['حافظه'] ) ) {
		$features[] = array(
			'label' => __( 'حافظه', 'almas-land' ),
			'value' => implode( ' ', array_slice( array_unique( $by_label['حافظه'] ), 0, 2 ) ),
		);
	}

	return almasland_cache_set( $cache_key, array_slice( $features, 0, 4 ) );
}

/**
 * Checkout page header and steps.
 */
function almasland_checkout_header() {
	?>
	<div class="cart-header">
		<div class="cart-header__content">
			<h1><?php esc_html_e( 'تسویه حساب', 'almas-land' ); ?></h1>
			<ol class="checkout-steps" aria-label="<?php esc_attr_e( 'مراحل خرید', 'almas-land' ); ?>">
		<li class="is-complete"><?php esc_html_e( '۱. بررسی سبد', 'almas-land' ); ?></li>
		<li class="is-active"><?php esc_html_e( '۲. اطلاعات ارسال', 'almas-land' ); ?></li>
		<li><?php esc_html_e( '۳. پرداخت امن', 'almas-land' ); ?></li>
		<li><?php esc_html_e( '۴. ثبت سفارش', 'almas-land' ); ?></li>
	</ol>
		</div>
		<a class="btn btn--outline" href="<?php echo esc_url( wc_get_cart_url() ); ?>"><?php esc_html_e( 'بازگشت به سبد', 'almas-land' ); ?></a>
	</div>
	
	<?php
}
add_action( 'woocommerce_before_checkout_form', 'almasland_checkout_header', 5 );

/**
 * Checkout submit button text.
 *
 * @return string
 */
function almasland_checkout_order_button_text() {
	return __( 'ثبت و پرداخت سفارش', 'almas-land' );
}
add_filter( 'woocommerce_order_button_text', 'almasland_checkout_order_button_text' );

/**
 * First character for avatar initials.
 *
 * @param WP_User|null $user User object.
 * @return string
 */
function almasland_get_user_avatar_initial( $user = null ) {
	$user = $user instanceof WP_User ? $user : wp_get_current_user();
	$name = $user->first_name ? $user->first_name : ( $user->display_name ? $user->display_name : $user->user_login );

	if ( ! $name ) {
		return '?';
	}

	return function_exists( 'mb_substr' ) ? mb_substr( $name, 0, 1, 'UTF-8' ) : substr( $name, 0, 1 );
}

/**
 * SVG icon for account navigation items.
 *
 * @param string $endpoint Menu endpoint key.
 * @return string
 */
function almasland_account_nav_icon( $endpoint ) {
	$icons = array(
		'dashboard'       => '<svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M4 10.5 12 4l8 6.5V20a1 1 0 0 1-1 1h-5v-6H10v6H5a1 1 0 0 1-1-1v-9.5Z" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/></svg>',
		'orders'          => '<svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M7 4h10l1 3H6l1-3Zm-1 5h12l-1 11H8L6 9Z" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/></svg>',
		'downloads'       => '<svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M12 3v10m0 0 4-4m-4 4-4-4M5 20h14" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>',
		'edit-address'    => '<svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M12 21s7-4.5 7-11a7 7 0 1 0-14 0c0 6.5 7 11 7 11Z" stroke="currentColor" stroke-width="1.8"/><circle cx="12" cy="10" r="2.5" stroke="currentColor" stroke-width="1.8"/></svg>',
		'payment-methods' => '<svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><rect x="3" y="6" width="18" height="12" rx="2" stroke="currentColor" stroke-width="1.8"/><path d="M3 10h18" stroke="currentColor" stroke-width="1.8"/></svg>',
		'edit-account'    => '<svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><circle cx="12" cy="8" r="3.4" stroke="currentColor" stroke-width="1.8"/><path d="M5.5 19c1.4-3.2 3.8-4.8 6.5-4.8s5.1 1.6 6.5 4.8" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>',
		'customer-logout' => '<svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M10 7V5a1 1 0 0 1 1-1h8v16h-8a1 1 0 0 1-1-1v-2M7 12H3m0 0 3-3m-3 3 3 3" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>',
	);

	return isset( $icons[ $endpoint ] ) ? $icons[ $endpoint ] : '<svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><circle cx="12" cy="12" r="8" stroke="currentColor" stroke-width="1.8"/></svg>';
}

/**
 * Account page hero (dashboard only).
 */
function almasland_account_page_hero() {
	return;
}

/**
 * Login page hero.
 */
function almasland_account_login_hero() {
	return;
}

/**
 * Persian labels for account menu items.
 *
 * @param array $items Menu items.
 * @return array
 */
function almasland_account_menu_items( $items ) {
	$labels = array(
		'dashboard'       => __( 'داشبورد', 'almas-land' ),
		'orders'          => __( 'سفارش‌ها', 'almas-land' ),
		'downloads'       => __( 'دانلودها', 'almas-land' ),
		'edit-address'    => __( 'آدرس‌ها', 'almas-land' ),
		'payment-methods' => __( 'روش‌های پرداخت', 'almas-land' ),
		'edit-account'    => __( 'جزئیات حساب', 'almas-land' ),
		'customer-logout' => __( 'خروج', 'almas-land' ),
	);

	foreach ( $labels as $endpoint => $label ) {
		if ( isset( $items[ $endpoint ] ) ) {
			$items[ $endpoint ] = $label;
		}
	}

	return $items;
}
add_filter( 'woocommerce_account_menu_items', 'almasland_account_menu_items' );

/**
 * Remove default WooCommerce dashboard copy (theme provides its own dashboard).
 */
function almasland_customize_account_hooks() {
	remove_action( 'woocommerce_account_dashboard', 'woocommerce_account_dashboard' );
}
add_action( 'init', 'almasland_customize_account_hooks' );

/**
 * Persian labels for downloadable products table.
 *
 * @param array $columns Columns.
 * @return array
 */
function almasland_account_download_columns( $columns ) {
	return array(
		'download-product'   => __( 'محصول', 'almas-land' ),
		'download-remaining' => __( 'باقی‌مانده', 'almas-land' ),
		'download-expires'   => __( 'انقضا', 'almas-land' ),
		'download-file'      => __( 'دانلود', 'almas-land' ),
	);
}
add_filter( 'woocommerce_account_downloads_columns', 'almasland_account_download_columns' );

/**
 * Persian order action labels.
 *
 * @param array    $actions Actions.
 * @param WC_Order $order   Order.
 * @return array
 */
function almasland_account_order_actions( $actions, $order ) {
	$map = array(
		'pay'    => __( 'پرداخت', 'almas-land' ),
		'view'   => __( 'جزئیات', 'almas-land' ),
		'cancel' => __( 'لغو', 'almas-land' ),
	);

	foreach ( $actions as $key => $action ) {
		if ( isset( $map[ $key ] ) ) {
			$actions[ $key ]['name'] = $map[ $key ];
		}
	}

	return $actions;
}
add_filter( 'woocommerce_my_account_my_orders_actions', 'almasland_account_order_actions', 10, 2 );

/**
 * Map order status to label class.
 *
 * @param string $status Order status slug.
 * @return string
 */
function almasland_order_status_label_class( $status ) {
	$status = str_replace( 'wc-', '', (string) $status );

	$map = array(
		'completed'  => 'status-label--success',
		'processing' => 'status-label--warning',
		'on-hold'    => 'status-label--warning',
		'pending'    => 'status-label--info',
		'cancelled'  => 'status-label--muted',
		'refunded'   => 'status-label--info',
		'failed'     => 'status-label--danger',
	);

	return isset( $map[ $status ] ) ? $map[ $status ] : 'status-label--info';
}

/**
 * Count a customer's orders without loading every order ID.
 *
 * `wc_get_orders()` with `limit => -1` materializes the customer's entire order
 * history just to call `count()` on it. Asking for one paginated row instead
 * returns the same total from the query's own row count.
 *
 * @param int      $user_id  User ID.
 * @param string[] $statuses Order statuses to include.
 * @return int
 */
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

/**
 * Get customer order counts for dashboard stats.
 *
 * @param int $user_id User ID.
 * @return array{total:int,active:int,addresses:int}
 */
function almasland_get_account_order_stats( $user_id = 0 ) {
	$user_id = $user_id ? $user_id : get_current_user_id();
	$stats   = array(
		'total'     => 0,
		'active'    => 0,
		'addresses' => 0,
	);

	if ( ! $user_id ) {
		return $stats;
	}

	$stats['total']  = almasland_count_customer_orders( $user_id, array_keys( wc_get_order_statuses() ) );
	$stats['active'] = almasland_count_customer_orders( $user_id, array( 'wc-pending', 'wc-processing', 'wc-on-hold' ) );

	$customer = new WC_Customer( $user_id );
	if ( $customer->get_billing_address_1() ) {
		++$stats['addresses'];
	}
	if ( $customer->get_shipping_address_1() && $customer->get_shipping_address_1() !== $customer->get_billing_address_1() ) {
		++$stats['addresses'];
	}

	return $stats;
}

/**
 * Get product stock label class.
 *
 * @param WC_Product $product Product.
 * @return string
 */
function almasland_stock_class( $product ) {
	if ( ! $product || ! $product->is_in_stock() ) {
		return 'stock--unavailable';
	}

	return $product->is_on_backorder() ? 'stock--limited' : 'stock--available';
}

/**
 * Get product discount percentage.
 *
 * @param WC_Product $product Product.
 * @return int
 */
function almasland_get_discount_percent( $product ) {
	if ( ! $product || ! $product->is_on_sale() ) {
		return 0;
	}

	$regular = (float) $product->get_regular_price();
	$sale    = (float) $product->get_sale_price();

	if ( $regular <= 0 || $sale <= 0 ) {
		return 0;
	}

	return (int) round( ( ( $regular - $sale ) / $regular ) * 100 );
}

/**
 * Whether the product has a numeric price greater than zero for buy-card display.
 *
 * @param WC_Product|null $product Product.
 * @return bool
 */
function almasland_product_has_purchasable_price( $product ) {
	if ( ! $product instanceof WC_Product || ! $product->is_in_stock() ) {
		return false;
	}

	if ( $product->is_type( 'variable' ) ) {
		return (float) $product->get_variation_price( 'min', true ) > 0;
	}

	$raw_price = $product->get_price();

	if ( '' === $raw_price || null === $raw_price ) {
		return false;
	}

	return (float) wc_get_price_to_display( $product ) > 0;
}

/**
 * Buy-card fallback when no price is set.
 *
 * @return string
 */
function almasland_get_buy_price_contact_html() {
	return '<strong class="buy-card__price-current buy-card__price-current--contact">' . esc_html__( 'تماس بگیرید', 'almas-land' ) . '</strong>';
}

/**
 * Credit (Digipay) markup rate as a percent of the final purchasable price.
 *
 * @return float
 */
function almasland_get_credit_price_percent() {
	return (float) apply_filters( 'almasland_credit_price_percent', 7.53 );
}

/**
 * Final display price used for buy-card and credit pricing.
 *
 * Uses the payable amount (sale price when discounted), including tax rules
 * via `wc_get_price_to_display()` / variation min display price.
 *
 * @param WC_Product|null $product Product or variation.
 * @return float
 */
function almasland_get_product_final_display_price( $product ) {
	if ( ! $product instanceof WC_Product || ! $product->is_in_stock() ) {
		return 0.0;
	}

	if ( $product->is_type( 'variable' ) ) {
		$min = $product->get_variation_price( 'min', true );

		return is_numeric( $min ) ? (float) $min : 0.0;
	}

	$raw_price = $product->get_price();

	if ( '' === $raw_price || null === $raw_price ) {
		return 0.0;
	}

	return (float) wc_get_price_to_display( $product );
}

/**
 * Round a final price up with the Digipay / credit surcharge.
 *
 * @param float $final_price Final payable product price.
 * @return float
 */
function almasland_calculate_credit_price( $final_price ) {
	$final_price = (float) $final_price;

	if ( $final_price <= 0 ) {
		return 0.0;
	}

	$rate = almasland_get_credit_price_percent() / 100;

	return (float) round( $final_price * ( 1 + $rate ) );
}

/**
 * Credit price amount for a product or variation.
 *
 * @param WC_Product|null $product Product.
 * @return float
 */
function almasland_get_credit_price( $product ) {
	return almasland_calculate_credit_price( almasland_get_product_final_display_price( $product ) );
}

/**
 * Whether the Digipay block should show a numeric credit price.
 *
 * @param WC_Product|null $product Product.
 * @return bool
 */
function almasland_product_has_credit_price( $product ) {
	return almasland_get_credit_price( $product ) > 0;
}

/**
 * Formatted HTML for the Digipay credit price.
 *
 * @param WC_Product|null $product Product or variation.
 * @return string
 */
function almasland_get_credit_price_html( $product ) {
	$amount = almasland_get_credit_price( $product );

	if ( $amount <= 0 ) {
		return '';
	}

	$from_label = '';

	if ( $product instanceof WC_Product && $product->is_type( 'variable' ) ) {
		$min = (float) $product->get_variation_price( 'min', true );
		$max = (float) $product->get_variation_price( 'max', true );

		if ( abs( $min - $max ) > 0.0001 ) {
			$from_label = __( 'از', 'almas-land' );
		}
	}

	$html = '';

	if ( $from_label ) {
		$html .= '<span class="buy-card__digipay-from">' . esc_html( $from_label ) . '</span> ';
	}

	$html .= wp_kses_post( wc_price( $amount ) );

	return $html;
}

/**
 * Buy-card price markup: current price on top, discount badge + strikethrough below.
 *
 * @param WC_Product $product Product or variation.
 * @return string
 */
function almasland_get_buy_price_html( $product ) {
	if ( ! $product || ! is_a( $product, 'WC_Product' ) ) {
		return '';
	}

	if ( ! $product->is_in_stock() ) {
		return '';
	}

	$from_label = '';
	$current    = 0.0;
	$regular    = 0.0;
	$discount   = 0;

	if ( $product->is_type( 'variable' ) ) {
		$current  = (float) $product->get_variation_price( 'min', true );
		$max      = (float) $product->get_variation_price( 'max', true );
		$regular  = (float) $product->get_variation_regular_price( 'min', true );
		$discount = ( $regular > $current && $regular > 0 )
			? (int) round( ( ( $regular - $current ) / $regular ) * 100 )
			: 0;

		if ( abs( $current - $max ) > 0.0001 ) {
			$from_label = __( 'از', 'almas-land' );
			$discount   = 0;
			$regular    = 0;
		}
	} else {
		$raw_price = $product->get_price();
		if ( '' === $raw_price || null === $raw_price ) {
			return almasland_get_buy_price_contact_html();
		}

		$current  = (float) wc_get_price_to_display( $product );
		$regular  = ( '' !== $product->get_regular_price() )
			? (float) wc_get_price_to_display( $product, array( 'price' => $product->get_regular_price() ) )
			: 0.0;
		$discount = almasland_get_discount_percent( $product );
	}

	if ( $current <= 0 ) {
		return almasland_get_buy_price_contact_html();
	}

	ob_start();
	?>
	<strong class="buy-card__price-current">
		<?php if ( $from_label ) : ?>
			<span class="buy-card__price-from"><?php echo esc_html( $from_label ); ?></span>
		<?php endif; ?>
		<?php echo wp_kses_post( wc_price( $current ) ); ?>
	</strong>
	<?php
	if ( $discount > 0 && $regular > $current ) {
		?>
		<div class="buy-card__price-meta">
			<del><?php echo wp_kses_post( wc_price( $regular ) ); ?></del>
			<span class="discount-badge">
				<?php
				echo esc_html(
					sprintf(
						/* translators: %s: discount percent */
						__( '%s٪ تخفیف', 'almas-land' ),
						almasland_persian_digits( (string) $discount )
					)
				);
				?>
			</span>
		</div>
		<?php
	}

	return (string) ob_get_clean();
}

/**
 * Expose theme price markup for each variation (used by single-product JS).
 *
 * @param array                $data      Variation data.
 * @param WC_Product_Variable  $product   Parent product.
 * @param WC_Product_Variation $variation Variation product.
 * @return array
 */
function almasland_available_variation_price_html( $data, $product, $variation ) {
	unset( $product );
	$data['almas_price_html']        = almasland_get_buy_price_html( $variation );
	$data['almas_credit_price_html'] = almasland_get_credit_price_html( $variation );
	if ( $variation && ! $variation->is_in_stock() ) {
		$data['price_html']              = '';
		$data['almas_credit_price_html'] = '';
	}
	return $data;
}
add_filter( 'woocommerce_available_variation', 'almasland_available_variation_price_html', 10, 3 );

/**
 * Hide price HTML for out-of-stock products site-wide.
 *
 * @param string     $price_html Price HTML.
 * @param WC_Product $product    Product.
 * @return string
 */
function almasland_hide_outofstock_price_html( $price_html, $product ) {
	if ( $product instanceof WC_Product && ! $product->is_in_stock() ) {
		return '';
	}
	return $price_html;
}
add_filter( 'woocommerce_get_price_html', 'almasland_hide_outofstock_price_html', 20, 2 );

/**
 * Whether cart quantity should be fixed (not editable).
 *
 * Applies when the product is sold individually or max purchasable qty is 1.
 *
 * @param WC_Product $product Product.
 * @return bool
 */
function almasland_is_cart_quantity_locked( $product ) {
	if ( ! $product instanceof WC_Product ) {
		return false;
	}

	if ( $product->is_sold_individually() ) {
		return true;
	}

	$max_quantity = (int) $product->get_max_purchase_quantity();

	return 1 === $max_quantity;
}

/**
 * Product category list as plain text.
 *
 * @param WC_Product $product Product.
 * @return string
 */
function almasland_product_category_text( $product ) {
	if ( ! $product ) {
		return '';
	}

	$terms = wc_get_product_terms( $product->get_id(), 'product_cat', array( 'fields' => 'names' ) );
	return $terms ? implode( '، ', $terms ) : '';
}

/**
 * Archive hero image for shop and taxonomy pages.
 *
 * @return string
 */
function almasland_get_archive_hero_image() {
	if ( is_product_taxonomy() ) {
		$term = get_queried_object();
		if ( $term && ! is_wp_error( $term ) ) {
			$thumb_id = get_term_meta( $term->term_id, 'thumbnail_id', true );
			if ( $thumb_id ) {
				$image = wp_get_attachment_image_url( $thumb_id, 'large' );
				if ( $image ) {
					return $image;
				}
			}
		}
	}

	return ALMASLAND_URI . '/assets/images/promo.svg';
}
