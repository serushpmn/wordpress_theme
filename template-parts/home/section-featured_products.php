<?php
/**
 * Homepage featured products section.
 *
 * @package AlmasLand
 */

if ( ! class_exists( 'WooCommerce' ) ) {
	return;
}

$shop     = almasland_get_panel_settings()['shop'];
$columns  = max( 1, min( 6, (int) ( $shop['columns'] ?? 4 ) ) );
$rows     = max( 1, (int) ( $shop['rows'] ?? 1 ) );
$limit    = $columns * $rows;
$ids      = array_filter( array_map( 'absint', (array) ( $shop['featured_product_ids'] ?? array() ) ) );
$shortcode = '';

if ( $ids ) {
	$shortcode = sprintf(
		'[products ids="%s" columns="%d" class="product-grid--home"]',
		esc_attr( implode( ',', $ids ) ),
		$columns
	);
} else {
	$shortcode = sprintf(
		'[products limit="%d" columns="%d" orderby="date" order="DESC" class="product-grid--home"]',
		$limit,
		$columns
	);
}
?>
<section class="container product-section" aria-labelledby="home-products">
	<div class="section-heading">
		<h2 id="home-products"><?php echo esc_html( almasland_get_option( 'home_products_title', 'محصولات جدید' ) ); ?></h2>
		<a href="<?php echo esc_url( wc_get_page_permalink( 'shop' ) ); ?>"><?php esc_html_e( 'مشاهده همه', 'almas-land' ); ?></a>
	</div>
	<?php echo do_shortcode( $shortcode ); ?>
</section>
