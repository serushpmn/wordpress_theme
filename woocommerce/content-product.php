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

$is_used      = function_exists( 'almasland_is_used_product' ) && almasland_is_used_product( $product );
$card_classes = 'product-card product-card--storefront';
if ( $is_used ) {
	$card_classes .= ' product-card--used';
}
if ( function_exists( 'wc_get_loop_prop' ) && wc_get_loop_prop( 'almasland_swiper' ) ) {
	$card_classes .= ' swiper-slide';
}

$product_link = $product->get_permalink();
$product_name = function_exists( 'almasland_get_product_card_title' ) ? almasland_get_product_card_title( $product ) : $product->get_name();
$stock_label  = function_exists( 'almasland_get_product_card_stock_label' ) ? almasland_get_product_card_stock_label( $product ) : __( 'موجود', 'almas-land' );
$stock_class  = function_exists( 'almasland_stock_class' ) ? almasland_stock_class( $product ) : '';
$summary      = function_exists( 'almasland_get_product_card_summary' ) ? almasland_get_product_card_summary( $product ) : '';
$grade        = $is_used && function_exists( 'almasland_get_product_grade_badge' ) ? almasland_get_product_grade_badge( $product ) : null;
$cta_label    = function_exists( 'almasland_get_product_card_cta_label' ) ? almasland_get_product_card_cta_label( $product ) : __( 'مشاهده و خرید', 'almas-land' );
$sale_price   = (float) $product->get_price();
$regular      = (float) $product->get_regular_price();
$grade_style  = '';

if ( $grade && ! empty( $grade['bg'] ) ) {
	$grade_style = sprintf(
		' style="background-color:%1$s;color:%2$s;"',
		esc_attr( $grade['bg'] ),
		esc_attr( $grade['color'] ?? '#ffffff' )
	);
}
?>
<li <?php wc_product_class( $card_classes, $product ); ?>>
	<button class="icon-button" type="button" aria-label="<?php esc_attr_e( 'افزودن به علاقه‌مندی‌ها', 'almas-land' ); ?>">
		<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 20.4 10.8 19C6.4 15.1 3.5 12.5 3.5 9.2A4.4 4.4 0 0 1 8 4.8c1.5 0 2.9.7 4 1.8a5.4 5.4 0 0 1 4-1.8 4.4 4.4 0 0 1 4.5 4.4c0 3.3-2.9 5.9-7.3 9.8L12 20.4Z"/></svg>
	</button>
	<a class="product-card__media" href="<?php echo esc_url( $product_link ); ?>">
		<?php echo wp_kses_post( $product->get_image( 'almasland-card' ) ); ?>
	</a>
	<div class="product-card__body">
		<a class="product-card__title" href="<?php echo esc_url( $product_link ); ?>"><?php echo esc_html( $product_name ); ?></a>

		<?php if ( $grade ) : ?>
			<span class="product-card__grade product-card__grade--<?php echo esc_attr( $grade['tone'] ); ?>"<?php echo $grade_style; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
				<?php echo esc_html( $grade['text'] ); ?>
			</span>
		<?php endif; ?>

		<?php if ( $summary ) : ?>
			<p class="product-card__specs"><?php echo esc_html( $summary ); ?></p>
		<?php endif; ?>

		<span class="product-card__stock stock <?php echo esc_attr( $stock_class ); ?>"><?php echo esc_html( $stock_label ); ?></span>

		<?php if ( almasland_should_show_product_price( $product ) ) : ?>
			<div class="product-card__prices">
				<?php if ( $sale_price > 0 ) : ?>
					<span class="product-card__price"><?php echo wp_kses_post( almasland_format_card_price_html( $sale_price ) ); ?></span>
				<?php endif; ?>
				<?php if ( $regular > 0 && $regular > $sale_price ) : ?>
					<span class="product-card__price-regular"><del><?php echo esc_html( almasland_format_plain_price( $regular ) ); ?></del></span>
				<?php endif; ?>
			</div>
		<?php endif; ?>

		<a class="product-card__cta<?php echo $product->is_in_stock() ? '' : ' product-card__cta--view'; ?>" href="<?php echo esc_url( $product_link ); ?>">
			<?php echo esc_html( $cta_label ); ?>
		</a>
	</div>
</li>
