<?php
/**
 * Main fallback template (blog-style listing).
 *
 * @package AlmasLand
 */

get_header();
?>
<main class="blog-page">
	<div class="container">
		<?php almasland_breadcrumb(); ?>

		<?php
		get_template_part(
			'template-parts/blog/hero',
			null,
			array(
				'title'       => __( 'مقالات', 'almas-land' ),
				'description' => '',
				'show_chips'  => true,
			)
		);
		?>

		<div class="blog-layout">
			<section class="blog-main" aria-label="<?php esc_attr_e( 'فهرست مقالات', 'almas-land' ); ?>">
				<?php if ( have_posts() ) : ?>
					<div class="blog-feed">
						<div class="blog-grid">
							<?php
							while ( have_posts() ) :
								the_post();
								get_template_part( 'template-parts/content' );
							endwhile;
							?>
						</div>
					</div>
					<?php almasland_pagination( array( 'aria_label' => esc_html__( 'صفحه‌بندی نوشته‌ها', 'almas-land' ) ) ); ?>
				<?php else : ?>
					<?php get_template_part( 'template-parts/content', 'none' ); ?>
				<?php endif; ?>
			</section>
			<?php get_sidebar(); ?>
		</div>
	</div>
</main>
<?php
get_footer();
