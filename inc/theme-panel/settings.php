<?php
/**
 * Theme panel get/save helpers.
 *
 * @package AlmasLand
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Get merged panel settings.
 *
 * @return array<string, mixed>
 */
function almasland_get_panel_settings() {
	static $settings = null;

	if ( null !== $settings ) {
		return $settings;
	}

	$stored = get_option( 'almasland_theme_panel', array() );
	if ( ! is_array( $stored ) ) {
		$stored = array();
	}

	$settings = almasland_deep_merge_settings( almasland_theme_panel_defaults(), $stored );

	if ( empty( $stored ) ) {
		$settings = almasland_migrate_theme_mods_to_panel( $settings );
	}

	return $settings;
}

/**
 * Deep merge settings with defaults.
 *
 * @param array<string, mixed> $defaults Defaults.
 * @param array<string, mixed> $stored   Stored values.
 * @return array<string, mixed>
 */
function almasland_deep_merge_settings( $defaults, $stored ) {
	foreach ( $stored as $key => $value ) {
		if ( is_array( $value ) && isset( $defaults[ $key ] ) && is_array( $defaults[ $key ] ) && almasland_is_assoc_array( $value ) && almasland_is_assoc_array( $defaults[ $key ] ) ) {
			$defaults[ $key ] = almasland_deep_merge_settings( $defaults[ $key ], $value );
		} else {
			$defaults[ $key ] = $value;
		}
	}

	return $defaults;
}

/**
 * Check if array is associative.
 *
 * @param array<mixed> $array Array.
 * @return bool
 */
function almasland_is_assoc_array( $array ) {
	if ( ! is_array( $array ) || array() === $array ) {
		return false;
	}

	return array_keys( $array ) !== range( 0, count( $array ) - 1 );
}

/**
 * Get a panel group value.
 *
 * @param string $group   Group key.
 * @param string $key     Field key.
 * @param mixed  $default Default.
 * @return mixed
 */
function almasland_get_panel( $group, $key, $default = '' ) {
	$settings = almasland_get_panel_settings();
	if ( ! isset( $settings[ $group ] ) || ! is_array( $settings[ $group ] ) ) {
		return $default;
	}
	return $settings[ $group ][ $key ] ?? $default;
}

/**
 * Save panel settings.
 *
 * @param array<string, mixed> $settings Settings.
 * @return bool
 */
function almasland_save_panel_settings( $settings ) {
	$merged = almasland_deep_merge_settings( almasland_theme_panel_defaults(), $settings );
	return update_option( 'almasland_theme_panel', $merged, false );
}

/**
 * Copy legacy theme_mod values into panel on first run.
 *
 * @param array<string, mixed> $settings Settings.
 * @return array<string, mixed>
 */
function almasland_migrate_theme_mods_to_panel( $settings ) {
	foreach ( almasland_theme_panel_legacy_map() as $legacy_key => $path ) {
		$value = get_theme_mod( 'almasland_' . $legacy_key, null );
		if ( null === $value || '' === $value ) {
			continue;
		}
		list( $group, $key ) = $path;
		if ( ! isset( $settings[ $group ] ) ) {
			$settings[ $group ] = array();
		}
		$settings[ $group ][ $key ] = $value;
	}

	$hero_image = get_theme_mod( 'almasland_hero_image', '' );
	if ( $hero_image ) {
		$attachment_id = attachment_url_to_postid( $hero_image ) ?: 0;
		if ( $attachment_id && empty( $settings['homepage']['hero_image_desktop'] ) ) {
			$settings['homepage']['hero_image_desktop'] = $attachment_id;
		}
	}
	if ( $hero_image && empty( $settings['sliders'] ) ) {
		$settings['sliders'] = array(
			array(
				'image'       => attachment_url_to_postid( $hero_image ) ?: 0,
				'title'       => $settings['homepage']['hero_title'] ?? '',
				'text'        => $settings['homepage']['hero_text'] ?? '',
				'button_text' => $settings['homepage']['hero_button_text'] ?? '',
				'link'        => $settings['homepage']['hero_button_url'] ?? '',
				'enabled'     => true,
			),
		);
	}

	for ( $i = 1; $i <= 3; $i++ ) {
		$promo = get_theme_mod( 'almasland_promo_image_' . $i, '' );
		if ( $promo ) {
			$settings['banners'][] = array(
				'image_desktop' => attachment_url_to_postid( $promo ) ?: 0,
				'image_mobile'  => 0,
				'title'         => '',
				'subtitle'      => '',
				'link'          => '',
				'button_text'   => '',
				'enabled'       => true,
			);
		}
	}

	update_option( 'almasland_theme_panel', $settings, false );
	return $settings;
}

/**
 * Get attachment image URL by ID.
 *
 * @param int    $attachment_id Attachment ID.
 * @param string $size          Image size.
 * @return string
 */
function almasland_get_attachment_url( $attachment_id, $size = 'full' ) {
	$attachment_id = absint( $attachment_id );
	if ( ! $attachment_id ) {
		return '';
	}
	$url = wp_get_attachment_image_url( $attachment_id, $size );
	return $url ? $url : '';
}

/**
 * Sanitize custom CSS before it is printed as inline style.
 *
 * @param string $css Raw CSS.
 * @return string
 */
function almasland_sanitize_custom_css( $css ) {
	$css = wp_strip_all_tags( (string) $css );
	$css = preg_replace( '/(?:expression\s*\(|javascript\s*:|@import\b)/i', '', $css );

	return trim( $css );
}

/**
 * Sanitize panel POST data.
 *
 * @param array<string, mixed> $input Raw input.
 * @return array<string, mixed>
 */
function almasland_sanitize_panel_settings( $input ) {
	if ( ! is_array( $input ) ) {
		return almasland_theme_panel_defaults();
	}

	$defaults = almasland_theme_panel_defaults();
	$clean    = almasland_deep_merge_settings( $defaults, $input );

	// Identity.
	if ( isset( $input['identity'] ) && is_array( $input['identity'] ) ) {
		$id = $input['identity'];
		$clean['identity']['logo_main']       = absint( $id['logo_main'] ?? 0 );
		$clean['identity']['logo_dark']       = absint( $id['logo_dark'] ?? 0 );
		$clean['identity']['logo_mobile']     = absint( $id['logo_mobile'] ?? 0 );
		$clean['identity']['favicon']         = absint( $id['favicon'] ?? 0 );
		$clean['identity']['primary_color']   = sanitize_hex_color( $id['primary_color'] ?? $defaults['identity']['primary_color'] ) ?: $defaults['identity']['primary_color'];
		$clean['identity']['secondary_color'] = sanitize_hex_color( $id['secondary_color'] ?? $defaults['identity']['secondary_color'] ) ?: $defaults['identity']['secondary_color'];
		$clean['identity']['button_color']    = sanitize_hex_color( $id['button_color'] ?? $defaults['identity']['button_color'] ) ?: $defaults['identity']['button_color'];
		$clean['identity']['link_color']      = sanitize_hex_color( $id['link_color'] ?? $defaults['identity']['link_color'] ) ?: $defaults['identity']['link_color'];
		$clean['identity']['custom_css']      = almasland_sanitize_custom_css( $id['custom_css'] ?? '' );
	}

	// Homepage.
	if ( isset( $input['homepage'] ) && is_array( $input['homepage'] ) ) {
		$hp = $input['homepage'];
		$labels = array_keys( almasland_homepage_section_labels() );
		$order  = isset( $hp['section_order'] ) ? array_map( 'sanitize_key', (array) $hp['section_order'] ) : $defaults['homepage']['section_order'];
		$clean['homepage']['section_order'] = array_values( array_intersect( $order, $labels ) );
		foreach ( $labels as $section_key ) {
			if ( ! in_array( $section_key, $clean['homepage']['section_order'], true ) ) {
				$clean['homepage']['section_order'][] = $section_key;
			}
			$clean['homepage']['sections'][ $section_key ] = ! empty( $hp['sections'][ $section_key ] );
		}
		$clean['homepage']['hero_title']         = sanitize_text_field( $hp['hero_title'] ?? '' );
		$clean['homepage']['hero_text']          = wp_kses_post( $hp['hero_text'] ?? '' );
		$clean['homepage']['hero_button_text']   = sanitize_text_field( $hp['hero_button_text'] ?? '' );
		$clean['homepage']['hero_button_url']    = esc_url_raw( $hp['hero_button_url'] ?? '' );
		$clean['homepage']['hero_enabled']       = ! empty( $hp['hero_enabled'] );
		$clean['homepage']['hero_image_desktop'] = absint( $hp['hero_image_desktop'] ?? 0 );
		$clean['homepage']['hero_image_tablet']  = absint( $hp['hero_image_tablet'] ?? 0 );
		$clean['homepage']['hero_image_mobile']  = absint( $hp['hero_image_mobile'] ?? 0 );
		$clean['homepage']['products_title']     = sanitize_text_field( $hp['products_title'] ?? '' );
		$clean['homepage']['blog_title']       = sanitize_text_field( $hp['blog_title'] ?? '' );
	}

	// Shop.
	if ( isset( $input['shop'] ) && is_array( $input['shop'] ) ) {
		$shop = $input['shop'];
		$clean['shop']['featured_product_ids']  = array_map( 'absint', (array) ( $shop['featured_product_ids'] ?? array() ) );
		$clean['shop']['featured_category_ids'] = array_map( 'absint', (array) ( $shop['featured_category_ids'] ?? array() ) );
		$clean['shop']['featured_brands']       = array_map( 'sanitize_text_field', (array) ( $shop['featured_brands'] ?? array() ) );
		$clean['shop']['per_page']              = max( 1, absint( $shop['per_page'] ?? 12 ) );
		$clean['shop']['columns']               = max( 1, min( 6, absint( $shop['columns'] ?? 3 ) ) );
		$clean['shop']['rows']                  = max( 1, min( 10, absint( $shop['rows'] ?? 4 ) ) );
	}

	// Sliders.
	$clean['sliders'] = almasland_sanitize_repeater_sliders( $input['sliders'] ?? array() );

	// Banners.
	$clean['banners'] = almasland_sanitize_repeater_banners( $input['banners'] ?? array() );

	// Blocks.
	$clean['blocks'] = almasland_sanitize_repeater_blocks( $input['blocks'] ?? array() );

	// Notifications.
	if ( isset( $input['notifications'] ) && is_array( $input['notifications'] ) ) {
		$n = $input['notifications'];
		$clean['notifications']['bar_enabled']   = ! empty( $n['bar_enabled'] );
		$clean['notifications']['bar_text']      = sanitize_text_field( $n['bar_text'] ?? '' );
		$clean['notifications']['bar_color']     = sanitize_hex_color( $n['bar_color'] ?? '#2457d6' ) ?: '#2457d6';
		$clean['notifications']['bar_link']      = esc_url_raw( $n['bar_link'] ?? '' );
		$clean['notifications']['bar_start']     = sanitize_text_field( $n['bar_start'] ?? '' );
		$clean['notifications']['bar_end']       = sanitize_text_field( $n['bar_end'] ?? '' );
		$clean['notifications']['popup_enabled'] = ! empty( $n['popup_enabled'] );
		$clean['notifications']['popup_title']   = sanitize_text_field( $n['popup_title'] ?? '' );
		$clean['notifications']['popup_text']    = wp_kses_post( $n['popup_text'] ?? '' );
		$clean['notifications']['popup_image']   = absint( $n['popup_image'] ?? 0 );
		$clean['notifications']['popup_button']  = sanitize_text_field( $n['popup_button'] ?? '' );
		$clean['notifications']['popup_link']    = esc_url_raw( $n['popup_link'] ?? '' );
		$clean['notifications']['popup_delay']   = max( 0, absint( $n['popup_delay'] ?? 3 ) );
		$clean['notifications']['popup_once']    = ! empty( $n['popup_once'] );
	}

	// Footer.
	if ( isset( $input['footer'] ) && is_array( $input['footer'] ) ) {
		$f = $input['footer'];
		$clean['footer']['about']         = wp_kses_post( $f['about'] ?? '' );
		$clean['footer']['copyright']     = sanitize_text_field( $f['copyright'] ?? '' );
		$clean['footer']['trust_badge_1'] = absint( $f['trust_badge_1'] ?? 0 );
		$clean['footer']['trust_badge_2'] = absint( $f['trust_badge_2'] ?? 0 );
		$clean['footer']['samandehi']     = absint( $f['samandehi'] ?? 0 );
		$clean['footer']['phone']         = sanitize_text_field( $f['phone'] ?? '' );
		$clean['footer']['email']         = sanitize_email( $f['email'] ?? '' );
		$clean['footer']['address']       = sanitize_text_field( $f['address'] ?? '' );
		$clean['footer']['links']         = almasland_sanitize_footer_links( $f['links'] ?? array() );
	}

	// Social.
	if ( isset( $input['social'] ) && is_array( $input['social'] ) ) {
		foreach ( array_keys( $defaults['social'] ) as $network ) {
			$clean['social'][ $network ] = esc_url_raw( $input['social'][ $network ] ?? '' );
		}
	}

	// Header.
	if ( isset( $input['header'] ) && is_array( $input['header'] ) ) {
		$clean['header']['show_topbar'] = ! empty( $input['header']['show_topbar'] );
		$clean['header']['topbar_text'] = sanitize_text_field( $input['header']['topbar_text'] ?? '' );
	}

	return $clean;
}

/**
 * Sanitize slider repeater.
 *
 * @param mixed $items Items.
 * @return array<int, array<string, mixed>>
 */
function almasland_sanitize_repeater_sliders( $items ) {
	$clean = array();
	if ( ! is_array( $items ) ) {
		return $clean;
	}
	foreach ( $items as $item ) {
		if ( ! is_array( $item ) ) {
			continue;
		}
		$clean[] = array(
			'image'       => absint( $item['image'] ?? 0 ),
			'title'       => sanitize_text_field( $item['title'] ?? '' ),
			'text'        => wp_kses_post( $item['text'] ?? '' ),
			'button_text' => sanitize_text_field( $item['button_text'] ?? '' ),
			'link'        => esc_url_raw( $item['link'] ?? '' ),
			'enabled'     => ! empty( $item['enabled'] ),
		);
	}
	return $clean;
}

/**
 * Sanitize banner repeater.
 *
 * @param mixed $items Items.
 * @return array<int, array<string, mixed>>
 */
function almasland_sanitize_repeater_banners( $items ) {
	$clean = array();
	if ( ! is_array( $items ) ) {
		return $clean;
	}
	foreach ( $items as $item ) {
		if ( ! is_array( $item ) ) {
			continue;
		}
		$clean[] = array(
			'image_desktop' => absint( $item['image_desktop'] ?? 0 ),
			'image_mobile'  => absint( $item['image_mobile'] ?? 0 ),
			'title'         => sanitize_text_field( $item['title'] ?? '' ),
			'subtitle'      => sanitize_text_field( $item['subtitle'] ?? '' ),
			'link'          => esc_url_raw( $item['link'] ?? '' ),
			'button_text'   => sanitize_text_field( $item['button_text'] ?? '' ),
			'enabled'       => ! empty( $item['enabled'] ),
		);
	}
	return $clean;
}

/**
 * Sanitize block repeater.
 *
 * @param mixed $items Items.
 * @return array<int, array<string, mixed>>
 */
function almasland_sanitize_repeater_blocks( $items ) {
	$clean = array();
	if ( ! is_array( $items ) ) {
		return $clean;
	}
	foreach ( $items as $item ) {
		if ( ! is_array( $item ) ) {
			continue;
		}
		$clean[] = array(
			'title'       => sanitize_text_field( $item['title'] ?? '' ),
			'description' => wp_kses_post( $item['description'] ?? '' ),
			'image'       => absint( $item['image'] ?? 0 ),
			'button_text' => sanitize_text_field( $item['button_text'] ?? '' ),
			'link'        => esc_url_raw( $item['link'] ?? '' ),
			'bg_color'    => sanitize_hex_color( $item['bg_color'] ?? '#f7f9fc' ) ?: '#f7f9fc',
			'order'       => absint( $item['order'] ?? 0 ),
			'enabled'     => ! empty( $item['enabled'] ),
		);
	}
	return $clean;
}

/**
 * Sanitize footer links repeater.
 *
 * @param mixed $items Items.
 * @return array<int, array<string, string>>
 */
function almasland_sanitize_footer_links( $items ) {
	$clean = array();
	if ( ! is_array( $items ) ) {
		return $clean;
	}
	foreach ( $items as $item ) {
		if ( ! is_array( $item ) ) {
			continue;
		}
		$title = sanitize_text_field( $item['title'] ?? '' );
		$url   = esc_url_raw( $item['url'] ?? '' );
		if ( ! $title || ! $url ) {
			continue;
		}
		$clean[] = array(
			'title' => $title,
			'url'   => $url,
			'order' => absint( $item['order'] ?? 0 ),
		);
	}
	return $clean;
}
