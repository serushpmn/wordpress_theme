<?php
/**
 * Front page hero slider / default gradient hero.
 *
 * The image slider renders when at least one slide has an uploaded image.
 * Otherwise a dynamic gradient hero (panel text + live stats) is shown —
 * but only while the hero section itself is enabled in the theme panel.
 *
 * @package AlmasLand
 */

$slides   = function_exists( 'almasland_get_home_hero_slides' ) ? almasland_get_home_hero_slides() : array();
$settings = function_exists( 'almasland_get_home_hero_slider_settings' ) ? almasland_get_home_hero_slider_settings() : array( 'autoplay' => true, 'interval' => 5000 );

if ( empty( $slides ) ) {
	$default = function_exists( 'almasland_get_default_hero' ) ? almasland_get_default_hero() : null;

	if ( empty( $default ) ) {
		return;
	}
	?>
	<section class="front-page-hero-section front-page-hero-section--default" aria-label="<?php esc_attr_e( 'معرفی فروشگاه', 'almas-land' ); ?>">
		<div class="front-page-hero front-page-hero--default">
			<div class="front-page-hero__glow front-page-hero__glow--a" aria-hidden="true"></div>
			<div class="front-page-hero__glow front-page-hero__glow--b" aria-hidden="true"></div>
			<div class="front-page-hero__grid" aria-hidden="true"></div>

			<div class="front-page-hero__content">
				<p class="front-page-hero__brand"><?php echo esc_html( $default['brand'] ); ?></p>
				<h1 class="front-page-hero__title"><?php echo esc_html( $default['title'] ); ?></h1>
				<p class="front-page-hero__text"><?php echo esc_html( $default['text'] ); ?></p>

				<?php if ( ! empty( $default['cta_text'] ) && ! empty( $default['cta_url'] ) ) : ?>
					<div class="front-page-hero__actions">
						<a class="front-page-hero__cta" href="<?php echo esc_url( $default['cta_url'] ); ?>">
							<?php echo esc_html( $default['cta_text'] ); ?>
							<svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M14 6 8 12l6 6" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
						</a>
					</div>
				<?php endif; ?>

				<?php if ( ! empty( $default['stats'] ) ) : ?>
					<ul class="front-page-hero__stats" aria-label="<?php esc_attr_e( 'آمار فروشگاه', 'almas-land' ); ?>">
						<?php foreach ( $default['stats'] as $stat ) : ?>
							<li class="front-page-hero__stat">
								<strong class="front-page-hero__stat-value"><?php echo esc_html( $stat['value'] ); ?></strong>
								<span class="front-page-hero__stat-label"><?php echo esc_html( $stat['label'] ); ?></span>
							</li>
						<?php endforeach; ?>
					</ul>
				<?php endif; ?>
			</div>
		</div>
	</section>
	<?php
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
