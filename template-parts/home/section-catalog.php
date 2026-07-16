<?php
/**
 * Front page catalog products with category filters.
 *
 * @package AlmasLand
 */

if ( ! class_exists( 'WooCommerce' ) ) {
	return;
}

$categories = function_exists( 'almasland_get_home_catalog_categories' ) ? almasland_get_home_catalog_categories( 4 ) : array();
$limit      = 8;
$all_products = almasland_get_home_catalog_products( 0, $limit );

if ( empty( $all_products ) && empty( $categories ) ) {
	return;
}

$panels = array(
	'all' => $all_products,
);

foreach ( $categories as $category ) {
	$panels[ (string) $category->term_id ] = almasland_get_home_catalog_products( $category->term_id, $limit );
}

$view_all_url = wc_get_page_permalink( 'shop' );
$active_key   = 'all';
?>
<section class="front-page-catalog" aria-label="<?php esc_attr_e( 'محصولات بر اساس دسته‌بندی', 'almas-land' ); ?>">
	<div class="front-page-catalog__toolbar">
		<div class="front-page-catalog__tabs" role="tablist" aria-label="<?php esc_attr_e( 'فیلتر دسته‌بندی', 'almas-land' ); ?>">
			<button
				type="button"
				class="front-page-catalog__tab is-active"
				role="tab"
				id="front-page-catalog-tab-all"
				aria-selected="true"
				aria-controls="front-page-catalog-panel-all"
				data-catalog-tab="all"
				data-catalog-url="<?php echo esc_url( $view_all_url ); ?>"
			>
				<?php esc_html_e( 'همه', 'almas-land' ); ?>
			</button>

			<?php foreach ( $categories as $category ) : ?>
				<?php
				$term_link = get_term_link( $category );
				$term_url  = is_wp_error( $term_link ) ? $view_all_url : $term_link;
				?>
				<button
					type="button"
					class="front-page-catalog__tab"
					role="tab"
					id="front-page-catalog-tab-<?php echo esc_attr( $category->term_id ); ?>"
					aria-selected="false"
					aria-controls="front-page-catalog-panel-<?php echo esc_attr( $category->term_id ); ?>"
					data-catalog-tab="<?php echo esc_attr( $category->term_id ); ?>"
					data-catalog-url="<?php echo esc_url( $term_url ); ?>"
				>
					<?php echo esc_html( $category->name ); ?>
				</button>
			<?php endforeach; ?>
		</div>

		<a class="front-page-catalog__all" href="<?php echo esc_url( $view_all_url ); ?>" data-catalog-view-all>
			<?php esc_html_e( 'مشاهده همه', 'almas-land' ); ?>
			<svg viewBox="0 0 24 24" aria-hidden="true">
				<path d="M15 18l-6-6 6-6" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
			</svg>
		</a>
	</div>

	<div class="front-page-catalog__panels">
		<?php foreach ( $panels as $panel_key => $products ) : ?>
			<?php
			$is_active = (string) $panel_key === $active_key;
			$panel_id  = 'front-page-catalog-panel-' . $panel_key;
			$tab_id    = 'front-page-catalog-tab-' . $panel_key;
			?>
			<div
				class="front-page-catalog__panel<?php echo $is_active ? ' is-active' : ''; ?>"
				id="<?php echo esc_attr( $panel_id ); ?>"
				role="tabpanel"
				aria-labelledby="<?php echo esc_attr( $tab_id ); ?>"
				data-catalog-panel="<?php echo esc_attr( $panel_key ); ?>"
				<?php echo $is_active ? '' : ' hidden'; ?>
			>
				<?php if ( empty( $products ) ) : ?>
					<p class="front-page-catalog__empty"><?php esc_html_e( 'محصولی در این دسته‌بندی یافت نشد.', 'almas-land' ); ?></p>
				<?php else : ?>
					<div class="front-page-catalog__grid">
						<?php foreach ( $products as $product ) : ?>
							<?php echo almasland_get_home_catalog_card_html( $product ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
						<?php endforeach; ?>
					</div>
				<?php endif; ?>
			</div>
		<?php endforeach; ?>
	</div>
</section>
