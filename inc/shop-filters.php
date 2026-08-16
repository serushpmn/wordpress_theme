<?php
/**
 * Shop archive filters, sorting, and query modifications.
 *
 * @package AlmasLand
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Remove default WooCommerce shop controls replaced by the theme.
 */
function almasland_customize_shop_loop() {
	if ( ! is_shop() && ! is_product_taxonomy() ) {
		return;
	}

	remove_action( 'woocommerce_before_shop_loop', 'woocommerce_result_count', 20 );
	remove_action( 'woocommerce_before_shop_loop', 'woocommerce_catalog_ordering', 30 );
}
add_action( 'wp', 'almasland_customize_shop_loop' );

/**
 * Sort options for horizontal toolbar.
 *
 * Keys match WooCommerce catalog `orderby` values where possible.
 *
 * @return array<string, array{label:string, orderby:string}>
 */
function almasland_get_shop_sort_options() {
	return array(
		'date'       => array(
			'label'   => __( 'جدیدترین', 'almas-land' ),
			'orderby' => 'date',
		),
		'modified'   => array(
			'label'   => __( 'بروزترین‌ها', 'almas-land' ),
			'orderby' => 'modified',
		),
		'popularity' => array(
			'label'   => __( 'پرفروش‌ترین', 'almas-land' ),
			'orderby' => 'popularity',
		),
		'rating'     => array(
			'label'   => __( 'پربازدیدترین', 'almas-land' ),
			'orderby' => 'rating',
		),
		'price'      => array(
			'label'   => __( 'ارزان‌ترین', 'almas-land' ),
			'orderby' => 'price',
		),
		'price-desc' => array(
			'label'   => __( 'گران‌ترین', 'almas-land' ),
			'orderby' => 'price-desc',
		),
	);
}

/**
 * Known shop filter query keys.
 *
 * @return string[]
 */
function almasland_shop_filter_keys() {
	return array(
		'orderby',
		'min_price',
		'max_price',
		'in_stock',
		'fast_shipping',
		'on_sale',
		'filter_brand',
		'filter_cat',
	);
}

/**
 * Read current shop filter state from query string.
 *
 * @return array<string, mixed>
 */
function almasland_get_shop_filter_state() {
	$sort_options = almasland_get_shop_sort_options();
	$orderby      = isset( $_GET['orderby'] ) ? sanitize_title( wp_unslash( $_GET['orderby'] ) ) : 'date';
	$orderby      = isset( $sort_options[ $orderby ] ) ? $orderby : 'date';

	$state = array(
		'orderby'        => $orderby,
		'min_price'      => isset( $_GET['min_price'] ) ? absint( wp_unslash( $_GET['min_price'] ) ) : 0,
		'max_price'      => isset( $_GET['max_price'] ) ? absint( wp_unslash( $_GET['max_price'] ) ) : 0,
		'in_stock'       => ! empty( $_GET['in_stock'] ),
		'fast_shipping'  => ! empty( $_GET['fast_shipping'] ),
		'on_sale'        => ! empty( $_GET['on_sale'] ),
		'filter_brand'   => isset( $_GET['filter_brand'] ) ? array_map( 'sanitize_text_field', (array) wp_unslash( $_GET['filter_brand'] ) ) : array(),
		'filter_cat'     => isset( $_GET['filter_cat'] ) ? array_map( 'absint', (array) wp_unslash( $_GET['filter_cat'] ) ) : array(),
	);

	$state['filter_brand'] = array_values( array_filter( $state['filter_brand'] ) );
	$state['filter_cat']   = array_values( array_filter( $state['filter_cat'] ) );

	return $state;
}

/**
 * Build shop URL preserving active filters.
 *
 * @param array<string, mixed> $args   Query args to merge.
 * @param string[]             $remove Keys to remove.
 * @return string
 */
function almasland_shop_filter_url( $args = array(), $remove = array() ) {
	$base = '';
	if ( is_shop() ) {
		$base = wc_get_page_permalink( 'shop' );
	} elseif ( is_product_taxonomy() ) {
		$term = get_queried_object();
		$base = ( $term && ! is_wp_error( $term ) ) ? get_term_link( $term ) : wc_get_page_permalink( 'shop' );
	} else {
		$base = wc_get_page_permalink( 'shop' );
	}

	if ( is_wp_error( $base ) ) {
		$base = home_url( '/' );
	}

	$current = array();
	$state   = almasland_get_shop_filter_state();
	foreach ( almasland_shop_filter_keys() as $key ) {
		if ( ! isset( $_GET[ $key ] ) && 'orderby' !== $key ) {
			continue;
		}

		if ( 'orderby' === $key ) {
			if ( isset( $_GET['orderby'] ) && 'date' !== $state['orderby'] ) {
				$current[ $key ] = $state[ $key ];
			}
			continue;
		}

		$value = wp_unslash( $_GET[ $key ] );
		if ( is_array( $value ) ) {
			$current[ $key ] = array_map( 'sanitize_text_field', $value );
		} elseif ( in_array( $key, array( 'min_price', 'max_price' ), true ) ) {
			$current[ $key ] = absint( $value );
		} else {
			$current[ $key ] = sanitize_text_field( $value );
		}
	}

	foreach ( $remove as $key ) {
		unset( $current[ $key ] );
	}

	$merged = array_merge( $current, $args );
	$merged = array_filter(
		$merged,
		static function ( $value ) {
			if ( is_array( $value ) ) {
				return ! empty( $value );
			}
			return '' !== $value && '0' !== (string) $value && 0 !== $value;
		}
	);

	return add_query_arg( $merged, $base );
}

/**
 * Detect product brand attribute taxonomy.
 *
 * @return string
 */
function almasland_get_brand_attribute_taxonomy() {
	$candidates = array( 'pa_brand', 'pa_برند' );
	foreach ( $candidates as $taxonomy ) {
		if ( taxonomy_exists( $taxonomy ) ) {
			return $taxonomy;
		}
	}

	$attributes = wc_get_attribute_taxonomies();
	foreach ( $attributes as $attribute ) {
		$name = $attribute->attribute_name;
		if ( false !== strpos( $name, 'brand' ) || false !== strpos( $name, 'برند' ) ) {
			return 'pa_' . $name;
		}
	}

	return '';
}

/**
 * Get available shop brands.
 *
 * @return array<int, array{value:string, label:string, count:int}>
 */
function almasland_get_shop_brand_options() {
	global $wpdb;

	$cached = get_transient( 'almasland_shop_brand_options' );
	if ( is_array( $cached ) ) {
		return $cached;
	}

	$brands = array();
	$rows   = $wpdb->get_results(
		"SELECT meta_value AS brand, COUNT(post_id) AS total
		FROM {$wpdb->postmeta} pm
		INNER JOIN {$wpdb->posts} p ON p.ID = pm.post_id
		WHERE pm.meta_key = '_almas_brand'
		AND pm.meta_value != ''
		AND p.post_type = 'product'
		AND p.post_status = 'publish'
		GROUP BY pm.meta_value
		ORDER BY pm.meta_value ASC",
		ARRAY_A
	);

	if ( ! is_array( $rows ) ) {
		$rows = array();
	}

	foreach ( $rows as $row ) {
		$brands[] = array(
			'value' => sanitize_text_field( $row['brand'] ),
			'label' => sanitize_text_field( $row['brand'] ),
			'count' => (int) $row['total'],
		);
	}

	$taxonomy = almasland_get_brand_attribute_taxonomy();
	if ( $taxonomy ) {
		$terms = get_terms(
			array(
				'taxonomy'   => $taxonomy,
				'hide_empty' => true,
			)
		);

		if ( ! is_wp_error( $terms ) ) {
			foreach ( $terms as $term ) {
				$exists = false;
				foreach ( $brands as $brand ) {
					if ( $brand['value'] === $term->slug || $brand['label'] === $term->name ) {
						$exists = true;
						break;
					}
				}
				if ( ! $exists ) {
					$brands[] = array(
						'value' => 'tax:' . $term->slug,
						'label' => $term->name,
						'count' => (int) $term->count,
					);
				}
			}
		}
	}

	set_transient( 'almasland_shop_brand_options', $brands, HOUR_IN_SECONDS );

	return $brands;
}

/**
 * Get category filter options.
 *
 * @return WP_Term[]
 */
function almasland_get_shop_category_options() {
	if ( is_product_taxonomy() ) {
		$term = get_queried_object();
		if ( $term && ! is_wp_error( $term ) && 'product_cat' === $term->taxonomy ) {
			$children = get_terms(
				array(
					'taxonomy'   => 'product_cat',
					'hide_empty' => true,
					'parent'     => (int) $term->term_id,
				)
			);
			return is_wp_error( $children ) ? array() : $children;
		}
	}

	$terms = get_terms(
		array(
			'taxonomy'   => 'product_cat',
			'hide_empty' => true,
			'parent'     => 0,
		)
	);

	return is_wp_error( $terms ) ? array() : $terms;
}

/**
 * Apply custom filters to WooCommerce product query.
 *
 * @param WP_Query $query Query.
 */
function almasland_apply_shop_filters( $query ) {
	if ( is_admin() || ! $query->is_main_query() || ! ( is_shop() || is_product_taxonomy() ) ) {
		return;
	}

	$state = almasland_get_shop_filter_state();

	$meta_query = $query->get( 'meta_query' );
	$tax_query  = $query->get( 'tax_query' );
	$meta_query = is_array( $meta_query ) ? $meta_query : array();
	$tax_query  = is_array( $tax_query ) ? $tax_query : array();

	// Custom orderby keys WooCommerce does not map natively.
	if ( 'modified' === $state['orderby'] ) {
		$query->set( 'orderby', 'modified' );
		$query->set( 'order', 'DESC' );
		$query->set( 'meta_key', '' );
	}

	/*
	 * In-stock filter: use product_visibility (WC 3+), not _stock_status meta.
	 * Price range is handled natively by WooCommerce via min_price/max_price GET
	 * params + lookup table clauses — do not add a second _price meta query.
	 */
	if ( $state['in_stock'] ) {
		$visibility_term_ids = function_exists( 'wc_get_product_visibility_term_ids' ) ? wc_get_product_visibility_term_ids() : array();
		if ( ! empty( $visibility_term_ids['outofstock'] ) ) {
			$tax_query[] = array(
				'taxonomy' => 'product_visibility',
				'field'    => 'term_taxonomy_id',
				'terms'    => array( (int) $visibility_term_ids['outofstock'] ),
				'operator' => 'NOT IN',
			);
		}
	}

	if ( $state['fast_shipping'] ) {
		$meta_query[] = array(
			'key'     => '_almas_fixed_badges',
			'value'   => '"express_delivery"',
			'compare' => 'LIKE',
		);
	}

	if ( $state['on_sale'] ) {
		$sale_ids = array_map( 'absint', wc_get_product_ids_on_sale() );
		if ( empty( $sale_ids ) ) {
			$sale_ids = array( 0 );
		}

		$existing_in = $query->get( 'post__in' );
		if ( ! empty( $existing_in ) ) {
			$sale_ids = array_values( array_intersect( array_map( 'absint', (array) $existing_in ), $sale_ids ) );
			if ( empty( $sale_ids ) ) {
				$sale_ids = array( 0 );
			}
		}

		$query->set( 'post__in', $sale_ids );
	}

	if ( ! empty( $state['filter_brand'] ) ) {
		$meta_brands = array();
		$tax_brands  = array();
		foreach ( $state['filter_brand'] as $brand ) {
			if ( 0 === strpos( $brand, 'tax:' ) ) {
				$tax_brands[] = substr( $brand, 4 );
			} else {
				$meta_brands[] = $brand;
			}
		}

		$brand_taxonomy = almasland_get_brand_attribute_taxonomy();
		$brand_ids      = array();

		if ( $meta_brands ) {
			$meta_ids = get_posts(
				array(
					'post_type'              => 'product',
					'post_status'            => 'publish',
					'posts_per_page'         => -1,
					'fields'                 => 'ids',
					'no_found_rows'          => true,
					'update_post_meta_cache' => false,
					'update_post_term_cache' => false,
					'meta_query'             => array(
						array(
							'key'     => '_almas_brand',
							'value'   => $meta_brands,
							'compare' => 'IN',
						),
					),
				)
			);
			$brand_ids = array_merge( $brand_ids, array_map( 'absint', $meta_ids ) );
		}

		if ( $tax_brands && $brand_taxonomy ) {
			$tax_ids = get_posts(
				array(
					'post_type'              => 'product',
					'post_status'            => 'publish',
					'posts_per_page'         => -1,
					'fields'                 => 'ids',
					'no_found_rows'          => true,
					'update_post_meta_cache' => false,
					'update_post_term_cache' => false,
					'tax_query'              => array(
						array(
							'taxonomy' => $brand_taxonomy,
							'field'    => 'slug',
							'terms'    => $tax_brands,
							'operator' => 'IN',
						),
					),
				)
			);
			$brand_ids = array_merge( $brand_ids, array_map( 'absint', $tax_ids ) );
		}

		$brand_ids = array_values( array_unique( array_filter( $brand_ids ) ) );
		if ( empty( $brand_ids ) ) {
			$brand_ids = array( 0 );
		}

		$existing_in = $query->get( 'post__in' );
		if ( ! empty( $existing_in ) ) {
			$brand_ids = array_values( array_intersect( array_map( 'absint', (array) $existing_in ), $brand_ids ) );
			if ( empty( $brand_ids ) ) {
				$brand_ids = array( 0 );
			}
		}

		$query->set( 'post__in', $brand_ids );
	}

	if ( ! empty( $state['filter_cat'] ) ) {
		$tax_query[] = array(
			'taxonomy'         => 'product_cat',
			'field'            => 'term_id',
			'terms'            => $state['filter_cat'],
			'operator'         => 'IN',
			'include_children' => true,
		);
	}

	$meta_clauses = array_filter(
		$meta_query,
		static function ( $clause ) {
			return is_array( $clause );
		}
	);
	if ( count( $meta_clauses ) > 1 && ! isset( $meta_query['relation'] ) ) {
		$meta_query['relation'] = 'AND';
	}

	$tax_clauses = array_filter(
		$tax_query,
		static function ( $clause, $key ) {
			return is_array( $clause ) && 'relation' !== $key;
		},
		ARRAY_FILTER_USE_BOTH
	);
	if ( count( $tax_clauses ) > 1 && ! isset( $tax_query['relation'] ) ) {
		$tax_query['relation'] = 'AND';
	}

	if ( ! empty( $meta_clauses ) ) {
		$query->set( 'meta_query', $meta_query );
	}
	if ( ! empty( $tax_clauses ) ) {
		$query->set( 'tax_query', $tax_query );
	}
}
add_action( 'woocommerce_product_query', 'almasland_apply_shop_filters', 20 );

/**
 * Drop empty/zero price query args before WooCommerce reads them.
 *
 * Empty number inputs still submit as "" which WC casts to 0 and then
 * excludes almost every priced product from the catalog.
 */
function almasland_sanitize_shop_price_query_vars() {
	if ( is_admin() ) {
		return;
	}

	foreach ( array( 'min_price', 'max_price' ) as $key ) {
		if ( ! isset( $_GET[ $key ] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			continue;
		}

		$raw = wp_unslash( $_GET[ $key ] ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		if ( '' === $raw || ! is_numeric( $raw ) || (float) $raw <= 0 ) {
			unset( $_GET[ $key ], $_REQUEST[ $key ] ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		}
	}
}
add_action( 'parse_request', 'almasland_sanitize_shop_price_query_vars', 1 );
add_action( 'init', 'almasland_sanitize_shop_price_query_vars', 1 );

/**
 * Render custom result count.
 */
function almasland_shop_result_count() {
	global $wp_query;

	$total    = (int) $wp_query->found_posts;
	$per_page = (int) $wp_query->get( 'posts_per_page' );
	$current  = max( 1, (int) $wp_query->get( 'paged' ) );
	$from     = 0 === $total ? 0 : ( ( $current - 1 ) * $per_page ) + 1;
	$to       = min( $total, $current * $per_page );

	if ( 0 === $total ) {
		echo '<p class="shop-result-count">' . esc_html__( 'محصولی یافت نشد', 'almas-land' ) . '</p>';
		return;
	}

	printf(
		'<p class="shop-result-count">%s</p>',
		esc_html(
			sprintf(
				/* translators: 1: from, 2: to, 3: total products */
				__( 'نمایش %1$s تا %2$s از %3$s محصول', 'almas-land' ),
				almasland_persian_digits( (string) $from ),
				almasland_persian_digits( (string) $to ),
				almasland_persian_digits( (string) $total )
			)
		)
	);
}

/**
 * Render horizontal sort bar.
 */
function almasland_shop_sort_bar() {
	$state      = almasland_get_shop_filter_state();
	$options    = almasland_get_shop_sort_options();
	$active_key = $state['orderby'];
	?>
	<div class="shop-sort-bar" role="toolbar" aria-label="<?php esc_attr_e( 'مرتب‌سازی محصولات', 'almas-land' ); ?>">
		<span class="shop-sort-bar__label">
			<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 7h16v2H4V7Zm2 4h12v2H6v-2Zm3 4h6v2H9v-2Z" fill="currentColor"/></svg>
			<?php esc_html_e( 'مرتب‌سازی', 'almas-land' ); ?>
		</span>
		<div class="shop-sort-bar__options">
			<?php foreach ( $options as $key => $option ) : ?>
				<?php
				$url = almasland_shop_filter_url(
					'date' === $option['orderby'] ? array() : array( 'orderby' => $option['orderby'] ),
					'date' === $option['orderby'] ? array( 'orderby' ) : array()
				);
				?>
				<a class="shop-sort-pill<?php echo $active_key === $key ? ' is-active' : ''; ?>" href="<?php echo esc_url( $url ); ?>">
					<?php echo esc_html( $option['label'] ); ?>
				</a>
			<?php endforeach; ?>
		</div>
	</div>
	<?php
}

/**
 * Categories for the archive top navigation strip.
 *
 * @return WP_Term[]
 */
function almasland_get_shop_nav_categories() {
	if ( ! taxonomy_exists( 'product_cat' ) ) {
		return array();
	}

	$exclude = array_filter( array( (int) get_option( 'default_product_cat', 0 ) ) );
	$terms   = get_terms(
		array(
			'taxonomy'   => 'product_cat',
			'parent'     => 0,
			'hide_empty' => true,
			'exclude'    => $exclude,
			'orderby'    => 'name',
			'order'      => 'ASC',
		)
	);

	return is_wp_error( $terms ) ? array() : $terms;
}

/**
 * Whether a category nav item should be marked active.
 *
 * @param WP_Term $term Category term.
 * @return bool
 */
function almasland_is_shop_nav_category_active( $term ) {
	if ( ! $term instanceof WP_Term || ! is_product_category() ) {
		return false;
	}

	$current = get_queried_object();
	if ( ! $current || is_wp_error( $current ) || empty( $current->term_id ) ) {
		return false;
	}

	$current_id = (int) $current->term_id;
	$term_id    = (int) $term->term_id;

	if ( $current_id === $term_id ) {
		return true;
	}

	return term_is_ancestor_of( $term_id, $current_id, 'product_cat' );
}

/**
 * Render archive category navigation strip.
 */
function almasland_shop_category_nav() {
	$terms = almasland_get_shop_nav_categories();
	if ( ! $terms ) {
		return;
	}
	?>
	<nav class="shop-cat-nav" aria-label="<?php esc_attr_e( 'دسته‌بندی محصولات', 'almas-land' ); ?>">
		<div class="shop-cat-nav__track">
			<?php foreach ( $terms as $term ) : ?>
				<?php
				$link   = get_term_link( $term );
				$image  = function_exists( 'almasland_get_product_category_image' ) ? almasland_get_product_category_image( $term->term_id ) : array( 'url' => '' );
				$active = almasland_is_shop_nav_category_active( $term );
				if ( is_wp_error( $link ) ) {
					continue;
				}
				?>
				<a class="shop-cat-nav__item<?php echo $active ? ' is-active' : ''; ?>" href="<?php echo esc_url( $link ); ?>">
					<span class="shop-cat-nav__icon">
						<?php if ( ! empty( $image['url'] ) ) : ?>
							<img src="<?php echo esc_url( $image['url'] ); ?>" alt="" width="56" height="56" loading="lazy" decoding="async">
						<?php else : ?>
							<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 7.5 12 3l8 4.5v9L12 21l-8-4.5v-9Z" fill="none" stroke="currentColor" stroke-width="1.6"/></svg>
						<?php endif; ?>
					</span>
					<span class="shop-cat-nav__name"><?php echo esc_html( $term->name ); ?></span>
				</a>
			<?php endforeach; ?>
		</div>
	</nav>
	<?php
}

/**
 * Shop archive page title.
 *
 * @return string
 */
function almasland_get_shop_archive_title() {
	if ( is_product_taxonomy() ) {
		$term = get_queried_object();
		if ( $term && ! is_wp_error( $term ) && ! empty( $term->name ) ) {
			return $term->name;
		}
	}

	if ( function_exists( 'woocommerce_page_title' ) ) {
		return wp_strip_all_tags( woocommerce_page_title( false ) );
	}

	return __( 'فروشگاه', 'almas-land' );
}

/**
 * Clear-filters URL for the current archive context.
 *
 * @return string
 */
function almasland_get_shop_clear_filters_url() {
	if ( is_product_taxonomy() ) {
		$term_link = get_term_link( get_queried_object() );
		if ( ! is_wp_error( $term_link ) ) {
			return $term_link;
		}
	}

	return wc_get_page_permalink( 'shop' );
}

/**
 * Render active filter chips.
 */
function almasland_shop_active_filters() {
	$state    = almasland_get_shop_filter_state();
	$chips    = array();
	$brands   = almasland_get_shop_brand_options();
	$brandmap = array();
	foreach ( $brands as $brand ) {
		$brandmap[ $brand['value'] ] = $brand['label'];
	}

	if ( $state['in_stock'] ) {
		$chips[] = array(
			'label' => __( 'کالاهای موجود', 'almas-land' ),
			'url'   => almasland_shop_filter_url( array(), array( 'in_stock' ) ),
		);
	}
	if ( $state['fast_shipping'] ) {
		$chips[] = array(
			'label' => __( 'ارسال سریع', 'almas-land' ),
			'url'   => almasland_shop_filter_url( array(), array( 'fast_shipping' ) ),
		);
	}
	if ( $state['on_sale'] ) {
		$chips[] = array(
			'label' => __( 'تخفیف‌دار', 'almas-land' ),
			'url'   => almasland_shop_filter_url( array(), array( 'on_sale' ) ),
		);
	}
	if ( $state['min_price'] > 0 || $state['max_price'] > 0 ) {
		$chips[] = array(
			'label' => sprintf(
				__( 'قیمت %1$s تا %2$s', 'almas-land' ),
				almasland_persian_digits( number_format_i18n( $state['min_price'] ) ),
				$state['max_price'] ? almasland_persian_digits( number_format_i18n( $state['max_price'] ) ) : '∞'
			),
			'url'   => almasland_shop_filter_url( array(), array( 'min_price', 'max_price' ) ),
		);
	}

	foreach ( $state['filter_brand'] as $brand_value ) {
		$chips[] = array(
			'label' => isset( $brandmap[ $brand_value ] ) ? $brandmap[ $brand_value ] : $brand_value,
			'url'   => almasland_shop_filter_url(
				array(
					'filter_brand' => array_values(
						array_diff( $state['filter_brand'], array( $brand_value ) )
					),
				)
			),
		);
	}

	foreach ( $state['filter_cat'] as $cat_id ) {
		$term = get_term( $cat_id, 'product_cat' );
		if ( ! $term || is_wp_error( $term ) ) {
			continue;
		}
		$chips[] = array(
			'label' => $term->name,
			'url'   => almasland_shop_filter_url(
				array(
					'filter_cat' => array_values(
						array_diff( $state['filter_cat'], array( $cat_id ) )
					),
				)
			),
		);
	}

	if ( ! $chips ) {
		return;
	}

	$clear_url = wc_get_page_permalink( 'shop' );
	if ( is_product_taxonomy() ) {
		$term_link = get_term_link( get_queried_object() );
		if ( ! is_wp_error( $term_link ) ) {
			$clear_url = $term_link;
		}
	}
	?>
	<div class="shop-active-filters" aria-label="<?php esc_attr_e( 'فیلترهای فعال', 'almas-land' ); ?>">
		<?php foreach ( $chips as $chip ) : ?>
			<a class="shop-active-filter" href="<?php echo esc_url( $chip['url'] ); ?>">
				<span><?php echo esc_html( $chip['label'] ); ?></span>
				<svg viewBox="0 0 24 24" aria-hidden="true"><path d="m6.4 5 12.6 12.6-1.4 1.4L5 6.4 6.4 5Zm12.6 1.4L6.4 19 5 17.6 17.6 5 19 6.4Z"/></svg>
			</a>
		<?php endforeach; ?>
		<a class="shop-active-filter shop-active-filter--clear" href="<?php echo esc_url( $clear_url ); ?>">
			<?php esc_html_e( 'پاک کردن همه', 'almas-land' ); ?>
		</a>
	</div>
	<?php
}

/**
 * Render shop filter form.
 */
function almasland_shop_filter_form() {
	$state  = almasland_get_shop_filter_state();
	$action = is_shop() ? wc_get_page_permalink( 'shop' ) : get_term_link( get_queried_object() );
	$brands = almasland_get_shop_brand_options();

	if ( is_wp_error( $action ) ) {
		$action = wc_get_page_permalink( 'shop' );
	}
	?>
	<form class="shop-filter-form" method="get" action="<?php echo esc_url( $action ); ?>">
		<?php if ( $state['orderby'] && 'date' !== $state['orderby'] ) : ?>
			<input type="hidden" name="orderby" value="<?php echo esc_attr( $state['orderby'] ); ?>">
		<?php endif; ?>

		<section class="shop-filter-section">
			<h3 class="shop-filter-section__title"><?php esc_html_e( 'محدوده قیمت', 'almas-land' ); ?></h3>
			<div class="shop-filter-price">
				<label class="shop-filter-price__field">
					<span><?php esc_html_e( 'از', 'almas-land' ); ?></span>
					<input type="number" name="min_price" min="0" step="100000" inputmode="numeric" value="<?php echo $state['min_price'] ? esc_attr( (string) $state['min_price'] ) : ''; ?>" placeholder="۰">
				</label>
				<span class="shop-filter-price__sep" aria-hidden="true"></span>
				<label class="shop-filter-price__field">
					<span><?php esc_html_e( 'تا', 'almas-land' ); ?></span>
					<input type="number" name="max_price" min="0" step="100000" inputmode="numeric" value="<?php echo $state['max_price'] ? esc_attr( (string) $state['max_price'] ) : ''; ?>" placeholder="<?php esc_attr_e( 'حداکثر', 'almas-land' ); ?>">
				</label>
			</div>
			<p class="shop-filter-hint"><?php esc_html_e( 'مبالغ به تومان', 'almas-land' ); ?></p>
		</section>

		<?php if ( $brands ) : ?>
			<section class="shop-filter-section">
				<h3 class="shop-filter-section__title"><?php esc_html_e( 'برند', 'almas-land' ); ?></h3>
				<div class="shop-filter-checklist">
					<?php foreach ( $brands as $brand ) : ?>
						<label class="shop-filter-check">
							<input type="checkbox" name="filter_brand[]" value="<?php echo esc_attr( $brand['value'] ); ?>" <?php checked( in_array( $brand['value'], $state['filter_brand'], true ) ); ?>>
							<span><?php echo esc_html( $brand['label'] ); ?></span>
							<em><?php echo esc_html( almasland_persian_digits( (string) $brand['count'] ) ); ?></em>
						</label>
					<?php endforeach; ?>
				</div>
			</section>
		<?php endif; ?>

		<section class="shop-filter-section shop-filter-section--switches">
			<label class="shop-filter-switch">
				<span class="shop-filter-switch__label"><?php esc_html_e( 'کالاهای موجود', 'almas-land' ); ?></span>
				<input type="checkbox" name="in_stock" value="1" <?php checked( $state['in_stock'] ); ?>>
				<span class="shop-filter-switch__track" aria-hidden="true"><span class="shop-filter-switch__thumb"></span></span>
			</label>
			<label class="shop-filter-switch">
				<span class="shop-filter-switch__label"><?php esc_html_e( 'ارسال سریع', 'almas-land' ); ?></span>
				<input type="checkbox" name="fast_shipping" value="1" <?php checked( $state['fast_shipping'] ); ?>>
				<span class="shop-filter-switch__track" aria-hidden="true"><span class="shop-filter-switch__thumb"></span></span>
			</label>
		</section>

		<div class="shop-filter-actions">
			<button class="btn btn--primary" type="submit"><?php esc_html_e( 'اعمال فیلتر', 'almas-land' ); ?></button>
			<a class="btn btn--ghost" href="<?php echo esc_url( $action ); ?>"><?php esc_html_e( 'بازنشانی', 'almas-land' ); ?></a>
		</div>
	</form>
	<?php
}
