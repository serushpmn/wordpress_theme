<?php
/**
 * Archive template.
 *
 * @package AlmasLand
 */

get_header();
?>
<div class="container">
	<?php almasland_breadcrumb(); ?>
	<header class="section-heading">
		<div>
			<h1><?php echo esc_html( wp_strip_all_tags( get_the_archive_title() ) ); ?></h1>
			<?php if ( get_the_archive_description() ) : ?>
				<p><?php echo wp_kses_post( get_the_archive_description() ); ?></p>
			<?php endif; ?>
		</div>
	</header>
	<div class="layout-with-sidebar">
		<section>
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
