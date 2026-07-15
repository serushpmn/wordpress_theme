<?php
/**
 * Single post template.
 *
 * @package AlmasLand
 */

get_header();
?>
<div class="container">
	<?php almasland_breadcrumb(); ?>
	<div class="post-layout">
		<div>
			<?php
			if ( have_posts() ) :
				while ( have_posts() ) :
					the_post();
					get_template_part( 'template-parts/content', 'single' );
					the_post_navigation();
					if ( comments_open() || get_comments_number() ) {
						comments_template();
					}
				endwhile;
			else :
				get_template_part( 'template-parts/content', 'none' );
			endif;
			?>
		</div>
		<?php get_sidebar(); ?>
	</div>
</div>
<?php
get_footer();
