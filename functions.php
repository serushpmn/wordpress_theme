<?php
/**
 * Almas Land theme bootstrap.
 *
 * @package AlmasLand
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'ALMASLAND_VERSION', '1.3.9' );
define( 'ALMASLAND_DIR', get_template_directory() );
define( 'ALMASLAND_URI', get_template_directory_uri() );

require ALMASLAND_DIR . '/inc/cache.php';
require ALMASLAND_DIR . '/inc/template-functions.php';
require ALMASLAND_DIR . '/inc/nav-walker.php';
require ALMASLAND_DIR . '/inc/theme-panel/bootstrap.php';

// Customizer registration only ever runs in wp-admin or the customizer preview.
if ( is_admin() || is_customize_preview() ) {
	require ALMASLAND_DIR . '/inc/customizer.php';
}

if ( class_exists( 'WooCommerce' ) ) {
	require ALMASLAND_DIR . '/inc/product-fields.php';
	require ALMASLAND_DIR . '/inc/product-badges.php';
	require ALMASLAND_DIR . '/inc/shop-filters.php';
	require ALMASLAND_DIR . '/inc/woocommerce.php';
	require ALMASLAND_DIR . '/inc/checkout-fields.php';
}

/**
 * Load the used-device health report module on demand.
 *
 * The module registers no hooks and is only consumed by the single product
 * template, so it stays out of every other request.
 *
 * @return void
 */
function almasland_load_used_device_health_report() {
	if ( ! class_exists( 'WooCommerce' ) ) {
		return;
	}

	require_once ALMASLAND_DIR . '/inc/used-device-health-report.php';
}

/**
 * Load WooCommerce modules that are only needed on specific screens.
 *
 * Runs on `wp` so the query is resolved but every hook these modules register
 * (earliest is `template_redirect`) is still ahead of us.
 *
 * @return void
 */
function almasland_load_conditional_modules() {
	if ( ! class_exists( 'WooCommerce' ) ) {
		return;
	}

	if ( function_exists( 'is_cart' ) && is_cart() ) {
		require_once ALMASLAND_DIR . '/inc/cart-save-for-later.php';
	}

	// Only used products can render a health report, and the single-product
	// template loads the module itself before calling into it, so this is a
	// fast path rather than the only guard.
	if ( function_exists( 'is_product' ) && is_product() ) {
		$queried = wc_get_product( get_queried_object_id() );

		if ( $queried && almasland_is_used_product( $queried ) ) {
			almasland_load_used_device_health_report();
		}
	}
}
add_action( 'wp', 'almasland_load_conditional_modules', 5 );

if ( ! function_exists( 'almasland_setup' ) ) {
	/**
	 * Register theme features.
	 */
	function almasland_setup() {
		load_theme_textdomain( 'almas-land', ALMASLAND_DIR . '/languages' );

		add_theme_support( 'automatic-feed-links' );
		add_theme_support( 'title-tag' );
		add_theme_support( 'post-thumbnails' );
		add_theme_support( 'responsive-embeds' );
		add_theme_support( 'customize-selective-refresh-widgets' );
		add_theme_support( 'align-wide' );
		add_theme_support(
			'html5',
			array(
				'search-form',
				'comment-form',
				'comment-list',
				'gallery',
				'caption',
				'style',
				'script',
			)
		);
		add_theme_support(
			'custom-logo',
			array(
				'height'      => 80,
				'width'       => 220,
				'flex-height' => true,
				'flex-width'  => true,
			)
		);

		if ( class_exists( 'WooCommerce' ) ) {
			add_theme_support(
				'woocommerce',
				array(
					'thumbnail_image_width' => 420,
					'single_image_width'    => 466,
					'product_grid'          => array(
						'default_rows'    => 4,
						'min_rows'        => 2,
						'max_rows'        => 8,
						'default_columns' => 3,
						'min_columns'     => 1,
						'max_columns'     => 4,
					),
				)
			);
		}

		register_nav_menus(
			array(
				'primary' => esc_html__( 'منوی اصلی', 'almas-land' ),
				'footer'  => esc_html__( 'منوی فوتر', 'almas-land' ),
				'mobile'  => esc_html__( 'منوی موبایل', 'almas-land' ),
			)
		);

		add_image_size( 'almasland-card', 640, 520, true );
		add_image_size( 'almasland-hero', 1100, 500, true );
		add_image_size( 'almasland-hero-tablet', 900, 410, true );
		add_image_size( 'almasland-hero-mobile', 768, 350, true );
		add_image_size( 'almasland-single', 466, 466, true );
	}
}
add_action( 'after_setup_theme', 'almasland_setup' );

/**
 * Register widget areas.
 */
function almasland_widgets_init() {
	register_sidebar(
		array(
			'name'          => esc_html__( 'سایدبار وبلاگ', 'almas-land' ),
			'id'            => 'sidebar-1',
			'description'   => esc_html__( 'ابزارک‌های نوشته‌ها، آرشیو و صفحه جستجو.', 'almas-land' ),
			'before_widget' => '<section id="%1$s" class="widget surface-panel ui-card %2$s">',
			'after_widget'  => '</section>',
			'before_title'  => '<h2 class="widget-title">',
			'after_title'   => '</h2>',
		)
	);

	register_sidebar(
		array(
			'name'          => esc_html__( 'فیلتر فروشگاه', 'almas-land' ),
			'id'            => 'shop-sidebar',
			'description'   => esc_html__( 'ابزارک‌های فیلتر و دسته‌بندی محصولات ووکامرس.', 'almas-land' ),
			'before_widget' => '<section id="%1$s" class="widget filter-widget %2$s">',
			'after_widget'  => '</section>',
			'before_title'  => '<h3 class="widget-title">',
			'after_title'   => '</h3>',
		)
	);
}
add_action( 'widgets_init', 'almasland_widgets_init' );

/**
 * Whether the current request renders a product catalog listing.
 *
 * Covers the shop page, product taxonomy archives and product search results —
 * every screen served by `woocommerce/archive-product.php`.
 *
 * @return bool
 */
function almasland_is_shop_context() {
	if ( ! function_exists( 'is_shop' ) ) {
		return false;
	}

	if ( is_shop() || is_product_taxonomy() ) {
		return true;
	}

	if ( ! is_search() ) {
		return false;
	}

	$post_type = get_query_var( 'post_type' );

	return 'product' === $post_type || ( is_array( $post_type ) && in_array( 'product', $post_type, true ) );
}

/**
 * Whether the current request renders markup styled by the blog module.
 *
 * Besides the post templates this covers any singular screen that outputs
 * `comments.php`, because the comment list uses the `.blog-comments` styles.
 *
 * @return bool
 */
function almasland_is_blog_context() {
	if ( is_home() || is_singular( 'post' ) || is_category() || is_tag() || is_author() || is_date() || is_search() ) {
		return true;
	}

	return is_singular() && ( comments_open() || get_comments_number() );
}

/**
 * Enqueue front-end assets.
 *
 * `assets/src/style.css` and `assets/src/main.js` are split into page-context
 * modules (see tools/split-css.mjs and PERFORMANCE-FRONTEND.md); only the
 * modules a template can actually use are loaded.
 */
function almasland_enqueue_assets() {
	wp_enqueue_style( 'almasland-theme', get_stylesheet_uri(), array(), ALMASLAND_VERSION );
	wp_enqueue_style( 'almasland-base', ALMASLAND_URI . '/assets/css/base.css', array( 'almasland-theme' ), ALMASLAND_VERSION );

	// Core script is always present; page modules depend on it, which both
	// guarantees load order and keeps `defer` execution order intact.
	wp_enqueue_script( 'almasland-core', ALMASLAND_URI . '/assets/js/core.js', array(), ALMASLAND_VERSION, true );
	wp_script_add_data( 'almasland-core', 'strategy', 'defer' );
	wp_localize_script(
		'almasland-core',
		'almasLandTheme',
		array(
			'ajaxUrl'      => admin_url( 'admin-ajax.php' ),
			'cartUrl'      => esc_url_raw( wc_get_cart_url() ),
			'wcAjaxUrl'    => class_exists( 'WC_AJAX' ) ? WC_AJAX::get_endpoint( '%%endpoint%%' ) : home_url( '/?wc-ajax=%%endpoint%%' ),
			'contactUrl'   => esc_url_raw( almasland_get_contact_url() ),
			'phoneDisplay' => sanitize_text_field( almasland_get_option( 'phone', '۰۲۱-۸۸۸۸۶۹۵۹' ) ),
			'phoneTel'     => preg_replace( '/[^0-9+]/', '', almasland_get_option( 'phone', '02188886959' ) ),
			'notifyPopup'  => almasland_get_notify_popup_config(),
			'cartChoiceDismiss' => 4,
		)
	);

	/*
	 * Style modules are listed in the same order they appeared in the source
	 * stylesheet, so the cascade between any two that load together is
	 * unchanged. `$last_style` tracks the final one so panel/customizer CSS
	 * keeps overriding everything, exactly as it did after the monolith.
	 */
	$last_style = 'almasland-base';

	$is_shop     = almasland_is_shop_context();
	$is_product  = function_exists( 'is_product' ) && is_product();
	$is_cart     = function_exists( 'is_cart' ) && is_cart();
	$is_checkout = function_exists( 'is_checkout' ) && is_checkout();

	$style_modules = array(
		'shop'          => $is_shop,
		'product'       => $is_product,
		'cart-checkout' => $is_cart || $is_checkout,
		'account'       => function_exists( 'is_account_page' ) && is_account_page(),
		'blog'          => almasland_is_blog_context(),
	);

	foreach ( $style_modules as $module => $needed ) {
		if ( ! $needed ) {
			continue;
		}

		$handle = 'almasland-' . $module;
		wp_enqueue_style( $handle, ALMASLAND_URI . '/assets/css/' . $module . '.css', array( 'almasland-base' ), ALMASLAND_VERSION );
		$last_style = $handle;
	}

	if ( $is_shop ) {
		wp_enqueue_script( 'almasland-shop', ALMASLAND_URI . '/assets/js/shop.js', array( 'almasland-core' ), ALMASLAND_VERSION, true );
		wp_script_add_data( 'almasland-shop', 'strategy', 'defer' );
	}

	if ( $is_product ) {
		wp_enqueue_script( 'wc-add-to-cart-variation' );
		wp_enqueue_script(
			'almasland-product',
			ALMASLAND_URI . '/assets/js/product.js',
			array( 'almasland-core', 'jquery', 'wc-add-to-cart-variation' ),
			ALMASLAND_VERSION,
			true
		);
		wp_script_add_data( 'almasland-product', 'strategy', 'defer' );
	}

	if ( $is_cart ) {
		wp_enqueue_script( 'almasland-cart', ALMASLAND_URI . '/assets/js/cart.js', array( 'almasland-core' ), ALMASLAND_VERSION, true );
		wp_script_add_data( 'almasland-cart', 'strategy', 'defer' );
	}

	if ( $is_checkout && ! is_order_received_page() ) {
		wp_enqueue_script( 'almasland-checkout', ALMASLAND_URI . '/assets/js/checkout.js', array( 'almasland-core', 'jquery' ), ALMASLAND_VERSION, true );
		wp_script_add_data( 'almasland-checkout', 'strategy', 'defer' );
	}

	if ( is_front_page() ) {
		wp_enqueue_style( 'almasland-swiper', ALMASLAND_URI . '/assets/vendor/swiper/swiper-bundle.min.css', array(), '11.0.0' );
		wp_enqueue_style( 'almasland-front-page', ALMASLAND_URI . '/assets/css/front-page.css', array( 'almasland-base' ), ALMASLAND_VERSION );

		wp_enqueue_script( 'almasland-swiper', ALMASLAND_URI . '/assets/vendor/swiper/swiper-bundle.min.js', array(), '11.0.0', true );
		wp_script_add_data( 'almasland-swiper', 'strategy', 'defer' );

		wp_enqueue_script( 'almasland-home', ALMASLAND_URI . '/assets/js/home.js', array( 'almasland-core', 'almasland-swiper' ), ALMASLAND_VERSION, true );
		wp_script_add_data( 'almasland-home', 'strategy', 'defer' );
	}

	$custom = function_exists( 'almasland_sanitize_custom_css' ) ? almasland_sanitize_custom_css( almasland_get_panel( 'identity', 'custom_css', '' ) ) : '';
	$inline = function_exists( 'almasland_get_theme_color_css' ) ? almasland_get_theme_color_css() : '';

	if ( $custom ) {
		$inline .= $custom;
	}
	if ( $inline ) {
		wp_add_inline_style( $last_style, $inline );
	}
}
add_action( 'wp_enqueue_scripts', 'almasland_enqueue_assets' );

/**
 * Popup config for front-end script.
 *
 * @return array<string, mixed>
 */
function almasland_get_notify_popup_config() {
	$n = almasland_get_panel_settings()['notifications'];

	return array(
		'enabled' => ! empty( $n['popup_enabled'] ),
		'delay'   => max( 0, (int) ( $n['popup_delay'] ?? 3 ) ),
		'once'    => ! empty( $n['popup_once'] ),
	);
}

/**
 * Output favicon from theme panel.
 */
function almasland_output_favicon() {
	$favicon_id = absint( almasland_get_panel( 'identity', 'favicon', 0 ) );
	if ( ! $favicon_id ) {
		return;
	}

	$url = wp_get_attachment_image_url( $favicon_id, 'full' );
	if ( ! $url ) {
		return;
	}

	printf( '<link rel="icon" href="%s" sizes="any">' . "\n", esc_url( $url ) );
}
add_action( 'wp_head', 'almasland_output_favicon', 2 );

/**
 * Render notification popup markup.
 */
function almasland_render_notification_popup() {
	$n = almasland_get_panel_settings()['notifications'];
	if ( empty( $n['popup_enabled'] ) ) {
		return;
	}
	if ( empty( $n['popup_title'] ) && empty( $n['popup_text'] ) && empty( $n['popup_image'] ) ) {
		return;
	}

	$image_url = almasland_get_attachment_url( $n['popup_image'], 'medium' );
	?>
	<div class="modal theme-notify-popup" id="theme-notify-popup" aria-hidden="true" role="dialog" aria-labelledby="theme-notify-popup-title">
		<div class="modal__dialog">
			<div class="modal__header">
				<h2 id="theme-notify-popup-title"><?php echo esc_html( $n['popup_title'] ); ?></h2>
				<button type="button" class="modal__close" data-modal-close aria-label="<?php esc_attr_e( 'بستن', 'almas-land' ); ?>">&times;</button>
			</div>
			<div class="modal__body">
				<?php if ( $image_url ) : ?>
					<img src="<?php echo esc_url( $image_url ); ?>" alt="" class="theme-notify-popup__image">
				<?php endif; ?>
				<?php if ( ! empty( $n['popup_text'] ) ) : ?>
					<div class="theme-notify-popup__text"><?php echo wp_kses_post( $n['popup_text'] ); ?></div>
				<?php endif; ?>
				<?php if ( ! empty( $n['popup_button'] ) && ! empty( $n['popup_link'] ) ) : ?>
					<p><a class="btn btn--primary" href="<?php echo esc_url( $n['popup_link'] ); ?>"><?php echo esc_html( $n['popup_button'] ); ?></a></p>
				<?php endif; ?>
			</div>
		</div>
	</div>
	<?php
}
add_action( 'wp_footer', 'almasland_render_notification_popup', 5 );

/**
 * WooCommerce shop grid from panel settings.
 *
 * @return int
 */
function almasland_shop_per_page() {
	return max( 1, (int) almasland_get_panel( 'shop', 'per_page', 12 ) );
}
add_filter( 'loop_shop_per_page', 'almasland_shop_per_page', 20 );

/**
 * WooCommerce columns from panel settings.
 *
 * @return int
 */
function almasland_shop_columns() {
	return max( 1, min( 6, (int) almasland_get_panel( 'shop', 'columns', 3 ) ) );
}
add_filter( 'loop_shop_columns', 'almasland_shop_columns', 20 );

/**
 * Preload local theme font (no external CDN).
 */
function almasland_preload_font() {
	echo '<link rel="preload" href="' . esc_url( ALMASLAND_URI . '/assets/fonts/Vazir%5Bwght%5D.woff2' ) . '" as="font" type="font/woff2" crossorigin>' . "\n";
}
add_action( 'wp_head', 'almasland_preload_font', 1 );

/**
 * Add editor styles.
 *
 * The editor keeps the unsplit stylesheet: it renders arbitrary block content
 * with no page context to select modules from, and it is admin-only, so the
 * split brings no benefit there.
 */
function almasland_editor_assets() {
	add_editor_style( 'assets/src/style.css' );
}
add_action( 'admin_init', 'almasland_editor_assets' );
