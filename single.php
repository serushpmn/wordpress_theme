<?php
/**
 * Single post template.
 *
 * @package AlmasLand
 */

get_header();
?>
<main class="blog-single-page">
	<div class="container">
		<?php almasland_breadcrumb(); ?>

		<?php if ( have_posts() ) : ?>
			<?php
			while ( have_posts() ) :
				the_post();
				?>
				<div class="blog-single-layout">
					<div class="blog-single-main">
						<?php get_template_part( 'template-parts/content', 'single' ); ?>

						<nav class="blog-post-nav" aria-label="<?php esc_attr_e( 'ناوبری نوشته‌ها', 'almas-land' ); ?>">
							<?php
							the_post_navigation(
								array(
									'prev_text' => '<span class="blog-post-nav__label">' . esc_html__( 'قبلی', 'almas-land' ) . '</span><span class="blog-post-nav__title">%title</span>',
									'next_text' => '<span class="blog-post-nav__label">' . esc_html__( 'بعدی', 'almas-land' ) . '</span><span class="blog-post-nav__title">%title</span>',
								)
							);
							?>
						</nav>

						<?php almasland_related_posts( 3 ); ?>

						<?php
						if ( comments_open() || get_comments_number() ) {
							comments_template();
						}
						?>
					</div>

					<?php get_sidebar(); ?>
				</div>
				<?php
			endwhile;
		else :
			get_template_part( 'template-parts/content', 'none' );
		endif;
		?>
	</div>
</main>
<?php
get_footer();
