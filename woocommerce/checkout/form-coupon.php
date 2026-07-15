<?php
/**
 * Checkout coupon form.
 *
 * @package AlmasLand
 * @version 9.8.0
 */

defined( 'ABSPATH' ) || exit;

if ( ! wc_coupons_enabled() ) {
	return;
}
?>
<div class="woocommerce-form-coupon-toggle checkout-inline-panel surface-panel">
	<strong><?php esc_html_e( 'کد تخفیف دارید؟', 'almas-land' ); ?></strong>
	<a href="#" role="button" aria-label="<?php esc_attr_e( 'وارد کردن کد تخفیف', 'almas-land' ); ?>" aria-controls="woocommerce-checkout-form-coupon" aria-expanded="false" class="showcoupon"><?php esc_html_e( 'کد را وارد کنید', 'almas-land' ); ?></a>
</div>

<form class="checkout_coupon woocommerce-form-coupon checkout-coupon-form surface-panel" method="post" style="display:none" id="woocommerce-checkout-form-coupon">
	<p class="form-row form-row-first">
		<label for="coupon_code" class="visually-hidden"><?php esc_html_e( 'کد تخفیف', 'almas-land' ); ?></label>
		<input type="text" name="coupon_code" class="input-text" placeholder="<?php esc_attr_e( 'کد تخفیف', 'almas-land' ); ?>" id="coupon_code" value="">
	</p>

	<p class="form-row form-row-last">
		<button type="submit" class="button btn btn--outline<?php echo esc_attr( wc_wp_theme_get_element_class_name( 'button' ) ? ' ' . wc_wp_theme_get_element_class_name( 'button' ) : '' ); ?>" name="apply_coupon" value="<?php esc_attr_e( 'اعمال کد', 'almas-land' ); ?>"><?php esc_html_e( 'اعمال کد', 'almas-land' ); ?></button>
	</p>

	<div class="clear"></div>
</form>
