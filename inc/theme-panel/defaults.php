<?php
/**
 * Theme panel default settings.
 *
 * @package AlmasLand
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Default panel settings.
 *
 * @return array<string, mixed>
 */
function almasland_theme_panel_defaults() {
	return array(
		'identity'      => array(
			'logo_main'       => 0,
			'logo_dark'       => 0,
			'logo_mobile'     => 0,
			'favicon'         => 0,
			'primary_color'   => '#ff3f5f',
			'secondary_color' => '#2457d6',
			'button_color'    => '#ff3f5f',
			'link_color'      => '#2457d6',
			'custom_css'      => '',
		),
		'homepage'      => array(
			'section_order' => array(
				'slider',
				'categories',
				'featured_products',
				'brands',
				'banner_1',
				'banner_2',
				'articles',
				'custom_blocks',
				'instagram',
				'newsletter',
			),
			'sections'      => array(
				'slider'             => true,
				'categories'         => true,
				'featured_products'  => true,
				'brands'             => false,
				'banner_1'           => true,
				'banner_2'           => false,
				'articles'           => true,
				'custom_blocks'      => true,
				'instagram'          => false,
				'newsletter'         => false,
			),
			'hero_title'         => 'فروشگاه تخصصی محصولات دیجیتال',
			'hero_text'          => 'خرید مطمئن لپ‌تاپ، موبایل، مانیتور و لوازم جانبی با پشتیبانی تخصصی.',
			'hero_button_text'   => 'مشاهده محصولات',
			'hero_button_url'    => '',
			'hero_enabled'       => true,
			'hero_image_desktop' => 0,
			'hero_image_tablet'  => 0,
			'hero_image_mobile'  => 0,
			'products_title'     => 'محصولات جدید',
			'blog_title'       => 'آخرین نوشته‌ها',
		),
		'shop'          => array(
			'featured_product_ids'   => array(),
			'featured_category_ids'  => array(),
			'featured_brands'        => array(),
			'per_page'               => 12,
			'columns'                => 3,
			'rows'                   => 4,
		),
		'sliders'       => array(),
		'banners'       => array(),
		'blocks'        => array(),
		'notifications' => array(
			'bar_enabled'    => false,
			'bar_text'       => '',
			'bar_color'      => '#2457d6',
			'bar_link'       => '',
			'bar_start'      => '',
			'bar_end'        => '',
			'popup_enabled'  => false,
			'popup_title'    => '',
			'popup_text'     => '',
			'popup_image'    => 0,
			'popup_button'   => '',
			'popup_link'     => '',
			'popup_delay'    => 3,
			'popup_once'     => true,
		),
		'footer'        => array(
			'about'          => 'فروشگاه تخصصی محصولات دیجیتال با تجربه خرید ساده، شفاف و مطمئن.',
			'copyright'      => 'تمام حقوق برای الماس لند محفوظ است.',
			'trust_badge_1'  => 0,
			'trust_badge_2'  => 0,
			'samandehi'      => 0,
			'phone'          => '۰۲۱-۸۸۸۸۶۹۵۹',
			'email'          => 'support@almasland.test',
			'address'        => 'تهران، خیابان ولیعصر، ساختمان الماس',
			'links'          => array(),
		),
		'social'        => array(
			'instagram' => '',
			'telegram'  => '',
			'whatsapp'  => '',
			'linkedin'  => '',
			'youtube'   => '',
			'aparat'    => '',
			'x'         => '',
			'facebook'  => '',
		),
		'header'        => array(
			'show_topbar'  => true,
			'topbar_text'  => 'ارسال سریع، ضمانت اصالت کالا و پشتیبانی تخصصی',
		),
	);
}

/**
 * Homepage section labels.
 *
 * @return array<string, string>
 */
function almasland_homepage_section_labels() {
	return array(
		'slider'            => __( 'اسلایدر', 'almas-land' ),
		'categories'        => __( 'دسته‌بندی', 'almas-land' ),
		'featured_products' => __( 'محصولات ویژه', 'almas-land' ),
		'brands'            => __( 'برندها', 'almas-land' ),
		'banner_1'          => __( 'بنر اول', 'almas-land' ),
		'banner_2'          => __( 'بنر دوم', 'almas-land' ),
		'articles'          => __( 'مقالات', 'almas-land' ),
		'custom_blocks'     => __( 'بلاک اختصاصی', 'almas-land' ),
		'instagram'         => __( 'اینستاگرام', 'almas-land' ),
		'newsletter'        => __( 'خبرنامه', 'almas-land' ),
	);
}

/**
 * Flat key map: legacy theme_mod key => panel path.
 *
 * @return array<string, array{0:string,1:string}>
 */
function almasland_theme_panel_legacy_map() {
	return array(
		'primary_color'       => array( 'identity', 'primary_color' ),
		'secondary_color'     => array( 'identity', 'secondary_color' ),
		'phone'               => array( 'footer', 'phone' ),
		'email'               => array( 'footer', 'email' ),
		'address'             => array( 'footer', 'address' ),
		'topbar_text'         => array( 'header', 'topbar_text' ),
		'show_topbar'         => array( 'header', 'show_topbar' ),
		'footer_about'        => array( 'footer', 'about' ),
		'footer_copyright'    => array( 'footer', 'copyright' ),
		'hero_title'          => array( 'homepage', 'hero_title' ),
		'hero_text'           => array( 'homepage', 'hero_text' ),
		'hero_button_text'    => array( 'homepage', 'hero_button_text' ),
		'hero_button_url'     => array( 'homepage', 'hero_button_url' ),
		'home_products_title' => array( 'homepage', 'products_title' ),
		'home_blog_title'     => array( 'homepage', 'blog_title' ),
		'social_instagram'    => array( 'social', 'instagram' ),
		'social_telegram'     => array( 'social', 'telegram' ),
		'social_linkedin'     => array( 'social', 'linkedin' ),
	);
}
