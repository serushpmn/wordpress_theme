<?php
/**
 * Single post content.
 *
 * @package AlmasLand
 */

?>
<article id="post-<?php the_ID(); ?>" <?php post_class( 'content-article article-body' ); ?>>
	<header class="post-header">
		<h1><?php echo esc_html( get_the_title() ); ?></h1>
		<?php almasland_post_meta(); ?>
	</header>

	<?php if ( has_post_thumbnail() ) : ?>
		<?php the_post_thumbnail( 'large', array( 'class' => 'post-cover' ) ); ?>
	<?php endif; ?>

	<div class="entry-content">
		<?php
		the_content();
		wp_link_pages( almasland_wp_link_pages_args() );
		?>
	</div>

	<footer class="tag-list">
		<?php echo wp_kses_post( get_the_tag_list( '', '', '' ) ); ?>
	</footer>
</article>
