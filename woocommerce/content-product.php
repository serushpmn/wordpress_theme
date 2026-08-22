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

$product_name = function_exists( 'almasland_get_product_card_title' ) ? almasland_get_product_card_title( $product ) : $product->get_name();
$stock_class  = function_exists( 'almasland_stock_class' ) ? almasland_stock_class( $product ) : '';
$summary      = function_exists( 'almasland_get_product_card_summary' ) ? almasland_get_product_card_summary( $product ) : '';
$grade        = function_exists( 'almasland_get_product_card_grade' ) ? almasland_get_product_card_grade( $product ) : null;
$rating       = (float) $product->get_average_rating();
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
	<?php
	if ( function_exists( 'almasland_render_product_card_overlay_link' ) ) {
		almasland_render_product_card_overlay_link( $product, $product_name );
	}
	?>
	<div class="product-card__media">
		<?php
		if ( function_exists( 'almasland_render_product_card_media' ) ) {
			almasland_render_product_card_media( $product );
		} else {
			echo wp_kses_post( $product->get_image( 'almasland-card' ) );
		}

		if ( function_exists( 'almasland_render_product_used_badge' ) ) {
			almasland_render_product_used_badge( $product );
		}
		?>
	</div>
	<div class="product-card__body">
		<div class="product-card__info">
			<h3 class="product-card__title"><?php echo esc_html( $product_name ); ?></h3>

			<div class="product-card__meta">
				<?php
				if ( function_exists( 'almasland_render_product_card_tags' ) ) {
					almasland_render_product_card_tags( $product, $grade, $grade_style );
				}
				?>
				<?php if ( $rating > 0 ) : ?>
					<span class="product-card__rating" aria-label="<?php echo esc_attr( sprintf( __( 'امتیاز %s از ۵', 'almas-land' ), almasland_persian_digits( number_format_i18n( $rating, 1 ) ) ) ); ?>">
						<svg viewBox="0 0 24 24" aria-hidden="true"><path d="m12 3.6 2.7 5.5 6 .9-4.4 4.2 1 6-5.3-2.8-5.3 2.8 1-6L3.3 10l6-.9L12 3.6Z" fill="currentColor"/></svg>
						<?php echo esc_html( almasland_persian_digits( number_format_i18n( $rating, 1 ) ) ); ?>
					</span>
				<?php endif; ?>
			</div>

			<?php if ( $summary ) : ?>
				<p class="product-card__specs"><?php echo esc_html( $summary ); ?></p>
			<?php endif; ?>

			<?php if ( ! $product->is_in_stock() ) : ?>
				<span class="product-card__stock stock <?php echo esc_attr( $stock_class ); ?>"><?php esc_html_e( 'ناموجود', 'almas-land' ); ?></span>
			<?php endif; ?>
		</div>

		<?php
		if ( function_exists( 'almasland_render_product_card_pricing' ) ) {
			almasland_render_product_card_pricing( $product );
		}
		?>
	</div>
</li>
