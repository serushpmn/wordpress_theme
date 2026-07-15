<?php
/**
 * Homepage slider section.
 *
 * @package AlmasLand
 */

$sliders  = almasland_get_enabled_sliders();
$shop_url = almasland_get_default_shop_url();

if ( empty( $sliders ) ) {
	$hero_image = almasland_get_option( 'hero_image', ALMASLAND_URI . '/assets/images/hero.webp' );
	$hero_title = almasland_get_option( 'hero_title', 'فروشگاه تخصصی محصولات دیجیتال' );
	?>
	<section class="hero-section">
		<div class="container">
			<a class="hero-banner" href="<?php echo esc_url( $shop_url ); ?>">
				<img src="<?php echo esc_url( $hero_image ? $hero_image : ALMASLAND_URI . '/assets/images/hero.jpg' ); ?>" alt="<?php echo esc_attr( $hero_title ); ?>" width="1200" height="430">
			</a>
		</div>
	</section>
	<?php
	return;
}
?>
<section class="hero-section hero-section--slider" aria-label="<?php esc_attr_e( 'اسلایدر صفحه اصلی', 'almas-land' ); ?>">
	<div class="container">
		<?php foreach ( $sliders as $slide ) : ?>
			<?php
			$image_url = almasland_get_attachment_url( $slide['image'], 'almasland-hero' );
			$link      = ! empty( $slide['link'] ) ? $slide['link'] : $shop_url;
			if ( ! $image_url ) {
				continue;
			}
			?>
			<a class="hero-banner" href="<?php echo esc_url( $link ); ?>">
				<img src="<?php echo esc_url( $image_url ); ?>" alt="<?php echo esc_attr( $slide['title'] ?? '' ); ?>" width="1200" height="430">
			</a>
			<?php if ( ! empty( $slide['title'] ) || ! empty( $slide['text'] ) ) : ?>
				<div class="hero-slide-caption">
					<?php if ( ! empty( $slide['title'] ) ) : ?>
						<h2><?php echo esc_html( $slide['title'] ); ?></h2>
					<?php endif; ?>
					<?php if ( ! empty( $slide['text'] ) ) : ?>
						<p><?php echo wp_kses_post( $slide['text'] ); ?></p>
					<?php endif; ?>
					<?php if ( ! empty( $slide['button_text'] ) ) : ?>
						<span class="btn btn--primary btn--small"><?php echo esc_html( $slide['button_text'] ); ?></span>
					<?php endif; ?>
				</div>
			<?php endif; ?>
		<?php endforeach; ?>
	</div>
</section>
