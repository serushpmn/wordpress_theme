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
	return almasland_persian_price( $price_html );
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
 */
function almasland_remove_default_wc_single_hooks() {
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

/**
 * Product loop columns.
 *
 * @return int
 */
function almasland_loop_columns() {
	return 4;
}
add_filter( 'loop_shop_columns', 'almasland_loop_columns' );

/**
 * Products per page.
 *
 * @return int
 */
function almasland_products_per_page() {
	return 12;
}
add_filter( 'loop_shop_per_page', 'almasland_products_per_page' );

/**
 * Related products args.
 *
 * @param array $args Args.
 * @return array
 */
function almasland_related_products_args( $args ) {
	$args['posts_per_page'] = 3;
	$args['columns']        = 3;

	return $args;
}
add_filter( 'woocommerce_output_related_products_args', 'almasland_related_products_args' );

/**
 * Output related products with HTML prototype markup.
 */
function almasland_output_related_products() {
	global $product;

	if ( ! $product instanceof WC_Product ) {
		return;
	}

	$related = wc_get_related_products( $product->get_id(), 3 );
	if ( empty( $related ) ) {
		return;
	}

	$terms = wc_get_product_terms( $product->get_id(), 'product_cat', array( 'number' => 1 ) );
	$term  = ! empty( $terms ) && ! is_wp_error( $terms ) ? reset( $terms ) : null;

	wc_set_loop_prop( 'is_related', true );

	?>
	<section class="related-products" aria-labelledby="related-title">
		<div class="section-heading">
			<h2 id="related-title"><?php esc_html_e( 'کالاهای مشابه', 'almas-land' ); ?></h2>
			<?php if ( $term ) : ?>
				<a href="<?php echo esc_url( get_term_link( $term ) ); ?>"><?php esc_html_e( 'مشاهده همه', 'almas-land' ); ?></a>
			<?php endif; ?>
		</div>
		<?php woocommerce_related_products( array( 'posts_per_page' => 3, 'columns' => 3 ) ); ?>
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

	$source_product = $product->is_type( 'variation' ) && $product->get_parent_id() ? wc_get_product( $product->get_parent_id() ) : $product;
	$attributes     = array();

	if ( ! empty( $cart_item['variation'] ) ) {
		foreach ( $cart_item['variation'] as $name => $value ) {
			if ( '' === $value ) {
				continue;
			}

			$taxonomy = str_replace( 'attribute_', '', $name );
			$label    = wc_attribute_label( $taxonomy, $source_product );

			if ( taxonomy_exists( $taxonomy ) ) {
				$term = get_term_by( 'slug', $value, $taxonomy );
				$value = $term && ! is_wp_error( $term ) ? $term->name : $value;
			}

			$attributes[] = array(
				'label' => almasland_cart_attribute_label( $label ),
				'value' => almasland_cart_attribute_value( $value ),
			);
		}
	}

	if ( $source_product instanceof WC_Product ) {
		foreach ( $source_product->get_attributes() as $attribute ) {
			$label  = wc_attribute_label( $attribute->get_name(), $source_product );
			$values = array();

			if ( $attribute->is_taxonomy() ) {
				$terms = wc_get_product_terms( $source_product->get_id(), $attribute->get_name(), array( 'fields' => 'names' ) );
				$values = is_wp_error( $terms ) ? array() : $terms;
			} else {
				$values = $attribute->get_options();
			}

			if ( empty( $values ) ) {
				continue;
			}

			$attributes[] = array(
				'label' => almasland_cart_attribute_label( $label ),
				'value' => almasland_cart_attribute_value( implode( '، ', array_slice( $values, 0, 2 ) ) ),
			);
		}
	}

	$by_label = array();
	foreach ( $attributes as $attribute ) {
		if ( empty( $attribute['label'] ) || empty( $attribute['value'] ) ) {
			continue;
		}

		$by_label[ $attribute['label'] ][] = $attribute['value'];
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

	return array_slice( $features, 0, 4 );
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

	$stats['total'] = count(
		wc_get_orders(
			array(
				'customer' => $user_id,
				'limit'    => -1,
				'return'   => 'ids',
				'status'   => array_keys( wc_get_order_statuses() ),
			)
		)
	);

	$stats['active'] = count(
		wc_get_orders(
			array(
				'customer' => $user_id,
				'limit'    => -1,
				'return'   => 'ids',
				'status'   => array( 'wc-pending', 'wc-processing', 'wc-on-hold' ),
			)
		)
	);

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
	$data['almas_price_html'] = almasland_get_buy_price_html( $variation );
	if ( $variation && ! $variation->is_in_stock() ) {
		$data['price_html'] = '';
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
