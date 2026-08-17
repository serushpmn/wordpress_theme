<?php
/**
 * Front page special offers slider.
 *
 * @package AlmasLand
 */

if ( ! class_exists( 'WooCommerce' ) ) {
	return;
}

$products = function_exists( 'almasland_get_home_special_offers_products' ) ? almasland_get_home_special_offers_products( 12 ) : array();

if ( empty( $products ) ) {
	return;
}

$view_all_url = add_query_arg( 'on_sale', '1', wc_get_page_permalink( 'shop' ) );
?>
<section class="front-page-offers" aria-labelledby="front-page-offers-title">
	<div class="front-page-offers__header">
		<div class="front-page-offers__title-wrap">
			<h2 class="front-page-offers__title" id="front-page-offers-title">
				<?php esc_html_e( 'پیشنهادهای ویژه', 'almas-land' ); ?>
			</h2>
			<span class="front-page-offers__badge"><?php esc_html_e( 'ویژه', 'almas-land' ); ?></span>
		</div>

		<a class="front-page-offers__all" href="<?php echo esc_url( $view_all_url ); ?>">
			<?php esc_html_e( 'مشاهده همه', 'almas-land' ); ?>
			<svg viewBox="0 0 24 24" aria-hidden="true">
				<path d="M15 18l-6-6 6-6" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
			</svg>
		</a>
	</div>

	<div class="front-page-offers__slider swiper" dir="rtl">
		<div class="swiper-wrapper">
			<?php foreach ( $products as $product ) : ?>
				<?php
				$product_link     = $product->get_permalink();
				$summary          = almasland_get_product_card_summary( $product );
				$is_used          = almasland_is_used_product( $product );
				$grade            = almasland_get_product_card_grade( $product );
				$stock_class      = almasland_stock_class( $product );
				$grade_style      = '';

				if ( $grade && ! empty( $grade['bg'] ) ) {
					$grade_style = sprintf(
						' style="background-color:%1$s;color:%2$s;"',
						esc_attr( $grade['bg'] ),
						esc_attr( $grade['color'] ?? '#ffffff' )
					);
				}
				?>
				<article class="front-page-offer-card swiper-slide<?php echo $is_used ? ' front-page-offer-card--used' : ''; ?>">
					<button class="front-page-offer-card__wishlist" type="button" aria-label="<?php esc_attr_e( 'افزودن به علاقه‌مندی‌ها', 'almas-land' ); ?>">
						<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 20.4 10.8 19C6.4 15.1 3.5 12.5 3.5 9.2A4.4 4.4 0 0 1 8 4.8c1.5 0 2.9.7 4 1.8a5.4 5.4 0 0 1 4-1.8 4.4 4.4 0 0 1 4.5 4.4c0 3.3-2.9 5.9-7.3 9.8L12 20.4Z" fill="none" stroke="currentColor" stroke-width="1.8"/></svg>
					</button>

					<a class="front-page-offer-card__media" href="<?php echo esc_url( $product_link ); ?>">
						<?php echo wp_kses_post( $product->get_image( 'almasland-card', array( 'loading' => 'lazy', 'decoding' => 'async' ) ) ); ?>
					</a>

					<div class="front-page-offer-card__body">
						<a class="front-page-offer-card__title" href="<?php echo esc_url( $product_link ); ?>">
							<?php echo esc_html( almasland_get_product_card_title( $product ) ); ?>
						</a>

						<?php almasland_render_product_card_tags( $product, $grade, $grade_style ); ?>

						<?php if ( $summary ) : ?>
							<p class="front-page-offer-card__specs"><?php echo esc_html( $summary ); ?></p>
						<?php endif; ?>

						<?php if ( ! $product->is_in_stock() ) : ?>
							<span class="front-page-offer-card__stock stock <?php echo esc_attr( $stock_class ); ?>"><?php esc_html_e( 'ناموجود', 'almas-land' ); ?></span>
						<?php endif; ?>

						<?php almasland_render_product_card_pricing( $product ); ?>
					</div>
				</article>
			<?php endforeach; ?>
		</div>

		<button type="button" class="front-page-offers__nav front-page-offers__nav--prev" aria-label="<?php esc_attr_e( 'محصول قبلی', 'almas-land' ); ?>">
			<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M9 18l6-6-6-6" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
		</button>
		<button type="button" class="front-page-offers__nav front-page-offers__nav--next" aria-label="<?php esc_attr_e( 'محصول بعدی', 'almas-land' ); ?>">
			<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M15 18l-6-6 6-6" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
		</button>

		<div class="front-page-offers__pagination swiper-pagination" aria-hidden="true"></div>
	</div>
</section>
