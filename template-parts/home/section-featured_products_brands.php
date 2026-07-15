<?php
/**
 * Featured products and vertical selected categories.
 *
 * @package AlmasLand
 */

if ( ! class_exists( 'WooCommerce' ) ) {
	return;
}

$shop     = almasland_get_panel_settings()['shop'];
$ids      = array_filter( array_map( 'absint', (array) ( $shop['featured_product_ids'] ?? array() ) ) );
$cat_ids  = array_filter( array_map( 'absint', (array) ( $shop['featured_category_ids'] ?? array() ) ) );
$shortcode = '';

if ( $ids ) {
	$shortcode = sprintf(
		'[products ids="%s" columns="4" class="product-grid--home"]',
		esc_attr( implode( ',', $ids ) )
	);
} else {
	$shortcode = '[products limit="8" columns="4" orderby="date" order="DESC" class="product-grid--home"]';
}
?>
<section class="container home-featured-layout" aria-labelledby="home-featured-products">
	<div class="home-featured-layout__main product-section">
		<div class="section-heading">
			<h2 id="home-featured-products"><?php esc_html_e( 'محصولات ویژه', 'almas-land' ); ?></h2>
			<a href="<?php echo esc_url( wc_get_page_permalink( 'shop' ) ); ?>"><?php esc_html_e( 'مشاهده همه', 'almas-land' ); ?></a>
		</div>
		<?php echo do_shortcode( $shortcode ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
	</div>
	<aside class="home-featured-layout__side" aria-label="<?php esc_attr_e( 'دسته‌های منتخب', 'almas-land' ); ?>">
		<?php
		if ( $cat_ids && taxonomy_exists( 'product_cat' ) ) :
			foreach ( $cat_ids as $cat_id ) :
				$term = get_term( $cat_id, 'product_cat' );
				if ( ! $term || is_wp_error( $term ) ) {
					continue;
				}
				$thumb_id = (int) get_term_meta( $term->term_id, 'thumbnail_id', true );
				$image    = $thumb_id ? wp_get_attachment_image_url( $thumb_id, 'medium' ) : ALMASLAND_URI . '/assets/images/promo.svg';
				?>
				<a class="home-featured-side-banner" href="<?php echo esc_url( get_term_link( $term ) ); ?>">
					<img src="<?php echo esc_url( $image ); ?>" alt="<?php echo esc_attr( $term->name ); ?>" width="320" height="220">
					<span><?php echo esc_html( $term->name ); ?></span>
				</a>
				<?php
			endforeach;
		endif;
		?>
	</aside>
</section>
