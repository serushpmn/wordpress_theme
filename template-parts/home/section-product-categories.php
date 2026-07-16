<?php
/**
 * Front page product categories grid.
 *
 * @package AlmasLand
 */

$categories = function_exists( 'almasland_get_home_product_categories' ) ? almasland_get_home_product_categories( 6 ) : array();

if ( empty( $categories ) ) {
	return;
}

$view_all_url = almasland_get_default_shop_url();
?>
<section class="front-page-categories" aria-labelledby="front-page-categories-title">
	<div class="front-page-categories__header">
		<h2 class="front-page-categories__title" id="front-page-categories-title">
			<?php esc_html_e( 'دسته‌بندی محصولات', 'almas-land' ); ?>
		</h2>

		<a class="front-page-categories__all" href="<?php echo esc_url( $view_all_url ); ?>">
			<?php esc_html_e( 'مشاهده همه دسته‌ها', 'almas-land' ); ?>
			<svg viewBox="0 0 24 24" aria-hidden="true">
				<path d="M15 18l-6-6 6-6" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
			</svg>
		</a>
	</div>

	<div class="front-page-categories__grid">
		<?php foreach ( $categories as $category ) : ?>
			<a class="front-page-categories__card" href="<?php echo esc_url( $category['url'] ); ?>">
				<span class="front-page-categories__icon" aria-hidden="true">
					<?php if ( ! empty( $category['image']['url'] ) ) : ?>
						<img
							src="<?php echo esc_url( $category['image']['url'] ); ?>"
							<?php if ( ! empty( $category['image']['srcset'] ) ) : ?>
								srcset="<?php echo esc_attr( $category['image']['srcset'] ); ?>"
								sizes="(max-width: 560px) 88px, 112px"
							<?php endif; ?>
							alt=""
							width="<?php echo esc_attr( $category['image']['width'] ); ?>"
							height="<?php echo esc_attr( $category['image']['height'] ); ?>"
							loading="lazy"
							decoding="async"
						>
					<?php endif; ?>
				</span>

				<strong class="front-page-categories__name"><?php echo esc_html( $category['name'] ); ?></strong>
				<span class="front-page-categories__count"><?php echo esc_html( $category['count_label'] ); ?></span>
			</a>
		<?php endforeach; ?>
	</div>
</section>
