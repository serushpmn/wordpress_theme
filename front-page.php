<?php
/**
 * Front page template.
 *
 * @package AlmasLand
 */

get_header();
?>
<div class="front-page" id="front-page">
	<div class="front-page__frame">
		<?php get_template_part( 'template-parts/home/section', 'hero' ); ?>
		<?php get_template_part( 'template-parts/home/section', 'trust-bar' ); ?>
		<?php get_template_part( 'template-parts/home/section', 'product-categories' ); ?>
	</div>
</div>
<?php
get_footer();
