<?php
/**
 * Popular categories section.
 *
 * @package AlmasLand
 */

$shop       = almasland_get_panel_settings()['shop'];
$cat_ids    = array_filter( array_map( 'absint', (array) ( $shop['featured_category_ids'] ?? array() ) ) );
$categories = array();

if ( $cat_ids && taxonomy_exists( 'product_cat' ) ) {
	foreach ( $cat_ids as $cat_id ) {
		$term = get_term( $cat_id, 'product_cat' );
		if ( $term && ! is_wp_error( $term ) ) {
			$categories[] = $term;
		}
	}
}

if ( empty( $categories ) && taxonomy_exists( 'product_cat' ) ) {
	$categories = get_terms(
		array(
			'taxonomy'   => 'product_cat',
			'hide_empty' => true,
			'number'     => 8,
		)
	);
	if ( is_wp_error( $categories ) ) {
		$categories = array();
	}
}

if ( empty( $categories ) ) {
	return;
}
?>
<section class="container home-popular-categories" aria-labelledby="home-popular-categories">
	<div class="section-heading">
		<h2 id="home-popular-categories"><?php esc_html_e( 'دسته‌بندی‌های محبوب', 'almas-land' ); ?></h2>
	</div>
	<div class="home-popular-categories__grid">
		<?php foreach ( $categories as $category ) : ?>
			<?php
			$thumb_id = (int) get_term_meta( $category->term_id, 'thumbnail_id', true );
			$image    = $thumb_id ? wp_get_attachment_image_url( $thumb_id, 'thumbnail' ) : ALMASLAND_URI . '/assets/images/category-laptop.svg';
			?>
			<a class="home-popular-category" href="<?php echo esc_url( get_term_link( $category ) ); ?>">
				<img src="<?php echo esc_url( $image ); ?>" alt="<?php echo esc_attr( $category->name ); ?>" width="80" height="80">
				<span><?php echo esc_html( $category->name ); ?></span>
			</a>
		<?php endforeach; ?>
	</div>
</section>
