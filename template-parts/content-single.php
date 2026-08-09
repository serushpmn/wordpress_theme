<?php
/**
 * Single post content.
 *
 * @package AlmasLand
 */

$categories = get_the_category();
$primary    = $categories ? $categories[0] : null;
?>
<article id="post-<?php the_ID(); ?>" <?php post_class( 'blog-article' ); ?>>
	<header class="blog-article__header">
		<?php if ( $primary ) : ?>
			<a class="blog-pill" href="<?php echo esc_url( get_category_link( $primary ) ); ?>"><?php echo esc_html( $primary->name ); ?></a>
		<?php endif; ?>
		<h1 class="blog-article__title"><?php echo esc_html( get_the_title() ); ?></h1>
		<?php almasland_post_meta(); ?>
	</header>

	<?php if ( has_post_thumbnail() ) : ?>
		<figure class="blog-article__cover">
			<?php the_post_thumbnail( 'large', array( 'class' => 'blog-article__cover-img' ) ); ?>
		</figure>
	<?php endif; ?>

	<div class="blog-article__content entry-content">
		<?php
		the_content();
		wp_link_pages( almasland_wp_link_pages_args() );
		?>
	</div>

	<?php if ( has_tag() ) : ?>
		<footer class="blog-article__tags">
			<span class="blog-article__tags-label"><?php esc_html_e( 'برچسب‌ها', 'almas-land' ); ?></span>
			<div class="tag-list">
				<?php echo wp_kses_post( get_the_tag_list( '', '', '' ) ); ?>
			</div>
		</footer>
	<?php endif; ?>
</article>
