<?php
/**
 * Checkout thank you.
 *
 * @package AlmasLand
 */

defined( 'ABSPATH' ) || exit;
?>
<section class="empty-state surface-panel ui-card">
	<?php if ( $order ) : ?>
		<?php if ( $order->has_status( 'failed' ) ) : ?>
			<h1><?php esc_html_e( 'پرداخت ناموفق بود', 'almas-land' ); ?></h1>
			<p><?php esc_html_e( 'در صورت کسر وجه، مبلغ طبق قوانین درگاه بازگردانده می‌شود.', 'almas-land' ); ?></p>
			<a class="btn btn--primary" href="<?php echo esc_url( $order->get_checkout_payment_url() ); ?>"><?php esc_html_e( 'پرداخت دوباره', 'almas-land' ); ?></a>
		<?php else : ?>
			<h1><?php esc_html_e( 'سفارش شما ثبت شد', 'almas-land' ); ?></h1>
			<p><?php esc_html_e( 'از خرید شما سپاسگزاریم. خلاصه سفارش در ادامه آمده است.', 'almas-land' ); ?></p>
			<ul class="checkout-steps">
				<li><?php printf( esc_html__( 'شماره سفارش: %s', 'almas-land' ), esc_html( $order->get_order_number() ) ); ?></li>
				<li><?php printf( esc_html__( 'تاریخ: %s', 'almas-land' ), esc_html( wc_format_datetime( $order->get_date_created() ) ) ); ?></li>
				<li><?php printf( esc_html__( 'مبلغ: %s', 'almas-land' ), wp_kses_post( $order->get_formatted_order_total() ) ); ?></li>
				<?php if ( $order->get_payment_method_title() ) : ?>
					<li><?php printf( esc_html__( 'روش پرداخت: %s', 'almas-land' ), esc_html( $order->get_payment_method_title() ) ); ?></li>
				<?php endif; ?>
			</ul>
		<?php endif; ?>
		<?php do_action( 'woocommerce_thankyou_' . $order->get_payment_method(), $order->get_id() ); ?>
		<?php do_action( 'woocommerce_thankyou', $order->get_id() ); ?>
	<?php else : ?>
		<h1><?php esc_html_e( 'سفارش دریافت شد', 'almas-land' ); ?></h1>
		<p><?php esc_html_e( 'از خرید شما سپاسگزاریم.', 'almas-land' ); ?></p>
	<?php endif; ?>
</section>
