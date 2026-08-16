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
				$discount_percent = almasland_get_discount_percent( $product );
				$summary          = almasland_get_product_card_summary( $product );
				$is_used          = almasland_is_used_product( $product );
				$grade            = $is_used ? almasland_get_product_grade_badge( $product ) : null;
				$regular_price    = (float) $product->get_regular_price();
				$sale_price       = (float) $product->get_price();
				$stock_label      = almasland_get_product_card_stock_label( $product );
				$stock_class      = almasland_stock_class( $product );
				$cta_label        = almasland_get_product_card_cta_label( $product );
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

					<?php if ( $discount_percent > 0 ) : ?>
						<span class="front-page-offer-card__discount">
							<?php
							echo esc_html(
								sprintf(
									/* translators: %s: discount percent */
									__( '%s%% تخفیف', 'almas-land' ),
									almasland_persian_digits( $discount_percent )
								)
							);
							?>
						</span>
					<?php endif; ?>

					<a class="front-page-offer-card__media" href="<?php echo esc_url( $product_link ); ?>">
						<?php echo wp_kses_post( $product->get_image( 'almasland-card', array( 'loading' => 'lazy', 'decoding' => 'async' ) ) ); ?>
					</a>

					<div class="front-page-offer-card__body">
						<a class="front-page-offer-card__title" href="<?php echo esc_url( $product_link ); ?>">
							<?php echo esc_html( almasland_get_product_card_title( $product ) ); ?>
						</a>

						<?php if ( $grade ) : ?>
							<span class="front-page-offer-card__grade front-page-offer-card__grade--<?php echo esc_attr( $grade['tone'] ); ?>"<?php echo $grade_style; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
								<?php echo esc_html( $grade['text'] ); ?>
							</span>
						<?php endif; ?>

						<?php if ( $summary ) : ?>
							<p class="front-page-offer-card__specs"><?php echo esc_html( $summary ); ?></p>
						<?php endif; ?>

						<span class="front-page-offer-card__stock stock <?php echo esc_attr( $stock_class ); ?>"><?php echo esc_html( $stock_label ); ?></span>

						<?php if ( almasland_should_show_product_price( $product ) ) : ?>
							<div class="front-page-offer-card__prices">
								<?php if ( $sale_price > 0 ) : ?>
									<span class="front-page-offer-card__price"><?php echo wp_kses_post( almasland_format_card_price_html( $sale_price ) ); ?></span>
								<?php endif; ?>

								<?php if ( $regular_price > 0 && $regular_price > $sale_price ) : ?>
									<span class="front-page-offer-card__price-regular">
										<del><?php echo esc_html( almasland_format_plain_price( $regular_price ) ); ?></del>
									</span>
								<?php endif; ?>
							</div>
						<?php endif; ?>

						<a class="front-page-offer-card__cart front-page-offer-card__cart--link<?php echo $product->is_in_stock() ? '' : ' front-page-offer-card__cart--view'; ?>" href="<?php echo esc_url( $product_link ); ?>">
							<?php echo esc_html( $cta_label ); ?>
						</a>
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
