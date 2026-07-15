<?php
/**
 * Single product wrapper.
 *
 * @package AlmasLand
 */

defined( 'ABSPATH' ) || exit;

get_header();
?>
<div class="container">
	<?php
	while ( have_posts() ) :
		the_post();
		wc_get_template_part( 'content', 'single-product' );
	endwhile;
	?>
</div>
<?php
get_footer();
