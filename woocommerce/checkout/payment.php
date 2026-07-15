<?php
/**
 * Checkout payment section.
 *
 * @package AlmasLand
 * @version 9.8.0
 */

defined( 'ABSPATH' ) || exit;

if ( ! wp_doing_ajax() ) {
	do_action( 'woocommerce_review_order_before_payment' );
}
?>
<section id="payment" class="woocommerce-checkout-payment checkout-payment-panel checkout-payment-panel--compact" aria-labelledby="checkout-payment-title">
	<header class="checkout-payment-panel__header checkout-payment-panel__header--compact">
		<h3 id="checkout-payment-title"><?php esc_html_e( 'روش پرداخت', 'almas-land' ); ?></h3>
	</header>

	<?php if ( WC()->cart && WC()->cart->needs_payment() ) : ?>
		<ul class="wc_payment_methods payment_methods methods">
			<?php
			if ( ! empty( $available_gateways ) ) {
				foreach ( $available_gateways as $gateway ) {
					wc_get_template( 'checkout/payment-method.php', array( 'gateway' => $gateway ) );
				}
			} else {
				echo '<li class="payment-method-empty">';
				wc_print_notice( apply_filters( 'woocommerce_no_available_payment_methods_message', WC()->customer->get_billing_country() ? esc_html__( 'در حال حاضر روش پرداخت فعالی برای آدرس شما وجود ندارد. لطفا با پشتیبانی تماس بگیرید.', 'almas-land' ) : esc_html__( 'برای نمایش روش‌های پرداخت، ابتدا اطلاعات بالا را کامل کنید.', 'almas-land' ) ), 'notice' ); // phpcs:ignore WooCommerce.Commenting.CommentHooks.MissingHookComment
				echo '</li>';
			}
			?>
		</ul>
	<?php endif; ?>

	<div class="form-row place-order">
		<noscript>
			<?php
			echo wp_kses_post(
				sprintf(
					__( 'جاوااسکریپت مرورگر شما غیرفعال است. لطفا قبل از ثبت سفارش روی %1$sبروزرسانی جمع کل%2$s بزنید تا مبلغ نهایی درست محاسبه شود.', 'almas-land' ),
					'<em>',
					'</em>'
				)
			);
			?>
			<br><button type="submit" class="button alt<?php echo esc_attr( wc_wp_theme_get_element_class_name( 'button' ) ? ' ' . wc_wp_theme_get_element_class_name( 'button' ) : '' ); ?>" name="woocommerce_checkout_update_totals" value="<?php esc_attr_e( 'بروزرسانی جمع کل', 'almas-land' ); ?>"><?php esc_html_e( 'بروزرسانی جمع کل', 'almas-land' ); ?></button>
		</noscript>

		<?php wc_get_template( 'checkout/terms.php' ); ?>

		<?php do_action( 'woocommerce_review_order_before_submit' ); ?>

		<?php echo apply_filters( 'woocommerce_order_button_html', '<button type="submit" class="button alt btn btn--primary btn--block' . esc_attr( wc_wp_theme_get_element_class_name( 'button' ) ? ' ' . wc_wp_theme_get_element_class_name( 'button' ) : '' ) . '" name="woocommerce_checkout_place_order" id="place_order" value="' . esc_attr( $order_button_text ) . '" data-value="' . esc_attr( $order_button_text ) . '">' . esc_html( $order_button_text ) . '</button>' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>

		<?php do_action( 'woocommerce_review_order_after_submit' ); ?>

		<?php wp_nonce_field( 'woocommerce-process_checkout', 'woocommerce-process-checkout-nonce' ); ?>
	</div>
</section>
<?php
if ( ! wp_doing_ajax() ) {
	do_action( 'woocommerce_review_order_after_payment' );
}
