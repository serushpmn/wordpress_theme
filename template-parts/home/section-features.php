<?php
/**
 * Front page service features bar.
 *
 * @package AlmasLand
 */

$features = function_exists( 'almasland_get_home_features_items' ) ? almasland_get_home_features_items() : array();

if ( empty( $features ) ) {
	return;
}
?>
<section class="front-page-features" aria-label="<?php esc_attr_e( 'مزایای خرید از الماس لند', 'almas-land' ); ?>">
	<div class="front-page-features__list">
		<?php foreach ( $features as $feature ) : ?>
			<div class="front-page-features__item">
				<span class="front-page-features__icon" aria-hidden="true">
					<?php echo $feature['icon']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
				</span>
				<span class="front-page-features__text">
					<strong class="front-page-features__title"><?php echo esc_html( $feature['title'] ); ?></strong>
					<span class="front-page-features__subtitle"><?php echo esc_html( $feature['subtitle'] ); ?></span>
				</span>
			</div>
		<?php endforeach; ?>
	</div>
</section>
