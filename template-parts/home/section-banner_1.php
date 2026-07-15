<?php
/**
 * Homepage banner row (first slot).
 *
 * @package AlmasLand
 */

$banners  = almasland_get_home_banners( 'banner_1' );
$shop_url = almasland_get_default_shop_url();
?>
<section class="container promo-row" aria-label="<?php esc_attr_e( 'بنرهای تبلیغاتی', 'almas-land' ); ?>">
	<?php if ( $banners ) : ?>
		<?php foreach ( $banners as $banner ) : ?>
			<?php
			$desktop = almasland_get_attachment_url( $banner['image_desktop'], 'large' );
			$mobile  = almasland_get_attachment_url( $banner['image_mobile'], 'medium' ) ?: $desktop;
			$link    = ! empty( $banner['link'] ) ? $banner['link'] : $shop_url;
			if ( ! $desktop && ! $mobile ) {
				continue;
			}
			?>
			<a href="<?php echo esc_url( $link ); ?>">
				<picture>
					<?php if ( $mobile ) : ?>
						<source media="(max-width: 720px)" srcset="<?php echo esc_url( $mobile ); ?>">
					<?php endif; ?>
					<img src="<?php echo esc_url( $desktop ?: $mobile ); ?>" alt="<?php echo esc_attr( $banner['title'] ?? '' ); ?>" width="820" height="360">
				</picture>
			</a>
		<?php endforeach; ?>
	<?php else : ?>
		<?php for ( $i = 1; $i <= 3; $i++ ) : ?>
			<?php $promo = almasland_get_option( 'promo_image_' . $i, ALMASLAND_URI . '/assets/images/promo.svg' ); ?>
			<a href="<?php echo esc_url( $shop_url ); ?>"><img src="<?php echo esc_url( $promo ? $promo : ALMASLAND_URI . '/assets/images/promo.svg' ); ?>" alt="" width="820" height="360"></a>
		<?php endfor; ?>
	<?php endif; ?>
</section>
