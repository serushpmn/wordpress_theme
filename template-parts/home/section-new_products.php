<?php
/**
 * New products section.
 *
 * @package AlmasLand
 */

if ( ! class_exists( 'WooCommerce' ) ) {
	return;
}
?>
<section class="container product-section" aria-labelledby="home-new-products">
	<div class="section-heading">
		<h2 id="home-new-products"><?php esc_html_e( 'محصولات جدید', 'almas-land' ); ?></h2>
		<a href="<?php echo esc_url( wc_get_page_permalink( 'shop' ) ); ?>"><?php esc_html_e( 'مشاهده همه', 'almas-land' ); ?></a>
	</div>
	<?php echo do_shortcode( '[products limit="8" columns="4" orderby="date" order="DESC" class="product-grid--home"]' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
</section>
