<?php
/**
 * Theme Customizer settings.
 *
 * @package AlmasLand
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Sanitize checkbox.
 *
 * @param mixed $checked Value.
 * @return bool
 */
function almasland_sanitize_checkbox( $checked ) {
	return (bool) $checked;
}

/**
 * Register customizer options.
 *
 * @param WP_Customize_Manager $wp_customize Customizer manager.
 */
function almasland_customize_register( $wp_customize ) {
	$wp_customize->add_panel(
		'almasland_theme_options',
		array(
			'title'       => esc_html__( 'تنظیمات قالب الماس لند', 'almas-land' ),
			'description' => esc_html__( 'تنظیمات عمومی هدر، فوتر، رنگ‌ها و صفحه اصلی.', 'almas-land' ),
			'priority'    => 30,
		)
	);

	$sections = array(
		'identity' => esc_html__( 'برند و رنگ‌ها', 'almas-land' ),
		'contact'  => esc_html__( 'اطلاعات تماس', 'almas-land' ),
		'header'   => esc_html__( 'هدر', 'almas-land' ),
		'footer'   => esc_html__( 'فوتر', 'almas-land' ),
		'home'     => esc_html__( 'صفحه اصلی', 'almas-land' ),
		'social'   => esc_html__( 'شبکه‌های اجتماعی', 'almas-land' ),
	);

	foreach ( $sections as $id => $title ) {
		$wp_customize->add_section(
			'almasland_' . $id,
			array(
				'title' => $title,
				'panel' => 'almasland_theme_options',
			)
		);
	}

	$settings = array(
		'primary_color'      => array( '#ff3f5f', 'sanitize_hex_color', 'identity', esc_html__( 'رنگ اصلی', 'almas-land' ), 'color' ),
		'secondary_color'    => array( '#2457d6', 'sanitize_hex_color', 'identity', esc_html__( 'رنگ دوم', 'almas-land' ), 'color' ),
		'phone'              => array( '۰۲۱-۸۸۸۸۶۹۵۹', 'sanitize_text_field', 'contact', esc_html__( 'شماره تماس', 'almas-land' ), 'text' ),
		'email'              => array( 'support@almasland.test', 'sanitize_email', 'contact', esc_html__( 'ایمیل', 'almas-land' ), 'email' ),
		'address'            => array( 'تهران، خیابان ولیعصر، ساختمان الماس', 'sanitize_text_field', 'contact', esc_html__( 'آدرس', 'almas-land' ), 'textarea' ),
		'topbar_text'        => array( 'ارسال سریع، ضمانت اصالت کالا و پشتیبانی تخصصی', 'sanitize_text_field', 'header', esc_html__( 'متن نوار بالای هدر', 'almas-land' ), 'text' ),
		'show_topbar'        => array( true, 'almasland_sanitize_checkbox', 'header', esc_html__( 'نمایش نوار بالای هدر', 'almas-land' ), 'checkbox' ),
		'footer_about'       => array( 'فروشگاه تخصصی محصولات دیجیتال با تجربه خرید ساده، شفاف و مطمئن.', 'wp_kses_post', 'footer', esc_html__( 'متن معرفی فوتر', 'almas-land' ), 'textarea' ),
		'footer_copyright'   => array( 'تمام حقوق برای الماس لند محفوظ است.', 'sanitize_text_field', 'footer', esc_html__( 'متن کپی‌رایت', 'almas-land' ), 'text' ),
		'hero_title'         => array( 'فروشگاه تخصصی محصولات دیجیتال', 'sanitize_text_field', 'home', esc_html__( 'عنوان بنر صفحه اصلی', 'almas-land' ), 'text' ),
		'hero_text'          => array( 'خرید مطمئن لپ‌تاپ، موبایل، مانیتور و لوازم جانبی با پشتیبانی تخصصی.', 'wp_kses_post', 'home', esc_html__( 'متن بنر صفحه اصلی', 'almas-land' ), 'textarea' ),
		'hero_button_text'   => array( 'مشاهده محصولات', 'sanitize_text_field', 'home', esc_html__( 'متن دکمه بنر', 'almas-land' ), 'text' ),
		'hero_button_url'    => array( '', 'esc_url_raw', 'home', esc_html__( 'لینک دکمه بنر', 'almas-land' ), 'url' ),
		'home_products_title'=> array( 'محصولات جدید', 'sanitize_text_field', 'home', esc_html__( 'عنوان سکشن محصولات', 'almas-land' ), 'text' ),
		'home_blog_title'    => array( 'آخرین نوشته‌ها', 'sanitize_text_field', 'home', esc_html__( 'عنوان سکشن وبلاگ', 'almas-land' ), 'text' ),
		'social_instagram'   => array( '', 'esc_url_raw', 'social', esc_html__( 'اینستاگرام', 'almas-land' ), 'url' ),
		'social_telegram'    => array( '', 'esc_url_raw', 'social', esc_html__( 'تلگرام', 'almas-land' ), 'url' ),
		'social_linkedin'    => array( '', 'esc_url_raw', 'social', esc_html__( 'لینکدین', 'almas-land' ), 'url' ),
	);

	foreach ( $settings as $key => $args ) {
		list( $default, $sanitize, $section, $label, $type ) = $args;
		$wp_customize->add_setting(
			'almasland_' . $key,
			array(
				'default'           => $default,
				'sanitize_callback' => $sanitize,
				'transport'         => 'refresh',
			)
		);

		if ( 'color' === $type ) {
			$wp_customize->add_control(
				new WP_Customize_Color_Control(
					$wp_customize,
					'almasland_' . $key,
					array(
						'label'   => $label,
						'section' => 'almasland_' . $section,
					)
				)
			);
			continue;
		}

		$wp_customize->add_control(
			'almasland_' . $key,
			array(
				'label'   => $label,
				'section' => 'almasland_' . $section,
				'type'    => $type,
			)
		);
	}

	$image_settings = array(
		'hero_image'   => esc_html__( 'تصویر بنر اصلی', 'almas-land' ),
		'promo_image_1'=> esc_html__( 'بنر تبلیغاتی اول', 'almas-land' ),
		'promo_image_2'=> esc_html__( 'بنر تبلیغاتی دوم', 'almas-land' ),
		'promo_image_3'=> esc_html__( 'بنر تبلیغاتی سوم', 'almas-land' ),
	);

	foreach ( $image_settings as $key => $label ) {
		$wp_customize->add_setting(
			'almasland_' . $key,
			array(
				'default'           => '',
				'sanitize_callback' => 'esc_url_raw',
			)
		);
		$wp_customize->add_control(
			new WP_Customize_Image_Control(
				$wp_customize,
				'almasland_' . $key,
				array(
					'label'   => $label,
					'section' => 'almasland_home',
				)
			)
		);
	}

	$wp_customize->add_setting(
		'almasland_contact_page_id',
		array(
			'default'           => 0,
			'sanitize_callback' => 'absint',
		)
	);
	$wp_customize->add_control(
		'almasland_contact_page_id',
		array(
			'label'   => esc_html__( 'صفحه تماس با ما', 'almas-land' ),
			'section' => 'almasland_contact',
			'type'    => 'dropdown-pages',
		)
	);
}
add_action( 'customize_register', 'almasland_customize_register' );

