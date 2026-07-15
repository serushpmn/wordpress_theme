<?php
/**
 * Budget products (lowest price) category section.
 *
 * @package AlmasLand
 */

// Try to find a specific "budget" category; otherwise just show cheapest products site-wide.
$term = almasland_get_product_category_by_candidates( array( 'economic', 'budget', 'products-economic', 'اقتصادی', 'محصولات اقتصادی' ) );

if ( $term instanceof WP_Term ) {
	almasland_render_home_category_products_section(
		__( 'محصولات اقتصادی', 'almas-land' ),
		array( $term->slug ),
		array(
			'orderby' => 'price',
			'order'   => 'ASC',
		)
	);
	return;
}

if ( ! class_exists( 'WooCommerce' ) ) {
	return;
}
?>
<section class="container product-section home-category-products" aria-labelledby="home-budget-products">
	<div class="section-heading">
		<h2 id="home-budget-products"><?php esc_html_e( 'محصولات اقتصادی', 'almas-land' ); ?></h2>
		<a href="<?php echo esc_url( wc_get_page_permalink( 'shop' ) ); ?>"><?php esc_html_e( 'مشاهده همه', 'almas-land' ); ?></a>
	</div>
	<?php echo do_shortcode( '[products limit="4" columns="4" orderby="price" order="ASC" class="product-grid--home"]' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
</section>

