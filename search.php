<?php
/**
 * Search results template.
 *
 * @package AlmasLand
 */

get_header();
?>
<div class="container">
	<?php almasland_breadcrumb(); ?>
	<header class="section-heading">
		<h1><?php printf( esc_html__( 'نتایج جستجو برای: %s', 'almas-land' ), esc_html( get_search_query() ) ); ?></h1>
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
