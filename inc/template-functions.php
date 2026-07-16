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
 * Resolve the first available attachment ID from candidates.
 *
 * @param int ...$ids Attachment IDs.
 * @return int
 */
function almasland_resolve_attachment_id( ...$ids ) {
	foreach ( $ids as $id ) {
		$id = absint( $id );
		if ( $id ) {
			return $id;
		}
	}

	return 0;
}

/**
 * Front page hero data for template rendering.
 *
 * @return array<string, mixed>|null
 */
function almasland_get_home_hero() {
	$homepage = almasland_get_panel_settings()['homepage'];

	if ( empty( $homepage['hero_enabled'] ) ) {
		return null;
	}

	$sliders    = almasland_get_enabled_sliders();
	$slider_id  = ! empty( $sliders[0]['image'] ) ? absint( $sliders[0]['image'] ) : 0;
	$legacy_url = almasland_get_option( 'hero_image', '' );
	$legacy_id  = $legacy_url ? attachment_url_to_postid( $legacy_url ) : 0;

	$desktop_id = almasland_resolve_attachment_id(
		$homepage['hero_image_desktop'] ?? 0,
		$slider_id,
		$legacy_id
	);
	$tablet_id = almasland_resolve_attachment_id(
		$homepage['hero_image_tablet'] ?? 0,
		$desktop_id
	);
	$mobile_id = almasland_resolve_attachment_id(
		$homepage['hero_image_mobile'] ?? 0,
		$tablet_id,
		$desktop_id
	);

	$button_url = ! empty( $homepage['hero_button_url'] ) ? $homepage['hero_button_url'] : '';
	if ( ! $button_url && ! empty( $sliders[0]['link'] ) ) {
		$button_url = $sliders[0]['link'];
	}
	if ( ! $button_url && class_exists( 'WooCommerce' ) ) {
		$button_url = almasland_get_default_shop_url();
	}

	$title = ! empty( $homepage['hero_title'] ) ? $homepage['hero_title'] : '';
	if ( ! $title && ! empty( $sliders[0]['title'] ) ) {
		$title = $sliders[0]['title'];
	}

	$text = ! empty( $homepage['hero_text'] ) ? $homepage['hero_text'] : '';
	if ( ! $text && ! empty( $sliders[0]['text'] ) ) {
		$text = $sliders[0]['text'];
	}

	$button_text = ! empty( $homepage['hero_button_text'] ) ? $homepage['hero_button_text'] : '';
	if ( ! $button_text && ! empty( $sliders[0]['button_text'] ) ) {
		$button_text = $sliders[0]['button_text'];
	}

	if ( ! $title && ! $text && ! $desktop_id ) {
		return null;
	}

	return array(
		'title'       => $title,
		'text'        => $text,
		'button_text' => $button_text,
		'link'        => $button_url,
		'images'      => array(
			'desktop' => $desktop_id ? almasland_get_attachment_url( $desktop_id, 'almasland-hero' ) : '',
			'tablet'  => $tablet_id ? almasland_get_attachment_url( $tablet_id, 'almasland-hero-tablet' ) : '',
			'mobile'  => $mobile_id ? almasland_get_attachment_url( $mobile_id, 'almasland-hero-mobile' ) : '',
		),
	);
}

/**
 * SVG icon markup for front-page trust items.
 *
 * @param string $icon Icon key.
 * @return string
 */
function almasland_get_home_trust_icon( $icon ) {
	$icons = array(
		'consult' => '<svg viewBox="0 0 48 48" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M18 30v3.5a2.5 2.5 0 0 0 4.3 1.7l2.2-2.2" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/><path d="M14 24.5V20a10 10 0 0 1 20 0v4.5" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"/><path d="M10 24.5h4M34 24.5h4" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"/><path d="M12.5 24.5h23v8.5a3 3 0 0 1-3 3h-17a3 3 0 0 1-3-3v-8.5Z" stroke="currentColor" stroke-width="2.2" stroke-linejoin="round"/></svg>',
		'shipping' => '<svg viewBox="0 0 48 48" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M8 32h2.8a4.2 4.2 0 1 0 8.2 0H29a4.2 4.2 0 1 0 8.1 0H40V22.5L34.5 17H28v15" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/><path d="M28 17V11h9.5L43 17v7" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/><path d="M8 22.5h14V32" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/></svg>',
		'test'     => '<svg viewBox="0 0 48 48" fill="none" xmlns="http://www.w3.org/2000/svg"><rect x="9" y="11" width="30" height="28" rx="4" stroke="currentColor" stroke-width="2.2"/><path d="M16 8.5V14M32 8.5V14M9 19.5h30" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"/><path d="M28.5 25.5H19.5L28.5 34.5" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"/></svg>',
		'guarantee' => '<svg viewBox="0 0 48 48" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M24 7.5 36.5 12v11.2c0 8.1-5.2 12.4-12.5 15.3C16.7 35.6 11.5 31.3 11.5 23.2V12L24 7.5Z" stroke="currentColor" stroke-width="2.2" stroke-linejoin="round"/><path d="M18.5 24.2 22.2 28l7.3-7.8" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/></svg>',
	);

	return $icons[ $icon ] ?? '';
}

/**
 * Trust-building items for the front page.
 *
 * @return array<int, array<string, mixed>>
 */
function almasland_get_home_trust_items() {
	$phone_display = almasland_get_option( 'phone', '۰۲۱-۸۸۸۸۶۹۵۹' );
	$phone_tel     = almasland_get_phone_tel();

	return array(
		array(
			'icon'          => almasland_get_home_trust_icon( 'consult' ),
			'title'         => __( 'مشاوره تخصصی', 'almas-land' ),
			'subtitle'      => __( 'قبل و بعد از خرید', 'almas-land' ),
			'tooltip_mode'  => 'hover-click',
			'tooltip_id'    => 'front-page-trust-consult',
			'tooltip_title' => __( 'تماس با کارشناسان', 'almas-land' ),
			'tooltip_text'  => $phone_display,
			'tooltip_link'  => $phone_tel ? 'tel:' . $phone_tel : '',
		),
		array(
			'icon'          => almasland_get_home_trust_icon( 'shipping' ),
			'title'         => __( 'ارسال سریع', 'almas-land' ),
			'subtitle'      => __( '۲۴ تا ۴۸ ساعت کاری', 'almas-land' ),
			'tooltip_mode'  => 'click',
			'tooltip_id'    => 'front-page-trust-shipping',
			'tooltip_title' => __( 'شرایط ارسال', 'almas-land' ),
			'tooltip_text'  => __( 'سفارش‌های تهران در همان روز یا حداکثر ۲۴ ساعت کاری ارسال می‌شوند. سایر شهرها بین ۲۴ تا ۴۸ ساعت کاری.', 'almas-land' ),
			'tooltip_link'  => '',
		),
		array(
			'icon'          => almasland_get_home_trust_icon( 'test' ),
			'title'         => __( '۷ روز مهلت تست', 'almas-land' ),
			'subtitle'      => __( 'بازگشت بدون قید و شرط', 'almas-land' ),
			'tooltip_mode'  => 'click',
			'tooltip_id'    => 'front-page-trust-test',
			'tooltip_title' => __( 'مهلت تست محصول', 'almas-land' ),
			'tooltip_text'  => __( 'تا ۷ روز پس از تحویل، در صورت نارضایتی می‌توانید محصول را بدون قید و شرط بازگردانید.', 'almas-land' ),
			'tooltip_link'  => '',
		),
		array(
			'icon'          => almasland_get_home_trust_icon( 'guarantee' ),
			'title'         => __( 'ضمانت اصالت کالا', 'almas-land' ),
			'subtitle'      => __( 'تمام محصولات تست فنی شده', 'almas-land' ),
			'tooltip_mode'  => 'click',
			'tooltip_id'    => 'front-page-trust-guarantee',
			'tooltip_title' => __( 'ضمانت اصالت و سلامت', 'almas-land' ),
			'tooltip_text'  => __( 'همه محصولات پیش از ارسال تست فنی می‌شوند و با ضمانت اصالت کالا به دست شما می‌رسند.', 'almas-land' ),
			'tooltip_link'  => '',
		),
	);
}

/**
 * Product category image for cards (thumbnail first, then larger sizes).
 *
 * @param int $term_id Category term ID.
 * @return array{url: string, width: int, height: int, srcset: string}
 */
function almasland_get_product_category_image( $term_id ) {
	$thumb_id = (int) get_term_meta( $term_id, 'thumbnail_id', true );

	if ( ! $thumb_id ) {
		$placeholder = function_exists( 'wc_placeholder_img_src' ) ? wc_placeholder_img_src() : '';

		return array(
			'url'    => $placeholder,
			'width'  => 120,
			'height' => 120,
			'srcset' => '',
		);
	}

	$sizes = array( 'woocommerce_thumbnail', 'medium', 'full' );

	foreach ( $sizes as $size ) {
		$url = wp_get_attachment_image_url( $thumb_id, $size );

		if ( ! $url ) {
			continue;
		}

		$meta = wp_get_attachment_image_src( $thumb_id, $size );

		return array(
			'url'    => $url,
			'width'  => isset( $meta[1] ) ? (int) $meta[1] : 120,
			'height' => isset( $meta[2] ) ? (int) $meta[2] : 120,
			'srcset' => (string) wp_get_attachment_image_srcset( $thumb_id, $size ),
		);
	}

	return array(
		'url'    => '',
		'width'  => 120,
		'height' => 120,
		'srcset' => '',
	);
}

/**
 * Product count for a WooCommerce category.
 *
 * @param WP_Term $term Category term.
 * @return int
 */
function almasland_get_product_category_count( $term ) {
	if ( function_exists( 'wc_get_term_product_count' ) ) {
		return (int) wc_get_term_product_count( $term->term_id, 'product_cat', true );
	}

	return (int) $term->count;
}

/**
 * Front page product categories for the categories grid.
 *
 * @param int $limit Maximum categories to return.
 * @return array<int, array<string, mixed>>
 */
function almasland_get_home_product_categories( $limit = 6 ) {
	if ( ! taxonomy_exists( 'product_cat' ) ) {
		return array();
	}

	$limit    = max( 1, (int) $limit );
	$shop     = almasland_get_panel_settings()['shop'];
	$cat_ids  = array_filter( array_map( 'absint', (array) ( $shop['featured_category_ids'] ?? array() ) ) );
	$terms    = array();
	$seen_ids = array();

	if ( $cat_ids ) {
		foreach ( $cat_ids as $cat_id ) {
			$term = get_term( $cat_id, 'product_cat' );

			if ( ! $term || is_wp_error( $term ) ) {
				continue;
			}

			$terms[]    = $term;
			$seen_ids[] = $term->term_id;

			if ( count( $terms ) >= $limit ) {
				break;
			}
		}
	}

	if ( count( $terms ) < $limit ) {
		$extra = get_terms(
			array(
				'taxonomy'   => 'product_cat',
				'parent'     => 0,
				'hide_empty' => true,
				'exclude'    => $seen_ids,
				'number'     => $limit - count( $terms ),
				'orderby'    => 'menu_order',
				'order'      => 'ASC',
			)
		);

		if ( ! is_wp_error( $extra ) && ! empty( $extra ) ) {
			$terms = array_merge( $terms, $extra );
		}
	}

	if ( empty( $terms ) ) {
		$terms = get_terms(
			array(
				'taxonomy'   => 'product_cat',
				'parent'     => 0,
				'hide_empty' => true,
				'number'     => $limit,
				'orderby'    => 'menu_order',
				'order'      => 'ASC',
			)
		);

		if ( is_wp_error( $terms ) ) {
			return array();
		}
	}

	$items = array();

	foreach ( array_slice( $terms, 0, $limit ) as $term ) {
		$link = get_term_link( $term );

		if ( is_wp_error( $link ) ) {
			continue;
		}

		$image = almasland_get_product_category_image( $term->term_id );
		$count = almasland_get_product_category_count( $term );

		$items[] = array(
			'id'          => $term->term_id,
			'name'        => $term->name,
			'url'         => $link,
			'image'       => $image,
			'count'       => $count,
			'count_label' => sprintf(
				/* translators: %s: product count */
				__( '%s محصول', 'almas-land' ),
				almasland_persian_digits( $count )
			),
		);
	}

	return $items;
}

/**
 * Best discount percent for homepage sale cards (supports variable products).
 *
 * @param WC_Product $product Product.
 * @return int
 */
function almasland_get_home_sale_discount_percent( $product ) {
	if ( ! $product || ! $product->is_on_sale() ) {
		return 0;
	}

	$percent = almasland_get_discount_percent( $product );

	if ( $percent > 0 ) {
		return $percent;
	}

	if ( ! $product->is_type( 'variable' ) ) {
		return 0;
	}

	$max_percent = 0;

	foreach ( $product->get_children() as $child_id ) {
		$variation = wc_get_product( $child_id );

		if ( ! $variation ) {
			continue;
		}

		$max_percent = max( $max_percent, almasland_get_discount_percent( $variation ) );
	}

	return $max_percent;
}

/**
 * Regular and sale prices for homepage offer cards.
 *
 * @param WC_Product $product Product.
 * @return array{regular: float, sale: float}
 */
function almasland_get_home_sale_prices( $product ) {
	if ( ! $product || ! $product->is_on_sale() ) {
		return array(
			'regular' => 0.0,
			'sale'    => 0.0,
		);
	}

	if ( $product->is_type( 'variable' ) ) {
		return array(
			'regular' => (float) $product->get_variation_regular_price( 'min', false ),
			'sale'    => (float) $product->get_variation_sale_price( 'min', false ),
		);
	}

	return array(
		'regular' => (float) $product->get_regular_price(),
		'sale'    => (float) $product->get_sale_price(),
	);
}

/**
 * Format a WooCommerce product for the front page special offers slider.
 *
 * @param WC_Product $product Product.
 * @return array<string, mixed>|null
 */
function almasland_format_home_sale_product( $product ) {
	if ( ! $product || ! $product->is_visible() || ! $product->is_on_sale() ) {
		return null;
	}

	$prices   = almasland_get_home_sale_prices( $product );
	$discount = almasland_get_home_sale_discount_percent( $product );

	if ( $prices['sale'] <= 0 ) {
		return null;
	}

	$image_id  = $product->get_image_id();
	$image_url = $image_id ? wp_get_attachment_image_url( $image_id, 'almasland-card' ) : '';

	if ( ! $image_url && function_exists( 'wc_placeholder_img_src' ) ) {
		$image_url = wc_placeholder_img_src();
	}

	$can_add_to_cart = $product->is_purchasable() && $product->is_in_stock() && $product->is_type( 'simple' );

	return array(
		'id'              => $product->get_id(),
		'name'            => $product->get_name(),
		'url'             => $product->get_permalink(),
		'image'           => $image_url,
		'discount'        => $discount,
		'discount_label'  => $discount > 0
			? sprintf(
				/* translators: %s: discount percent */
				__( '%s٪ تخفیف', 'almas-land' ),
				almasland_persian_digits( $discount )
			)
			: '',
		'price_html'      => almasland_persian_price( wc_price( $prices['sale'] ) ),
		'regular_html'    => $prices['regular'] > $prices['sale']
			? almasland_persian_price( wc_price( $prices['regular'] ) )
			: '',
		'can_add_to_cart' => $can_add_to_cart,
	);
}

/**
 * On-sale products for the front page special offers section.
 *
 * @param int $limit Maximum products.
 * @return array<int, array<string, mixed>>
 */
function almasland_get_home_sale_products( $limit = 12 ) {
	if ( ! class_exists( 'WooCommerce' ) ) {
		return array();
	}

	$limit    = max( 1, (int) $limit );
	$products = wc_get_products(
		array(
			'limit'   => $limit,
			'status'  => 'publish',
			'on_sale' => true,
			'orderby' => 'date',
			'order'   => 'DESC',
		)
	);

	if ( empty( $products ) ) {
		return array();
	}

	$items = array();

	foreach ( $products as $product ) {
		$formatted = almasland_format_home_sale_product( $product );

		if ( $formatted ) {
			$items[] = $formatted;
		}
	}

	return $items;
}

/**
 * Shop URL filtered to on-sale products.
 *
 * @return string
 */
function almasland_get_home_sale_products_url() {
	$shop_url = almasland_get_default_shop_url();

	return add_query_arg( 'on_sale', '1', $shop_url );
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
