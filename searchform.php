<?php
/**
 * Search form template.
 *
 * @package AlmasLand
 */

$search_target = 'product';
if ( ! class_exists( 'WooCommerce' ) ) {
	$search_target = 'post';
}
?>
<form class="search-form" role="search" method="get" action="<?php echo esc_url( home_url( '/' ) ); ?>">
	<label class="visually-hidden" for="site-search"><?php esc_html_e( 'جستجو', 'almas-land' ); ?></label>
	<input
		id="site-search"
		type="search"
		name="s"
		value="<?php echo esc_attr( get_search_query() ); ?>"
		placeholder="<?php esc_attr_e( 'جستجو در محصولات...', 'almas-land' ); ?>"
		autocomplete="off"
	>
	<?php if ( 'product' === $search_target ) : ?>
		<input type="hidden" name="post_type" value="product">
	<?php endif; ?>
	<button type="submit" aria-label="<?php esc_attr_e( 'جستجو', 'almas-land' ); ?>">
		<svg viewBox="0 0 24 24" aria-hidden="true" fill="none"><circle cx="11" cy="11" r="6.5" stroke="currentColor" stroke-width="2"/><path d="m16.2 16.2 4.3 4.3" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
	</button>
</form>
