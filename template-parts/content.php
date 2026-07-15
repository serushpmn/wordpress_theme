<?php
/**
 * Loop content card.
 *
 * @package AlmasLand
 */

?>
<article id="post-<?php the_ID(); ?>" <?php post_class( 'blog-card surface-panel' ); ?>>
	<a class="blog-card__media" href="<?php echo esc_url( get_permalink() ); ?>" aria-label="<?php the_title_attribute(); ?>">
		<?php if ( has_post_thumbnail() ) : ?>
			<?php the_post_thumbnail( 'almasland-card' ); ?>
		<?php else : ?>
			<img src="<?php echo esc_url( ALMASLAND_URI . '/assets/images/promo.svg' ); ?>" alt="" width="600" height="420">
		<?php endif; ?>
	</a>
	<div class="blog-card__body">
		<div class="blog-card__meta">
			<span><?php echo esc_html( get_the_date() ); ?></span>
			<?php if ( has_category() ) : ?>
				<span><?php echo wp_kses_post( get_the_category_list( '، ' ) ); ?></span>
			<?php endif; ?>
		</div>
		<a class="blog-card__title" href="<?php echo esc_url( get_permalink() ); ?>"><?php echo esc_html( get_the_title() ); ?></a>
		<?php the_excerpt(); ?>
		<a class="btn btn--ghost btn--small" href="<?php echo esc_url( get_permalink() ); ?>"><?php esc_html_e( 'ادامه مطلب', 'almas-land' ); ?></a>
	</div>
</article>
