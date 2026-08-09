<?php
/**
 * Sidebar template.
 *
 * @package AlmasLand
 */
?>
<aside class="blog-sidebar" aria-label="<?php esc_attr_e( 'سایدبار مقالات', 'almas-land' ); ?>">
	<?php if ( is_active_sidebar( 'sidebar-1' ) ) : ?>
		<div class="blog-sidebar__widgets">
			<?php dynamic_sidebar( 'sidebar-1' ); ?>
		</div>
	<?php else : ?>
		<section class="blog-widget">
			<h2 class="blog-widget__title"><?php esc_html_e( 'جستجو', 'almas-land' ); ?></h2>
			<?php get_search_form(); ?>
		</section>

		<section class="blog-widget">
			<h2 class="blog-widget__title"><?php esc_html_e( 'دسته‌بندی‌ها', 'almas-land' ); ?></h2>
			<ul class="blog-widget__list">
				<?php
				wp_list_categories(
					array(
						'title_li'   => '',
						'show_count' => true,
						'orderby'    => 'count',
						'order'      => 'DESC',
						'number'     => 8,
					)
				);
				?>
			</ul>
		</section>

		<section class="blog-widget">
			<h2 class="blog-widget__title"><?php esc_html_e( 'آخرین مقالات', 'almas-land' ); ?></h2>
			<ul class="blog-widget__posts">
				<?php
				$recent = wp_get_recent_posts(
					array(
						'numberposts' => 5,
						'post_status' => 'publish',
					),
					OBJECT
				);
				foreach ( (array) $recent as $recent_post ) :
					?>
					<li>
						<a href="<?php echo esc_url( get_permalink( $recent_post ) ); ?>">
							<?php if ( has_post_thumbnail( $recent_post ) ) : ?>
								<?php echo get_the_post_thumbnail( $recent_post, 'thumbnail', array( 'class' => 'blog-widget__thumb' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
							<?php endif; ?>
							<span>
								<strong><?php echo esc_html( get_the_title( $recent_post ) ); ?></strong>
								<em><?php echo esc_html( almasland_persian_digits( get_the_date( '', $recent_post ) ) ); ?></em>
							</span>
						</a>
					</li>
					<?php
				endforeach;
				?>
			</ul>
		</section>
	<?php endif; ?>
</aside>
