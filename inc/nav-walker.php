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
	echo '<ul class="container main-nav__inner">';
	echo '<li class="menu-item"><a href="' . esc_url( home_url( '/' ) ) . '">' . esc_html__( 'خانه', 'almas-land' ) . '</a></li>';

	if ( class_exists( 'WooCommerce' ) ) {
		echo '<li class="menu-item"><a href="' . esc_url( wc_get_page_permalink( 'shop' ) ) . '">' . esc_html__( 'فروشگاه', 'almas-land' ) . '</a></li>';
	}

	echo '<li class="menu-item"><a href="' . esc_url( almasland_get_contact_url() ) . '">' . esc_html__( 'تماس با ما', 'almas-land' ) . '</a></li>';
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
