<?php
/**
 * Front page hero slider.
 *
 * @package AlmasLand
 */

$slides   = function_exists( 'almasland_get_home_hero_slides' ) ? almasland_get_home_hero_slides() : array();
$settings = function_exists( 'almasland_get_home_hero_slider_settings' ) ? almasland_get_home_hero_slider_settings() : array( 'autoplay' => true, 'interval' => 5000 );

if ( empty( $slides ) ) {
	return;
}

$slide_count = count( $slides );
$is_slider   = $slide_count > 1;
?>
<section
	class="front-page-hero-section<?php echo $is_slider ? ' front-page-hero-section--slider' : ''; ?>"
	aria-label="<?php esc_attr_e( 'اسلایدر بنر اصلی', 'almas-land' ); ?>"
>
	<div
		class="front-page-hero-swiper swiper"
		<?php if ( $is_slider ) : ?>
			data-hero-swiper
			data-autoplay="<?php echo ! empty( $settings['autoplay'] ) ? 'true' : 'false'; ?>"
			data-interval="<?php echo esc_attr( (string) absint( $settings['interval'] ) ); ?>"
		<?php endif; ?>
	>
		<div class="swiper-wrapper">
			<?php foreach ( $slides as $index => $slide ) : ?>
				<?php
				$link = ! empty( $slide['link'] ) ? $slide['link'] : '';
				$tag  = $link ? 'a' : 'div';
				?>
				<div class="swiper-slide">
					<<?php echo $tag; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
						class="front-page-hero"
						<?php if ( $link ) : ?>
							href="<?php echo esc_url( $link ); ?>"
						<?php endif; ?>
					>
						<div class="front-page-hero__media">
							<picture>
								<?php if ( ! empty( $slide['images']['mobile'] ) ) : ?>
									<source media="(max-width: 767px)" srcset="<?php echo esc_url( $slide['images']['mobile'] ); ?>">
								<?php endif; ?>
								<img
									src="<?php echo esc_url( $slide['images']['desktop'] ); ?>"
									alt="<?php echo esc_attr( $slide['alt'] ); ?>"
									width="1300"
									height="400"
									loading="<?php echo 0 === $index ? 'eager' : 'lazy'; ?>"
									decoding="async"
									<?php echo 0 === $index ? 'fetchpriority="high"' : ''; ?>
								>
							</picture>
						</div>
					</<?php echo $tag; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
				</div>
			<?php endforeach; ?>
		</div>

		<?php if ( $is_slider ) : ?>
			<button type="button" class="front-page-hero-swiper__arrow front-page-hero-swiper__arrow--prev swiper-button-prev" aria-label="<?php esc_attr_e( 'اسلاید قبلی', 'almas-land' ); ?>">
				<svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M15 6 9 12l6 6" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
			</button>
			<button type="button" class="front-page-hero-swiper__arrow front-page-hero-swiper__arrow--next swiper-button-next" aria-label="<?php esc_attr_e( 'اسلاید بعدی', 'almas-land' ); ?>">
				<svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="m9 6 6 6-6 6" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
			</button>
			<div class="front-page-hero-swiper__pagination swiper-pagination"></div>
		<?php endif; ?>
	</div>
</section>
