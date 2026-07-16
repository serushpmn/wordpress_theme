<?php
/**
 * Front page special offers slider.
 *
 * @package AlmasLand
 */

if ( ! class_exists( 'WooCommerce' ) ) {
	return;
}

$products = function_exists( 'almasland_get_home_sale_products' ) ? almasland_get_home_sale_products( 12 ) : array();

if ( empty( $products ) ) {
	return;
}

$view_all_url = function_exists( 'almasland_get_home_sale_products_url' )
	? almasland_get_home_sale_products_url()
	: almasland_get_default_shop_url();
?>
<section class="front-page-offers" aria-labelledby="front-page-offers-title">
	<div class="front-page-offers__header">
		<div class="front-page-offers__heading">
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

	<div class="front-page-offers__slider swiper" data-offers-swiper>
		<div class="swiper-wrapper">
			<?php foreach ( $products as $product ) : ?>
				<div class="swiper-slide front-page-offers__slide">
					<article class="front-page-offer-card">
					<button class="front-page-offer-card__wishlist" type="button" aria-label="<?php esc_attr_e( 'افزودن به علاقه‌مندی‌ها', 'almas-land' ); ?>">
						<svg viewBox="0 0 24 24" aria-hidden="true">
							<path d="M12 20.4 10.8 19C6.4 15.1 3.5 12.5 3.5 9.2A4.4 4.4 0 0 1 8 4.8c1.5 0 2.9.7 4 1.8a5.4 5.4 0 0 1 4-1.8 4.4 4.4 0 0 1 4.5 4.4c0 3.3-2.9 5.9-7.3 9.8L12 20.4Z" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/>
						</svg>
					</button>

					<?php if ( ! empty( $product['discount_label'] ) ) : ?>
						<span class="front-page-offer-card__discount"><?php echo esc_html( $product['discount_label'] ); ?></span>
					<?php endif; ?>

					<a class="front-page-offer-card__media" href="<?php echo esc_url( $product['url'] ); ?>">
						<?php if ( ! empty( $product['image'] ) ) : ?>
							<img
								src="<?php echo esc_url( $product['image'] ); ?>"
								alt="<?php echo esc_attr( $product['name'] ); ?>"
								width="280"
								height="220"
								loading="lazy"
								decoding="async"
							>
						<?php endif; ?>
					</a>

					<div class="front-page-offer-card__body">
						<a class="front-page-offer-card__title" href="<?php echo esc_url( $product['url'] ); ?>">
							<?php echo esc_html( $product['name'] ); ?>
						</a>

						<div class="front-page-offer-card__prices">
							<span class="front-page-offer-card__price"><?php echo wp_kses_post( $product['price_html'] ); ?></span>
							<?php if ( ! empty( $product['regular_html'] ) ) : ?>
								<del class="front-page-offer-card__regular"><?php echo wp_kses_post( $product['regular_html'] ); ?></del>
							<?php endif; ?>
						</div>

						<?php if ( ! empty( $product['can_add_to_cart'] ) ) : ?>
							<button
								type="button"
								class="front-page-offer-card__cart"
								data-offer-add-to-cart
								data-product-id="<?php echo esc_attr( $product['id'] ); ?>"
								data-product-url="<?php echo esc_url( $product['url'] ); ?>"
							>
								<span class="front-page-offer-card__cart-label"><?php esc_html_e( 'افزودن به سبد خرید', 'almas-land' ); ?></span>
								<svg viewBox="0 0 24 24" aria-hidden="true">
									<path d="M7 18.5A1.5 1.5 0 1 0 7 21a1.5 1.5 0 0 0 0-2.5Zm10 0A1.5 1.5 0 1 0 17 21a1.5 1.5 0 0 0 0-2.5ZM6.2 6l.4 2h11.7l-1.1 5.2H8L6.4 4H3V2h5l.4 2H21l-2.2 11.2H7.8L7.3 13H19v2H7l-.8-4.2L5.3 6h.9Z" fill="currentColor"/>
								</svg>
							</button>
						<?php else : ?>
							<a class="front-page-offer-card__cart front-page-offer-card__cart--link" href="<?php echo esc_url( $product['url'] ); ?>">
								<?php esc_html_e( 'مشاهده محصول', 'almas-land' ); ?>
								<svg viewBox="0 0 24 24" aria-hidden="true">
									<path d="M7 18.5A1.5 1.5 0 1 0 7 21a1.5 1.5 0 0 0 0-2.5Zm10 0A1.5 1.5 0 1 0 17 21a1.5 1.5 0 0 0 0-2.5ZM6.2 6l.4 2h11.7l-1.1 5.2H8L6.4 4H3V2h5l.4 2H21l-2.2 11.2H7.8L7.3 13H19v2H7l-.8-4.2L5.3 6h.9Z" fill="currentColor"/>
								</svg>
							</a>
						<?php endif; ?>
					</div>
				</article>
				</div>
			<?php endforeach; ?>
		</div>

		<button class="front-page-offers__nav front-page-offers__nav--prev swiper-button-prev" type="button" aria-label="<?php esc_attr_e( 'محصول قبلی', 'almas-land' ); ?>">
			<svg viewBox="0 0 24 24" aria-hidden="true">
				<path d="M15 18l-6-6 6-6" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
			</svg>
		</button>
		<button class="front-page-offers__nav front-page-offers__nav--next swiper-button-next" type="button" aria-label="<?php esc_attr_e( 'محصول بعدی', 'almas-land' ); ?>">
			<svg viewBox="0 0 24 24" aria-hidden="true">
				<path d="M9 6l6 6-6 6" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
			</svg>
		</button>

		<div class="front-page-offers__pagination swiper-pagination"></div>
	</div>
</section>
