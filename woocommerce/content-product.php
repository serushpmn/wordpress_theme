<?php
/**
 * Product loop card.
 *
 * @package AlmasLand
 */

defined( 'ABSPATH' ) || exit;

global $product;

if ( empty( $product ) || ! $product->is_visible() ) {
	return;
}

$card_classes = 'product-card';
if ( function_exists( 'wc_get_loop_prop' ) && wc_get_loop_prop( 'almasland_swiper' ) ) {
	$card_classes .= ' swiper-slide';
}

$product_link = $product->get_permalink();
$product_name = $product->get_name();
$stock_label  = __( 'موجود', 'almas-land' );
$price_html   = preg_replace( '/<span class="screen-reader-text">.*?<\/span>/u', '', $product->get_price_html() );

if ( ! $product->is_in_stock() ) {
	$stock_label = __( 'ناموجود', 'almas-land' );
} elseif ( $product->is_on_backorder() ) {
	$stock_label = __( 'قابل پیش‌سفارش', 'almas-land' );
}
?>
<li <?php wc_product_class( $card_classes, $product ); ?>>
	<button class="icon-button" type="button" aria-label="<?php esc_attr_e( 'افزودن به علاقه‌مندی‌ها', 'almas-land' ); ?>">
		<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 20.4 10.8 19C6.4 15.1 3.5 12.5 3.5 9.2A4.4 4.4 0 0 1 8 4.8c1.5 0 2.9.7 4 1.8a5.4 5.4 0 0 1 4-1.8 4.4 4.4 0 0 1 4.5 4.4c0 3.3-2.9 5.9-7.3 9.8L12 20.4Z"/></svg>
	</button>
	<a class="product-card__media" href="<?php echo esc_url( $product_link ); ?>">
		<?php echo wp_kses_post( $product->get_image( 'almasland-card' ) ); ?>
	</a>
	<?php if ( $product->is_on_sale() ) : ?>
		<span class="product-card__badge"><?php esc_html_e( 'تخفیف ویژه', 'almas-land' ); ?></span>
	<?php endif; ?>
	<div class="product-card__body">
		<a class="product-card__title" href="<?php echo esc_url( $product_link ); ?>"><?php echo esc_html( $product_name ); ?></a>
		<?php if ( wc_review_ratings_enabled() ) : ?>
			<div class="product-card__rating"><?php echo wp_kses_post( wc_get_rating_html( $product->get_average_rating() ) ); ?></div>
		<?php endif; ?>
		<span class="stock <?php echo esc_attr( almasland_stock_class( $product ) ); ?>"><?php echo esc_html( $stock_label ); ?></span>
		<div class="price"><?php echo wp_kses_post( almasland_persian_price( $price_html ) ); ?></div>
		<?php
		echo wp_kses_post( almasland_loop_add_to_cart_link( '', $product, array() ) );
		?>
	</div>
</li>
