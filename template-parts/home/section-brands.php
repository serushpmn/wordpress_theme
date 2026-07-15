<?php
/**
 * Homepage brands section.
 *
 * @package AlmasLand
 */

$brands = array_filter( array_map( 'trim', (array) almasland_get_panel( 'shop', 'featured_brands', array() ) ) );

if ( empty( $brands ) ) {
	return;
}
?>
<section class="container home-brands" aria-labelledby="home-brands">
	<div class="section-heading">
		<h2 id="home-brands"><?php esc_html_e( 'برندهای ویژه', 'almas-land' ); ?></h2>
	</div>
	<div class="home-brands__grid">
		<?php foreach ( $brands as $brand ) : ?>
			<?php
			$shop_url = class_exists( 'WooCommerce' ) ? wc_get_page_permalink( 'shop' ) : home_url( '/' );
			$link     = add_query_arg( 's', $brand, $shop_url );
			?>
			<a class="home-brands__item" href="<?php echo esc_url( $link ); ?>"><?php echo esc_html( $brand ); ?></a>
		<?php endforeach; ?>
	</div>
</section>
