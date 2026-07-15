<?php
/**
 * Order details.
 *
 * @package AlmasLand
 */

defined( 'ABSPATH' ) || exit;

if ( ! isset( $order ) || ! $order instanceof WC_Order ) {
	$order = wc_get_order( isset( $order_id ) ? $order_id : 0 );
}
if ( ! $order ) {
	return;
}
?>
<section class="order-details surface-panel ui-card">
	<h2><?php esc_html_e( 'جزئیات سفارش', 'almas-land' ); ?></h2>
	<div class="table-wrapper">
		<table class="data-table">
			<thead>
				<tr>
					<th><?php esc_html_e( 'محصول', 'almas-land' ); ?></th>
					<th><?php esc_html_e( 'جمع', 'almas-land' ); ?></th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ( $order->get_items() as $item_id => $item ) : ?>
					<tr>
						<td>
							<?php
							echo wp_kses_post( $item->get_name() );
							wc_display_item_meta( $item );
							?>
						</td>
						<td><?php echo wp_kses_post( $order->get_formatted_line_subtotal( $item ) ); ?></td>
					</tr>
				<?php endforeach; ?>
			</tbody>
			<tfoot>
				<?php foreach ( $order->get_order_item_totals() as $total ) : ?>
					<tr>
						<th><?php echo esc_html( $total['label'] ); ?></th>
						<td><?php echo wp_kses_post( $total['value'] ); ?></td>
					</tr>
				<?php endforeach; ?>
			</tfoot>
		</table>
	</div>
</section>
<?php do_action( 'woocommerce_order_details_after_order_table', $order ); ?>
