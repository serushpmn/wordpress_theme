<?php
/**
 * Payment methods.
 *
 * @package AlmasLand
 * @version 8.9.0
 */

defined( 'ABSPATH' ) || exit;

$saved_methods = wc_get_customer_saved_methods_list( get_current_user_id() );
$has_methods   = (bool) $saved_methods;

do_action( 'woocommerce_before_account_payment_methods', $has_methods );
?>
<div class="account-card surface-panel">
	<h2><?php esc_html_e( 'روش‌های پرداخت', 'almas-land' ); ?></h2>

	<?php if ( $has_methods ) : ?>
		<div class="table-wrapper">
			<table class="data-table woocommerce-MyAccount-paymentMethods shop_table shop_table_responsive account-payment-methods-table">
				<thead>
					<tr>
						<?php foreach ( wc_get_account_payment_methods_columns() as $column_id => $column_name ) : ?>
							<th><?php echo esc_html( $column_name ); ?></th>
						<?php endforeach; ?>
					</tr>
				</thead>
				<tbody>
					<?php foreach ( $saved_methods as $type => $methods ) : // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited ?>
						<?php foreach ( $methods as $method ) : ?>
							<tr class="payment-method<?php echo ! empty( $method['is_default'] ) ? ' default-payment-method' : ''; ?>">
								<?php foreach ( wc_get_account_payment_methods_columns() as $column_id => $column_name ) : ?>
									<td data-title="<?php echo esc_attr( $column_name ); ?>">
										<?php
										if ( has_action( 'woocommerce_account_payment_methods_column_' . $column_id ) ) {
											do_action( 'woocommerce_account_payment_methods_column_' . $column_id, $method );
										} elseif ( 'method' === $column_id ) {
											if ( ! empty( $method['method']['last4'] ) ) {
												echo esc_html(
													sprintf(
														/* translators: 1: card brand 2: last four digits */
														__( '%1$s منتهی به %2$s', 'almas-land' ),
														wc_get_credit_card_type_label( $method['method']['brand'] ),
														$method['method']['last4']
													)
												);
											} else {
												echo esc_html( wc_get_credit_card_type_label( $method['method']['brand'] ) );
											}
										} elseif ( 'expires' === $column_id ) {
											echo esc_html( almasland_persian_digits( $method['expires'] ) );
										} elseif ( 'actions' === $column_id ) {
											foreach ( $method['actions'] as $key => $action ) { // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
												echo '<a href="' . esc_url( $action['url'] ) . '" class="btn btn--ghost btn--small ' . esc_attr( sanitize_html_class( $key ) ) . '">' . esc_html( $action['name'] ) . '</a> ';
											}
										}
										?>
									</td>
								<?php endforeach; ?>
							</tr>
						<?php endforeach; ?>
					<?php endforeach; ?>
				</tbody>
			</table>
		</div>
	<?php else : ?>
		<div class="empty-state">
			<p><?php esc_html_e( 'روش پرداخت ذخیره‌شده‌ای یافت نشد.', 'almas-land' ); ?></p>
		</div>
	<?php endif; ?>

	<?php if ( WC()->payment_gateways->get_available_payment_gateways() ) : ?>
		<p style="margin-top: var(--space-5);">
			<a class="btn btn--primary" href="<?php echo esc_url( wc_get_endpoint_url( 'add-payment-method' ) ); ?>"><?php esc_html_e( 'افزودن روش پرداخت', 'almas-land' ); ?></a>
		</p>
	<?php endif; ?>
</div>
<?php do_action( 'woocommerce_after_account_payment_methods', $has_methods ); ?>
