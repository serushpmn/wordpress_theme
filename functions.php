<?php
/**
 * Almas Land theme bootstrap.
 *
 * @package AlmasLand
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'ALMASLAND_VERSION', '1.0.0' );
define( 'ALMASLAND_DIR', get_template_directory() );
define( 'ALMASLAND_URI', get_template_directory_uri() );

require ALMASLAND_DIR . '/inc/template-functions.php';
require ALMASLAND_DIR . '/inc/nav-walker.php';
require ALMASLAND_DIR . '/inc/customizer.php';
require ALMASLAND_DIR . '/inc/theme-panel/bootstrap.php';

if ( class_exists( 'WooCommerce' ) ) {
	require ALMASLAND_DIR . '/inc/product-fields.php';
	require ALMASLAND_DIR . '/inc/product-badges.php';
	require ALMASLAND_DIR . '/inc/shop-filters.php';
	require ALMASLAND_DIR . '/inc/woocommerce.php';
	require ALMASLAND_DIR . '/inc/checkout-fields.php';
}

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
 * Enqueue front-end assets.
 */
function almasland_enqueue_assets() {
	$style_deps  = array( 'almasland-theme' );
	$script_deps = array();

	wp_enqueue_style( 'almasland-theme', get_stylesheet_uri(), array(), ALMASLAND_VERSION );
	wp_enqueue_style( 'almasland-main', ALMASLAND_URI . '/assets/css/style.css', $style_deps, ALMASLAND_VERSION );

	if ( is_front_page() ) {
		wp_enqueue_style( 'almasland-front-page', ALMASLAND_URI . '/assets/css/front-page.css', array( 'almasland-main' ), ALMASLAND_VERSION );

		if ( class_exists( 'WooCommerce' ) ) {
			wp_enqueue_style( 'almasland-swiper', ALMASLAND_URI . '/assets/vendor/swiper/swiper-bundle.min.css', array(), '14.0.5' );
			wp_enqueue_script( 'almasland-swiper', ALMASLAND_URI . '/assets/vendor/swiper/swiper-bundle.min.js', array(), '14.0.5', true );
			wp_script_add_data( 'almasland-swiper', 'strategy', 'defer' );
			$script_deps[] = 'almasland-swiper';
		}
	}

	wp_enqueue_script( 'almasland-main', ALMASLAND_URI . '/assets/js/main.js', $script_deps, ALMASLAND_VERSION, true );
	wp_script_add_data( 'almasland-main', 'strategy', 'defer' );
	wp_localize_script(
		'almasland-main',
		'almasLandTheme',
		array(
			'ajaxUrl'      => admin_url( 'admin-ajax.php' ),
			'cartUrl'      => esc_url_raw( wc_get_cart_url() ),
			'wcAjaxUrl'    => class_exists( 'WC_AJAX' ) ? WC_AJAX::get_endpoint( '%%endpoint%%' ) : home_url( '/?wc-ajax=%%endpoint%%' ),
			'contactUrl'   => esc_url_raw( almasland_get_contact_url() ),
			'phoneDisplay' => sanitize_text_field( almasland_get_option( 'phone', '۰۲۱-۸۸۸۸۶۹۵۹' ) ),
			'phoneTel'     => preg_replace( '/[^0-9+]/', '', almasland_get_option( 'phone', '02188886959' ) ),
			'notifyPopup'  => almasland_get_notify_popup_config(),
		)
	);

	$primary   = sanitize_hex_color( almasland_get_panel( 'identity', 'primary_color', almasland_get_option( 'primary_color', '#ff3f5f' ) ) );
	$secondary = sanitize_hex_color( almasland_get_panel( 'identity', 'secondary_color', almasland_get_option( 'secondary_color', '#2457d6' ) ) );
	$button    = sanitize_hex_color( almasland_get_panel( 'identity', 'button_color', '' ) );
	$link      = sanitize_hex_color( almasland_get_panel( 'identity', 'link_color', '' ) );
	$custom    = function_exists( 'almasland_sanitize_custom_css' ) ? almasland_sanitize_custom_css( almasland_get_panel( 'identity', 'custom_css', '' ) ) : '';
	$inline    = '';

	if ( $primary ) {
		$inline .= ':root{--color-primary:' . $primary . ';--color-primary-dark:' . $primary . ';}';
	}
	if ( $secondary ) {
		$inline .= ':root{--color-secondary:' . $secondary . ';}';
	}
	if ( $button ) {
		$inline .= ':root{--color-button:' . $button . ';}';
		$inline .= '.btn--primary{background-color:' . $button . ';border-color:' . $button . ';}';
	}
	if ( $link ) {
		$inline .= ':root{--color-link:' . $link . ';}';
		$inline .= 'a{color:' . $link . ';}';
	}
	if ( $custom ) {
		$inline .= $custom;
	}
	if ( $inline ) {
		wp_add_inline_style( 'almasland-main', $inline );
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
	echo '<link rel="preload" href="' . esc_url( ALMASLAND_URI . '/assets/fonts/Vazir.woff2' ) . '" as="font" type="font/woff2" crossorigin>' . "\n";
}
add_action( 'wp_head', 'almasland_preload_font', 1 );

/**
 * Add editor styles.
 */
function almasland_editor_assets() {
	add_editor_style( 'assets/css/style.css' );
}
add_action( 'admin_init', 'almasland_editor_assets' );
