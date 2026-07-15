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
 * @return array<string, array{label:string, orderby:string, order:string}>
 */
function almasland_get_shop_sort_options() {
	return array(
		'date'       => array(
			'label'   => __( 'جدیدترین', 'almas-land' ),
			'orderby' => 'date',
			'order'   => 'DESC',
		),
		'popularity' => array(
			'label'   => __( 'پرفروش‌ترین', 'almas-land' ),
			'orderby' => 'popularity',
			'order'   => 'DESC',
		),
		'rating'     => array(
			'label'   => __( 'محبوب‌ترین', 'almas-land' ),
			'orderby' => 'rating',
			'order'   => 'DESC',
		),
		'price'      => array(
			'label'   => __( 'ارزان‌ترین', 'almas-land' ),
			'orderby' => 'price',
			'order'   => 'ASC',
		),
		'price-desc' => array(
			'label'   => __( 'گران‌ترین', 'almas-land' ),
			'orderby' => 'price',
			'order'   => 'DESC',
		),
		'title'      => array(
			'label'   => __( 'الفبایی', 'almas-land' ),
			'orderby' => 'title',
			'order'   => 'ASC',
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
		'order',
		'min_price',
		'max_price',
		'in_stock',
		'on_sale',
		'featured',
		'new_arrival',
		'has_warranty',
		'min_rating',
		'filter_brand',
		'filter_color',
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
	$orderby      = isset( $_GET['orderby'] ) ? sanitize_key( wp_unslash( $_GET['orderby'] ) ) : 'date';
	$order        = isset( $_GET['order'] ) ? strtoupper( sanitize_key( wp_unslash( $_GET['order'] ) ) ) : 'DESC';
	$orderby      = isset( $sort_options[ $orderby ] ) ? $orderby : 'date';
	$order        = in_array( $order, array( 'ASC', 'DESC' ), true ) ? $order : 'DESC';

	$state = array(
		'orderby'      => $orderby,
		'order'        => $order,
		'min_price'    => isset( $_GET['min_price'] ) ? absint( wp_unslash( $_GET['min_price'] ) ) : 0,
		'max_price'    => isset( $_GET['max_price'] ) ? absint( wp_unslash( $_GET['max_price'] ) ) : 0,
		'in_stock'     => ! empty( $_GET['in_stock'] ),
		'on_sale'      => ! empty( $_GET['on_sale'] ),
		'featured'     => ! empty( $_GET['featured'] ),
		'new_arrival'  => ! empty( $_GET['new_arrival'] ),
		'has_warranty' => ! empty( $_GET['has_warranty'] ),
		'min_rating'   => isset( $_GET['min_rating'] ) ? min( 5, absint( wp_unslash( $_GET['min_rating'] ) ) ) : 0,
		'filter_brand' => isset( $_GET['filter_brand'] ) ? array_map( 'sanitize_text_field', (array) wp_unslash( $_GET['filter_brand'] ) ) : array(),
		'filter_color' => isset( $_GET['filter_color'] ) ? array_map( 'sanitize_title', (array) wp_unslash( $_GET['filter_color'] ) ) : array(),
		'filter_cat'   => isset( $_GET['filter_cat'] ) ? array_map( 'absint', (array) wp_unslash( $_GET['filter_cat'] ) ) : array(),
	);

	$state['filter_brand'] = array_values( array_filter( $state['filter_brand'] ) );
	$state['filter_color'] = array_values( array_filter( $state['filter_color'] ) );
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
		if ( ! isset( $_GET[ $key ] ) ) {
			continue;
		}

		if ( in_array( $key, array( 'orderby', 'order' ), true ) ) {
			$current[ $key ] = $state[ $key ];
			continue;
		}

		$value = wp_unslash( $_GET[ $key ] );
		if ( is_array( $value ) ) {
			$current[ $key ] = array_map( 'sanitize_text_field', $value );
		} elseif ( in_array( $key, array( 'min_price', 'max_price', 'min_rating' ), true ) ) {
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
 * Detect product color attribute taxonomy.
 *
 * @return string
 */
function almasland_get_color_attribute_taxonomy() {
	$candidates = array( 'pa_color', 'pa_colour', 'pa_رنگ' );
	foreach ( $candidates as $taxonomy ) {
		if ( taxonomy_exists( $taxonomy ) ) {
			return $taxonomy;
		}
	}

	$attributes = wc_get_attribute_taxonomies();
	foreach ( $attributes as $attribute ) {
		$name = $attribute->attribute_name;
		if ( false !== strpos( $name, 'color' ) || false !== strpos( $name, 'rang' ) || false !== strpos( $name, 'رنگ' ) ) {
			return 'pa_' . $name;
		}
	}

	return '';
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
 * Map color label to hex for swatches.
 *
 * @param string $label Color label.
 * @return string
 */
function almasland_get_color_swatch_hex( $label ) {
	$map = array(
		'black'   => '#111827',
		'white'   => '#f8fafc',
		'red'     => '#ef4444',
		'blue'    => '#2563eb',
		'green'   => '#16a34a',
		'yellow'  => '#facc15',
		'orange'  => '#f97316',
		'purple'  => '#8b5cf6',
		'pink'    => '#ec4899',
		'gray'    => '#94a3b8',
		'grey'    => '#94a3b8',
		'silver'  => '#cbd5e1',
		'gold'    => '#d4af37',
		'brown'   => '#92400e',
		'مشکی'    => '#111827',
		'سفید'    => '#f8fafc',
		'قرمز'    => '#ef4444',
		'آبی'     => '#2563eb',
		'سبز'     => '#16a34a',
		'زرد'     => '#facc15',
		'نارنجی'  => '#f97316',
		'بنفش'    => '#8b5cf6',
		'صورتی'   => '#ec4899',
		'خاکستری' => '#94a3b8',
		'نقره‌ای' => '#cbd5e1',
		'طلایی'   => '#d4af37',
	);

	$key = strtolower( trim( $label ) );
	if ( isset( $map[ $key ] ) ) {
		return $map[ $key ];
	}

	$key = trim( $label );
	if ( isset( $map[ $key ] ) ) {
		return $map[ $key ];
	}

	return '#' . substr( md5( $label ), 0, 6 );
}

/**
 * Get available color filter options.
 *
 * @return array<int, array{value:string, label:string, hex:string, count:int}>
 */
function almasland_get_shop_color_options() {
	$taxonomy = almasland_get_color_attribute_taxonomy();
	if ( ! $taxonomy ) {
		return array();
	}

	$terms = get_terms(
		array(
			'taxonomy'   => $taxonomy,
			'hide_empty' => true,
		)
	);

	if ( is_wp_error( $terms ) ) {
		return array();
	}

	$options = array();
	foreach ( $terms as $term ) {
		$options[] = array(
			'value' => $term->slug,
			'label' => $term->name,
			'hex'   => almasland_get_color_swatch_hex( $term->name ),
			'count' => (int) $term->count,
		);
	}

	return $options;
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
			'number'     => 12,
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
	if ( is_admin() || ! ( is_shop() || is_product_taxonomy() ) ) {
		return;
	}

	$state = almasland_get_shop_filter_state();

	$meta_query = (array) $query->get( 'meta_query' );
	$tax_query  = (array) $query->get( 'tax_query' );

	if ( $state['in_stock'] ) {
		$meta_query[] = array(
			'key'     => '_stock_status',
			'value'   => 'instock',
			'compare' => '=',
		);
	}

	if ( $state['on_sale'] ) {
		$meta_query[] = array(
			'key'     => '_sale_price',
			'value'   => 0,
			'compare' => '>',
			'type'    => 'NUMERIC',
		);
	}

	if ( $state['featured'] ) {
		$tax_query[] = array(
			'taxonomy' => 'product_visibility',
			'field'    => 'name',
			'terms'    => array( 'featured' ),
			'operator' => 'IN',
		);
	}

	if ( $state['new_arrival'] ) {
		$query->set(
			'date_query',
			array(
				array(
					'column' => 'post_date',
					'after'  => '30 days ago',
				),
			)
		);
	}

	if ( $state['has_warranty'] ) {
		$meta_query[] = array(
			'key'     => '_almas_warranty',
			'value'   => '',
			'compare' => '!=',
		);
	}

	if ( $state['min_rating'] > 0 ) {
		$meta_query[] = array(
			'key'     => '_wc_average_rating',
			'value'   => $state['min_rating'],
			'compare' => '>=',
			'type'    => 'DECIMAL',
		);
	}

	if ( $state['min_price'] > 0 ) {
		$meta_query[] = array(
			'key'     => '_price',
			'value'   => $state['min_price'],
			'compare' => '>=',
			'type'    => 'NUMERIC',
		);
	}

	if ( $state['max_price'] > 0 ) {
		$meta_query[] = array(
			'key'     => '_price',
			'value'   => $state['max_price'],
			'compare' => '<=',
			'type'    => 'NUMERIC',
		);
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

		if ( $meta_brands ) {
			$meta_query[] = array(
				'key'     => '_almas_brand',
				'value'   => $meta_brands,
				'compare' => 'IN',
			);
		}

		$brand_taxonomy = almasland_get_brand_attribute_taxonomy();
		if ( $tax_brands && $brand_taxonomy ) {
			$tax_query[] = array(
				'taxonomy' => $brand_taxonomy,
				'field'    => 'slug',
				'terms'    => $tax_brands,
				'operator' => 'IN',
			);
		}
	}

	if ( ! empty( $state['filter_color'] ) ) {
		$color_taxonomy = almasland_get_color_attribute_taxonomy();
		if ( $color_taxonomy ) {
			$tax_query[] = array(
				'taxonomy' => $color_taxonomy,
				'field'    => 'slug',
				'terms'    => $state['filter_color'],
				'operator' => 'IN',
			);
		}
	}

	if ( ! empty( $state['filter_cat'] ) && ! is_product_category() ) {
		$tax_query[] = array(
			'taxonomy' => 'product_cat',
			'field'    => 'term_id',
			'terms'    => $state['filter_cat'],
			'operator' => 'IN',
		);
	}

	if ( count( $meta_query ) > 1 ) {
		$meta_query['relation'] = 'AND';
	}
	if ( count( $tax_query ) > 1 ) {
		$tax_query['relation'] = 'AND';
	}

	if ( $meta_query ) {
		$query->set( 'meta_query', $meta_query );
	}
	if ( $tax_query ) {
		$query->set( 'tax_query', $tax_query );
	}
}
add_action( 'woocommerce_product_query', 'almasland_apply_shop_filters' );

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
	$state   = almasland_get_shop_filter_state();
	$options = almasland_get_shop_sort_options();
	$current = $state['orderby'];
	if ( 'price' === $current && 'ASC' === strtoupper( $state['order'] ) ) {
		$active_key = 'price';
	} elseif ( 'price' === $current && 'DESC' === strtoupper( $state['order'] ) ) {
		$active_key = 'price-desc';
	} else {
		$active_key = $current;
	}
	?>
	<div class="shop-sort-bar" role="toolbar" aria-label="<?php esc_attr_e( 'مرتب‌سازی محصولات', 'almas-land' ); ?>">
		<span class="shop-sort-bar__label"><?php esc_html_e( 'مرتب‌سازی:', 'almas-land' ); ?></span>
		<div class="shop-sort-bar__options">
			<?php foreach ( $options as $key => $option ) : ?>
				<?php
				$url = almasland_shop_filter_url(
					array(
						'orderby' => $option['orderby'],
						'order'   => $option['order'],
					)
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
 * Render active filter chips.
 */
function almasland_shop_active_filters() {
	$state    = almasland_get_shop_filter_state();
	$chips    = array();
	$brands   = almasland_get_shop_brand_options();
	$colors   = almasland_get_shop_color_options();
	$brandmap = array();
	foreach ( $brands as $brand ) {
		$brandmap[ $brand['value'] ] = $brand['label'];
	}
	$colormap = array();
	foreach ( $colors as $color ) {
		$colormap[ $color['value'] ] = $color['label'];
	}

	if ( $state['in_stock'] ) {
		$chips[] = array(
			'label' => __( 'فقط موجود', 'almas-land' ),
			'url'   => almasland_shop_filter_url( array(), array( 'in_stock' ) ),
		);
	}
	if ( $state['on_sale'] ) {
		$chips[] = array(
			'label' => __( 'تخفیف‌دار', 'almas-land' ),
			'url'   => almasland_shop_filter_url( array(), array( 'on_sale' ) ),
		);
	}
	if ( $state['featured'] ) {
		$chips[] = array(
			'label' => __( 'محصول ویژه', 'almas-land' ),
			'url'   => almasland_shop_filter_url( array(), array( 'featured' ) ),
		);
	}
	if ( $state['new_arrival'] ) {
		$chips[] = array(
			'label' => __( 'تازه‌ها', 'almas-land' ),
			'url'   => almasland_shop_filter_url( array(), array( 'new_arrival' ) ),
		);
	}
	if ( $state['has_warranty'] ) {
		$chips[] = array(
			'label' => __( 'گارانتی‌دار', 'almas-land' ),
			'url'   => almasland_shop_filter_url( array(), array( 'has_warranty' ) ),
		);
	}
	if ( $state['min_rating'] > 0 ) {
		$chips[] = array(
			'label' => sprintf( __( 'امتیاز %s+', 'almas-land' ), almasland_persian_digits( (string) $state['min_rating'] ) ),
			'url'   => almasland_shop_filter_url( array(), array( 'min_rating' ) ),
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

	foreach ( $state['filter_color'] as $color_value ) {
		$chips[] = array(
			'label' => isset( $colormap[ $color_value ] ) ? $colormap[ $color_value ] : $color_value,
			'url'   => almasland_shop_filter_url(
				array(
					'filter_color' => array_values(
						array_diff( $state['filter_color'], array( $color_value ) )
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
	?>
	<div class="shop-active-filters" aria-label="<?php esc_attr_e( 'فیلترهای فعال', 'almas-land' ); ?>">
		<?php foreach ( $chips as $chip ) : ?>
			<a class="shop-active-filter" href="<?php echo esc_url( $chip['url'] ); ?>">
				<span><?php echo esc_html( $chip['label'] ); ?></span>
				<svg viewBox="0 0 24 24" aria-hidden="true"><path d="m6.4 5 12.6 12.6-1.4 1.4L5 6.4 6.4 5Zm12.6 1.4L6.4 19 5 17.6 17.6 5 19 6.4Z"/></svg>
			</a>
		<?php endforeach; ?>
		<a class="shop-active-filter shop-active-filter--clear" href="<?php echo esc_url( is_shop() ? wc_get_page_permalink( 'shop' ) : get_term_link( get_queried_object() ) ); ?>">
			<?php esc_html_e( 'پاک کردن همه', 'almas-land' ); ?>
		</a>
	</div>
	<?php
}

/**
 * Render shop filter form.
 */
function almasland_shop_filter_form() {
	$state      = almasland_get_shop_filter_state();
	$action     = is_shop() ? wc_get_page_permalink( 'shop' ) : get_term_link( get_queried_object() );
	$categories = almasland_get_shop_category_options();
	$brands     = almasland_get_shop_brand_options();
	$colors     = almasland_get_shop_color_options();

	if ( is_wp_error( $action ) ) {
		$action = wc_get_page_permalink( 'shop' );
	}
	?>
	<form class="shop-filter-form" method="get" action="<?php echo esc_url( $action ); ?>">
		<?php if ( $state['orderby'] && 'date' !== $state['orderby'] ) : ?>
			<input type="hidden" name="orderby" value="<?php echo esc_attr( $state['orderby'] ); ?>">
		<?php endif; ?>
		<?php if ( $state['order'] && 'DESC' !== strtoupper( $state['order'] ) ) : ?>
			<input type="hidden" name="order" value="<?php echo esc_attr( $state['order'] ); ?>">
		<?php endif; ?>

		<div class="shop-filter-quick">
			<label class="shop-filter-toggle<?php echo $state['in_stock'] ? ' is-active' : ''; ?>">
				<input type="checkbox" name="in_stock" value="1" <?php checked( $state['in_stock'] ); ?>>
				<span><?php esc_html_e( 'فقط موجود', 'almas-land' ); ?></span>
			</label>
			<label class="shop-filter-toggle<?php echo $state['on_sale'] ? ' is-active' : ''; ?>">
				<input type="checkbox" name="on_sale" value="1" <?php checked( $state['on_sale'] ); ?>>
				<span><?php esc_html_e( 'تخفیف‌دار', 'almas-land' ); ?></span>
			</label>
			<label class="shop-filter-toggle<?php echo $state['featured'] ? ' is-active' : ''; ?>">
				<input type="checkbox" name="featured" value="1" <?php checked( $state['featured'] ); ?>>
				<span><?php esc_html_e( 'ویژه', 'almas-land' ); ?></span>
			</label>
			<label class="shop-filter-toggle<?php echo $state['new_arrival'] ? ' is-active' : ''; ?>">
				<input type="checkbox" name="new_arrival" value="1" <?php checked( $state['new_arrival'] ); ?>>
				<span><?php esc_html_e( 'تازه‌ها', 'almas-land' ); ?></span>
			</label>
			<label class="shop-filter-toggle<?php echo $state['has_warranty'] ? ' is-active' : ''; ?>">
				<input type="checkbox" name="has_warranty" value="1" <?php checked( $state['has_warranty'] ); ?>>
				<span><?php esc_html_e( 'گارانتی‌دار', 'almas-land' ); ?></span>
			</label>
		</div>

		<?php if ( $categories ) : ?>
			<details class="shop-filter-group" open>
				<summary><?php esc_html_e( 'دسته‌بندی', 'almas-land' ); ?></summary>
				<div class="shop-filter-group__body shop-filter-checklist">
					<?php foreach ( $categories as $term ) : ?>
						<label class="shop-filter-check">
							<input type="checkbox" name="filter_cat[]" value="<?php echo esc_attr( (string) $term->term_id ); ?>" <?php checked( in_array( (int) $term->term_id, $state['filter_cat'], true ) ); ?>>
							<span><?php echo esc_html( $term->name ); ?></span>
							<em><?php echo esc_html( almasland_persian_digits( (string) $term->count ) ); ?></em>
						</label>
					<?php endforeach; ?>
				</div>
			</details>
		<?php endif; ?>

		<?php if ( $brands ) : ?>
			<details class="shop-filter-group" open>
				<summary><?php esc_html_e( 'برند', 'almas-land' ); ?></summary>
				<div class="shop-filter-group__body shop-filter-checklist">
					<?php foreach ( $brands as $brand ) : ?>
						<label class="shop-filter-check">
							<input type="checkbox" name="filter_brand[]" value="<?php echo esc_attr( $brand['value'] ); ?>" <?php checked( in_array( $brand['value'], $state['filter_brand'], true ) ); ?>>
							<span><?php echo esc_html( $brand['label'] ); ?></span>
							<em><?php echo esc_html( almasland_persian_digits( (string) $brand['count'] ) ); ?></em>
						</label>
					<?php endforeach; ?>
				</div>
			</details>
		<?php endif; ?>

		<?php if ( $colors ) : ?>
			<details class="shop-filter-group" open>
				<summary><?php esc_html_e( 'رنگ', 'almas-land' ); ?></summary>
				<div class="shop-filter-group__body shop-filter-colors">
					<?php foreach ( $colors as $color ) : ?>
						<label class="shop-color-swatch<?php echo in_array( $color['value'], $state['filter_color'], true ) ? ' is-active' : ''; ?>" title="<?php echo esc_attr( $color['label'] ); ?>">
							<input type="checkbox" name="filter_color[]" value="<?php echo esc_attr( $color['value'] ); ?>" <?php checked( in_array( $color['value'], $state['filter_color'], true ) ); ?>>
							<span style="--swatch-color: <?php echo esc_attr( $color['hex'] ); ?>;"></span>
							<small><?php echo esc_html( $color['label'] ); ?></small>
						</label>
					<?php endforeach; ?>
				</div>
			</details>
		<?php endif; ?>

		<details class="shop-filter-group" open>
			<summary><?php esc_html_e( 'امتیاز محصول', 'almas-land' ); ?></summary>
			<div class="shop-filter-group__body shop-filter-rating">
				<?php foreach ( array( 4, 3 ) as $rating ) : ?>
					<label class="shop-filter-toggle<?php echo (int) $state['min_rating'] === $rating ? ' is-active' : ''; ?>">
						<input type="radio" name="min_rating" value="<?php echo esc_attr( (string) $rating ); ?>" <?php checked( (int) $state['min_rating'], $rating ); ?>>
						<span><?php echo esc_html( sprintf( __( '%s ستاره به بالا', 'almas-land' ), almasland_persian_digits( (string) $rating ) ) ); ?></span>
					</label>
				<?php endforeach; ?>
				<label class="shop-filter-toggle<?php echo 0 === (int) $state['min_rating'] ? ' is-active' : ''; ?>">
					<input type="radio" name="min_rating" value="0" <?php checked( (int) $state['min_rating'], 0 ); ?>>
					<span><?php esc_html_e( 'همه امتیازها', 'almas-land' ); ?></span>
				</label>
			</div>
		</details>

		<details class="shop-filter-group" open>
			<summary><?php esc_html_e( 'بازه قیمت (تومان)', 'almas-land' ); ?></summary>
			<div class="shop-filter-group__body shop-filter-price">
				<label>
					<span><?php esc_html_e( 'حداقل', 'almas-land' ); ?></span>
					<input type="number" name="min_price" min="0" step="100000" value="<?php echo $state['min_price'] ? esc_attr( (string) $state['min_price'] ) : ''; ?>" placeholder="۰">
				</label>
				<label>
					<span><?php esc_html_e( 'حداکثر', 'almas-land' ); ?></span>
					<input type="number" name="max_price" min="0" step="100000" value="<?php echo $state['max_price'] ? esc_attr( (string) $state['max_price'] ) : ''; ?>" placeholder="∞">
				</label>
			</div>
		</details>

		<div class="shop-filter-actions">
			<button class="btn btn--primary" type="submit"><?php esc_html_e( 'اعمال فیلتر', 'almas-land' ); ?></button>
			<a class="btn btn--ghost" href="<?php echo esc_url( $action ); ?>"><?php esc_html_e( 'بازنشانی', 'almas-land' ); ?></a>
		</div>
	</form>
	<?php
}
