<?php
/**
 * Homepage banner row (second slot).
 *
 * @package AlmasLand
 */

$banners  = almasland_get_home_banners( 'banner_2' );
$shop_url = almasland_get_default_shop_url();

if ( empty( $banners ) ) {
	return;
}
?>
<section class="container promo-row promo-row--secondary" aria-label="<?php esc_attr_e( 'بنرهای تکمیلی', 'almas-land' ); ?>">
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
</section>
