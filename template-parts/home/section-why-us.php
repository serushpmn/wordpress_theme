<?php
/**
 * Front page "Why Almas Land" trust section.
 *
 * @package AlmasLand
 */

$why = function_exists( 'almasland_get_home_why_section' ) ? almasland_get_home_why_section() : null;

if ( ! $why ) {
	return;
}

$stats = $why['stats'] ?? array();
?>
<section class="front-page-why" aria-labelledby="front-page-why-title">
	<div class="front-page-why__card">
		<div class="front-page-why__content">
			<h2 class="front-page-why__title" id="front-page-why-title">
				<?php echo esc_html( $why['title'] ); ?>
			</h2>

			<?php if ( ! empty( $why['text'] ) ) : ?>
				<p class="front-page-why__text"><?php echo esc_html( $why['text'] ); ?></p>
			<?php endif; ?>

			<?php if ( $stats ) : ?>
				<div class="front-page-why__stats">
					<?php foreach ( $stats as $stat ) : ?>
						<button
							type="button"
							class="front-page-why__stat"
							data-trust-tooltip="<?php echo esc_attr( $stat['tooltip_mode'] ); ?>"
							aria-describedby="<?php echo esc_attr( $stat['tooltip_id'] ); ?>"
							aria-expanded="false"
						>
							<span class="front-page-why__stat-icon" aria-hidden="true">
								<?php echo $stat['icon']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
							</span>
							<strong class="front-page-why__stat-title"><?php echo esc_html( $stat['title'] ); ?></strong>
							<span class="front-page-why__stat-subtitle"><?php echo esc_html( $stat['subtitle'] ); ?></span>

							<span
								class="front-page-trust__tooltip"
								id="<?php echo esc_attr( $stat['tooltip_id'] ); ?>"
								role="tooltip"
								hidden
							>
								<?php if ( ! empty( $stat['tooltip_title'] ) ) : ?>
									<strong class="front-page-trust__tooltip-title"><?php echo esc_html( $stat['tooltip_title'] ); ?></strong>
								<?php endif; ?>
								<span class="front-page-trust__tooltip-body"><?php echo esc_html( $stat['tooltip_text'] ); ?></span>
							</span>
						</button>
					<?php endforeach; ?>
				</div>
			<?php endif; ?>
		</div>

		<div class="front-page-why__media" aria-hidden="true">
			<img
				src="<?php echo esc_url( $why['image'] ); ?>"
				alt=""
				width="640"
				height="480"
				loading="lazy"
				decoding="async"
			>
		</div>
	</div>
</section>
