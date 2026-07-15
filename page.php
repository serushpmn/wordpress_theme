<?php
/**
 * Page template.
 *
 * @package AlmasLand
 */

get_header();
?>
<div class="container">
	<?php almasland_breadcrumb(); ?>
	<?php
	if ( have_posts() ) :
		while ( have_posts() ) :
			the_post();
			if ( function_exists( 'is_cart' ) && ( is_cart() || is_checkout() || is_account_page() ) ) {
				get_template_part( 'template-parts/content', 'woocommerce' );
			} else {
				get_template_part( 'template-parts/content', 'page' );
				if ( comments_open() || get_comments_number() ) {
					comments_template();
				}
			}
		endwhile;
	else :
		get_template_part( 'template-parts/content', 'none' );
	endif;
	?>
</div>
<?php
get_footer();
