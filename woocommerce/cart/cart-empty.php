<?php
/**
 * Empty cart page.
 *
 * @package AlmasLand
 * @version 7.0.1
 */

defined( 'ABSPATH' ) || exit;
?>
<section class="cart-empty-state surface-panel" aria-labelledby="cart-empty-title">
	<div class="cart-empty-state__notice">
		<?php do_action( 'woocommerce_cart_is_empty' ); ?>
	</div>
	<div>
		<span class="eyebrow"><?php esc_html_e( 'سبد خرید', 'almas-land' ); ?></span>
		<h2 id="cart-empty-title"><?php esc_html_e( 'هنوز محصولی به سبد اضافه نشده است', 'almas-land' ); ?></h2>
		<p><?php esc_html_e( 'از فروشگاه دیدن کنید و کالاهای موردنظرتان را برای مقایسه و پرداخت به اینجا بیاورید.', 'almas-land' ); ?></p>
	</div>

	<?php if ( wc_get_page_id( 'shop' ) > 0 ) : ?>
		<p class="return-to-shop">
			<a class="btn btn--primary wc-backward<?php echo esc_attr( wc_wp_theme_get_element_class_name( 'button' ) ? ' ' . wc_wp_theme_get_element_class_name( 'button' ) : '' ); ?>" href="<?php echo esc_url( apply_filters( 'woocommerce_return_to_shop_redirect', wc_get_page_permalink( 'shop' ) ) ); ?>">
				<?php echo esc_html( apply_filters( 'woocommerce_return_to_shop_text', __( 'رفتن به فروشگاه', 'almas-land' ) ) ); ?>
			</a>
		</p>
	<?php endif; ?>
</section>
