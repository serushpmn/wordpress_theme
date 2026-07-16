<?php
/**
 * Front page trust-building bar.
 *
 * @package AlmasLand
 */

$trust_items = function_exists( 'almasland_get_home_trust_items' ) ? almasland_get_home_trust_items() : array();

if ( empty( $trust_items ) ) {
	return;
}
?>
<section class="front-page-trust" aria-label="<?php esc_attr_e( 'مزایای خرید', 'almas-land' ); ?>">
	<div class="front-page-trust__bar">
		<?php foreach ( $trust_items as $item ) : ?>
			<button
				type="button"
				class="front-page-trust__item"
				data-trust-tooltip="<?php echo esc_attr( $item['tooltip_mode'] ); ?>"
				aria-describedby="<?php echo esc_attr( $item['tooltip_id'] ); ?>"
				aria-expanded="false"
			>
				<span class="front-page-trust__icon" aria-hidden="true">
					<?php echo $item['icon']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
				</span>

				<span class="front-page-trust__text">
					<strong class="front-page-trust__title"><?php echo esc_html( $item['title'] ); ?></strong>
					<span class="front-page-trust__subtitle"><?php echo esc_html( $item['subtitle'] ); ?></span>
				</span>

				<span
					class="front-page-trust__tooltip"
					id="<?php echo esc_attr( $item['tooltip_id'] ); ?>"
					role="tooltip"
					hidden
				>
					<?php if ( ! empty( $item['tooltip_title'] ) ) : ?>
						<strong class="front-page-trust__tooltip-title"><?php echo esc_html( $item['tooltip_title'] ); ?></strong>
					<?php endif; ?>
					<span class="front-page-trust__tooltip-body">
						<?php
						if ( ! empty( $item['tooltip_link'] ) ) {
							printf(
								'<a href="%1$s">%2$s</a>',
								esc_url( $item['tooltip_link'] ),
								esc_html( $item['tooltip_text'] )
							);
						} else {
							echo esc_html( $item['tooltip_text'] );
						}
						?>
					</span>
				</span>
			</button>
		<?php endforeach; ?>
	</div>
</section>
