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
				$product_id       = $product->get_id();
				$product_link     = $product->get_permalink();
				$discount_percent = almasland_get_discount_percent( $product );
				$summary          = almasland_get_product_card_summary( $product );
				$grade            = almasland_get_product_grade_badge( $product );
				$regular_price    = (float) $product->get_regular_price();
				$sale_price       = (float) $product->get_price();
				$can_ajax_cart    = $product->is_purchasable() && $product->is_in_stock() && $product->is_type( 'simple' );
				$grade_style      = '';

				if ( $grade && ! empty( $grade['bg'] ) ) {
					$grade_style = sprintf(
						' style="background-color:%1$s;color:%2$s;"',
						esc_attr( $grade['bg'] ),
						esc_attr( $grade['color'] ?? '#ffffff' )
					);
				}
				?>
				<article class="front-page-offer-card swiper-slide">
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
							<?php echo esc_html( $product->get_name() ); ?>
						</a>

						<?php if ( $summary ) : ?>
							<p class="front-page-offer-card__specs"><?php echo esc_html( $summary ); ?></p>
						<?php endif; ?>

						<?php if ( $grade ) : ?>
							<span class="front-page-offer-card__grade front-page-offer-card__grade--<?php echo esc_attr( $grade['tone'] ); ?>"<?php echo $grade_style; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
								<?php echo esc_html( $grade['text'] ); ?>
							</span>
						<?php endif; ?>

						<div class="front-page-offer-card__prices">
							<?php if ( $sale_price > 0 ) : ?>
								<span class="front-page-offer-card__price"><?php echo esc_html( almasland_format_plain_price( $sale_price ) ); ?></span>
							<?php endif; ?>

							<?php if ( $regular_price > 0 && $regular_price > $sale_price ) : ?>
								<span class="front-page-offer-card__price-regular">
									<del><?php echo esc_html( almasland_format_plain_price( $regular_price ) ); ?></del>
								</span>
							<?php endif; ?>
						</div>

						<?php if ( $can_ajax_cart ) : ?>
							<button
								type="button"
								class="front-page-offer-card__cart"
								data-offer-add-to-cart="<?php echo esc_attr( $product_id ); ?>"
							>
								<?php esc_html_e( 'افزودن به سبد خرید', 'almas-land' ); ?>
								<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M7 18.5A1.5 1.5 0 1 0 7 21a1.5 1.5 0 0 0 0-2.5Zm10 0A1.5 1.5 0 1 0 17 21a1.5 1.5 0 0 0 0-2.5ZM6.2 6l.4 2h11.7l-1.1 5.2H8L6.4 4H3V2h5l.4 2H21l-2.2 11.2H7.8L7.3 13H19v2H7l-.8-4.2L5.3 6h.9Z" fill="currentColor"/></svg>
							</button>
						<?php else : ?>
							<a class="front-page-offer-card__cart front-page-offer-card__cart--link" href="<?php echo esc_url( $product_link ); ?>">
								<?php echo esc_html( $product->is_in_stock() ? __( 'مشاهده و خرید', 'almas-land' ) : __( 'ناموجود', 'almas-land' ) ); ?>
								<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M7 18.5A1.5 1.5 0 1 0 7 21a1.5 1.5 0 0 0 0-2.5Zm10 0A1.5 1.5 0 1 0 17 21a1.5 1.5 0 0 0 0-2.5ZM6.2 6l.4 2h11.7l-1.1 5.2H8L6.4 4H3V2h5l.4 2H21l-2.2 11.2H7.8L7.3 13H19v2H7l-.8-4.2L5.3 6h.9Z" fill="currentColor"/></svg>
							</a>
						<?php endif; ?>
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
