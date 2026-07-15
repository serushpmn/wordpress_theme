<?php
/**
 * Reusable template helpers.
 *
 * @package AlmasLand
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Read a theme option from panel, then Customizer.
 *
 * @param string $key     Setting key without prefix.
 * @param mixed  $default Default value.
 * @return mixed
 */
function almasland_get_option( $key, $default = '' ) {
	if ( function_exists( 'almasland_theme_panel_legacy_map' ) ) {
		$map = almasland_theme_panel_legacy_map();
		if ( isset( $map[ $key ] ) ) {
			list( $group, $field ) = $map[ $key ];
			$settings = almasland_get_panel_settings();
			if ( isset( $settings[ $group ][ $field ] ) ) {
				$value = $settings[ $group ][ $field ];
				if ( is_bool( $value ) ) {
					return $value;
				}
				if ( null !== $value && '' !== $value ) {
					return $value;
				}
			}
		}
	}

	return get_theme_mod( 'almasland_' . $key, $default );
}

/**
 * Whether the notification bar should display.
 *
 * @return bool
 */
function almasland_is_notification_bar_visible() {
	$notifications = almasland_get_panel_settings()['notifications'];
	if ( empty( $notifications['bar_enabled'] ) || empty( $notifications['bar_text'] ) ) {
		return false;
	}

	$today = current_time( 'Y-m-d' );
	if ( ! empty( $notifications['bar_start'] ) && $today < $notifications['bar_start'] ) {
		return false;
	}
	if ( ! empty( $notifications['bar_end'] ) && $today > $notifications['bar_end'] ) {
		return false;
	}

	return true;
}

/**
 * Logo attachment ID for current context.
 *
 * @return int
 */
function almasland_get_logo_id() {
	$logo_main   = absint( almasland_get_panel( 'identity', 'logo_main', 0 ) );
	$logo_dark   = absint( almasland_get_panel( 'identity', 'logo_dark', 0 ) );
	$logo_mobile = absint( almasland_get_panel( 'identity', 'logo_mobile', 0 ) );

	if ( wp_is_mobile() && $logo_mobile ) {
		return $logo_mobile;
	}

	return $logo_main ?: $logo_dark;
}

/**
 * Site logo with fallback text.
 */
function almasland_site_logo() {
	$logo_id = almasland_get_logo_id();

	if ( ! $logo_id && has_custom_logo() ) {
		the_custom_logo();
		return;
	}

	if ( $logo_id ) {
		$alt = get_bloginfo( 'name' );
		printf(
			'<a class="logo custom-logo-link" href="%1$s" rel="home">%2$s</a>',
			esc_url( home_url( '/' ) ),
			wp_get_attachment_image( $logo_id, 'full', false, array( 'class' => 'custom-logo', 'alt' => $alt ) )
		);
		return;
	}
	?>
	<a class="logo" href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home">
		<span><?php echo esc_html( get_bloginfo( 'name' ) ); ?></span>
		<i aria-hidden="true"></i>
	</a>
	<?php
}

/**
 * Get contact page URL.
 *
 * @return string
 */
function almasland_get_contact_url() {
	$page_id = absint( almasland_get_option( 'contact_page_id', 0 ) );
	if ( $page_id ) {
		$url = get_permalink( $page_id );
		if ( $url ) {
			return $url;
		}
	}

	$page = get_page_by_path( 'contact' );
	if ( $page ) {
		return get_permalink( $page );
	}

	$contact_page = get_page_by_title( 'تماس' );
	if ( $contact_page ) {
		return get_permalink( $contact_page );
	}

	return home_url( '/' );
}

/**
 * Get phone as tel: friendly value.
 *
 * @return string
 */
function almasland_get_phone_tel() {
	return preg_replace( '/[^0-9+]/', '', almasland_get_option( 'phone', '02188886959' ) );
}

/**
 * Cart action for header.
 */
function almasland_header_cart() {
	if ( ! class_exists( 'WooCommerce' ) ) {
		return;
	}

	$count = WC()->cart ? WC()->cart->get_cart_contents_count() : 0;
	?>
	<a class="header-action header-action--cart" href="<?php echo esc_url( wc_get_cart_url() ); ?>" aria-label="<?php esc_attr_e( 'سبد خرید', 'almas-land' ); ?>">
		<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M7 18.5A1.5 1.5 0 1 0 7 21a1.5 1.5 0 0 0 0-2.5Zm10 0A1.5 1.5 0 1 0 17 21a1.5 1.5 0 0 0 0-2.5ZM6.2 6l.4 2h11.7l-1.1 5.2H8L6.4 4H3V2h5l.4 2H21l-2.2 11.2H7.8L7.3 13H19v2H7l-.8-4.2L5.3 6h.9Z"/></svg>
		<span class="cart-count" data-cart-count><?php echo esc_html( almasland_persian_digits( $count ) ); ?></span>
	</a>
	<?php
}

/**
 * Convert latin digits to Persian digits for UI.
 *
 * @param string|int|float $value Value.
 * @return string
 */
function almasland_persian_digits( $value ) {
	$value = (string) $value;

	return preg_replace_callback(
		'/&(?:#[0-9]+|#x[0-9a-fA-F]+|[a-zA-Z][a-zA-Z0-9]+);|[0-9]/',
		static function ( $matches ) {
			$token = $matches[0];

			if ( '&' === $token[0] ) {
				return $token;
			}

			$digits = array(
				'0' => '۰',
				'1' => '۱',
				'2' => '۲',
				'3' => '۳',
				'4' => '۴',
				'5' => '۵',
				'6' => '۶',
				'7' => '۷',
				'8' => '۸',
				'9' => '۹',
			);

			return $digits[ $token ];
		},
		$value
	);
}

/**
 * Convert WooCommerce price HTML to Persian digits without breaking currency entities.
 *
 * @param string $html Price HTML or plain text.
 * @return string
 */
function almasland_persian_price( $html ) {
	if ( ! is_scalar( $html ) || '' === (string) $html ) {
		return (string) $html;
	}

	$html = (string) $html;

	$decoded = html_entity_decode( $html, ENT_QUOTES | ENT_HTML5, 'UTF-8' );

	if ( false === strpos( $decoded, '<' ) ) {
		return almasland_persian_digits( $decoded );
	}

	return preg_replace_callback(
		'/>([^<]+)</u',
		static function ( $matches ) {
			return '>' . almasland_persian_digits( $matches[1] ) . '<';
		},
		$decoded
	);
}

/**
 * CSS class for the main landmark element.
 *
 * @return string
 */
function almasland_get_main_class() {
	$classes = array();

	if ( function_exists( 'is_woocommerce' ) && ( is_shop() || is_product_taxonomy() ) ) {
		$classes[] = 'category-page';
	}

	if ( function_exists( 'is_cart' ) && is_cart() ) {
		$classes[] = 'cart-page';
	}

	if ( function_exists( 'is_checkout' ) && is_checkout() && ! is_wc_endpoint_url( 'order-received' ) ) {
		$classes[] = 'cart-page';
		$classes[] = 'checkout-page';
	}

	return implode( ' ', $classes );
}

/**
 * Add HTML prototype body classes.
 *
 * @param string[] $classes Body classes.
 * @return string[]
 */
function almasland_body_class( $classes ) {
	if ( function_exists( 'is_product' ) && is_product() ) {
		$classes[] = 'product-detail-page';
	}

	if ( function_exists( 'is_account_page' ) && is_account_page() ) {
		$classes[] = 'woocommerce-account';
	}

	return $classes;
}
add_filter( 'body_class', 'almasland_body_class' );

/**
 * Breadcrumb output for pages, posts and WooCommerce screens.
 *
 * @param array $args Optional arguments.
 */
function almasland_breadcrumb( $args = array() ) {
	if ( is_front_page() ) {
		return;
	}

	$defaults = array(
		'class' => 'breadcrumb',
	);
	$args     = wp_parse_args( $args, $defaults );

	if ( function_exists( 'is_product' ) && is_product() ) {
		$args['class'] .= ' breadcrumb--product';
	}

	?>
	<nav class="<?php echo esc_attr( $args['class'] ); ?>" aria-label="<?php esc_attr_e( 'مسیر صفحه', 'almas-land' ); ?>">
		<?php if ( function_exists( 'is_product' ) && is_product() ) : ?>
			<a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'فروشگاه الماس لند', 'almas-land' ); ?></a>
			<?php
			$terms = wc_get_product_terms( get_the_ID(), 'product_cat', array( 'orderby' => 'parent', 'order' => 'ASC' ) );
			if ( ! empty( $terms ) && ! is_wp_error( $terms ) ) {
				$term = $terms[0];
				$ancestors = array_reverse( get_ancestors( $term->term_id, 'product_cat' ) );
				foreach ( $ancestors as $ancestor_id ) {
					$ancestor = get_term( $ancestor_id, 'product_cat' );
					if ( $ancestor && ! is_wp_error( $ancestor ) ) {
						printf( '<a href="%s">%s</a>', esc_url( get_term_link( $ancestor ) ), esc_html( $ancestor->name ) );
					}
				}
				printf( '<a href="%s">%s</a>', esc_url( get_term_link( $term ) ), esc_html( $term->name ) );
			}
			?>
			<span><?php echo esc_html( get_the_title() ); ?></span>
		<?php else : ?>
		<a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'خانه', 'almas-land' ); ?></a>
		<?php if ( function_exists( 'is_shop' ) && is_shop() ) : ?>
			<span><?php echo esc_html( woocommerce_page_title( false ) ); ?></span>
		<?php elseif ( function_exists( 'is_product_taxonomy' ) && is_product_taxonomy() ) : ?>
			<span><?php echo esc_html( single_term_title( '', false ) ); ?></span>
		<?php elseif ( function_exists( 'is_cart' ) && is_cart() ) : ?>
			<span><?php esc_html_e( 'سبد خرید', 'almas-land' ); ?></span>
		<?php elseif ( function_exists( 'is_checkout' ) && is_checkout() && ! is_wc_endpoint_url( 'order-received' ) ) : ?>
			<a href="<?php echo esc_url( wc_get_cart_url() ); ?>"><?php esc_html_e( 'سبد خرید', 'almas-land' ); ?></a>
			<span><?php esc_html_e( 'تسویه حساب', 'almas-land' ); ?></span>
		<?php elseif ( function_exists( 'is_account_page' ) && is_account_page() ) : ?>
			<span><?php esc_html_e( 'حساب کاربری', 'almas-land' ); ?></span>
		<?php elseif ( is_singular() ) : ?>
			<?php
			$ancestors = array_reverse( get_post_ancestors( get_the_ID() ) );
			foreach ( $ancestors as $ancestor ) :
				?>
				<a href="<?php echo esc_url( get_permalink( $ancestor ) ); ?>"><?php echo esc_html( get_the_title( $ancestor ) ); ?></a>
			<?php endforeach; ?>
			<span><?php echo esc_html( get_the_title() ); ?></span>
		<?php elseif ( is_archive() && ! ( function_exists( 'is_woocommerce' ) && is_woocommerce() ) ) : ?>
			<span><?php echo esc_html( wp_strip_all_tags( get_the_archive_title() ) ); ?></span>
		<?php elseif ( is_search() ) : ?>
			<span><?php printf( esc_html__( 'جستجو برای %s', 'almas-land' ), esc_html( get_search_query() ) ); ?></span>
		<?php elseif ( is_404() ) : ?>
			<span><?php esc_html_e( 'صفحه پیدا نشد', 'almas-land' ); ?></span>
		<?php endif; ?>
		<?php endif; ?>
	</nav>
	<?php
}

/**
 * Posted on meta.
 */
function almasland_post_meta() {
	?>
	<div class="post-meta">
		<span><?php echo esc_html( get_the_date() ); ?></span>
		<span><?php echo wp_kses_post( get_the_author_posts_link() ); ?></span>
		<?php if ( has_category() ) : ?>
			<span><?php echo wp_kses_post( get_the_category_list( '، ' ) ); ?></span>
		<?php endif; ?>
	</div>
	<?php
}

/**
 * Enabled slider items from panel.
 *
 * @return array<int, array<string, mixed>>
 */
function almasland_get_enabled_sliders() {
	$sliders = almasland_get_panel_settings()['sliders'];

	return array_values(
		array_filter(
			$sliders,
			static function ( $slide ) {
				return ! empty( $slide['enabled'] ) && ! empty( $slide['image'] );
			}
		)
	);
}

/**
 * Enabled banners for a homepage slot.
 *
 * @param string $slot banner_1 or banner_2.
 * @return array<int, array<string, mixed>>
 */
function almasland_get_home_banners( $slot ) {
	$banners = array_values(
		array_filter(
			almasland_get_panel_settings()['banners'],
			static function ( $banner ) {
				return ! empty( $banner['enabled'] ) && ( ! empty( $banner['image_desktop'] ) || ! empty( $banner['image_mobile'] ) );
			}
		)
	);

	if ( empty( $banners ) ) {
		return array();
	}

	$mid = (int) ceil( count( $banners ) / 2 );

	if ( 'banner_1' === $slot ) {
		return array_slice( $banners, 0, $mid );
	}

	return array_slice( $banners, $mid );
}

/**
 * Enabled custom blocks sorted by order.
 *
 * @return array<int, array<string, mixed>>
 */
function almasland_get_enabled_blocks() {
	$blocks = array_values(
		array_filter(
			almasland_get_panel_settings()['blocks'],
			static function ( $block ) {
				return ! empty( $block['enabled'] ) && ( ! empty( $block['title'] ) || ! empty( $block['description'] ) );
			}
		)
	);

	usort(
		$blocks,
		static function ( $a, $b ) {
			return (int) ( $a['order'] ?? 0 ) <=> (int) ( $b['order'] ?? 0 );
		}
	);

	return $blocks;
}

/**
 * Default shop URL for homepage CTAs.
 *
 * @return string
 */
function almasland_get_default_shop_url() {
	$panel_url = almasland_get_option( 'hero_button_url', '' );
	if ( $panel_url ) {
		return $panel_url;
	}

	return class_exists( 'WooCommerce' ) ? wc_get_page_permalink( 'shop' ) : home_url( '/' );
}

/**
 * Render a WooCommerce product loop for the hardcoded homepage.
 *
 * @param array<string, mixed> $args Query args for wc_get_products().
 * @return bool True when products were rendered.
 */
function almasland_render_home_product_loop( $args = array() ) {
	if ( ! class_exists( 'WooCommerce' ) ) {
		return false;
	}

	$defaults = array(
		'limit'   => 4,
		'status'  => 'publish',
		'orderby' => 'date',
		'order'   => 'DESC',
	);
	$query = wp_parse_args( $args, $defaults );
	$as_swiper = ! empty( $query['swiper'] );
	unset( $query['swiper'] );

	if ( ! empty( $query['category_candidates'] ) ) {
		$term = almasland_get_product_category_by_candidates( (array) $query['category_candidates'] );
		unset( $query['category_candidates'] );
		if ( $term instanceof WP_Term ) {
			$query['category'] = array( $term->slug );
		} elseif ( empty( $query['allow_empty_category'] ) ) {
			return false;
		}
		unset( $query['allow_empty_category'] );
	}

	$products = wc_get_products( $query );
	if ( empty( $products ) ) {
		return false;
	}

	$columns         = min( 4, count( $products ) );
	$loop_classes    = array( 'products', 'columns-' . $columns );
	$previous_product = isset( $GLOBALS['product'] ) ? $GLOBALS['product'] : null;
	$previous_post    = isset( $GLOBALS['post'] ) ? $GLOBALS['post'] : null;
	$previous_swiper  = wc_get_loop_prop( 'almasland_swiper' );

	if ( $as_swiper ) {
		$loop_classes[] = 'swiper-wrapper';
		$loop_classes[] = 'home-swiper-track';
	}

	wc_set_loop_prop( 'columns', $columns );
	wc_set_loop_prop( 'almasland_swiper', $as_swiper );

	printf( '<ul class="%s">', esc_attr( implode( ' ', $loop_classes ) ) );

	foreach ( $products as $product ) {
		$GLOBALS['product'] = $product;
		$GLOBALS['post']    = get_post( $product->get_id() );
		if ( $GLOBALS['post'] instanceof WP_Post ) {
			setup_postdata( $GLOBALS['post'] );
		}
		wc_get_template_part( 'content', 'product' );
	}

	echo '</ul>';

	if ( null === $previous_product ) {
		unset( $GLOBALS['product'] );
	} else {
		$GLOBALS['product'] = $previous_product;
	}

	if ( $previous_post instanceof WP_Post ) {
		$GLOBALS['post'] = $previous_post;
		setup_postdata( $GLOBALS['post'] );
	} else {
		unset( $GLOBALS['post'] );
		wp_reset_postdata();
	}

	wc_set_loop_prop( 'almasland_swiper', $previous_swiper );
	wc_reset_loop();

	return true;
}

/**
 * Resolve product category by candidate slugs/names.
 *
 * @param array<int, string> $candidates Candidate slugs/names.
 * @return WP_Term|null
 */
function almasland_get_product_category_by_candidates( $candidates ) {
	if ( ! taxonomy_exists( 'product_cat' ) ) {
		return null;
	}

	foreach ( $candidates as $candidate ) {
		$candidate = trim( (string) $candidate );
		if ( '' === $candidate ) {
			continue;
		}

		$term = get_term_by( 'slug', sanitize_title( $candidate ), 'product_cat' );
		if ( $term instanceof WP_Term ) {
			return $term;
		}

		$term = get_term_by( 'name', $candidate, 'product_cat' );
		if ( $term instanceof WP_Term ) {
			return $term;
		}
	}

	return null;
}

/**
 * Render a home section with one category and 4 products.
 *
 * @param string                $title      Section title.
 * @param array<int, string>    $candidates Category candidates.
 * @param array<string, string> $args       Optional args.
 * @return void
 */
function almasland_render_home_category_products_section( $title, $candidates, $args = array() ) {
	if ( ! class_exists( 'WooCommerce' ) ) {
		return;
	}

	$category = almasland_get_product_category_by_candidates( $candidates );
	if ( ! $category instanceof WP_Term ) {
		return;
	}

	$defaults = array(
		'orderby'      => 'date',
		'order'        => 'DESC',
		'show_view_all' => '1',
	);
	$args     = wp_parse_args( $args, $defaults );
	$section_id = 'home-cat-' . $category->slug;
	$shortcode  = sprintf(
		'[products category="%1$s" limit="4" columns="4" orderby="%2$s" order="%3$s" class="product-grid--home"]',
		esc_attr( $category->slug ),
		esc_attr( $args['orderby'] ),
		esc_attr( $args['order'] )
	);
	?>
	<section class="container product-section home-category-products" aria-labelledby="<?php echo esc_attr( $section_id ); ?>">
		<div class="section-heading">
			<h2 id="<?php echo esc_attr( $section_id ); ?>"><?php echo esc_html( $title ); ?></h2>
			<?php if ( '1' === (string) $args['show_view_all'] ) : ?>
				<a href="<?php echo esc_url( get_term_link( $category ) ); ?>"><?php esc_html_e( 'مشاهده همه', 'almas-land' ); ?></a>
			<?php endif; ?>
		</div>
		<?php echo do_shortcode( $shortcode ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
	</section>
	<?php
}

/**
 * Social links from theme panel.
 */
function almasland_social_links() {
	$labels = array(
		'instagram' => 'Instagram',
		'telegram'  => 'Telegram',
		'whatsapp'  => 'WhatsApp',
		'linkedin'  => 'LinkedIn',
		'youtube'   => 'YouTube',
		'aparat'    => 'Aparat',
		'x'         => 'X',
		'facebook'  => 'Facebook',
	);

	$social  = almasland_get_panel_settings()['social'];
	$links   = array();
	foreach ( $labels as $key => $label ) {
		if ( ! empty( $social[ $key ] ) ) {
			$links[ $label ] = $social[ $key ];
		}
	}

	if ( empty( $links ) ) {
		return;
	}
	?>
	<div class="social-links" aria-label="<?php esc_attr_e( 'شبکه‌های اجتماعی', 'almas-land' ); ?>">
		<?php foreach ( $links as $label => $url ) : ?>
			<a href="<?php echo esc_url( $url ); ?>" rel="noopener noreferrer" target="_blank"><?php echo esc_html( $label ); ?></a>
		<?php endforeach; ?>
	</div>
	<?php
}

/**
 * Query args that should persist in shop pagination links.
 *
 * @return array<string, mixed>
 */
function almasland_get_shop_pagination_add_args() {
	if ( ! function_exists( 'almasland_get_shop_filter_state' ) || ! function_exists( 'is_shop' ) || ( ! is_shop() && ! is_product_taxonomy() ) ) {
		return array();
	}

	$state    = almasland_get_shop_filter_state();
	$add_args = array();

	if ( 'date' !== $state['orderby'] || 'DESC' !== $state['order'] ) {
		$add_args['orderby'] = $state['orderby'];
		$add_args['order']   = $state['order'];
	}

	if ( $state['min_price'] ) {
		$add_args['min_price'] = $state['min_price'];
	}

	if ( $state['max_price'] ) {
		$add_args['max_price'] = $state['max_price'];
	}

	foreach ( array( 'in_stock', 'on_sale', 'featured', 'new_arrival', 'has_warranty' ) as $flag ) {
		if ( ! empty( $state[ $flag ] ) ) {
			$add_args[ $flag ] = 1;
		}
	}

	if ( $state['min_rating'] ) {
		$add_args['min_rating'] = $state['min_rating'];
	}

	if ( ! empty( $state['filter_brand'] ) ) {
		$add_args['filter_brand'] = $state['filter_brand'];
	}

	if ( ! empty( $state['filter_color'] ) ) {
		$add_args['filter_color'] = $state['filter_color'];
	}

	if ( ! empty( $state['filter_cat'] ) ) {
		$add_args['filter_cat'] = $state['filter_cat'];
	}

	return $add_args;
}

/**
 * Render numbered pagination markup shared across theme templates.
 *
 * @param array<string, mixed> $args Pagination args for paginate_links().
 */
function almasland_pagination( $args = array() ) {
	global $wp_query;

	$query = isset( $args['query'] ) ? $args['query'] : $wp_query;
	unset( $args['query'] );

	$total = isset( $args['total'] ) ? max( 1, (int) $args['total'] ) : max( 1, (int) $query->max_num_pages );

	if ( $total <= 1 ) {
		return;
	}

	$paged_query_var = max( 0, (int) get_query_var( 'paged' ) );
	$page_query_var  = max( 0, (int) get_query_var( 'page' ) );
	$current         = isset( $args['current'] ) ? max( 1, (int) $args['current'] ) : max( 1, $paged_query_var ?: $page_query_var ?: 1 );

	$defaults = array(
		'total'     => $total,
		'current'   => $current,
		'type'      => 'list',
		'mid_size'  => 2,
		'end_size'  => 1,
		'prev_text' => esc_html__( 'قبلی', 'almas-land' ),
		'next_text' => esc_html__( 'بعدی', 'almas-land' ),
	);

	$aria_label = isset( $args['aria_label'] ) ? $args['aria_label'] : esc_html__( 'صفحه‌بندی', 'almas-land' );
	unset( $args['aria_label'] );

	$args  = wp_parse_args( $args, $defaults );
	$links = paginate_links( $args );

	if ( ! $links ) {
		return;
	}

	echo '<nav class="pagination" aria-label="' . esc_attr( $aria_label ) . '">';
	echo $links; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	echo '</nav>';
}

/**
 * Pagination for WooCommerce account orders.
 *
 * @param int $current_page Current page.
 * @param int $max_pages    Total pages.
 */
function almasland_account_orders_pagination( $current_page, $max_pages ) {
	$max_pages    = max( 1, (int) $max_pages );
	$current_page = max( 1, (int) $current_page );

	if ( $max_pages <= 1 ) {
		return;
	}

	$base = esc_url_raw(
		str_replace(
			999999999,
			'%#%',
			wc_get_endpoint_url( 'orders', 999999999, wc_get_page_permalink( 'myaccount' ) )
		)
	);

	almasland_pagination(
		array(
			'base'       => $base,
			'format'     => '',
			'current'    => $current_page,
			'total'      => $max_pages,
			'add_args'   => false,
			'aria_label' => esc_html__( 'صفحه‌بندی سفارش‌ها', 'almas-land' ),
		)
	);
}

/**
 * Shared wp_link_pages() args for multi-page posts and pages.
 *
 * @return array<string, string>
 */
function almasland_wp_link_pages_args() {
	return array(
		'before'           => '<nav class="pagination page-links" aria-label="' . esc_attr__( 'صفحات مطلب', 'almas-land' ) . '">',
		'after'            => '</nav>',
		'link_before'      => '',
		'link_after'       => '',
		'next_or_number'   => 'number',
		'separator'        => '',
		'nextpagelink'     => esc_html__( 'بعدی', 'almas-land' ),
		'previouspagelink' => esc_html__( 'قبلی', 'almas-land' ),
	);
}
