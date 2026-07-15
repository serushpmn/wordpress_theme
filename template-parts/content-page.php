<?php
/**
 * Page content.
 *
 * @package AlmasLand
 */

?>
<article id="post-<?php the_ID(); ?>" <?php post_class( 'content-article article-body' ); ?>>
	<h1><?php echo esc_html( get_the_title() ); ?></h1>
	<div class="entry-content">
		<?php
		the_content();
		wp_link_pages( almasland_wp_link_pages_args() );
		?>
	</div>
</article>
