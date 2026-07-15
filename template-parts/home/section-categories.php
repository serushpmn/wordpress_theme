<?php
/**
 * Homepage categories section.
 *
 * @package AlmasLand
 */

$shop_url   = almasland_get_default_shop_url();
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
			'number'     => 3,
		)
	);
	if ( is_wp_error( $categories ) ) {
		$categories = array();
	}
}
?>
<section class="container category-showcase" aria-labelledby="home-categories">
	<div class="section-heading">
		<div>
			<h1 id="home-categories"><?php echo esc_html( almasland_get_option( 'hero_title', 'فروشگاه تخصصی محصولات دیجیتال' ) ); ?></h1>
			<p><?php echo wp_kses_post( almasland_get_option( 'hero_text', 'خرید مطمئن لپ‌تاپ، موبایل، مانیتور و لوازم جانبی با پشتیبانی تخصصی.' ) ); ?></p>
		</div>
		<a href="<?php echo esc_url( $shop_url ); ?>"><?php echo esc_html( almasland_get_option( 'hero_button_text', 'مشاهده محصولات' ) ); ?></a>
	</div>

	<div class="category-cards">
		<?php foreach ( $categories as $category ) : ?>
			<?php
			$thumb_id = get_term_meta( $category->term_id, 'thumbnail_id', true );
			$image    = $thumb_id ? wp_get_attachment_image_url( $thumb_id, 'almasland-card' ) : ALMASLAND_URI . '/assets/images/category-laptop.svg';
			?>
			<a class="category-card category-card--large" href="<?php echo esc_url( get_term_link( $category ) ); ?>">
				<img src="<?php echo esc_url( $image ); ?>" alt="<?php echo esc_attr( $category->name ); ?>" width="420" height="520">
				<span><?php echo esc_html( $category->name ); ?></span>
			</a>
		<?php endforeach; ?>
		<article class="deal-card">
			<span class="badge"><?php esc_html_e( 'پیشنهاد امروز', 'almas-land' ); ?></span>
			<img src="<?php echo esc_url( ALMASLAND_URI . '/assets/images/monitor.svg' ); ?>" alt="" width="600" height="460">
			<div>
				<h2><?php esc_html_e( 'مشاوره خرید تخصصی', 'almas-land' ); ?></h2>
				<p><?php esc_html_e( 'برای انتخاب دقیق‌تر محصول با تیم فروش در تماس باشید.', 'almas-land' ); ?></p>
				<a class="btn btn--primary btn--small" href="<?php echo esc_url( almasland_get_contact_url() ); ?>"><?php esc_html_e( 'تماس با ما', 'almas-land' ); ?></a>
			</div>
		</article>
	</div>
</section>
