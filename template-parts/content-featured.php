<?php
/**
 * Featured blog card (first post on archive page 1).
 *
 * @package AlmasLand
 */

$categories = get_the_category();
$primary    = $categories ? $categories[0] : null;
?>
<article id="post-<?php the_ID(); ?>" <?php post_class( 'blog-featured' ); ?>>
	<a class="blog-featured__media" href="<?php echo esc_url( get_permalink() ); ?>" aria-hidden="true" tabindex="-1">
		<?php if ( has_post_thumbnail() ) : ?>
			<?php the_post_thumbnail( 'large' ); ?>
		<?php else : ?>
			<img src="<?php echo esc_url( ALMASLAND_URI . '/assets/images/promo.svg' ); ?>" alt="" width="960" height="640">
		<?php endif; ?>
	</a>
	<div class="blog-featured__body">
		<div class="blog-featured__meta">
			<?php if ( $primary ) : ?>
				<a class="blog-pill" href="<?php echo esc_url( get_category_link( $primary ) ); ?>"><?php echo esc_html( $primary->name ); ?></a>
			<?php endif; ?>
			<span><?php echo esc_html( almasland_persian_digits( get_the_date() ) ); ?></span>
			<span><?php echo esc_html( almasland_get_reading_time() ); ?></span>
		</div>
		<h2 class="blog-featured__title">
			<a href="<?php echo esc_url( get_permalink() ); ?>"><?php echo esc_html( get_the_title() ); ?></a>
		</h2>
		<p class="blog-featured__excerpt"><?php echo esc_html( wp_trim_words( get_the_excerpt(), 28, '…' ) ); ?></p>
		<a class="btn btn--primary btn--small" href="<?php echo esc_url( get_permalink() ); ?>">
			<?php esc_html_e( 'ادامه مطلب', 'almas-land' ); ?>
		</a>
	</div>
</article>
