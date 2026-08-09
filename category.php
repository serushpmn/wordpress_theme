<?php
/**
 * Category archive template.
 *
 * @package AlmasLand
 */

get_header();

$term        = get_queried_object();
$title       = ( $term && ! is_wp_error( $term ) ) ? $term->name : wp_strip_all_tags( get_the_archive_title() );
$description = ( $term && ! empty( $term->description ) ) ? term_description() : get_the_archive_description();
?>
<main class="blog-page blog-page--category">
	<div class="container">
		<?php almasland_breadcrumb(); ?>

		<?php
		get_template_part(
			'template-parts/blog/hero',
			null,
			array(
				'title'       => $title,
				'description' => $description,
				'show_chips'  => true,
			)
		);
		?>

		<div class="blog-layout">
			<section class="blog-main" aria-label="<?php esc_attr_e( 'مقالات این دسته', 'almas-land' ); ?>">
				<?php if ( have_posts() ) : ?>
					<?php
					$show_featured = ! is_paged();
					$index         = 0;
					$grid_open     = false;
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
