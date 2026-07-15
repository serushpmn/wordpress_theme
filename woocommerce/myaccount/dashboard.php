<?php
/**
 * My account dashboard.
 *
 * @package AlmasLand
 */

defined( 'ABSPATH' ) || exit;

$current_user    = wp_get_current_user();
$stats           = almasland_get_account_order_stats( $current_user->ID );
$recent_orders   = wc_get_orders(
	array(
		'customer' => $current_user->ID,
		'limit'    => 3,
		'orderby'  => 'date',
		'order'    => 'DESC',
		'status'   => array_keys( wc_get_order_statuses() ),
	)
);
$has_recent      = ! empty( $recent_orders );
?>
<div class="account-summary account-summary--stats">
	<a class="account-stat" href="<?php echo esc_url( wc_get_account_endpoint_url( 'orders' ) ); ?>">
		<strong><?php echo esc_html( almasland_persian_digits( $stats['active'] ) ); ?></strong>
		<span><?php esc_html_e( 'سفارش فعال', 'almas-land' ); ?></span>
	</a>
	<a class="account-stat" href="<?php echo esc_url( wc_get_account_endpoint_url( 'orders' ) ); ?>">
		<strong><?php echo esc_html( almasland_persian_digits( $stats['total'] ) ); ?></strong>
		<span><?php esc_html_e( 'کل سفارش‌ها', 'almas-land' ); ?></span>
	</a>
	<a class="account-stat" href="<?php echo esc_url( wc_get_account_endpoint_url( 'edit-address' ) ); ?>">
		<strong><?php echo esc_html( almasland_persian_digits( $stats['addresses'] ) ); ?></strong>
		<span><?php esc_html_e( 'آدرس ثبت‌شده', 'almas-land' ); ?></span>
	</a>
</div>

<div class="account-card surface-panel">
	<h2><?php printf( esc_html__( 'سلام %s', 'almas-land' ), esc_html( $current_user->display_name ) ); ?></h2>
	<p><?php esc_html_e( 'از این بخش می‌توانید سفارش‌ها، آدرس‌ها و اطلاعات حساب کاربری خود را مدیریت کنید.', 'almas-land' ); ?></p>
	<div class="account-summary account-summary--links">
		<a class="account-stat" href="<?php echo esc_url( wc_get_account_endpoint_url( 'orders' ) ); ?>"><strong><?php esc_html_e( 'سفارش‌ها', 'almas-land' ); ?></strong><span><?php esc_html_e( 'مشاهده وضعیت خریدها', 'almas-land' ); ?></span></a>
		<a class="account-stat" href="<?php echo esc_url( wc_get_account_endpoint_url( 'edit-address' ) ); ?>"><strong><?php esc_html_e( 'آدرس‌ها', 'almas-land' ); ?></strong><span><?php esc_html_e( 'مدیریت نشانی‌ها', 'almas-land' ); ?></span></a>
		<a class="account-stat" href="<?php echo esc_url( wc_get_account_endpoint_url( 'edit-account' ) ); ?>"><strong><?php esc_html_e( 'حساب', 'almas-land' ); ?></strong><span><?php esc_html_e( 'ویرایش اطلاعات', 'almas-land' ); ?></span></a>
	</div>
	<?php do_action( 'woocommerce_account_dashboard' ); ?>
</div>

<?php if ( $has_recent ) : ?>
	<section class="account-card surface-panel page-section" aria-labelledby="recent-orders-title">
		<div class="section-heading">
			<h2 id="recent-orders-title"><?php esc_html_e( 'آخرین سفارش‌ها', 'almas-land' ); ?></h2>
			<a href="<?php echo esc_url( wc_get_account_endpoint_url( 'orders' ) ); ?>"><?php esc_html_e( 'مشاهده همه', 'almas-land' ); ?></a>
		</div>
		<div class="table-wrapper">
			<table class="data-table woocommerce-orders-table">
				<thead>
					<tr>
						<th><?php esc_html_e( 'شماره سفارش', 'almas-land' ); ?></th>
						<th><?php esc_html_e( 'تاریخ', 'almas-land' ); ?></th>
						<th><?php esc_html_e( 'وضعیت', 'almas-land' ); ?></th>
						<th><?php esc_html_e( 'مبلغ', 'almas-land' ); ?></th>
						<th><?php esc_html_e( 'عملیات', 'almas-land' ); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ( $recent_orders as $order ) : ?>
						<tr>
							<td><a href="<?php echo esc_url( $order->get_view_order_url() ); ?>">#<?php echo esc_html( almasland_persian_digits( $order->get_order_number() ) ); ?></a></td>
							<td><?php echo esc_html( almasland_persian_digits( wc_format_datetime( $order->get_date_created() ) ) ); ?></td>
							<td><span class="status-label <?php echo esc_attr( almasland_order_status_label_class( $order->get_status() ) ); ?>"><?php echo esc_html( wc_get_order_status_name( $order->get_status() ) ); ?></span></td>
							<td><?php echo wp_kses_post( $order->get_formatted_order_total() ); ?></td>
							<td><a class="text-link" href="<?php echo esc_url( $order->get_view_order_url() ); ?>"><?php esc_html_e( 'جزئیات', 'almas-land' ); ?></a></td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
		</div>
	</section>
<?php endif; ?>
