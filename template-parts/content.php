<?php
/**
 * Loop content card.
 *
 * @package AlmasLand
 */

$categories = get_the_category();
$primary    = $categories ? $categories[0] : null;
?>
<article id="post-<?php the_ID(); ?>" <?php post_class( 'blog-card' ); ?>>
	<a class="blog-card__media" href="<?php echo esc_url( get_permalink() ); ?>" aria-label="<?php the_title_attribute(); ?>">
		<?php if ( has_post_thumbnail() ) : ?>
			<?php the_post_thumbnail( 'almasland-card' ); ?>
		<?php else : ?>
			<img src="<?php echo esc_url( ALMASLAND_URI . '/assets/images/promo.svg' ); ?>" alt="" width="600" height="420">
		<?php endif; ?>
		<?php if ( $primary ) : ?>
			<span class="blog-card__badge"><?php echo esc_html( $primary->name ); ?></span>
		<?php endif; ?>
	</a>
	<div class="blog-card__body">
		<div class="blog-card__meta">
			<span><?php echo esc_html( almasland_persian_digits( get_the_date() ) ); ?></span>
			<span><?php echo esc_html( almasland_get_reading_time() ); ?></span>
		</div>
		<a class="blog-card__title" href="<?php echo esc_url( get_permalink() ); ?>"><?php echo esc_html( get_the_title() ); ?></a>
		<p class="blog-card__excerpt"><?php echo esc_html( wp_trim_words( get_the_excerpt(), 18, '…' ) ); ?></p>
		<a class="blog-card__more" href="<?php echo esc_url( get_permalink() ); ?>">
			<?php esc_html_e( 'ادامه مطلب', 'almas-land' ); ?>
			<svg viewBox="0 0 24 24" aria-hidden="true" width="16" height="16"><path d="M15 18l-6-6 6-6" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
		</a>
	</div>
</article>
