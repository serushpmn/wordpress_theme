<?php
/**
 * Checkout form template.
 *
 * @package AlmasLand
 */

defined( 'ABSPATH' ) || exit;

do_action( 'woocommerce_before_checkout_form', $checkout );

if ( ! $checkout->is_registration_enabled() && $checkout->is_registration_required() && ! is_user_logged_in() ) {
	printf(
		'<div class="checkout-login-required surface-panel">%s</div>',
		esc_html( apply_filters( 'woocommerce_checkout_must_be_logged_in_message', __( 'برای پرداخت باید وارد حساب کاربری شوید.', 'almas-land' ) ) )
	);
	return;
}
?>
<form name="checkout" method="post" class="checkout woocommerce-checkout checkout-layout checkout-layout--compact" action="<?php echo esc_url( wc_get_checkout_url() ); ?>" enctype="multipart/form-data">
	<section class="checkout-main">
		<?php if ( $checkout->get_checkout_fields() ) : ?>
			<?php do_action( 'woocommerce_checkout_before_customer_details' ); ?>
			<div id="customer_details" class="checkout-fields checkout-fields--compact surface-panel">
				<?php do_action( 'woocommerce_checkout_billing' ); ?>
				<?php do_action( 'woocommerce_checkout_shipping' ); ?>
			</div>
			<?php do_action( 'woocommerce_checkout_after_customer_details' ); ?>
		<?php endif; ?>
	</section>

	<aside class="order-card order-card--compact surface-panel" aria-labelledby="order_review_heading">
		<?php do_action( 'woocommerce_checkout_before_order_review_heading' ); ?>
		<div class="order-card__header">
			<div>
				<span><?php esc_html_e( 'مرور نهایی', 'almas-land' ); ?></span>
				<h2 id="order_review_heading"><?php esc_html_e( 'خلاصه سفارش', 'almas-land' ); ?></h2>
			</div>
			<strong><?php echo esc_html( almasland_persian_digits( WC()->cart ? WC()->cart->get_cart_contents_count() : 0 ) ); ?></strong>
		</div>
		<?php do_action( 'woocommerce_checkout_before_order_review' ); ?>
		<div id="order_review" class="woocommerce-checkout-review-order">
			<?php do_action( 'woocommerce_checkout_order_review' ); ?>
		</div>
		<?php do_action( 'woocommerce_checkout_after_order_review' ); ?>
	</aside>
</form>
<?php do_action( 'woocommerce_after_checkout_form', $checkout ); ?>
