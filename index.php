<?php
/**
 * Main template.
 *
 * @package AlmasLand
 */

get_header();
?>
<div class="container">
	<?php almasland_breadcrumb(); ?>
	<div class="layout-with-sidebar">
		<section class="ui-grid">
			<?php if ( have_posts() ) : ?>
				<div class="blog-grid">
					<?php
					while ( have_posts() ) :
						the_post();
						get_template_part( 'template-parts/content' );
					endwhile;
					?>
				</div>
				<?php almasland_pagination( array( 'aria_label' => esc_html__( 'صفحه‌بندی نوشته‌ها', 'almas-land' ) ) ); ?>
			<?php else : ?>
				<?php get_template_part( 'template-parts/content', 'none' ); ?>
			<?php endif; ?>
		</section>
		<?php get_sidebar(); ?>
	</div>
</div>
<?php
get_footer();
