<?php
/**
 * Homepage custom blocks section.
 *
 * @package AlmasLand
 */

$blocks = almasland_get_enabled_blocks();

if ( empty( $blocks ) ) {
	return;
}
?>
<section class="container home-custom-blocks" aria-label="<?php esc_attr_e( 'بلاک‌های اختصاصی', 'almas-land' ); ?>">
	<?php foreach ( $blocks as $block ) : ?>
		<?php
		$image_url = almasland_get_attachment_url( $block['image'], 'almasland-card' );
		$bg_color  = sanitize_hex_color( $block['bg_color'] ?? '#f7f9fc' ) ?: '#f7f9fc';
		?>
		<article class="home-custom-block" style="<?php echo esc_attr( 'background-color:' . $bg_color ); ?>">
			<?php if ( $image_url ) : ?>
				<img src="<?php echo esc_url( $image_url ); ?>" alt="<?php echo esc_attr( $block['title'] ?? '' ); ?>" width="420" height="320" loading="lazy">
			<?php endif; ?>
			<div>
				<?php if ( ! empty( $block['title'] ) ) : ?>
					<h2><?php echo esc_html( $block['title'] ); ?></h2>
				<?php endif; ?>
				<?php if ( ! empty( $block['description'] ) ) : ?>
					<p><?php echo wp_kses_post( $block['description'] ); ?></p>
				<?php endif; ?>
				<?php if ( ! empty( $block['button_text'] ) && ! empty( $block['link'] ) ) : ?>
					<a class="btn btn--primary btn--small" href="<?php echo esc_url( $block['link'] ); ?>"><?php echo esc_html( $block['button_text'] ); ?></a>
				<?php endif; ?>
			</div>
		</article>
	<?php endforeach; ?>
</section>
