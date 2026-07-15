<?php
/**
 * Orders table.
 *
 * @package AlmasLand
 */

defined( 'ABSPATH' ) || exit;

do_action( 'woocommerce_before_account_orders', $has_orders );
?>
<div class="account-card surface-panel">
	<h2><?php esc_html_e( 'سفارش‌ها', 'almas-land' ); ?></h2>
	<?php if ( $has_orders ) : ?>
		<div class="table-wrapper">
			<table class="data-table woocommerce-orders-table">
				<thead>
					<tr>
						<th><?php esc_html_e( 'سفارش', 'almas-land' ); ?></th>
						<th><?php esc_html_e( 'تاریخ', 'almas-land' ); ?></th>
						<th><?php esc_html_e( 'وضعیت', 'almas-land' ); ?></th>
						<th><?php esc_html_e( 'جمع کل', 'almas-land' ); ?></th>
						<th><?php esc_html_e( 'عملیات', 'almas-land' ); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ( $customer_orders->orders as $customer_order ) : ?>
						<?php $order = wc_get_order( $customer_order ); ?>
						<?php if ( ! $order ) : ?>
							<?php continue; ?>
						<?php endif; ?>
						<tr>
							<td><a href="<?php echo esc_url( $order->get_view_order_url() ); ?>">#<?php echo esc_html( almasland_persian_digits( $order->get_order_number() ) ); ?></a></td>
							<td><?php echo esc_html( almasland_persian_digits( wc_format_datetime( $order->get_date_created() ) ) ); ?></td>
							<td><span class="status-label <?php echo esc_attr( almasland_order_status_label_class( $order->get_status() ) ); ?>"><?php echo esc_html( wc_get_order_status_name( $order->get_status() ) ); ?></span></td>
							<td><?php echo wp_kses_post( $order->get_formatted_order_total() ); ?></td>
							<td>
								<?php foreach ( wc_get_account_orders_actions( $order ) as $action ) : ?>
									<a class="btn btn--ghost btn--small" href="<?php echo esc_url( $action['url'] ); ?>"><?php echo esc_html( $action['name'] ); ?></a>
								<?php endforeach; ?>
							</td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
		</div>
		<?php do_action( 'woocommerce_before_account_orders_pagination' ); ?>
		<?php almasland_account_orders_pagination( $current_page, $customer_orders->max_num_pages ); ?>
	<?php else : ?>
		<div class="empty-state">
			<p><?php esc_html_e( 'هنوز سفارشی ثبت نشده است.', 'almas-land' ); ?></p>
			<a class="btn btn--primary" href="<?php echo esc_url( wc_get_page_permalink( 'shop' ) ); ?>"><?php esc_html_e( 'رفتن به فروشگاه', 'almas-land' ); ?></a>
		</div>
	<?php endif; ?>
</div>
<?php do_action( 'woocommerce_after_account_orders', $has_orders ); ?>
