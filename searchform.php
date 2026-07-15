<?php
/**
 * Search form template.
 *
 * @package AlmasLand
 */

$search_target = 'post';
if ( class_exists( 'WooCommerce' ) && ( is_shop() || is_product_taxonomy() || is_singular( 'product' ) || is_front_page() ) ) {
	$search_target = 'product';
}
?>
<form class="search-form" role="search" method="get" action="<?php echo esc_url( home_url( '/' ) ); ?>">
	<label class="visually-hidden" for="site-search"><?php esc_html_e( 'جستجو', 'almas-land' ); ?></label>
	<input id="site-search" type="search" name="s" value="<?php echo esc_attr( get_search_query() ); ?>" placeholder="<?php esc_attr_e( 'جستجوی محصول، مقاله یا دسته‌بندی...', 'almas-land' ); ?>">
	<?php if ( 'product' === $search_target ) : ?>
		<input type="hidden" name="post_type" value="product">
	<?php endif; ?>
	<button type="submit" aria-label="<?php esc_attr_e( 'جستجو', 'almas-land' ); ?>">
		<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M10.8 4.2a6.6 6.6 0 1 0 0 13.2 6.6 6.6 0 0 0 0-13.2Zm0 1.8a4.8 4.8 0 1 1 0 9.6 4.8 4.8 0 0 1 0-9.6Zm5.2 9.1 4.2 4.2-1.3 1.3-4.2-4.2 1.3-1.3Z"/></svg>
	</button>
</form>
