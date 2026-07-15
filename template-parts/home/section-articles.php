<?php
/**
 * Homepage articles section.
 *
 * @package AlmasLand
 */
?>
<section class="container product-shelf" aria-labelledby="home-blog">
	<div class="section-heading">
		<h2 id="home-blog"><?php echo esc_html( almasland_get_option( 'home_blog_title', 'آخرین نوشته‌ها' ) ); ?></h2>
		<a href="<?php echo esc_url( get_permalink( get_option( 'page_for_posts' ) ) ?: home_url( '/' ) ); ?>"><?php esc_html_e( 'مشاهده همه', 'almas-land' ); ?></a>
	</div>
	<div class="blog-grid">
		<?php
		$latest_posts = new WP_Query(
			array(
				'post_type'           => 'post',
				'posts_per_page'      => 3,
				'ignore_sticky_posts' => true,
			)
		);
		if ( $latest_posts->have_posts() ) :
			while ( $latest_posts->have_posts() ) :
				$latest_posts->the_post();
				get_template_part( 'template-parts/content' );
			endwhile;
		else :
			get_template_part( 'template-parts/content', 'none' );
		endif;
		wp_reset_postdata();
		?>
	</div>
</section>
