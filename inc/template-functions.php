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
 * Parse a hex color into RGB components.
 *
 * @param string $hex Hex color.
 * @return array{0:int,1:int,2:int}|null
 */
function almasland_hex_to_rgb( $hex ) {
	$hex = ltrim( (string) $hex, '#' );
	if ( 3 === strlen( $hex ) ) {
		$hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
	}
	if ( ! preg_match( '/^[0-9a-fA-F]{6}$/', $hex ) ) {
		return null;
	}

	return array(
		hexdec( substr( $hex, 0, 2 ) ),
		hexdec( substr( $hex, 2, 2 ) ),
		hexdec( substr( $hex, 4, 2 ) ),
	);
}

/**
 * Darken a hex color by a percentage.
 *
 * @param string $hex     Hex color.
 * @param int    $percent Percent to darken (0–100).
 * @return string
 */
function almasland_darken_hex( $hex, $percent = 14 ) {
	$rgb = almasland_hex_to_rgb( $hex );
	if ( ! $rgb ) {
		return $hex;
	}

	$factor = max( 0, min( 1, 1 - ( (int) $percent / 100 ) ) );

	return sprintf(
		'#%02x%02x%02x',
		(int) round( $rgb[0] * $factor ),
		(int) round( $rgb[1] * $factor ),
		(int) round( $rgb[2] * $factor )
	);
}

/**
 * Build :root CSS custom properties from theme panel colors.
 * Only variables — no element rules (keeps footer / dark mode intact).
 *
 * @return string
 */
function almasland_get_theme_color_css() {
	$primary      = sanitize_hex_color( almasland_get_panel( 'identity', 'primary_color', almasland_get_option( 'primary_color', '#ff3f5f' ) ) ) ?: '#ff3f5f';
	$secondary    = sanitize_hex_color( almasland_get_panel( 'identity', 'secondary_color', almasland_get_option( 'secondary_color', '#2457d6' ) ) ) ?: '#2457d6';
	$button       = sanitize_hex_color( almasland_get_panel( 'identity', 'button_color', '' ) ) ?: $primary;
	$link         = sanitize_hex_color( almasland_get_panel( 'identity', 'link_color', '' ) ) ?: $secondary;
	$primary_dark = almasland_darken_hex( $primary, 14 );
	$button_dark  = almasland_darken_hex( $button, 14 );
	$rgb          = almasland_hex_to_rgb( $primary );
	$rgb_csv      = $rgb ? implode( ', ', $rgb ) : '255, 63, 95';

	$brand_vars = array(
		'--color-primary'            => $primary,
		'--color-primary-dark'       => $primary_dark,
		'--color-primary-rgb'        => $rgb_csv,
		'--color-shadow-primary-rgb' => $rgb_csv,
		'--color-secondary'          => $secondary,
		'--color-button'             => $button,
		'--color-button-dark'        => $button_dark,
		'--color-link'               => $link,
		'--color-promo-start'        => $primary_dark,
		'--color-promo-end'          => $primary,
		'--header-accent'            => $primary,
		'--footer-accent'            => $primary,
		'--offers-accent'            => $primary,
		'--catalog-accent'           => $primary,
		'--category-accent'          => $primary,
		'--trust-accent'             => $primary,
		'--why-accent'               => $primary,
		'--features-accent'          => $primary,
		'--checkout-trust-accent'    => $primary,
	);

	$light_only = array(
		'--color-primary-soft' => 'color-mix(in srgb, ' . $primary . ' 14%, #ffffff)',
	);

	$dark_only = array(
		'--color-primary-soft' => 'color-mix(in srgb, ' . $primary . ' 22%, #101827)',
	);

	$build = static function ( array $vars ) {
		$css = '';
		foreach ( $vars as $name => $value ) {
			$css .= $name . ':' . $value . ';';
		}
		return $css;
	};

	return ':root{' . $build( array_merge( $brand_vars, $light_only ) ) . '}'
		. '[data-theme="dark"]{' . $build( array_merge( $brand_vars, $dark_only ) ) . '}';
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
 * Logo attachment IDs for desktop / mobile / dark contexts.
 *
 * @return array{main:int,dark:int,mobile:int}
 */
function almasland_get_logo_ids() {
	return array(
		'main'   => absint( almasland_get_panel( 'identity', 'logo_main', 0 ) ),
		'dark'   => absint( almasland_get_panel( 'identity', 'logo_dark', 0 ) ),
		'mobile' => absint( almasland_get_panel( 'identity', 'logo_mobile', 0 ) ),
	);
}

/**
 * Logo attachment ID for current context (legacy helper).
 *
 * @return int
 */
function almasland_get_logo_id() {
	$ids = almasland_get_logo_ids();

	if ( wp_is_mobile() && $ids['mobile'] ) {
		return $ids['mobile'];
	}

	return $ids['main'] ?: $ids['dark'];
}

/**
 * Render a logo <img> with responsive-safe attributes.
 *
 * @param int    $attachment_id Attachment ID.
 * @param string $class         Extra classes.
 * @param string $alt           Alt text.
 * @return string
 */
function almasland_get_logo_image_html( $attachment_id, $class = 'custom-logo', $alt = '' ) {
	$attachment_id = absint( $attachment_id );
	if ( ! $attachment_id ) {
		return '';
	}

	return wp_get_attachment_image(
		$attachment_id,
		'full',
		false,
		array(
			'class'    => $class,
			'alt'      => $alt ? $alt : get_bloginfo( 'name' ),
			'loading'  => 'eager',
			'decoding' => 'async',
			'sizes'    => '(max-width: 960px) 140px, 220px',
		)
	);
}

/**
 * Site logo with fallback text.
 */
function almasland_site_logo() {
	$ids     = almasland_get_logo_ids();
	$tagline = get_bloginfo( 'description' );
	if ( ! $tagline ) {
		$tagline = __( 'تجهیزات دیجیتال با ضمانت', 'almas-land' );
	}

	$alt          = get_bloginfo( 'name' );
	$main_id      = $ids['main'] ?: $ids['dark'];
	$mobile_id    = $ids['mobile'] ?: $main_id;
	$dark_id      = $ids['dark'] ?: $main_id;
	$has_panel    = (bool) ( $main_id || $mobile_id || $dark_id );

	if ( ! $has_panel && has_custom_logo() ) {
		echo '<div class="logo logo--image">';
		the_custom_logo();
		echo '<span class="logo__tagline">' . esc_html( $tagline ) . '</span>';
		echo '</div>';
		return;
	}

	if ( $has_panel ) {
		$desktop_html = almasland_get_logo_image_html( $main_id, 'custom-logo custom-logo--desktop custom-logo--light', $alt );
		$dark_html    = almasland_get_logo_image_html( $dark_id, 'custom-logo custom-logo--desktop custom-logo--dark', $alt );
		$mobile_html  = almasland_get_logo_image_html( $mobile_id, 'custom-logo custom-logo--mobile', $alt );

		if ( ! $desktop_html && $mobile_html ) {
			$desktop_html = str_replace( 'custom-logo--mobile', 'custom-logo--desktop custom-logo--light', $mobile_html );
		}
		if ( ! $dark_html && $desktop_html ) {
			$dark_html = str_replace( 'custom-logo--light', 'custom-logo--dark', $desktop_html );
		}

		printf(
			'<a class="logo logo--image custom-logo-link" href="%1$s" rel="home"><span class="logo__media">%2$s%3$s%4$s</span><span class="logo__tagline">%5$s</span></a>',
			esc_url( home_url( '/' ) ),
			$desktop_html, // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			$dark_html, // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			$mobile_html, // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			esc_html( $tagline )
		);
		return;
	}
	?>
	<a class="logo" href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home">
		<span class="logo__mark" aria-hidden="true"></span>
		<span class="logo__copy">
			<strong class="logo__name"><?php echo esc_html( get_bloginfo( 'name' ) ); ?></strong>
			<span class="logo__tagline"><?php echo esc_html( $tagline ); ?></span>
		</span>
	</a>
	<?php
}

/**
 * Get contact page URL.
 *
 * @return string
 */
function almasland_get_page_by_title( $title, $post_type = 'page' ) {
	$title = trim( (string) $title );
	if ( '' === $title ) {
		return null;
	}

	$query = new WP_Query(
		array(
			'post_type'              => $post_type,
			'title'                  => $title,
			'post_status'            => 'publish',
			'posts_per_page'         => 1,
			'no_found_rows'          => true,
			'ignore_sticky_posts'    => true,
			'update_post_term_cache' => false,
			'update_post_meta_cache' => false,
		)
	);

	if ( ! empty( $query->posts[0] ) ) {
		return $query->posts[0];
	}

	return null;
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

	$contact_page = almasland_get_page_by_title( 'تماس' );
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
 * Account action for header.
 */
function almasland_header_account() {
	$account_url = class_exists( 'WooCommerce' ) ? wc_get_page_permalink( 'myaccount' ) : wp_login_url();

	if ( is_user_logged_in() ) {
		$user    = wp_get_current_user();
		$initial = function_exists( 'almasland_get_user_avatar_initial' )
			? almasland_get_user_avatar_initial( $user )
			: mb_substr( $user->display_name, 0, 1, 'UTF-8' );
		$label   = $user->first_name ? $user->first_name : __( 'حساب من', 'almas-land' );
		?>
		<a class="header-action header-action--account is-logged-in" href="<?php echo esc_url( $account_url ); ?>" aria-label="<?php esc_attr_e( 'پنل کاربری', 'almas-land' ); ?>">
			<span class="header-action__icon header-action__avatar" aria-hidden="true"><?php echo esc_html( $initial ); ?></span>
			<span><?php echo esc_html( $label ); ?></span>
		</a>
		<?php
		return;
	}
	?>
	<a class="header-action header-action--account" href="<?php echo esc_url( $account_url ); ?>">
		<svg viewBox="0 0 24 24" aria-hidden="true" fill="none"><circle cx="12" cy="8" r="3.4" stroke="currentColor" stroke-width="1.8"/><path d="M5.5 19c1.4-3.2 3.8-4.8 6.5-4.8s5.1 1.6 6.5 4.8" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>
		<span><?php esc_html_e( 'ورود / ثبت‌نام', 'almas-land' ); ?></span>
	</a>
	<?php
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
		<span class="header-action__icon">
			<svg viewBox="0 0 24 24" aria-hidden="true" fill="none"><path d="M7 18.5A1.5 1.5 0 1 0 7 21a1.5 1.5 0 0 0 0-2.5Zm10 0A1.5 1.5 0 1 0 17 21a1.5 1.5 0 0 0 0-2.5ZM6.2 6l.4 2h11.7l-1.1 5.2H8L6.4 4H3V2h5l.4 2H21l-2.2 11.2H7.8L7.3 13H19v2H7l-.8-4.2L5.3 6h.9Z" fill="currentColor"/></svg>
		<span class="cart-count" data-cart-count><?php echo esc_html( almasland_persian_digits( $count ) ); ?></span>
		</span>
		<span><?php esc_html_e( 'سبد خرید', 'almas-land' ); ?></span>
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
		<span class="post-meta__item post-meta__item--date"><?php echo esc_html( almasland_persian_digits( get_the_date() ) ); ?></span>
		<span class="post-meta__item post-meta__item--author"><?php echo wp_kses_post( get_the_author_posts_link() ); ?></span>
		<?php if ( has_category() ) : ?>
			<span class="post-meta__item post-meta__item--cats"><?php echo wp_kses_post( get_the_category_list( '، ' ) ); ?></span>
		<?php endif; ?>
		<span class="post-meta__item post-meta__item--read"><?php echo esc_html( almasland_get_reading_time() ); ?></span>
	</div>
	<?php
}

/**
 * Estimated reading time for the current post.
 *
 * @param int $post_id Optional post ID.
 * @return string
 */
function almasland_get_reading_time( $post_id = 0 ) {
	$post_id = $post_id ? absint( $post_id ) : get_the_ID();
	$content = (string) get_post_field( 'post_content', $post_id );
	$chars   = mb_strlen( wp_strip_all_tags( $content ) );
	$minutes = max( 1, (int) ceil( $chars / 1000 ) );

	return sprintf(
		/* translators: %s: minutes */
		__( '%s دقیقه مطالعه', 'almas-land' ),
		almasland_persian_digits( (string) $minutes )
	);
}

/**
 * Blog category chips for archive headers.
 *
 * @param int $limit Max categories.
 * @return void
 */
function almasland_blog_category_chips( $limit = 10 ) {
	$terms = get_categories(
		array(
			'hide_empty' => true,
			'number'     => max( 1, (int) $limit ),
			'orderby'    => 'count',
			'order'      => 'DESC',
		)
	);

	if ( empty( $terms ) || is_wp_error( $terms ) ) {
		return;
	}

	$current = is_category() ? (int) get_queried_object_id() : 0;
	$all_url = (int) get_option( 'page_for_posts' )
		? get_permalink( (int) get_option( 'page_for_posts' ) )
		: ( get_post_type_archive_link( 'post' ) ?: home_url( '/' ) );
	?>
	<nav class="blog-chips" aria-label="<?php esc_attr_e( 'دسته‌بندی مقالات', 'almas-land' ); ?>">
		<a class="blog-chip<?php echo 0 === $current ? ' is-active' : ''; ?>" href="<?php echo esc_url( $all_url ); ?>">
			<?php esc_html_e( 'همه', 'almas-land' ); ?>
		</a>
		<?php foreach ( $terms as $term ) : ?>
			<a
				class="blog-chip<?php echo (int) $term->term_id === $current ? ' is-active' : ''; ?>"
				href="<?php echo esc_url( get_category_link( $term ) ); ?>"
			>
				<?php echo esc_html( $term->name ); ?>
				<em><?php echo esc_html( almasland_persian_digits( (string) $term->count ) ); ?></em>
			</a>
		<?php endforeach; ?>
	</nav>
	<?php
}

/**
 * Related blog posts by shared categories.
 *
 * @param int $limit Max posts.
 * @return void
 */
function almasland_related_posts( $limit = 3 ) {
	$post_id = get_the_ID();
	$cats    = wp_get_post_categories( $post_id );

	if ( ! $cats ) {
		return;
	}

	$query = new WP_Query(
		array(
			'post_type'           => 'post',
			'posts_per_page'      => max( 1, (int) $limit ),
			'post__not_in'        => array( $post_id ),
			'category__in'        => $cats,
			'ignore_sticky_posts' => true,
			'no_found_rows'       => true,
		)
	);

	if ( ! $query->have_posts() ) {
		return;
	}
	?>
	<section class="blog-related" aria-labelledby="blog-related-title">
		<header class="blog-related__header">
			<h2 id="blog-related-title"><?php esc_html_e( 'مقالات مرتبط', 'almas-land' ); ?></h2>
			<p><?php esc_html_e( 'ادامه مطالعه درباره همین موضوع', 'almas-land' ); ?></p>
		</header>
		<div class="blog-grid blog-grid--related">
			<?php
			while ( $query->have_posts() ) :
				$query->the_post();
				get_template_part( 'template-parts/content' );
			endwhile;
			wp_reset_postdata();
			?>
		</div>
	</section>
	<?php
}

/**
 * Enabled hero slides from panel.
 *
 * @return array<int, array<string, mixed>>
 */
function almasland_get_home_hero_slides() {
	$homepage = almasland_get_panel_settings()['homepage'];

	if ( empty( $homepage['hero_enabled'] ) ) {
		return array();
	}

	$slides = isset( $homepage['hero_slides'] ) && is_array( $homepage['hero_slides'] ) ? $homepage['hero_slides'] : array();

	return array_values(
		array_filter(
			array_map(
				static function ( $slide ) {
					$desktop_id = absint( $slide['image_desktop'] ?? 0 );
					$mobile_id  = absint( $slide['image_mobile'] ?? 0 );

					if ( empty( $slide['enabled'] ) || ( ! $desktop_id && ! $mobile_id ) ) {
						return null;
					}

					if ( ! $desktop_id ) {
						$desktop_id = $mobile_id;
					}
					if ( ! $mobile_id ) {
						$mobile_id = $desktop_id;
					}

					$desktop_url = almasland_get_attachment_url( $desktop_id, 'almasland-hero' );
					$mobile_url  = almasland_get_attachment_url( $mobile_id, 'almasland-hero-mobile' );

					if ( ! $desktop_url && ! $mobile_url ) {
						return null;
					}

					return array(
						'link'   => ! empty( $slide['link'] ) ? $slide['link'] : '',
						'alt'    => ! empty( $slide['alt'] ) ? $slide['alt'] : __( 'بنر تبلیغاتی', 'almas-land' ),
						'images' => array(
							'desktop' => $desktop_url ?: $mobile_url,
							'mobile'  => $mobile_url ?: $desktop_url,
						),
					);
				},
				$slides
			)
		)
	);
}

/**
 * Hero slider settings for front-end JS.
 *
 * @return array{autoplay:bool,interval:int}
 */
function almasland_get_home_hero_slider_settings() {
	$homepage = almasland_get_panel_settings()['homepage'];

	return array(
		'autoplay' => ! empty( $homepage['hero_autoplay'] ),
		'interval' => max( 2000, absint( $homepage['hero_interval'] ?? 5000 ) ),
	);
}

/**
 * Enabled slider items from panel (legacy).
 *
 * @return array<int, array<string, mixed>>
 */
function almasland_get_enabled_sliders() {
	$slides = almasland_get_home_hero_slides();

	return array_map(
		static function ( $slide ) {
			return array(
				'image' => 0,
				'link'  => $slide['link'],
				'title' => $slide['alt'],
				'enabled' => true,
			);
		},
		$slides
	);
}

/**
 * Front page hero data for template rendering (legacy single slide).
 *
 * @return array<string, mixed>|null
 */
function almasland_get_home_hero() {
	$slides = almasland_get_home_hero_slides();

	if ( empty( $slides ) ) {
		return null;
	}

	$first = $slides[0];

	return array(
		'title'       => $first['alt'],
		'text'        => '',
		'button_text' => '',
		'link'        => $first['link'],
		'images'      => $first['images'],
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
 * SVG icons for the front-page features bar.
 *
 * @param string $icon Icon key.
 * @return string
 */
function almasland_get_home_features_icon( $icon ) {
	$icons = array(
		'packaging' => '<svg viewBox="0 0 48 48" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M24 8.5 38 15.5v17L24 39.5 10 32.5v-17L24 8.5Z" stroke="currentColor" stroke-width="2.2" stroke-linejoin="round"/><path d="M24 22.5 38 15.5M24 22.5 10 15.5M24 22.5V39.5" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/></svg>',
		'support'   => '<svg viewBox="0 0 48 48" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M18 30v3.5a2.5 2.5 0 0 0 4.3 1.7l2.2-2.2" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/><path d="M14 24.5V20a10 10 0 0 1 20 0v4.5" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"/><path d="M10 24.5h4M34 24.5h4" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"/><path d="M12.5 24.5h23v8.5a3 3 0 0 1-3 3h-17a3 3 0 0 1-3-3v-8.5Z" stroke="currentColor" stroke-width="2.2" stroke-linejoin="round"/></svg>',
		'price'     => '<svg viewBox="0 0 48 48" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M24 9.5 28.2 14l5.8.6-4.3 4.1 1.2 5.7L24 21.8l-6.9 2.6 1.2-5.7-4.3-4.1 5.8-.6L24 9.5Z" stroke="currentColor" stroke-width="2.2" stroke-linejoin="round"/><path d="M16.5 27.5h15M18.5 32.5h11M20.5 37.5h7" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"/></svg>',
		'payment'   => '<svg viewBox="0 0 48 48" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M24 7.5 36.5 12v11.2c0 8.1-5.2 12.4-12.5 15.3C16.7 35.6 11.5 31.3 11.5 23.2V12L24 7.5Z" stroke="currentColor" stroke-width="2.2" stroke-linejoin="round"/><path d="M18.5 24.2 22.2 28l7.3-7.8" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/></svg>',
	);

	return $icons[ $icon ] ?? '';
}

/**
 * Front-page service feature items.
 *
 * @return array<int, array<string, string>>
 */
function almasland_get_home_features_items() {
	return array(
		array(
			'icon'     => almasland_get_home_features_icon( 'packaging' ),
			'title'    => __( 'بسته‌بندی مطمئن', 'almas-land' ),
			'subtitle' => __( 'ارسال ایمن محصولات', 'almas-land' ),
		),
		array(
			'icon'     => almasland_get_home_features_icon( 'support' ),
			'title'    => __( 'پشتیبانی ۲۴/۷', 'almas-land' ),
			'subtitle' => __( 'همیشه در کنار شما', 'almas-land' ),
		),
		array(
			'icon'     => almasland_get_home_features_icon( 'price' ),
			'title'    => __( 'تضمین بهترین قیمت', 'almas-land' ),
			'subtitle' => __( 'قیمت رقابتی بازار', 'almas-land' ),
		),
		array(
			'icon'     => almasland_get_home_features_icon( 'payment' ),
			'title'    => __( 'پرداخت امن', 'almas-land' ),
			'subtitle' => __( 'درگاه پرداخت معتبر', 'almas-land' ),
		),
	);
}

/**
 * SVG icons for the "Why Almas Land" section.
 *
 * @param string $icon Icon key.
 * @return string
 */
function almasland_get_home_why_icon( $icon ) {
	$icons = array(
		'customers' => '<svg viewBox="0 0 48 48" fill="none" xmlns="http://www.w3.org/2000/svg"><circle cx="24" cy="16" r="6.5" stroke="currentColor" stroke-width="2.2"/><path d="M12.5 36.5c1.8-6.2 6.2-9.2 11.5-9.2s9.7 3 11.5 9.2" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"/></svg>',
		'years'     => '<svg viewBox="0 0 48 48" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M16 14h16l3.5 5.5V36a3 3 0 0 1-3 3H15.5a3 3 0 0 1-3-3V19.5L16 14Z" stroke="currentColor" stroke-width="2.2" stroke-linejoin="round"/><path d="M16 14v5.5h16V14M20 26.5h8M20 31.5h8" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"/></svg>',
		'test'      => '<svg viewBox="0 0 48 48" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M28.5 12.5A11 11 0 1 1 17 14.8" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"/><path d="M28.5 12.5 32 8.8M28.5 12.5l3.8 3.5" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/><path d="M19.5 35.5A11 11 0 1 1 31 33.2" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"/><path d="M19.5 35.5 16 39.2M19.5 35.5l-3.8-3.5" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/></svg>',
		'guarantee' => '<svg viewBox="0 0 48 48" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M24 7.5 36.5 12v11.2c0 8.1-5.2 12.4-12.5 15.3C16.7 35.6 11.5 31.3 11.5 23.2V12L24 7.5Z" stroke="currentColor" stroke-width="2.2" stroke-linejoin="round"/><path d="M18.5 24.2 22.2 28l7.3-7.8" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/></svg>',
	);

	return $icons[ $icon ] ?? '';
}

/**
 * "Why Almas Land" section data for the front page.
 *
 * @return array<string, mixed>
 */
function almasland_get_home_why_section() {
	$image_url = ALMASLAND_URI . '/assets/images/laptop.jpg';

	return array(
		'title'       => __( 'چرا الماس لند؟', 'almas-land' ),
		'text'        => __( 'با بیش از ۲ سال تجربه در فروش لپ‌تاپ و محصولات دیجیتال، الماس لند همراه مطمئن شما در خرید هوشمندانه است. تمام محصولات با تست دقیق و ضمانت کتبی ارائه می‌شوند.', 'almas-land' ),
		'image'       => $image_url,
		'image_alt'   => __( 'خرید مطمئن از الماس لند', 'almas-land' ),
		'stats'       => array(
			array(
				'icon'          => almasland_get_home_why_icon( 'customers' ),
				'title'         => almasland_persian_digits( '۵۰۰+' ),
				'subtitle'      => __( 'مشتریان راضی', 'almas-land' ),
				'tooltip_mode'  => 'hover-click',
				'tooltip_id'    => 'front-page-why-customers',
				'tooltip_title' => __( 'اعتماد مشتریان', 'almas-land' ),
				'tooltip_text'  => __( 'بیش از ۱۰ هزار خریدار از سراسر کشور به الماس لند اعتماد کرده‌اند و تجربه خرید خود را مثبت ارزیابی کرده‌اند.', 'almas-land' ),
			),
			array(
				'icon'          => almasland_get_home_why_icon( 'years' ),
				'title'         => almasland_persian_digits( '۲ سال' ),
				'subtitle'      => __( 'سابقه فعالیت', 'almas-land' ),
				'tooltip_mode'  => 'click',
				'tooltip_id'    => 'front-page-why-years',
				'tooltip_title' => __( 'تجربه تخصصی', 'almas-land' ),
				'tooltip_text'  => __( 'بیش از ۵ سال فعالیت تخصصی در حوزه لپ‌تاپ و تجهیزات دیجیتال، پشتوانه تجربه و شناخت دقیق بازار ماست.', 'almas-land' ),
			),
			array(
				'icon'          => almasland_get_home_why_icon( 'test' ),
				'title'         => __( 'تست ۷ روزه', 'almas-land' ),
				'subtitle'      => __( 'تمام محصولات', 'almas-land' ),
				'tooltip_mode'  => 'click',
				'tooltip_id'    => 'front-page-why-test',
				'tooltip_title' => __( 'مهلت تست محصول', 'almas-land' ),
				'tooltip_text'  => __( 'همه محصولات تا ۷ روز پس از تحویل قابل تست هستند و در صورت نارضایتی می‌توانید بدون قید و شرط مرجوع کنید.', 'almas-land' ),
			),
			array(
				'icon'          => almasland_get_home_why_icon( 'guarantee' ),
				'title'         => __( 'ضمانت کتبی', 'almas-land' ),
				'subtitle'      => __( 'سلامت کالا', 'almas-land' ),
				'tooltip_mode'  => 'click',
				'tooltip_id'    => 'front-page-why-guarantee',
				'tooltip_title' => __( 'ضمانت سلامت کالا', 'almas-land' ),
				'tooltip_text'  => __( 'سلامت فنی و ظاهری هر محصول با ضمانت کتبی تضمین می‌شود تا خرید شما با خیال راحت انجام شود.', 'almas-land' ),
			),
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
 * Format a numeric amount for storefront cards.
 *
 * @param float|string $amount Raw amount.
 * @return string
 */
function almasland_format_plain_price( $amount ) {
	if ( '' === $amount || null === $amount ) {
		return '';
	}

	if ( ! function_exists( 'wc_get_price_decimals' ) ) {
		return almasland_persian_digits( (string) $amount );
	}

	return almasland_persian_digits(
		number_format(
			(float) $amount,
			wc_get_price_decimals(),
			wc_get_price_decimal_separator(),
			wc_get_price_thousand_separator()
		)
	);
}

/**
 * Compact تومان currency icon markup for product cards.
 *
 * @return string
 */
function almasland_get_toman_icon_html() {
	return '<span class="almasland-toman-icon" aria-hidden="true"><span class="almasland-toman-icon__top">تو</span><span class="almasland-toman-icon__bottom">مان</span></span><span class="screen-reader-text">تومان</span>';
}

/**
 * Card price amount + تومان icon.
 *
 * @param float|string $amount Raw amount.
 * @return string
 */
function almasland_format_card_price_html( $amount ) {
	$formatted = almasland_format_plain_price( $amount );
	if ( '' === $formatted ) {
		return '';
	}

	return '<span class="almasland-card-price__amount">' . esc_html( $formatted ) . '</span>' . almasland_get_toman_icon_html();
}

/**
 * Whether product price should be shown on storefront.
 *
 * @param WC_Product|null $product Product.
 * @return bool
 */
function almasland_should_show_product_price( $product ) {
	return $product instanceof WC_Product && $product->is_in_stock();
}

/**
 * Secondary gallery image ID for product card hover swap.
 *
 * @param WC_Product|null $product Product.
 * @return int
 */
function almasland_get_product_card_secondary_image_id( $product ) {
	if ( ! $product instanceof WC_Product ) {
		return 0;
	}

	$gallery_ids = $product->get_gallery_image_ids();

	return ! empty( $gallery_ids ) ? (int) $gallery_ids[0] : 0;
}

/**
 * Full-card overlay link for product cards.
 *
 * @param WC_Product|null $product Product.
 * @param string          $label   Optional accessible label.
 * @return void
 */
function almasland_render_product_card_overlay_link( $product, $label = '' ) {
	if ( ! $product instanceof WC_Product ) {
		return;
	}

	if ( '' === $label ) {
		$label = almasland_get_product_card_title( $product );
	}

	printf(
		'<a class="almas-card-link" href="%1$s" aria-label="%2$s"></a>',
		esc_url( $product->get_permalink() ),
		esc_attr(
			sprintf(
				/* translators: %s: product title */
				__( 'مشاهده %s', 'almas-land' ),
				$label
			)
		)
	);
}

/**
 * Product card media with optional hover image swap.
 *
 * @param WC_Product|null $product        Product.
 * @param string          $wrapper_class  Optional extra class on media wrapper.
 * @param string          $size           Image size.
 * @param array           $image_attrs    Attributes for wp_get_attachment_image().
 * @return void
 */
function almasland_render_product_card_media( $product, $wrapper_class = '', $size = 'almasland-card', $image_attrs = array() ) {
	if ( ! $product instanceof WC_Product ) {
		return;
	}

	$image_attrs = wp_parse_args(
		$image_attrs,
		array(
			'loading'  => 'lazy',
			'decoding' => 'async',
		)
	);

	$secondary_id = almasland_get_product_card_secondary_image_id( $product );
	$classes      = 'almas-card-media';

	if ( $wrapper_class ) {
		$classes .= ' ' . $wrapper_class;
	}

	if ( $secondary_id > 0 ) {
		$classes .= ' almas-card-media--has-hover';
	}

	echo '<div class="' . esc_attr( $classes ) . '">';
	echo '<div class="almas-card-media__image almas-card-media__image--primary">';
	echo wp_kses_post( $product->get_image( $size, $image_attrs ) );
	echo '</div>';

	if ( $secondary_id > 0 ) {
		echo '<div class="almas-card-media__image almas-card-media__image--secondary">';
		echo wp_kses_post( wp_get_attachment_image( $secondary_id, $size, false, $image_attrs ) );
		echo '</div>';
	}

	echo '</div>';
}

/**
 * Render modern product-card pricing footer or out-of-stock CTA.
 *
 * @param WC_Product|null $product Product.
 * @return void
 */
function almasland_render_product_card_pricing( $product ) {
	if ( ! $product instanceof WC_Product ) {
		return;
	}

	$product_link = $product->get_permalink();

	if ( ! $product->is_in_stock() ) {
		printf(
			'<a class="almas-card-pricing__cta almas-card-pricing__cta--view" href="%1$s">%2$s</a>',
			esc_url( $product_link ),
			esc_html__( 'مشاهده محصول', 'almas-land' )
		);
		return;
	}

	if ( ! almasland_should_show_product_price( $product ) ) {
		return;
	}

	$sale_price = (float) $product->get_price();
	$regular    = (float) $product->get_regular_price();
	$discount   = function_exists( 'almasland_get_discount_percent' ) ? almasland_get_discount_percent( $product ) : 0;
	$on_sale    = $regular > 0 && $sale_price > 0 && $regular > $sale_price;

	echo '<div class="almas-card-pricing">';

	if ( $on_sale ) {
		echo '<div class="almas-card-pricing__deals">';
		printf(
			'<span class="almas-card-pricing__discount">%s</span>',
			esc_html(
				sprintf(
					/* translators: %s: discount percent */
					__( '%s%%', 'almas-land' ),
					almasland_persian_digits( (string) $discount )
				)
			)
		);
		printf(
			'<del class="almas-card-pricing__old">%s</del>',
			esc_html( almasland_format_plain_price( $regular ) )
		);
		echo '</div>';
	}

	if ( $sale_price > 0 ) {
		printf(
			'<div class="almas-card-pricing__final">%s</div>',
			wp_kses_post( almasland_format_card_price_html( $sale_price ) )
		);
	}

	echo '</div>';
}

/**
 * Grade badge for any product that has card grade meta (not only used items).
 *
 * @param WC_Product|null $product Product.
 * @return array{text: string, tone: string, bg?: string, color?: string}|null
 */
function almasland_get_product_card_grade( $product ) {
	return function_exists( 'almasland_get_product_grade_badge' )
		? almasland_get_product_grade_badge( $product )
		: null;
}

/**
 * Render grade badge and color swatch row for product cards.
 *
 * @param WC_Product|null $product     Product.
 * @param array|null      $grade       Optional preloaded grade data.
 * @param string          $grade_style Optional inline grade styles.
 * @return void
 */
function almasland_render_product_card_tags( $product, $grade = null, $grade_style = '' ) {
	if ( ! $product instanceof WC_Product ) {
		return;
	}

	if ( null === $grade ) {
		$grade = almasland_get_product_card_grade( $product );
	}

	$color_html = function_exists( 'almasland_render_product_color_swatch' )
		? almasland_render_product_color_swatch( $product, array( 'size' => 'sm' ) )
		: '';

	if ( ! $grade && '' === $color_html ) {
		return;
	}

	echo '<div class="product-card__tags">';

	if ( $grade ) {
		printf(
			'<span class="product-card__grade product-card__grade--%1$s"%3$s>%2$s</span>',
			esc_attr( $grade['tone'] ),
			esc_html( $grade['text'] ),
			$grade_style // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		);
	}

	if ( $color_html ) {
		echo $color_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in renderer.
	}

	echo '</div>';
}

/**
 * CTA label for product cards.
 *
 * @param WC_Product $product Product.
 * @return string
 */
function almasland_get_product_card_cta_label( $product ) {
	if ( ! $product instanceof WC_Product ) {
		return __( 'مشاهده', 'almas-land' );
	}

	return $product->is_in_stock() ? __( 'مشاهده و خرید', 'almas-land' ) : __( 'مشاهده', 'almas-land' );
}

/**
 * Stock label for product cards.
 *
 * @param WC_Product $product Product.
 * @return string
 */
function almasland_get_product_card_stock_label( $product ) {
	if ( ! $product instanceof WC_Product ) {
		return '';
	}

	if ( ! $product->is_in_stock() ) {
		return __( 'ناموجود', 'almas-land' );
	}

	if ( $product->is_on_backorder() ) {
		return __( 'قابل پیش‌سفارش', 'almas-land' );
	}

	return __( 'موجود', 'almas-land' );
}

/**
 * Whether a product belongs to the used / second-hand category.
 *
 * @param WC_Product|null $product Product.
 * @return bool
 */
function almasland_is_used_product( $product ) {
	if ( ! $product instanceof WC_Product ) {
		return false;
	}

	$product_id = $product->is_type( 'variation' ) ? $product->get_parent_id() : $product->get_id();

	return $product_id > 0 && has_term( 'used', 'product_cat', $product_id );
}

/**
 * Render the used-product badge for catalog cards.
 *
 * @param WC_Product|null $product Product.
 * @return void
 */
function almasland_render_product_used_badge( $product ) {
	if ( ! almasland_is_used_product( $product ) ) {
		return;
	}
	?>
	<span class="product-used-badge">
		<svg viewBox="0 0 24 24" aria-hidden="true" focusable="false">
			<path d="M7.5 7.2 4.8 9.8l2.7 2.6" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
			<path d="M5.2 9.8h9.6a4.2 4.2 0 0 1 0 8.4H12" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
			<path d="m16.5 16.8 2.7-2.6-2.7-2.6" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
			<path d="M18.8 14.2H9.2a4.2 4.2 0 0 1 0-8.4H12" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
		</svg>
		<?php esc_html_e( 'کالای دست دوم', 'almas-land' ); ?>
	</span>
	<?php
}

/**
 * Short one-line summary for product cards (admin field only — never attributes).
 *
 * @param WC_Product $product Product.
 * @return string
 */
function almasland_get_product_card_summary( $product ) {
	if ( ! $product instanceof WC_Product ) {
		return '';
	}

	$source = $product;
	if ( $product->is_type( 'variation' ) ) {
		$parent = wc_get_product( $product->get_parent_id() );
		if ( $parent ) {
			$source = $parent;
		}
	}

	return trim( (string) $source->get_meta( '_almas_card_specs' ) );
}

/**
 * Grade badge data for product cards.
 *
 * @param WC_Product $product Product.
 * @return array{text: string, tone: string, bg?: string, color?: string}|null
 */
function almasland_get_product_grade_badge( $product ) {
	if ( ! $product instanceof WC_Product ) {
		return null;
	}

	$source = $product;
	if ( $product->is_type( 'variation' ) ) {
		$parent = wc_get_product( $product->get_parent_id() );
		if ( $parent ) {
			$source = $parent;
		}
	}

	$key         = sanitize_key( (string) $source->get_meta( '_almas_card_grade' ) );
	$definitions = function_exists( 'almasland_get_product_card_grade_definitions' )
		? almasland_get_product_card_grade_definitions()
		: array();

	if ( $key && isset( $definitions[ $key ] ) ) {
		return $definitions[ $key ];
	}

	return null;
}

/**
 * Map grade label to a UI tone.
 *
 * @param string $text Grade label.
 * @return string
 */
function almasland_get_product_grade_tone( $text ) {
	$map = array(
		'مشابه نو'   => 'like-new',
		'بسیار تمیز' => 'very-clean',
		'تمیز'       => 'clean',
		'معمولی'     => 'fair',
	);

	$text = trim( (string) $text );
	if ( isset( $map[ $text ] ) ) {
		return $map[ $text ];
	}

	return 'default';
}

/**
 * On-sale products for the front page special offers slider.
 *
 * @param int $limit Maximum products.
 * @return array<int, WC_Product>
 */
function almasland_get_home_special_offers_products( $limit = 12 ) {
	if ( ! class_exists( 'WooCommerce' ) ) {
		return array();
	}

	$limit = max( 1, (int) $limit );

	// wc_get_products() has no `on_sale` arg — use sale IDs (parents only; skip variations).
	$sale_ids = array_values(
		array_filter(
			array_map( 'absint', wc_get_product_ids_on_sale() ),
			static function ( $id ) {
				return $id > 0 && 'product' === get_post_type( $id );
			}
		)
	);

	if ( empty( $sale_ids ) ) {
		return array();
	}

	$products = wc_get_products(
		array(
			'limit'   => $limit,
			'status'  => 'publish',
			'include' => $sale_ids,
			'orderby' => 'date',
			'order'   => 'DESC',
			'return'  => 'objects',
		)
	);

	if ( empty( $products ) ) {
		return array();
	}

	return array_values(
		array_filter(
			$products,
			static function ( $product ) {
				return $product instanceof WC_Product && $product->is_visible() && $product->is_on_sale();
			}
		)
	);
}

/**
 * Categories for the front-page catalog filter tabs.
 * Top-level WooCommerce categories ordered by product count (including children).
 *
 * @param int $limit Maximum category tabs.
 * @return array<int, WP_Term>
 */
function almasland_get_home_catalog_categories( $limit = 10 ) {
	if ( ! taxonomy_exists( 'product_cat' ) ) {
		return array();
	}

	$limit   = max( 1, (int) $limit );
	$exclude = array_filter( array( (int) get_option( 'default_product_cat', 0 ) ) );

	$terms = get_terms(
		array(
			'taxonomy'   => 'product_cat',
			'parent'     => 0,
			'hide_empty' => false,
			'exclude'    => $exclude,
		)
	);

	if ( is_wp_error( $terms ) || empty( $terms ) ) {
		return array();
	}

	foreach ( $terms as $term ) {
		$total    = (int) $term->count;
		$children = get_term_children( (int) $term->term_id, 'product_cat' );

		if ( ! is_wp_error( $children ) && $children ) {
			foreach ( $children as $child_id ) {
				$child = get_term( (int) $child_id, 'product_cat' );
				if ( $child && ! is_wp_error( $child ) ) {
					$total += (int) $child->count;
				}
			}
		}

		$term->count = $total;
	}

	$terms = array_values(
		array_filter(
			$terms,
			static function ( $term ) {
				return (int) $term->count > 0;
			}
		)
	);

	usort(
		$terms,
		static function ( $a, $b ) {
			$count_cmp = (int) $b->count <=> (int) $a->count;
			if ( 0 !== $count_cmp ) {
				return $count_cmp;
			}
			return strcasecmp( (string) $a->name, (string) $b->name );
		}
	);

	return array_slice( $terms, 0, $limit );
}

/**
 * Products for the front-page catalog grid.
 *
 * @param int $category_id Product category ID (0 = all).
 * @param int $limit       Maximum products.
 * @return array<int, WC_Product>
 */
function almasland_get_home_catalog_products( $category_id = 0, $limit = 8 ) {
	if ( ! class_exists( 'WooCommerce' ) ) {
		return array();
	}

	$args = array(
		'limit'   => max( 1, (int) $limit ),
		'status'  => 'publish',
		'orderby' => 'date',
		'order'   => 'DESC',
		'return'  => 'objects',
	);

	$category_id = absint( $category_id );
	if ( $category_id ) {
		$term = get_term( $category_id, 'product_cat' );
		if ( ! $term || is_wp_error( $term ) ) {
			return array();
		}
		$args['category'] = array( $term->slug );
	}

	$products = wc_get_products( $args );

	if ( empty( $products ) ) {
		return array();
	}

	return array_values(
		array_filter(
			$products,
			static function ( $product ) {
				return $product instanceof WC_Product && $product->is_visible();
			}
		)
	);
}

/**
 * Render one catalog product card HTML.
 *
 * @param WC_Product $product Product.
 * @return string
 */
function almasland_get_home_catalog_card_html( $product ) {
	if ( ! $product instanceof WC_Product ) {
		return '';
	}

	$summary       = almasland_get_product_card_summary( $product );
	$is_used       = almasland_is_used_product( $product );
	$grade         = almasland_get_product_card_grade( $product );
	$stock_class   = function_exists( 'almasland_stock_class' ) ? almasland_stock_class( $product ) : '';
	$grade_style   = '';
	$card_class    = 'front-page-catalog-card front-page-catalog-card--storefront' . ( $is_used ? ' front-page-catalog-card--used' : '' );

	if ( $grade && ! empty( $grade['bg'] ) ) {
		$grade_style = sprintf(
			' style="background-color:%1$s;color:%2$s;"',
			esc_attr( $grade['bg'] ),
			esc_attr( $grade['color'] ?? '#ffffff' )
		);
	}

	ob_start();
	?>
	<article class="<?php echo esc_attr( $card_class ); ?>">
		<?php almasland_render_product_card_overlay_link( $product ); ?>

		<div class="front-page-catalog-card__media">
			<?php almasland_render_product_card_media( $product ); ?>
			<?php almasland_render_product_used_badge( $product ); ?>
		</div>
		<div class="front-page-catalog-card__body">
			<h3 class="front-page-catalog-card__title">
				<?php echo esc_html( almasland_get_product_card_title( $product ) ); ?>
			</h3>
			<?php almasland_render_product_card_tags( $product, $grade, $grade_style ); ?>
			<?php if ( $summary ) : ?>
				<span class="front-page-catalog-card__specs"><?php echo esc_html( $summary ); ?></span>
			<?php endif; ?>
			<?php if ( ! $product->is_in_stock() ) : ?>
				<span class="front-page-catalog-card__stock stock <?php echo esc_attr( $stock_class ); ?>"><?php esc_html_e( 'ناموجود', 'almas-land' ); ?></span>
			<?php endif; ?>
			<?php almasland_render_product_card_pricing( $product ); ?>
		</div>
	</article>
	<?php
	return (string) ob_get_clean();
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

	$social = almasland_get_panel_settings()['social'];
	$links  = array();
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
 * Footer social icons with SVG.
 */
function almasland_footer_social_links() {
	$icons = array(
		'instagram' => '<svg viewBox="0 0 24 24" aria-hidden="true"><rect x="3.5" y="3.5" width="17" height="17" rx="5" fill="none" stroke="currentColor" stroke-width="1.8"/><circle cx="12" cy="12" r="4" fill="none" stroke="currentColor" stroke-width="1.8"/><circle cx="17.2" cy="6.8" r="1" fill="currentColor"/></svg>',
		'telegram'  => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M20.5 4.5 3.8 11.2c-1.1.4-1.1 1.1-.2 1.4l4.3 1.3 1.7 5.1c.2.7.4.9 1 .9.5 0 .7-.2 1-.6l2.4-3.9 4.5 3.3c.8.5 1.4.2 1.6-.8L21.8 5.6c.3-1.2-.4-1.7-1.3-1.1Z" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linejoin="round"/></svg>',
		'whatsapp'  => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 3.5a8.5 8.5 0 0 0-7.3 12.8L4 20.5l4.3-.7A8.5 8.5 0 1 0 12 3.5Z" fill="none" stroke="currentColor" stroke-width="1.8"/><path d="M9.2 9.4c.2-.4.4-.4.6-.4h.5c.2 0 .4 0 .5.4l.7 1.7c.1.2 0 .4-.1.6l-.4.5c-.1.1-.1.3 0 .4.4.7 1.1 1.4 1.9 1.8.2.1.3.1.4 0l.6-.4c.2-.1.4-.1.5 0l1.6.9c.2.1.3.3.3.5v.5c0 .2 0 .4-.4.6-.4.2-1 .4-1.7.2-1.8-.4-3.4-1.6-4.5-3.2-1-1.5-1.3-3.1-1.1-4.2.1-.5.3-.8.6-1Z" fill="currentColor"/></svg>',
		'linkedin'  => '<svg viewBox="0 0 24 24" aria-hidden="true"><rect x="3.5" y="3.5" width="17" height="17" rx="3" fill="none" stroke="currentColor" stroke-width="1.8"/><path d="M8 10.5V16M8 7.8v.2M12 16v-3.2a1.8 1.8 0 0 1 3.6 0V16" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>',
		'youtube'   => '<svg viewBox="0 0 24 24" aria-hidden="true"><rect x="2.5" y="6" width="19" height="12" rx="3" fill="none" stroke="currentColor" stroke-width="1.8"/><path d="m10.5 9.5 5 2.5-5 2.5v-5Z" fill="currentColor"/></svg>',
		'aparat'    => '<svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="8.5" fill="none" stroke="currentColor" stroke-width="1.8"/><circle cx="12" cy="12" r="3" fill="currentColor"/></svg>',
		'x'         => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="m6 6 12 12M18 6 6 18" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>',
		'facebook'  => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M14 8h2.5V4.8C16 4.6 15 4.5 14 4.5c-2.4 0-4 1.5-4 4.2V11H7.5v3.2H10V20h3.2v-5.8H16l.5-3.2h-3.3V9c0-.6.2-1 1.1-1Z" fill="currentColor"/></svg>',
	);

	$labels = array(
		'instagram' => __( 'اینستاگرام', 'almas-land' ),
		'telegram'  => __( 'تلگرام', 'almas-land' ),
		'whatsapp'  => __( 'واتساپ', 'almas-land' ),
		'linkedin'  => __( 'لینکدین', 'almas-land' ),
		'youtube'   => __( 'یوتیوب', 'almas-land' ),
		'aparat'    => __( 'آپارات', 'almas-land' ),
		'x'         => 'X',
		'facebook'  => __( 'فیسبوک', 'almas-land' ),
	);

	$social = almasland_get_panel_settings()['social'];
	$items  = array();

	foreach ( $icons as $key => $svg ) {
		if ( empty( $social[ $key ] ) ) {
			continue;
		}
		$items[] = array(
			'url'   => $social[ $key ],
			'label' => $labels[ $key ],
			'icon'  => $svg,
		);
	}

	if ( empty( $items ) ) {
		return;
	}

	foreach ( $items as $item ) {
		printf(
			'<a class="footer-socials__link" href="%1$s" target="_blank" rel="noopener noreferrer" aria-label="%2$s">%3$s</a>',
			esc_url( $item['url'] ),
			esc_attr( $item['label'] ),
			$item['icon'] // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		);
	}
}

/**
 * Footer trust highlights shown above badges.
 *
 * @return array<int, array{icon: string, text: string}>
 */
function almasland_get_footer_trust_highlights() {
	$items = array(
		array(
			'icon' => 'truck',
			'text' => __( 'امکان تحویل اکسپرس', 'almas-land' ),
		),
		array(
			'icon' => 'shield',
			'text' => __( 'پرداخت امن اینترنتی', 'almas-land' ),
		),
		array(
			'icon' => 'return',
			'text' => __( '۴۸ ساعت ضمانت بازگشت کالا', 'almas-land' ),
		),
		array(
			'icon' => 'check',
			'text' => __( 'ضمانت اصل بودن کالا', 'almas-land' ),
		),
		array(
			'icon' => 'support',
			'text' => __( 'پشتیبانی تخصصی خرید', 'almas-land' ),
		),
		array(
			'icon' => 'box',
			'text' => __( 'بسته‌بندی ایمن و استاندارد', 'almas-land' ),
		),
	);

	/**
	 * Filter footer trust highlight items.
	 *
	 * @param array<int, array{icon: string, text: string}> $items Items.
	 */
	return apply_filters( 'almasland_footer_trust_highlights', $items );
}

/**
 * Inline icon for footer trust highlights.
 *
 * @param string $icon Icon id.
 * @return string
 */
function almasland_footer_trust_icon( $icon ) {
	$icons = array(
		'truck'   => '<path d="M3 7h11v8H3V7Z" fill="none" stroke="currentColor" stroke-width="1.6"/><path d="M14 10h3l2 2v3h-5v-5Z" fill="none" stroke="currentColor" stroke-width="1.6"/><circle cx="7" cy="17" r="1.6" fill="none" stroke="currentColor" stroke-width="1.6"/><circle cx="17" cy="17" r="1.6" fill="none" stroke="currentColor" stroke-width="1.6"/>',
		'shield'  => '<path d="M12 3 19 6v6c0 4.2-2.8 7.4-7 9-4.2-1.6-7-4.8-7-9V6l7-3Z" fill="none" stroke="currentColor" stroke-width="1.6"/>',
		'return'  => '<path d="M20 8v6a2 2 0 0 1-2 2H8" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/><path d="m5 11-3-3 3-3" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/>',
		'check'   => '<path d="m8.5 12.2 2.2 2.2 4.8-4.8" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/><circle cx="12" cy="12" r="9" fill="none" stroke="currentColor" stroke-width="1.6"/>',
		'support' => '<path d="M5 6h14a1 1 0 0 1 1 1v7a1 1 0 0 1-1 1H9l-4 3v-3H5a1 1 0 0 1-1-1V7a1 1 0 0 1 1-1Z" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linejoin="round"/>',
		'box'     => '<path d="M4 8 12 4l8 4-8 4-8-4Z" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linejoin="round"/><path d="M4 8v8l8 4 8-4V8" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linejoin="round"/>',
	);

	if ( ! isset( $icons[ $icon ] ) ) {
		return '';
	}

	return '<svg class="footer-trust-strip__icon-svg" viewBox="0 0 24 24" aria-hidden="true" focusable="false">' . $icons[ $icon ] . '</svg>';
}

/**
 * e-Namad URL from theme panel.
 *
 * @return string
 */
function almasland_get_footer_enamad_url() {
	$url = function_exists( 'almasland_get_panel' )
		? almasland_get_panel( 'footer', 'enamad_url', '' )
		: '';

	if ( ! $url ) {
		$url = 'https://trustseal.enamad.ir/?id=576236&Code=Rm9yFPbIrrgOK8hJAHxwSrjcCcc4crzd';
	}

	return esc_url( $url );
}

/**
 * e-Namad image URL from panel or theme default.
 *
 * @return string
 */
function almasland_get_footer_enamad_image_url() {
	$image_id = function_exists( 'almasland_get_panel' ) ? absint( almasland_get_panel( 'footer', 'enamad_image', 0 ) ) : 0;

	if ( $image_id ) {
		$src = wp_get_attachment_image_url( $image_id, 'medium' );
		if ( $src ) {
			return esc_url( $src );
		}
	}

	return esc_url( ALMASLAND_URI . '/assets/images/enamad.png' );
}

/**
 * Render footer trust strip and badge row.
 *
 * @return void
 */
function almasland_render_footer_trust_section() {
	$footer_settings = function_exists( 'almasland_get_panel_settings' ) ? almasland_get_panel_settings()['footer'] : array();
	$highlights      = almasland_get_footer_trust_highlights();
	$badge_ids       = array(
		absint( $footer_settings['trust_badge_1'] ?? 0 ),
		absint( $footer_settings['trust_badge_2'] ?? 0 ),
		absint( $footer_settings['samandehi'] ?? 0 ),
	);
	$has_badges      = ! empty( almasland_get_footer_enamad_url() );

	foreach ( $badge_ids as $badge_id ) {
		if ( $badge_id > 0 ) {
			$has_badges = true;
			break;
		}
	}

	if ( empty( $highlights ) && ! $has_badges ) {
		return;
	}
	?>
	<div class="footer-trust">
		<?php if ( ! empty( $highlights ) ) : ?>
			<div class="container footer-trust-strip" aria-label="<?php esc_attr_e( 'مزیت‌های خرید از الماس لند', 'almas-land' ); ?>">
				<ul class="footer-trust-strip__list">
					<?php foreach ( $highlights as $item ) : ?>
						<li class="footer-trust-strip__item">
							<span class="footer-trust-strip__icon"><?php echo almasland_footer_trust_icon( $item['icon'] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
							<span><?php echo esc_html( $item['text'] ); ?></span>
						</li>
					<?php endforeach; ?>
				</ul>
			</div>
		<?php endif; ?>

		<?php if ( $has_badges ) : ?>
			<div class="container footer-trust-badges" aria-label="<?php esc_attr_e( 'نمادهای اعتماد', 'almas-land' ); ?>">
				<a
					class="footer-trust-badges__link footer-trust-badges__link--enamad"
					href="<?php echo esc_url( almasland_get_footer_enamad_url() ); ?>"
					target="_blank"
					rel="noopener noreferrer"
					referrerpolicy="origin"
					title="<?php esc_attr_e( 'نماد اعتماد الکترونیکی', 'almas-land' ); ?>"
				>
					<img
						src="<?php echo esc_url( almasland_get_footer_enamad_image_url() ); ?>"
						alt="<?php esc_attr_e( 'نماد اعتماد الکترونیکی (e-Namad)', 'almas-land' ); ?>"
						width="180"
						height="86"
						loading="lazy"
						decoding="async"
						referrerpolicy="origin"
					>
				</a>

				<?php foreach ( $badge_ids as $badge_id ) : ?>
					<?php if ( $badge_id <= 0 ) : ?>
						<?php continue; ?>
					<?php endif; ?>
					<span class="footer-trust-badges__item">
						<?php echo wp_get_attachment_image( $badge_id, 'medium', false, array( 'class' => 'footer-trust-badges__image' ) ); ?>
					</span>
				<?php endforeach; ?>
			</div>
		<?php endif; ?>
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

	if ( ! empty( $state['orderby'] ) && 'date' !== $state['orderby'] ) {
		$add_args['orderby'] = $state['orderby'];
	}

	if ( $state['min_price'] ) {
		$add_args['min_price'] = $state['min_price'];
	}

	if ( $state['max_price'] ) {
		$add_args['max_price'] = $state['max_price'];
	}

	foreach ( array( 'in_stock', 'fast_shipping', 'on_sale' ) as $flag ) {
		if ( ! empty( $state[ $flag ] ) ) {
			$add_args[ $flag ] = 1;
		}
	}

	if ( ! empty( $state['filter_brand'] ) ) {
		$add_args['filter_brand'] = $state['filter_brand'];
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
