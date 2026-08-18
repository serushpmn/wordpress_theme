<?php
/**
 * Theme panel admin pages.
 *
 * @package AlmasLand
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register admin menu.
 */
function almasland_theme_panel_menu() {
	$cap = 'manage_options';
	$slug = 'almasland-theme-panel';

	add_menu_page(
		__( 'پنل مدیریت قالب', 'almas-land' ),
		__( 'پنل مدیریت قالب', 'almas-land' ),
		$cap,
		$slug,
		'almasland_panel_page_dashboard',
		'dashicons-admin-customizer',
		59
	);

	$pages = array(
		'dashboard'     => __( 'داشبورد', 'almas-land' ),
		'identity'      => __( 'هویت سایت', 'almas-land' ),
		'homepage'      => __( 'صفحه اصلی', 'almas-land' ),
		'shop'          => __( 'فروشگاه', 'almas-land' ),
		'sliders'       => __( 'اسلایدرها', 'almas-land' ),
		'banners'       => __( 'بنرها', 'almas-land' ),
		'blocks'        => __( 'بلاک‌ها', 'almas-land' ),
		'notifications' => __( 'اطلاع‌رسانی', 'almas-land' ),
		'footer'        => __( 'فوتر', 'almas-land' ),
		'social'        => __( 'شبکه‌های اجتماعی', 'almas-land' ),
	);

	foreach ( $pages as $id => $title ) {
		$callback = 'almasland_panel_page_' . $id;
		if ( 'dashboard' === $id ) {
			continue;
		}
		add_submenu_page( $slug, $title, $title, $cap, 'almasland-panel-' . $id, $callback );
	}
}
add_action( 'admin_menu', 'almasland_theme_panel_menu' );

/**
 * Enqueue admin assets.
 *
 * @param string $hook Hook.
 */
function almasland_theme_panel_assets( $hook ) {
	if ( false === strpos( $hook, 'almasland' ) ) {
		return;
	}

	wp_enqueue_style( 'wp-color-picker' );
	wp_enqueue_script( 'wp-color-picker' );
	wp_enqueue_media();
	wp_enqueue_script( 'jquery-ui-sortable' );

	wp_enqueue_style(
		'almasland-theme-panel',
		ALMASLAND_URI . '/assets/css/admin-theme-panel.css',
		array(),
		ALMASLAND_VERSION
	);
	wp_enqueue_script(
		'almasland-theme-panel',
		ALMASLAND_URI . '/assets/js/admin-theme-panel.js',
		array( 'jquery', 'wp-color-picker', 'jquery-ui-sortable' ),
		ALMASLAND_VERSION,
		true
	);
}
add_action( 'admin_enqueue_scripts', 'almasland_theme_panel_assets' );

/**
 * Handle save.
 */
function almasland_save_theme_panel() {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( esc_html__( 'دسترسی غیرمجاز.', 'almas-land' ) );
	}

	$page = isset( $_POST['panel_page'] ) ? sanitize_key( wp_unslash( $_POST['panel_page'] ) ) : '';
	if ( ! $page || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['almasland_panel_nonce'] ?? '' ) ), 'almasland_save_panel_' . $page ) ) {
		wp_die( esc_html__( 'درخواست نامعتبر.', 'almas-land' ) );
	}

	$current = almasland_get_panel_settings();
	$input   = isset( $_POST['almasland_panel'] ) ? wp_unslash( $_POST['almasland_panel'] ) : array(); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput

	if ( isset( $input['shop']['featured_brands'] ) && is_string( $input['shop']['featured_brands'] ) ) {
		$lines = preg_split( '/\r\n|\r|\n/', $input['shop']['featured_brands'] );
		$input['shop']['featured_brands'] = array_values( array_filter( array_map( 'trim', $lines ) ) );
	}

	if ( 'shop' === $page && isset( $input['shop'] ) ) {
		if ( ! isset( $input['shop']['featured_product_ids'] ) ) {
			$input['shop']['featured_product_ids'] = array();
		}
		if ( ! isset( $input['shop']['featured_category_ids'] ) ) {
			$input['shop']['featured_category_ids'] = array();
		}
	}

	$merged = almasland_deep_merge_settings( $current, is_array( $input ) ? $input : array() );
	$clean  = almasland_sanitize_panel_settings( $merged );

	almasland_save_panel_settings( $clean );

	$redirect = add_query_arg(
		array(
			'page'    => 'dashboard' === $page ? 'almasland-theme-panel' : 'almasland-panel-' . $page,
			'updated' => 'true',
		),
		admin_url( 'admin.php' )
	);
	wp_safe_redirect( $redirect );
	exit;
}
add_action( 'admin_post_almasland_save_theme_panel', 'almasland_save_theme_panel' );

/**
 * Panel wrapper.
 *
 * @param string $title Page title.
 * @param callable $callback Render callback.
 */
function almasland_panel_wrap( $title, $callback ) {
	$updated = isset( $_GET['updated'] ) ? sanitize_text_field( wp_unslash( $_GET['updated'] ) ) : '';
	?>
	<div class="wrap almasland-panel-wrap" dir="rtl">
		<h1><?php echo esc_html( $title ); ?></h1>
		<?php if ( 'true' === $updated ) : ?>
			<div class="notice notice-success is-dismissible"><p><?php esc_html_e( 'تنظیمات ذخیره شد.', 'almas-land' ); ?></p></div>
		<?php endif; ?>
		<?php call_user_func( $callback ); ?>
	</div>
	<?php
}

/**
 * Dashboard page.
 */
function almasland_panel_page_dashboard() {
	almasland_panel_wrap(
		__( 'داشبورد پنل قالب', 'almas-land' ),
		static function () {
			$links = array(
				'identity'      => __( 'هویت سایت', 'almas-land' ),
				'homepage'      => __( 'صفحه اصلی', 'almas-land' ),
				'shop'          => __( 'فروشگاه', 'almas-land' ),
				'sliders'       => __( 'اسلایدرها', 'almas-land' ),
				'banners'       => __( 'بنرها', 'almas-land' ),
				'blocks'        => __( 'بلاک‌ها', 'almas-land' ),
				'notifications' => __( 'اطلاع‌رسانی', 'almas-land' ),
				'footer'        => __( 'فوتر', 'almas-land' ),
				'social'        => __( 'شبکه‌های اجتماعی', 'almas-land' ),
			);
			echo '<div class="almasland-dashboard-grid">';
			foreach ( $links as $slug => $label ) {
				printf(
					'<a class="almasland-dashboard-card" href="%s"><strong>%s</strong></a>',
					esc_url( admin_url( 'admin.php?page=almasland-panel-' . $slug ) ),
					esc_html( $label )
				);
			}
			echo '</div>';
			echo '<p>' . esc_html__( 'تنظیمات قالب از این پنل مدیریت می‌شود. Customizer وردپرس همچنان به‌عنوان پشتیبان در دسترس است.', 'almas-land' ) . '</p>';
		}
	);
}

/**
 * Identity page.
 */
function almasland_panel_page_identity() {
	almasland_panel_wrap( __( 'هویت سایت', 'almas-land' ), 'almasland_panel_render_identity' );
}

function almasland_panel_render_identity() {
	$s = almasland_get_panel_settings()['identity'];
	almasland_panel_form_open( 'identity' );
	almasland_panel_card_open( __( 'لوگوها', 'almas-land' ) );
	almasland_panel_field_image( 'almasland_panel[identity][logo_main]', __( 'لوگو اصلی', 'almas-land' ), $s['logo_main'] );
	almasland_panel_field_image( 'almasland_panel[identity][logo_dark]', __( 'لوگو حالت تیره', 'almas-land' ), $s['logo_dark'] );
	almasland_panel_field_image( 'almasland_panel[identity][logo_mobile]', __( 'لوگو موبایل', 'almas-land' ), $s['logo_mobile'] );
	almasland_panel_field_image( 'almasland_panel[identity][favicon]', __( 'فاوآیکون', 'almas-land' ), $s['favicon'] );
	almasland_panel_card_close();
	almasland_panel_card_open( __( 'رنگ‌ها', 'almas-land' ) );
	echo '<p class="description">' . esc_html__( 'این رنگ‌ها در کل قالب به‌صورت داینامیک اعمال می‌شوند. Primary جایگزین بنفش‌های قبلی شده است.', 'almas-land' ) . '</p>';
	almasland_panel_field_color( 'almasland_panel[identity][primary_color]', __( 'رنگ اصلی (Primary)', 'almas-land' ), $s['primary_color'] );
	almasland_panel_field_color( 'almasland_panel[identity][secondary_color]', __( 'رنگ دوم (Secondary)', 'almas-land' ), $s['secondary_color'] );
	almasland_panel_field_color( 'almasland_panel[identity][button_color]', __( 'رنگ دکمه‌ها (Button)', 'almas-land' ), $s['button_color'] );
	almasland_panel_field_color( 'almasland_panel[identity][link_color]', __( 'رنگ لینک‌ها (Link)', 'almas-land' ), $s['link_color'] );
	almasland_panel_card_close();
	almasland_panel_card_open( __( 'CSS سفارشی', 'almas-land' ) );
	almasland_panel_field_textarea( 'almasland_panel[identity][custom_css]', __( 'کد CSS', 'almas-land' ), $s['custom_css'], 8 );
	almasland_panel_card_close();
	almasland_panel_form_close();
}

/**
 * Homepage page.
 */
function almasland_panel_page_homepage() {
	almasland_panel_wrap( __( 'صفحه اصلی', 'almas-land' ), 'almasland_panel_render_homepage' );
}

function almasland_panel_render_homepage() {
	$hp     = almasland_get_panel_settings()['homepage'];
	$labels = almasland_homepage_section_labels();
	almasland_panel_form_open( 'homepage' );
	almasland_panel_card_open( __( 'نمایش سکشن‌ها', 'almas-land' ) );
	echo '<ul id="almasland-section-sort" class="almasland-sortable">';
	foreach ( $hp['section_order'] as $key ) {
		if ( ! isset( $labels[ $key ] ) ) {
			continue;
		}
		$enabled = ! empty( $hp['sections'][ $key ] );
		echo '<li class="almasland-sort-item">';
		echo '<span class="dashicons dashicons-menu"></span>';
		echo '<input type="hidden" name="almasland_panel[homepage][section_order][]" value="' . esc_attr( $key ) . '">';
		printf( '<input type="hidden" name="almasland_panel[homepage][sections][%1$s]" value="0">', esc_attr( $key ) );
		printf(
			'<label><input type="checkbox" name="almasland_panel[homepage][sections][%1$s]" value="1" %2$s> %3$s</label>',
			esc_attr( $key ),
			checked( $enabled, true, false ),
			esc_html( $labels[ $key ] )
		);
		echo '</li>';
	}
	echo '</ul>';
	almasland_panel_card_close();

	almasland_panel_card_open( __( 'اسلایدر Hero', 'almas-land' ) );
	almasland_panel_hero_size_guide();
	echo '<div class="almasland-hero-settings">';
	almasland_panel_field_checkbox( 'almasland_panel[homepage][hero_enabled]', __( 'نمایش اسلایدر', 'almas-land' ), ! empty( $hp['hero_enabled'] ) );
	almasland_panel_field_checkbox( 'almasland_panel[homepage][hero_autoplay]', __( 'پخش خودکار', 'almas-land' ), ! empty( $hp['hero_autoplay'] ) );
	almasland_panel_field_text( 'almasland_panel[homepage][hero_interval]', __( 'فاصله بین اسلایدها (ثانیه)', 'almas-land' ), max( 2, (int) ( ( $hp['hero_interval'] ?? 5000 ) / 1000 ) ), 'number' );
	echo '</div>';
	$hero_slides = ! empty( $hp['hero_slides'] ) ? $hp['hero_slides'] : array();
	if ( empty( $hero_slides ) ) {
		$hero_slides = array(
			array(
				'image_desktop' => $hp['hero_image_desktop'] ?? 0,
				'image_mobile'  => $hp['hero_image_mobile'] ?? 0,
				'link'          => $hp['hero_button_url'] ?? '',
				'alt'           => $hp['hero_title'] ?? '',
				'enabled'       => true,
			),
		);
	}
	if ( empty( array_filter( wp_list_pluck( $hero_slides, 'image_desktop' ) ) ) && empty( array_filter( wp_list_pluck( $hero_slides, 'image_mobile' ) ) ) ) {
		$hero_slides = array(
			array(
				'image_desktop' => 0,
				'image_mobile'  => 0,
				'link'          => '',
				'alt'           => '',
				'enabled'       => true,
			),
		);
	}
	echo '<div class="almasland-hero-slides-head">';
	echo '<h3>' . esc_html__( 'اسلایدها', 'almas-land' ) . '</h3>';
	echo '<p class="description">' . esc_html__( 'هر اسلاید دو تصویر جدا دارد. با کشیدن می‌توانید ترتیب را تغییر دهید.', 'almas-land' ) . '</p>';
	echo '</div>';
	echo '<div id="almasland-repeater-hero-slides" class="almasland-hero-slides" data-repeater="hero-slides">';
	foreach ( $hero_slides as $i => $slide ) {
		almasland_panel_render_hero_slide_row( $i, $slide );
	}
	echo '</div>';
	echo '<button type="button" class="button button-secondary almasland-add-repeater almasland-hero-add-slide" data-target="hero-slides">' . esc_html__( '+ افزودن اسلاید جدید', 'almas-land' ) . '</button>';
	almasland_panel_card_close();

	almasland_panel_card_open( __( 'متن‌های سکشن', 'almas-land' ) );
	almasland_panel_field_text( 'almasland_panel[homepage][products_title]', __( 'عنوان محصولات', 'almas-land' ), $hp['products_title'] );
	almasland_panel_field_text( 'almasland_panel[homepage][blog_title]', __( 'عنوان مقالات', 'almas-land' ), $hp['blog_title'] );
	almasland_panel_card_close();
	almasland_panel_form_close();
}

/**
 * Shop page.
 */
function almasland_panel_page_shop() {
	almasland_panel_wrap( __( 'فروشگاه', 'almas-land' ), 'almasland_panel_render_shop' );
}

function almasland_panel_render_shop() {
	$shop = almasland_get_panel_settings()['shop'];
	almasland_panel_form_open( 'shop' );
	almasland_panel_card_open( __( 'تنظیمات گرید', 'almas-land' ) );
	almasland_panel_field_text( 'almasland_panel[shop][per_page]', __( 'تعداد محصولات', 'almas-land' ), $shop['per_page'], 'number' );
	almasland_panel_field_text( 'almasland_panel[shop][columns]', __( 'تعداد ستون‌ها', 'almas-land' ), $shop['columns'], 'number' );
	almasland_panel_field_text( 'almasland_panel[shop][rows]', __( 'تعداد ردیف‌ها', 'almas-land' ), $shop['rows'], 'number' );
	almasland_panel_card_close();

	if ( class_exists( 'WooCommerce' ) ) {
		almasland_panel_card_open( __( 'محصولات ویژه', 'almas-land' ) );
		$products = wc_get_products( array( 'limit' => 100, 'status' => 'publish', 'orderby' => 'title', 'order' => 'ASC' ) );
		echo '<select name="almasland_panel[shop][featured_product_ids][]" multiple class="almasland-select-multi">';
		foreach ( $products as $product ) {
			printf(
				'<option value="%1$d" %2$s>%3$s</option>',
				(int) $product->get_id(),
				selected( in_array( $product->get_id(), (array) $shop['featured_product_ids'], true ), true, false ),
				esc_html( $product->get_name() )
			);
		}
		echo '</select>';
		almasland_panel_card_close();

		almasland_panel_card_open( __( 'دسته‌های ویژه', 'almas-land' ) );
		$terms = get_terms( array( 'taxonomy' => 'product_cat', 'hide_empty' => false ) );
		if ( ! is_wp_error( $terms ) ) {
			echo '<select name="almasland_panel[shop][featured_category_ids][]" multiple class="almasland-select-multi">';
			foreach ( $terms as $term ) {
				printf(
					'<option value="%1$d" %2$s>%3$s</option>',
					(int) $term->term_id,
					selected( in_array( $term->term_id, (array) $shop['featured_category_ids'], true ), true, false ),
					esc_html( $term->name )
				);
			}
			echo '</select>';
		}
		almasland_panel_card_close();
	}

	almasland_panel_card_open( __( 'برندهای ویژه', 'almas-land' ) );
	almasland_panel_field_textarea( 'almasland_panel[shop][featured_brands]', __( 'هر برند در یک خط', 'almas-land' ), implode( "\n", (array) $shop['featured_brands'] ), 5 );
	echo '<p class="description">' . esc_html__( 'نام برندها را هر کدام در یک خط بنویسید.', 'almas-land' ) . '</p>';
	almasland_panel_card_close();
	almasland_panel_form_close();
}

/**
 * Sliders page.
 */
function almasland_panel_page_sliders() {
	almasland_panel_wrap( __( 'اسلایدرها', 'almas-land' ), 'almasland_panel_render_sliders' );
}

function almasland_panel_render_sliders() {
	echo '<div class="notice notice-info inline"><p>' . esc_html__( 'اسلایدر Hero صفحه اصلی از بخش «صفحه اصلی → اسلایدر Hero» مدیریت می‌شود.', 'almas-land' ) . ' <a href="' . esc_url( admin_url( 'admin.php?page=almasland-panel-homepage' ) ) . '">' . esc_html__( 'رفتن به تنظیمات Hero', 'almas-land' ) . '</a></p></div>';
	$sliders = almasland_get_panel_settings()['sliders'];
	if ( empty( $sliders ) ) {
		$sliders = array( array( 'image' => 0, 'title' => '', 'text' => '', 'button_text' => '', 'link' => '', 'enabled' => true ) );
	}
	almasland_panel_form_open( 'sliders' );
	echo '<div id="almasland-repeater-sliders" data-repeater="sliders">';
	foreach ( $sliders as $i => $slide ) {
		almasland_panel_render_slider_row( $i, $slide );
	}
	echo '</div>';
	echo '<button type="button" class="button almasland-add-repeater" data-target="sliders" data-template="slider">' . esc_html__( 'افزودن اسلاید', 'almas-land' ) . '</button>';
	almasland_panel_form_close();
}

/**
 * Hero image size guide for admin panel.
 */
function almasland_panel_hero_size_guide() {
	?>
	<div class="almasland-hero-guide" aria-label="<?php esc_attr_e( 'راهنمای ابعاد تصویر', 'almas-land' ); ?>">
		<div class="almasland-hero-guide__intro">
			<strong><?php esc_html_e( 'راهنمای سایز تصاویر', 'almas-land' ); ?></strong>
			<p><?php esc_html_e( 'برای بهترین نتیجه، تصاویر را دقیقاً با ابعاد زیر آماده کنید. فرمت JPG یا WebP با کیفیت ۸۰–۹۰٪ پیشنهاد می‌شود.', 'almas-land' ); ?></p>
		</div>
		<div class="almasland-hero-guide__grid">
			<div class="almasland-hero-guide__item almasland-hero-guide__item--desktop">
				<span class="almasland-hero-guide__badge"><?php esc_html_e( 'دسکتاپ', 'almas-land' ); ?></span>
				<strong>1300 × 400 px</strong>
				<span><?php esc_html_e( 'نسبت ۱۳:۴ — عرض کامل بنر', 'almas-land' ); ?></span>
			</div>
			<div class="almasland-hero-guide__item almasland-hero-guide__item--mobile">
				<span class="almasland-hero-guide__badge"><?php esc_html_e( 'موبایل', 'almas-land' ); ?></span>
				<strong>768 × 960 px</strong>
				<span><?php esc_html_e( 'نسبت ۴:۵ — عمودی و تمام‌عرض', 'almas-land' ); ?></span>
			</div>
		</div>
	</div>
	<?php
}

/**
 * Render one hero slide row.
 *
 * @param int|string $i    Index.
 * @param array      $item Slide data.
 */
function almasland_panel_render_hero_slide_row( $i, $item ) {
	$slide_number = is_numeric( $i ) ? ( (int) $i + 1 ) : 1;
	echo '<article class="almasland-repeater-row almasland-hero-slide" data-index="' . esc_attr( (string) $i ) . '">';
	echo '<div class="almasland-hero-slide__head">';
	echo '<div class="almasland-hero-slide__title">';
	echo '<span class="almasland-hero-slide__number">' . esc_html( almasland_persian_digits( (string) $slide_number ) ) . '</span>';
	echo '<strong>' . esc_html__( 'اسلاید', 'almas-land' ) . '</strong>';
	echo '</div>';
	echo '<div class="almasland-hero-slide__tools">';
	printf( '<input type="hidden" name="almasland_panel[homepage][hero_slides][%1$s][enabled]" value="0">', esc_attr( (string) $i ) );
	printf(
		'<label class="almasland-hero-slide__toggle"><input type="checkbox" name="almasland_panel[homepage][hero_slides][%1$s][enabled]" value="1" %2$s> %3$s</label>',
		esc_attr( (string) $i ),
		checked( ! empty( $item['enabled'] ), true, false ),
		esc_html__( 'فعال', 'almas-land' )
	);
	echo '<button type="button" class="button-link-delete almasland-remove-row" aria-label="' . esc_attr__( 'حذف اسلاید', 'almas-land' ) . '">&times;</button>';
	echo '</div>';
	echo '</div>';
	echo '<div class="almasland-hero-slide__images">';
	almasland_panel_field_image(
		"almasland_panel[homepage][hero_slides][{$i}][image_desktop]",
		__( 'تصویر دسکتاپ', 'almas-land' ),
		$item['image_desktop'] ?? 0,
		__( '1300×400 px', 'almas-land' )
	);
	almasland_panel_field_image(
		"almasland_panel[homepage][hero_slides][{$i}][image_mobile]",
		__( 'تصویر موبایل', 'almas-land' ),
		$item['image_mobile'] ?? 0,
		__( '768×960 px', 'almas-land' )
	);
	echo '</div>';
	echo '<div class="almasland-hero-slide__meta">';
	almasland_panel_field_text( "almasland_panel[homepage][hero_slides][{$i}][link]", __( 'لینک کلیک', 'almas-land' ), $item['link'] ?? '', 'url' );
	almasland_panel_field_text( "almasland_panel[homepage][hero_slides][{$i}][alt]", __( 'متن Alt (SEO)', 'almas-land' ), $item['alt'] ?? '' );
	echo '</div>';
	echo '</article>';
}

/**
 * Render one slider row.
 *
 * @param int|string $i    Index.
 * @param array      $item Slide data.
 */
function almasland_panel_render_slider_row( $i, $item ) {
	echo '<div class="almasland-repeater-row" data-index="' . esc_attr( (string) $i ) . '">';
	echo '<h3>' . esc_html__( 'اسلاید', 'almas-land' ) . ' <button type="button" class="button-link-delete almasland-remove-row">&times;</button></h3>';
	almasland_panel_field_image( "almasland_panel[sliders][{$i}][image]", __( 'تصویر', 'almas-land' ), $item['image'] ?? 0 );
	almasland_panel_field_text( "almasland_panel[sliders][{$i}][title]", __( 'عنوان', 'almas-land' ), $item['title'] ?? '' );
	almasland_panel_field_textarea( "almasland_panel[sliders][{$i}][text]", __( 'متن', 'almas-land' ), $item['text'] ?? '', 2 );
	almasland_panel_field_text( "almasland_panel[sliders][{$i}][button_text]", __( 'متن دکمه', 'almas-land' ), $item['button_text'] ?? '' );
	almasland_panel_field_text( "almasland_panel[sliders][{$i}][link]", __( 'لینک', 'almas-land' ), $item['link'] ?? '', 'url' );
	almasland_panel_field_checkbox( "almasland_panel[sliders][{$i}][enabled]", __( 'فعال', 'almas-land' ), ! empty( $item['enabled'] ) );
	echo '</div>';
}

/**
 * Banners page.
 */
function almasland_panel_page_banners() {
	almasland_panel_wrap( __( 'بنرها', 'almas-land' ), 'almasland_panel_render_banners' );
}

function almasland_panel_render_banners() {
	$banners = almasland_get_panel_settings()['banners'];
	if ( empty( $banners ) ) {
		$banners = array( array() );
	}
	almasland_panel_form_open( 'banners' );
	echo '<div id="almasland-repeater-banners" data-repeater="banners">';
	foreach ( $banners as $i => $banner ) {
		almasland_panel_render_banner_row( $i, $banner );
	}
	echo '</div>';
	echo '<button type="button" class="button almasland-add-repeater" data-target="banners" data-template="banner">' . esc_html__( 'افزودن بنر', 'almas-land' ) . '</button>';
	almasland_panel_form_close();
}

function almasland_panel_render_banner_row( $i, $item ) {
	echo '<div class="almasland-repeater-row" data-index="' . esc_attr( (string) $i ) . '">';
	echo '<h3>' . esc_html__( 'بنر', 'almas-land' ) . ' <button type="button" class="button-link-delete almasland-remove-row">&times;</button></h3>';
	almasland_panel_field_image( "almasland_panel[banners][{$i}][image_desktop]", __( 'تصویر دسکتاپ', 'almas-land' ), $item['image_desktop'] ?? 0 );
	almasland_panel_field_image( "almasland_panel[banners][{$i}][image_mobile]", __( 'تصویر موبایل', 'almas-land' ), $item['image_mobile'] ?? 0 );
	almasland_panel_field_text( "almasland_panel[banners][{$i}][title]", __( 'عنوان', 'almas-land' ), $item['title'] ?? '' );
	almasland_panel_field_text( "almasland_panel[banners][{$i}][subtitle]", __( 'زیرعنوان', 'almas-land' ), $item['subtitle'] ?? '' );
	almasland_panel_field_text( "almasland_panel[banners][{$i}][link]", __( 'لینک', 'almas-land' ), $item['link'] ?? '', 'url' );
	almasland_panel_field_text( "almasland_panel[banners][{$i}][button_text]", __( 'متن دکمه', 'almas-land' ), $item['button_text'] ?? '' );
	almasland_panel_field_checkbox( "almasland_panel[banners][{$i}][enabled]", __( 'فعال', 'almas-land' ), ! empty( $item['enabled'] ) );
	echo '</div>';
}

/**
 * Blocks page.
 */
function almasland_panel_page_blocks() {
	almasland_panel_wrap( __( 'بلاک‌های اختصاصی', 'almas-land' ), 'almasland_panel_render_blocks' );
}

function almasland_panel_render_blocks() {
	$blocks = almasland_get_panel_settings()['blocks'];
	if ( empty( $blocks ) ) {
		$blocks = array( array() );
	}
	almasland_panel_form_open( 'blocks' );
	echo '<div id="almasland-repeater-blocks" data-repeater="blocks">';
	foreach ( $blocks as $i => $block ) {
		almasland_panel_render_block_row( $i, $block );
	}
	echo '</div>';
	echo '<button type="button" class="button almasland-add-repeater" data-target="blocks" data-template="block">' . esc_html__( 'افزودن بلاک', 'almas-land' ) . '</button>';
	almasland_panel_form_close();
}

function almasland_panel_render_block_row( $i, $item ) {
	echo '<div class="almasland-repeater-row" data-index="' . esc_attr( (string) $i ) . '">';
	echo '<h3>' . esc_html__( 'بلاک', 'almas-land' ) . ' <button type="button" class="button-link-delete almasland-remove-row">&times;</button></h3>';
	almasland_panel_field_text( "almasland_panel[blocks][{$i}][title]", __( 'عنوان', 'almas-land' ), $item['title'] ?? '' );
	almasland_panel_field_textarea( "almasland_panel[blocks][{$i}][description]", __( 'توضیح', 'almas-land' ), $item['description'] ?? '' );
	almasland_panel_field_image( "almasland_panel[blocks][{$i}][image]", __( 'تصویر', 'almas-land' ), $item['image'] ?? 0 );
	almasland_panel_field_text( "almasland_panel[blocks][{$i}][button_text]", __( 'متن دکمه', 'almas-land' ), $item['button_text'] ?? '' );
	almasland_panel_field_text( "almasland_panel[blocks][{$i}][link]", __( 'لینک', 'almas-land' ), $item['link'] ?? '', 'url' );
	almasland_panel_field_color( "almasland_panel[blocks][{$i}][bg_color]", __( 'رنگ پس‌زمینه', 'almas-land' ), $item['bg_color'] ?? '#f7f9fc' );
	almasland_panel_field_text( "almasland_panel[blocks][{$i}][order]", __( 'ترتیب', 'almas-land' ), $item['order'] ?? 0, 'number' );
	almasland_panel_field_checkbox( "almasland_panel[blocks][{$i}][enabled]", __( 'فعال', 'almas-land' ), ! empty( $item['enabled'] ) );
	echo '</div>';
}

/**
 * Notifications page.
 */
function almasland_panel_page_notifications() {
	almasland_panel_wrap( __( 'اطلاع‌رسانی', 'almas-land' ), 'almasland_panel_render_notifications' );
}

function almasland_panel_render_notifications() {
	$n = almasland_get_panel_settings()['notifications'];
	almasland_panel_form_open( 'notifications' );
	almasland_panel_card_open( __( 'نوار اطلاع‌رسانی', 'almas-land' ) );
	almasland_panel_field_checkbox( 'almasland_panel[notifications][bar_enabled]', __( 'نمایش نوار', 'almas-land' ), $n['bar_enabled'] );
	almasland_panel_field_text( 'almasland_panel[notifications][bar_text]', __( 'متن', 'almas-land' ), $n['bar_text'] );
	almasland_panel_field_color( 'almasland_panel[notifications][bar_color]', __( 'رنگ', 'almas-land' ), $n['bar_color'] );
	almasland_panel_field_text( 'almasland_panel[notifications][bar_link]', __( 'لینک', 'almas-land' ), $n['bar_link'], 'url' );
	almasland_panel_field_text( 'almasland_panel[notifications][bar_start]', __( 'تاریخ شروع', 'almas-land' ), $n['bar_start'], 'date' );
	almasland_panel_field_text( 'almasland_panel[notifications][bar_end]', __( 'تاریخ پایان', 'almas-land' ), $n['bar_end'], 'date' );
	almasland_panel_card_close();
	almasland_panel_card_open( __( 'Popup', 'almas-land' ) );
	almasland_panel_field_checkbox( 'almasland_panel[notifications][popup_enabled]', __( 'نمایش Popup', 'almas-land' ), $n['popup_enabled'] );
	almasland_panel_field_text( 'almasland_panel[notifications][popup_title]', __( 'عنوان', 'almas-land' ), $n['popup_title'] );
	almasland_panel_field_textarea( 'almasland_panel[notifications][popup_text]', __( 'متن', 'almas-land' ), $n['popup_text'] );
	almasland_panel_field_image( 'almasland_panel[notifications][popup_image]', __( 'تصویر', 'almas-land' ), $n['popup_image'] );
	almasland_panel_field_text( 'almasland_panel[notifications][popup_button]', __( 'متن دکمه', 'almas-land' ), $n['popup_button'] );
	almasland_panel_field_text( 'almasland_panel[notifications][popup_link]', __( 'لینک', 'almas-land' ), $n['popup_link'], 'url' );
	almasland_panel_field_text( 'almasland_panel[notifications][popup_delay]', __( 'زمان نمایش (ثانیه)', 'almas-land' ), $n['popup_delay'], 'number' );
	almasland_panel_field_checkbox( 'almasland_panel[notifications][popup_once]', __( 'نمایش فقط یکبار', 'almas-land' ), $n['popup_once'] );
	almasland_panel_card_close();
	almasland_panel_form_close();
}

/**
 * Footer page.
 */
function almasland_panel_page_footer() {
	almasland_panel_wrap( __( 'فوتر', 'almas-land' ), 'almasland_panel_render_footer' );
}

function almasland_panel_render_footer() {
	$f = almasland_get_panel_settings()['footer'];
	$h = almasland_get_panel_settings()['header'];
	almasland_panel_form_open( 'footer' );
	almasland_panel_card_open( __( 'اطلاعات', 'almas-land' ) );
	almasland_panel_field_textarea( 'almasland_panel[footer][about]', __( 'متن معرفی', 'almas-land' ), $f['about'] );
	almasland_panel_field_text( 'almasland_panel[footer][copyright]', __( 'کپی‌رایت', 'almas-land' ), $f['copyright'] );
	almasland_panel_field_text( 'almasland_panel[footer][phone]', __( 'شماره تماس', 'almas-land' ), $f['phone'] );
	almasland_panel_field_text( 'almasland_panel[footer][email]', __( 'ایمیل', 'almas-land' ), $f['email'], 'email' );
	almasland_panel_field_text( 'almasland_panel[footer][address]', __( 'آدرس', 'almas-land' ), $f['address'] );
	almasland_panel_card_close();
	almasland_panel_card_open( __( 'نمادهای اعتماد', 'almas-land' ) );
	almasland_panel_field_text( 'almasland_panel[footer][enamad_url]', __( 'لینک اینماد', 'almas-land' ), $f['enamad_url'] ?? '', 'url' );
	almasland_panel_field_image( 'almasland_panel[footer][enamad_image]', __( 'تصویر اینماد', 'almas-land' ), $f['enamad_image'] ?? 0 );
	almasland_panel_field_image( 'almasland_panel[footer][trust_badge_1]', __( 'نماد اعتماد ۱', 'almas-land' ), $f['trust_badge_1'] );
	almasland_panel_field_image( 'almasland_panel[footer][trust_badge_2]', __( 'نماد اعتماد ۲', 'almas-land' ), $f['trust_badge_2'] );
	almasland_panel_field_image( 'almasland_panel[footer][samandehi]', __( 'نماد ساماندهی', 'almas-land' ), $f['samandehi'] );
	almasland_panel_card_close();
	almasland_panel_card_open( __( 'هدر — نوار بالا', 'almas-land' ) );
	almasland_panel_field_checkbox( 'almasland_panel[header][show_topbar]', __( 'نمایش نوار بالای هدر', 'almas-land' ), $h['show_topbar'] );
	almasland_panel_field_text( 'almasland_panel[header][topbar_text]', __( 'متن نوار', 'almas-land' ), $h['topbar_text'] );
	almasland_panel_card_close();
	almasland_panel_card_open( __( 'لینک‌های مهم', 'almas-land' ) );
	$links = ! empty( $f['links'] ) ? $f['links'] : array( array( 'title' => '', 'url' => '', 'order' => 0 ) );
	echo '<div id="almasland-repeater-links" data-repeater="links">';
	foreach ( $links as $i => $link ) {
		echo '<div class="almasland-repeater-row almasland-repeater-row--inline">';
		almasland_panel_field_text( "almasland_panel[footer][links][{$i}][title]", __( 'عنوان', 'almas-land' ), $link['title'] ?? '' );
		almasland_panel_field_text( "almasland_panel[footer][links][{$i}][url]", __( 'لینک', 'almas-land' ), $link['url'] ?? '', 'url' );
		almasland_panel_field_text( "almasland_panel[footer][links][{$i}][order]", __( 'ترتیب', 'almas-land' ), $link['order'] ?? 0, 'number' );
		echo '<button type="button" class="button-link-delete almasland-remove-row">&times;</button></div>';
	}
	echo '</div>';
	echo '<button type="button" class="button almasland-add-repeater" data-target="links" data-template="link">' . esc_html__( 'افزودن لینک', 'almas-land' ) . '</button>';
	almasland_panel_card_close();
	almasland_panel_form_close();
}

/**
 * Social page.
 */
function almasland_panel_page_social() {
	almasland_panel_wrap( __( 'شبکه‌های اجتماعی', 'almas-land' ), 'almasland_panel_render_social' );
}

function almasland_panel_render_social() {
	$s       = almasland_get_panel_settings()['social'];
	$labels  = array(
		'instagram' => 'Instagram',
		'telegram'  => 'Telegram',
		'whatsapp'  => 'WhatsApp',
		'linkedin'  => 'LinkedIn',
		'youtube'   => 'YouTube',
		'aparat'    => 'Aparat',
		'x'         => 'X',
		'facebook'  => 'Facebook',
	);
	almasland_panel_form_open( 'social' );
	almasland_panel_card_open( __( 'لینک شبکه‌ها', 'almas-land' ) );
	foreach ( $labels as $key => $label ) {
		almasland_panel_field_text( 'almasland_panel[social][' . $key . ']', $label, $s[ $key ] ?? '', 'url' );
	}
	almasland_panel_card_close();
	almasland_panel_form_close();
}
