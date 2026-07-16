<?php
/**
 * Primary navigation helpers.
 *
 * @package AlmasLand
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Fallback markup when no primary menu is assigned.
 */
function almasland_primary_menu_fallback() {
	$shop_url = class_exists( 'WooCommerce' ) ? wc_get_page_permalink( 'shop' ) : home_url( '/' );
	$items    = array(
		array( home_url( '/' ), __( 'صفحه اصلی', 'almas-land' ) ),
		array( $shop_url, __( 'محصولات', 'almas-land' ) ),
		array( add_query_arg( 'on_sale', '1', $shop_url ), __( 'پیشنهاد ویژه', 'almas-land' ) ),
		array( home_url( '/brands/' ), __( 'برندها', 'almas-land' ) ),
		array( get_post_type_archive_link( 'post' ) ?: home_url( '/blog/' ), __( 'مقالات', 'almas-land' ) ),
		array( home_url( '/about/' ), __( 'درباره ما', 'almas-land' ) ),
		array( almasland_get_contact_url(), __( 'تماس با ما', 'almas-land' ) ),
	);

	echo '<ul class="main-nav__inner">';
	foreach ( $items as $item ) {
		printf(
			'<li class="menu-item nav-item"><a class="nav-link" href="%1$s">%2$s</a></li>',
			esc_url( $item[0] ),
			esc_html( $item[1] )
		);
	}
	echo '</ul>';
}

/**
 * Add theme classes to WordPress menu items.
 *
 * @param string[] $classes CSS classes.
 * @param WP_Post  $item    Menu item.
 * @param stdClass $args    Menu args.
 * @param int      $depth   Depth.
 * @return string[]
 */
function almasland_nav_menu_css_class( $classes, $item, $args, $depth ) {
	if ( isset( $args->theme_location ) && 'primary' === $args->theme_location && 0 === $depth ) {
		$classes[] = 'nav-item';
		if ( in_array( 'menu-item-has-children', $classes, true ) ) {
			$classes[] = 'nav-item--mega';
		}
	}

	return $classes;
}
add_filter( 'nav_menu_css_class', 'almasland_nav_menu_css_class', 10, 4 );

/**
 * Add theme classes to menu links.
 *
 * @param string[] $atts  Link attributes.
 * @param WP_Post  $item  Menu item.
 * @param stdClass $args  Menu args.
 * @param int      $depth Depth.
 * @return string[]
 */
function almasland_nav_menu_link_attributes( $atts, $item, $args, $depth ) {
	if ( isset( $args->theme_location ) && 'primary' === $args->theme_location && 0 === $depth ) {
		$classes   = isset( $atts['class'] ) ? explode( ' ', $atts['class'] ) : array();
		$classes[] = 'nav-link';
		$atts['class'] = implode( ' ', array_filter( $classes ) );
	}

	return $atts;
}
add_filter( 'nav_menu_link_attributes', 'almasland_nav_menu_link_attributes', 10, 4 );

/**
 * Mark the current menu item for styling.
 *
 * @param string[] $classes CSS classes.
 * @param WP_Post  $item    Menu item.
 * @return string[]
 */
function almasland_nav_menu_current_class( $classes, $item ) {
	if ( in_array( 'current-menu-item', $classes, true ) || in_array( 'current-menu-ancestor', $classes, true ) ) {
		$classes[] = 'is-current';
	}

	return $classes;
}
add_filter( 'nav_menu_css_class', 'almasland_nav_menu_current_class', 20, 2 );
