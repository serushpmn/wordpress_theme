<?php
/**
 * Front page hero section.
 *
 * @package AlmasLand
 */

$hero = function_exists( 'almasland_get_home_hero' ) ? almasland_get_home_hero() : null;

if ( ! $hero ) {
	return;
}

$has_image  = ! empty( $hero['images']['desktop'] );
$hero_class = 'front-page-hero' . ( $has_image ? '' : ' front-page-hero--placeholder' );
$link       = $hero['link'] ? $hero['link'] : '';
?>
<section class="front-page-hero-section" aria-label="<?php esc_attr_e( 'بنر اصلی', 'almas-land' ); ?>">
	<<?php echo $link ? 'a' : 'div'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
		class="<?php echo esc_attr( $hero_class ); ?>"
		<?php if ( $link ) : ?>
			href="<?php echo esc_url( $link ); ?>"
		<?php endif; ?>
	>
		<div class="front-page-hero__media" aria-hidden="true">
			<?php if ( $has_image ) : ?>
				<picture>
					<?php if ( ! empty( $hero['images']['mobile'] ) ) : ?>
						<source media="(max-width: 767px)" srcset="<?php echo esc_url( $hero['images']['mobile'] ); ?>">
					<?php endif; ?>
					<?php if ( ! empty( $hero['images']['tablet'] ) ) : ?>
						<source media="(max-width: 1023px)" srcset="<?php echo esc_url( $hero['images']['tablet'] ); ?>">
					<?php endif; ?>
					<img
						src="<?php echo esc_url( $hero['images']['desktop'] ); ?>"
						alt="<?php echo esc_attr( $hero['title'] ); ?>"
						width="1100"
						height="500"
						decoding="async"
						fetchpriority="high"
					>
				</picture>
			<?php endif; ?>
		</div>

		<span class="front-page-hero__shimmer" aria-hidden="true"></span>

		<div class="front-page-hero__content">
			<?php if ( $hero['title'] ) : ?>
				<h1 class="front-page-hero__title"><?php echo esc_html( $hero['title'] ); ?></h1>
			<?php endif; ?>

			<?php if ( $hero['text'] ) : ?>
				<p class="front-page-hero__text"><?php echo wp_kses_post( $hero['text'] ); ?></p>
			<?php endif; ?>

			<?php if ( $hero['button_text'] && ! $link ) : ?>
				<span class="btn btn--primary front-page-hero__cta"><?php echo esc_html( $hero['button_text'] ); ?></span>
			<?php elseif ( $hero['button_text'] && $link ) : ?>
				<span class="btn btn--primary front-page-hero__cta"><?php echo esc_html( $hero['button_text'] ); ?></span>
			<?php endif; ?>
		</div>
	</<?php echo $link ? 'a' : 'div'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
</section>
