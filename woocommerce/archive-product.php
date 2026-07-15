<?php
/**
 * Product archive template.
 *
 * @package AlmasLand
 */

defined( 'ABSPATH' ) || exit;

get_header();

$is_taxonomy = is_product_taxonomy();
$hero_text   = '';


?>
<div class="container">
	<?php almasland_breadcrumb(); ?>
	<?php woocommerce_output_all_notices(); ?>

	<div class="shop-toolbar surface-panel">
		<div class="shop-toolbar__top">
			<button class="btn btn--outline filter-open" type="button" data-filter-open>
				<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 6h16v2H4V6Zm3 5h10v2H7v-2Zm3 5h4v2h-4v-2Z"/></svg>
				<?php esc_html_e( 'فیلترها', 'almas-land' ); ?>
			</button>
			<?php almasland_shop_result_count(); ?>
			<div class="view-switcher" data-view-switcher aria-label="<?php esc_attr_e( 'تغییر نوع نمایش', 'almas-land' ); ?>">
				<button class="is-active" type="button" data-view-mode="grid" aria-label="<?php esc_attr_e( 'نمایش شبکه‌ای', 'almas-land' ); ?>">
					<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 4h7v7H4V4Zm9 0h7v7h-7V4ZM4 13h7v7H4v-7Zm9 0h7v7h-7v-7Z"/></svg>
				</button>
				<button type="button" data-view-mode="list" aria-label="<?php esc_attr_e( 'نمایش لیستی', 'almas-land' ); ?>">
					<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 6h16v2H4V6Zm0 5h16v2H4v-2Zm0 5h16v2H4v-2Z"/></svg>
				</button>
			</div>
		</div>
		<?php almasland_shop_sort_bar(); ?>
		<?php almasland_shop_active_filters(); ?>
	</div>

	<div class="category-layout shop-layout">
		<aside class="filter-panel shop-filter-panel" data-filter-panel aria-label="<?php esc_attr_e( 'فیلتر محصولات', 'almas-land' ); ?>">
			<div class="filter-panel__header">
				<h2><?php esc_html_e( 'فیلترها', 'almas-land' ); ?></h2>
				<button class="icon-button filter-close" type="button" data-filter-close aria-label="<?php esc_attr_e( 'بستن فیلترها', 'almas-land' ); ?>">
					<svg viewBox="0 0 24 24" aria-hidden="true"><path d="m6.4 5 12.6 12.6-1.4 1.4L5 6.4 6.4 5Zm12.6 1.4L6.4 19 5 17.6 17.6 5 19 6.4Z"/></svg>
				</button>
			</div>
			<?php almasland_shop_filter_form(); ?>
		</aside>
		<div class="filter-backdrop" data-filter-close aria-hidden="true"></div>

		<section class="shop-products" aria-label="<?php esc_attr_e( 'فهرست محصولات', 'almas-land' ); ?>" data-shop-products>
			<?php if ( woocommerce_product_loop() ) : ?>
				<?php woocommerce_product_loop_start(); ?>
				<?php
				while ( have_posts() ) :
					the_post();
					wc_get_template_part( 'content', 'product' );
				endwhile;
				?>
				<?php woocommerce_product_loop_end(); ?>
				<?php woocommerce_pagination(); ?>
			<?php else : ?>
				<?php do_action( 'woocommerce_no_products_found' ); ?>
			<?php endif; ?>
		</section>
	</div>
</div>
<?php
get_footer();
