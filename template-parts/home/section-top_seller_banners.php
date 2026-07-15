<?php
/**
 * Top seller category banners.
 *
 * @package AlmasLand
 */

$items = array(
	array(
		'label'      => __( 'پرفروش‌ترین لپ‌تاپ‌ها', 'almas-land' ),
		'candidates' => array( 'laptop', 'laptops', 'lap-top', 'لپتاپ', 'لپ تاپ' ),
		'image'      => ALMASLAND_URI . '/assets/images/laptop.svg',
	),
	array(
		'label'      => __( 'پرفروش‌ترین آل این وان', 'almas-land' ),
		'candidates' => array( 'all-in-one', 'allinone', 'aio', 'آل این وان', 'ال این وان' ),
		'image'      => ALMASLAND_URI . '/assets/images/category-ai.svg',
	),
	array(
		'label'      => __( 'پرفروش‌ترین مانیتور', 'almas-land' ),
		'candidates' => array( 'monitor', 'monitors', 'مانیتور' ),
		'image'      => ALMASLAND_URI . '/assets/images/monitor.svg',
	),
);
?>
<section class="container home-top-seller-banners" aria-label="<?php esc_attr_e( 'بنر پرفروش‌ها', 'almas-land' ); ?>">
	<?php foreach ( $items as $item ) : ?>
		<?php
		$term = almasland_get_product_category_by_candidates( $item['candidates'] );
		$url  = $term ? get_term_link( $term ) : almasland_get_default_shop_url();
		?>
		<a class="home-top-seller-banner" href="<?php echo esc_url( $url ); ?>">
			<img src="<?php echo esc_url( $item['image'] ); ?>" alt="<?php echo esc_attr( $item['label'] ); ?>" width="420" height="200">
			<span><?php echo esc_html( $item['label'] ); ?></span>
		</a>
	<?php endforeach; ?>
</section>
