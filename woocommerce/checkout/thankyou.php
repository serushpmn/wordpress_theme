<?php
/**
 * Checkout thank you / order received.
 *
 * @package AlmasLand
 */

defined( 'ABSPATH' ) || exit;

$shop_url    = function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'shop' ) : home_url( '/' );
$account_url = function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'myaccount' ) : home_url( '/' );
$is_failed   = $order && $order->has_status( 'failed' );
?>
<section class="order-received<?php echo $is_failed ? ' order-received--failed' : ' order-received--success'; ?>" aria-labelledby="order-received-title">
	<?php if ( $order ) : ?>

		<header class="order-received__hero">
			<div class="order-received__icon" aria-hidden="true">
				<?php if ( $is_failed ) : ?>
					<svg viewBox="0 0 24 24" fill="none"><circle cx="12" cy="12" r="9" stroke="currentColor" stroke-width="1.8"/><path d="M9 9l6 6M15 9l-6 6" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>
				<?php else : ?>
					<svg viewBox="0 0 24 24" fill="none"><circle cx="12" cy="12" r="9" stroke="currentColor" stroke-width="1.8"/><path d="M8 12.5l2.8 2.8L16.5 9" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
				<?php endif; ?>
			</div>

			<?php if ( $is_failed ) : ?>
				<p class="order-received__eyebrow"><?php esc_html_e( 'پرداخت تکمیل نشد', 'almas-land' ); ?></p>
				<h1 id="order-received-title"><?php esc_html_e( 'پرداخت ناموفق بود', 'almas-land' ); ?></h1>
				<p class="order-received__lead"><?php esc_html_e( 'اگر مبلغی از حساب شما کسر شده، معمولاً طبق قوانین درگاه بازگردانده می‌شود. می‌توانید دوباره پرداخت را انجام دهید.', 'almas-land' ); ?></p>
				<div class="order-received__actions">
					<a class="btn btn--primary" href="<?php echo esc_url( $order->get_checkout_payment_url() ); ?>"><?php esc_html_e( 'پرداخت دوباره', 'almas-land' ); ?></a>
					<a class="btn btn--ghost" href="<?php echo esc_url( $shop_url ); ?>"><?php esc_html_e( 'بازگشت به فروشگاه', 'almas-land' ); ?></a>
				</div>
			<?php else : ?>
				<p class="order-received__eyebrow"><?php esc_html_e( 'خرید با موفقیت ثبت شد', 'almas-land' ); ?></p>
				<h1 id="order-received-title"><?php esc_html_e( 'سفارش شما دریافت شد', 'almas-land' ); ?></h1>
				<p class="order-received__lead"><?php esc_html_e( 'از اعتماد شما سپاسگزاریم. جزئیات سفارش در ادامه آمده است و وضعیت آن را می‌توانید از حساب کاربری پیگیری کنید.', 'almas-land' ); ?></p>
				<div class="order-received__actions">
					<a class="btn btn--primary" href="<?php echo esc_url( $shop_url ); ?>"><?php esc_html_e( 'ادامه خرید', 'almas-land' ); ?></a>
					<?php if ( is_user_logged_in() ) : ?>
						<a class="btn btn--ghost" href="<?php echo esc_url( $account_url ); ?>"><?php esc_html_e( 'مشاهده سفارش‌ها', 'almas-land' ); ?></a>
					<?php endif; ?>
				</div>
			<?php endif; ?>
		</header>

		<div class="order-received__meta" role="list">
			<div class="order-received__meta-item" role="listitem">
				<span><?php esc_html_e( 'شماره سفارش', 'almas-land' ); ?></span>
				<strong>#<?php echo esc_html( almasland_persian_digits( $order->get_order_number() ) ); ?></strong>
			</div>
			<div class="order-received__meta-item" role="listitem">
				<span><?php esc_html_e( 'تاریخ', 'almas-land' ); ?></span>
				<strong><?php echo esc_html( almasland_persian_digits( wc_format_datetime( $order->get_date_created() ) ) ); ?></strong>
			</div>
			<div class="order-received__meta-item" role="listitem">
				<span><?php esc_html_e( 'مبلغ کل', 'almas-land' ); ?></span>
				<strong><?php echo wp_kses_post( $order->get_formatted_order_total() ); ?></strong>
			</div>
			<div class="order-received__meta-item" role="listitem">
				<span><?php esc_html_e( 'وضعیت', 'almas-land' ); ?></span>
				<strong>
					<span class="status-label <?php echo esc_attr( almasland_order_status_label_class( $order->get_status() ) ); ?>">
						<?php echo esc_html( wc_get_order_status_name( $order->get_status() ) ); ?>
					</span>
				</strong>
			</div>
			<?php if ( $order->get_payment_method_title() ) : ?>
				<div class="order-received__meta-item" role="listitem">
					<span><?php esc_html_e( 'روش پرداخت', 'almas-land' ); ?></span>
					<strong><?php echo esc_html( $order->get_payment_method_title() ); ?></strong>
				</div>
			<?php endif; ?>
			<?php if ( $order->get_billing_email() ) : ?>
				<div class="order-received__meta-item" role="listitem">
					<span><?php esc_html_e( 'ایمیل', 'almas-land' ); ?></span>
					<strong><?php echo esc_html( $order->get_billing_email() ); ?></strong>
				</div>
			<?php endif; ?>
		</div>

		<?php if ( ! $is_failed ) : ?>
			<div class="order-received__trust" aria-label="<?php esc_attr_e( 'اطمینان از خرید', 'almas-land' ); ?>">
				<span><?php esc_html_e( 'پرداخت امن', 'almas-land' ); ?></span>
				<span><?php esc_html_e( 'پیگیری آنلاین سفارش', 'almas-land' ); ?></span>
				<span><?php esc_html_e( 'پشتیبانی پس از خرید', 'almas-land' ); ?></span>
			</div>
		<?php endif; ?>

		<div class="order-received__body">
			<?php do_action( 'woocommerce_thankyou_' . $order->get_payment_method(), $order->get_id() ); ?>
			<?php do_action( 'woocommerce_thankyou', $order->get_id() ); ?>
		</div>

	<?php else : ?>

		<header class="order-received__hero">
			<div class="order-received__icon" aria-hidden="true">
				<svg viewBox="0 0 24 24" fill="none"><circle cx="12" cy="12" r="9" stroke="currentColor" stroke-width="1.8"/><path d="M8 12.5l2.8 2.8L16.5 9" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
			</div>
			<p class="order-received__eyebrow"><?php esc_html_e( 'سفارش دریافت شد', 'almas-land' ); ?></p>
			<h1 id="order-received-title"><?php esc_html_e( 'از خرید شما سپاسگزاریم', 'almas-land' ); ?></h1>
			<p class="order-received__lead"><?php esc_html_e( 'سفارش شما ثبت شده است. برای مشاهده جزئیات وارد حساب کاربری شوید.', 'almas-land' ); ?></p>
			<div class="order-received__actions">
				<a class="btn btn--primary" href="<?php echo esc_url( $shop_url ); ?>"><?php esc_html_e( 'بازگشت به فروشگاه', 'almas-land' ); ?></a>
				<a class="btn btn--ghost" href="<?php echo esc_url( $account_url ); ?>"><?php esc_html_e( 'حساب کاربری', 'almas-land' ); ?></a>
			</div>
		</header>

	<?php endif; ?>
</section>
