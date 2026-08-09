<?php
/**
 * Archive template (blog categories, tags, dates, authors).
 *
 * @package AlmasLand
 */

get_header();

$archive_title = wp_strip_all_tags( get_the_archive_title() );
$archive_desc  = get_the_archive_description();
$show_chips    = is_category() || is_tag();
$show_featured = ! is_paged() && ( is_category() || is_tag() );
?>
<main class="blog-page blog-page--archive">
	<div class="container">
		<?php almasland_breadcrumb(); ?>

		<?php
		get_template_part(
			'template-parts/blog/hero',
			null,
			array(
				'title'       => $archive_title,
				'description' => $archive_desc,
				'show_chips'  => $show_chips,
			)
		);
		?>

		<div class="blog-layout">
			<section class="blog-main" aria-label="<?php esc_attr_e( 'فهرست مقالات', 'almas-land' ); ?>">
				<?php if ( have_posts() ) : ?>
					<?php
					$index     = 0;
					$grid_open = false;
					?>
					<div class="blog-feed">
						<?php
						while ( have_posts() ) :
							the_post();

							if ( $show_featured && 0 === $index ) {
								get_template_part( 'template-parts/content', 'featured' );
							} else {
								if ( ! $grid_open ) {
									echo '<div class="blog-grid">';
									$grid_open = true;
								}
								get_template_part( 'template-parts/content' );
							}

							++$index;
						endwhile;

						if ( $grid_open ) {
							echo '</div>';
						}
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
</main>
<?php
get_footer();
