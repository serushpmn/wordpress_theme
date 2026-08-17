<?php
/**
 * My account dashboard.
 *
 * @package AlmasLand
 */

defined( 'ABSPATH' ) || exit;

$current_user  = wp_get_current_user();
$stats         = almasland_get_account_order_stats( $current_user->ID );
$recent_orders = wc_get_orders(
	array(
		'customer' => $current_user->ID,
		'limit'    => 4,
		'orderby'  => 'date',
		'order'    => 'DESC',
		'status'   => array_keys( wc_get_order_statuses() ),
	)
);
$has_recent    = ! empty( $recent_orders );
$member_since  = mysql2date( 'Y/m/d', $current_user->user_registered );
?>
<div class="account-dashboard">
	<header class="account-dashboard__hero surface-panel">
		<div class="account-dashboard__profile">
			<span class="account-dashboard__avatar" aria-hidden="true"><?php echo esc_html( almasland_get_user_avatar_initial( $current_user ) ); ?></span>
			<div class="account-dashboard__intro">
				<p class="account-dashboard__eyebrow"><?php esc_html_e( 'داشبورد', 'almas-land' ); ?></p>
				<h2><?php printf( esc_html__( 'سلام، %s', 'almas-land' ), esc_html( $current_user->display_name ) ); ?></h2>
				<p class="account-dashboard__meta">
					<span><?php echo esc_html( $current_user->user_email ); ?></span>
					<span class="account-dashboard__dot" aria-hidden="true">•</span>
					<span><?php printf( esc_html__( 'عضو از %s', 'almas-land' ), esc_html( almasland_persian_digits( $member_since ) ) ); ?></span>
				</p>
			</div>
		</div>
	</header>

	<div class="account-dashboard__stats" aria-label="<?php esc_attr_e( 'آمار حساب', 'almas-land' ); ?>">
		<a class="account-dashboard__stat surface-panel" href="<?php echo esc_url( wc_get_account_endpoint_url( 'orders' ) ); ?>">
			<span class="account-dashboard__stat-icon account-dashboard__stat-icon--active" aria-hidden="true">
				<svg viewBox="0 0 24 24" fill="none"><path d="M12 8v4l3 2" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/><circle cx="12" cy="12" r="8" stroke="currentColor" stroke-width="1.8"/></svg>
			</span>
			<span class="account-dashboard__stat-value"><?php echo esc_html( almasland_persian_digits( $stats['active'] ) ); ?></span>
			<span class="account-dashboard__stat-label"><?php esc_html_e( 'سفارش فعال', 'almas-land' ); ?></span>
		</a>
		<a class="account-dashboard__stat surface-panel" href="<?php echo esc_url( wc_get_account_endpoint_url( 'orders' ) ); ?>">
			<span class="account-dashboard__stat-icon account-dashboard__stat-icon--orders" aria-hidden="true">
				<svg viewBox="0 0 24 24" fill="none"><path d="M7 4h10l1 3H6l1-3Zm-1 5h12l-1 11H8L6 9Z" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/></svg>
			</span>
			<span class="account-dashboard__stat-value"><?php echo esc_html( almasland_persian_digits( $stats['total'] ) ); ?></span>
			<span class="account-dashboard__stat-label"><?php esc_html_e( 'کل سفارش‌ها', 'almas-land' ); ?></span>
		</a>
		<a class="account-dashboard__stat surface-panel" href="<?php echo esc_url( wc_get_account_endpoint_url( 'edit-address' ) ); ?>">
			<span class="account-dashboard__stat-icon account-dashboard__stat-icon--address" aria-hidden="true">
				<svg viewBox="0 0 24 24" fill="none"><path d="M12 21s7-4.5 7-11a7 7 0 1 0-14 0c0 6.5 7 11 7 11Z" stroke="currentColor" stroke-width="1.8"/><circle cx="12" cy="10" r="2.5" stroke="currentColor" stroke-width="1.8"/></svg>
			</span>
			<span class="account-dashboard__stat-value"><?php echo esc_html( almasland_persian_digits( $stats['addresses'] ) ); ?></span>
			<span class="account-dashboard__stat-label"><?php esc_html_e( 'آدرس ثبت‌شده', 'almas-land' ); ?></span>
		</a>
	</div>

	<section class="account-dashboard__shortcuts" aria-labelledby="account-shortcuts-title">
		<h3 id="account-shortcuts-title"><?php esc_html_e( 'دسترسی سریع', 'almas-land' ); ?></h3>
		<div class="account-dashboard__shortcut-grid">
			<a class="account-dashboard__shortcut surface-panel" href="<?php echo esc_url( wc_get_account_endpoint_url( 'orders' ) ); ?>">
				<span class="account-dashboard__shortcut-icon" aria-hidden="true"><?php echo almasland_account_nav_icon( 'orders' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
				<span class="account-dashboard__shortcut-copy">
					<strong><?php esc_html_e( 'سفارش‌ها', 'almas-land' ); ?></strong>
					<span><?php esc_html_e( 'پیگیری وضعیت خریدها', 'almas-land' ); ?></span>
				</span>
			</a>
			<a class="account-dashboard__shortcut surface-panel" href="<?php echo esc_url( wc_get_account_endpoint_url( 'edit-address' ) ); ?>">
				<span class="account-dashboard__shortcut-icon" aria-hidden="true"><?php echo almasland_account_nav_icon( 'edit-address' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
				<span class="account-dashboard__shortcut-copy">
					<strong><?php esc_html_e( 'آدرس‌ها', 'almas-land' ); ?></strong>
					<span><?php esc_html_e( 'مدیریت نشانی‌ها', 'almas-land' ); ?></span>
				</span>
			</a>
			<a class="account-dashboard__shortcut surface-panel" href="<?php echo esc_url( wc_get_account_endpoint_url( 'edit-account' ) ); ?>">
				<span class="account-dashboard__shortcut-icon" aria-hidden="true"><?php echo almasland_account_nav_icon( 'edit-account' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
				<span class="account-dashboard__shortcut-copy">
					<strong><?php esc_html_e( 'جزئیات حساب', 'almas-land' ); ?></strong>
					<span><?php esc_html_e( 'ویرایش اطلاعات کاربری', 'almas-land' ); ?></span>
				</span>
			</a>
			<a class="account-dashboard__shortcut surface-panel" href="<?php echo esc_url( wc_get_page_permalink( 'shop' ) ); ?>">
				<span class="account-dashboard__shortcut-icon" aria-hidden="true">
					<svg viewBox="0 0 24 24" fill="none"><path d="M6 6h15l-1.5 9H7.5L6 6ZM6 6 5 3H2M9 20a1 1 0 1 0 0-2 1 1 0 0 0 0 2Zm8 0a1 1 0 1 0 0-2 1 1 0 0 0 0 2Z" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
				</span>
				<span class="account-dashboard__shortcut-copy">
					<strong><?php esc_html_e( 'فروشگاه', 'almas-land' ); ?></strong>
					<span><?php esc_html_e( 'ادامه خرید', 'almas-land' ); ?></span>
				</span>
			</a>
		</div>
	</section>

	<?php if ( $has_recent ) : ?>
		<section class="account-dashboard__recent" aria-labelledby="recent-orders-title">
			<div class="account-dashboard__recent-head">
				<h3 id="recent-orders-title"><?php esc_html_e( 'آخرین سفارش‌ها', 'almas-land' ); ?></h3>
				<a class="account-dashboard__recent-link" href="<?php echo esc_url( wc_get_account_endpoint_url( 'orders' ) ); ?>"><?php esc_html_e( 'مشاهده همه', 'almas-land' ); ?></a>
			</div>
			<div class="account-dashboard__order-list">
				<?php foreach ( $recent_orders as $order ) : ?>
					<article class="account-order-card surface-panel">
						<div class="account-order-card__main">
							<a class="account-order-card__number" href="<?php echo esc_url( $order->get_view_order_url() ); ?>">
								<?php printf( esc_html__( 'سفارش #%s', 'almas-land' ), esc_html( almasland_persian_digits( $order->get_order_number() ) ) ); ?>
							</a>
							<p class="account-order-card__date"><?php echo esc_html( almasland_persian_digits( wc_format_datetime( $order->get_date_created() ) ) ); ?></p>
						</div>
						<div class="account-order-card__meta">
							<span class="status-label <?php echo esc_attr( almasland_order_status_label_class( $order->get_status() ) ); ?>"><?php echo esc_html( wc_get_order_status_name( $order->get_status() ) ); ?></span>
							<strong class="account-order-card__total"><?php echo wp_kses_post( $order->get_formatted_order_total() ); ?></strong>
						</div>
						<a class="account-order-card__action text-link" href="<?php echo esc_url( $order->get_view_order_url() ); ?>"><?php esc_html_e( 'جزئیات', 'almas-land' ); ?></a>
					</article>
				<?php endforeach; ?>
			</div>
		</section>
	<?php else : ?>
		<section class="account-dashboard__empty surface-panel">
			<h3><?php esc_html_e( 'هنوز سفارشی ثبت نشده', 'almas-land' ); ?></h3>
			<p><?php esc_html_e( 'اولین خرید خود را انجام دهید و سفارش‌ها را از اینجا پیگیری کنید.', 'almas-land' ); ?></p>
			<a class="btn btn--primary" href="<?php echo esc_url( wc_get_page_permalink( 'shop' ) ); ?>"><?php esc_html_e( 'رفتن به فروشگاه', 'almas-land' ); ?></a>
		</section>
	<?php endif; ?>

	<?php do_action( 'woocommerce_account_dashboard' ); ?>
</div>
