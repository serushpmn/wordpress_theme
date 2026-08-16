<?php
/**
 * Product archive template.
 *
 * @package AlmasLand
 */

defined( 'ABSPATH' ) || exit;

get_header();

$archive_title = function_exists( 'almasland_get_shop_archive_title' ) ? almasland_get_shop_archive_title() : woocommerce_page_title( false );
$clear_url     = function_exists( 'almasland_get_shop_clear_filters_url' ) ? almasland_get_shop_clear_filters_url() : wc_get_page_permalink( 'shop' );
?>
<div class="container shop-archive">
	<?php almasland_breadcrumb(); ?>
	<?php woocommerce_output_all_notices(); ?>

	<?php almasland_shop_category_nav(); ?>

	<div class="shop-layout">
		<aside class="filter-panel shop-filter-panel" data-filter-panel aria-label="<?php esc_attr_e( 'فیلتر محصولات', 'almas-land' ); ?>">
			<div class="filter-panel__header">
				<h2><?php esc_html_e( 'فیلترها', 'almas-land' ); ?></h2>
				<a class="filter-panel__clear" href="<?php echo esc_url( $clear_url ); ?>"><?php esc_html_e( 'حذف فیلترها', 'almas-land' ); ?></a>
				<button class="icon-button filter-close" type="button" data-filter-close aria-label="<?php esc_attr_e( 'بستن فیلترها', 'almas-land' ); ?>">
					<svg viewBox="0 0 24 24" aria-hidden="true"><path d="m6.4 5 12.6 12.6-1.4 1.4L5 6.4 6.4 5Zm12.6 1.4L6.4 19 5 17.6 17.6 5 19 6.4Z"/></svg>
				</button>
			</div>
			<?php almasland_shop_filter_form(); ?>
		</aside>
		<div class="filter-backdrop" data-filter-close aria-hidden="true"></div>

		<section class="shop-products" aria-label="<?php esc_attr_e( 'فهرست محصولات', 'almas-land' ); ?>" data-shop-products>
			<div class="shop-products__toolbar">
				<div class="shop-products__heading">
					<button class="btn btn--outline filter-open" type="button" data-filter-open>
						<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 6h16v2H4V6Zm3 5h10v2H7v-2Zm3 5h4v2h-4v-2Z"/></svg>
						<?php esc_html_e( 'فیلترها', 'almas-land' ); ?>
					</button>
					<h1 class="shop-products__title"><?php echo esc_html( $archive_title ); ?></h1>
				</div>
				<?php almasland_shop_sort_bar(); ?>
			</div>

			<?php almasland_shop_active_filters(); ?>

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
