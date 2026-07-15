<?php
/**
 * Product archive pagination.
 *
 * @package AlmasLand
 * @version 9.3.0
 */

defined( 'ABSPATH' ) || exit;

$total   = isset( $total ) ? $total : wc_get_loop_prop( 'total_pages' );
$current = isset( $current ) ? $current : wc_get_loop_prop( 'current_page' );
$base    = isset( $base ) ? $base : esc_url_raw( str_replace( 999999999, '%#%', remove_query_arg( 'add-to-cart', get_pagenum_link( 999999999, false ) ) ) );
$format  = isset( $format ) ? $format : '';

if ( (int) $total <= 1 ) {
	return;
}

$pagination_args = apply_filters(
	'woocommerce_pagination_args',
	array(
		'base'       => $base,
		'format'     => $format,
		'add_args'   => almasland_get_shop_pagination_add_args(),
		'current'    => max( 1, (int) $current ),
		'total'      => max( 1, (int) $total ),
		'aria_label' => esc_html__( 'صفحه‌بندی محصولات', 'almas-land' ),
	)
);

$shop_args = almasland_get_shop_pagination_add_args();
if ( $shop_args ) {
	$existing_add_args         = is_array( $pagination_args['add_args'] ?? null ) ? $pagination_args['add_args'] : array();
	$pagination_args['add_args'] = array_merge( $shop_args, $existing_add_args );
}

almasland_pagination( $pagination_args );
