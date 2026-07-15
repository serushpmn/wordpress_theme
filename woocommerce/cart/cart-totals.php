<?php
/**
 * Cart totals.
 *
 * @package AlmasLand
 * @version 2.3.6
 */

defined( 'ABSPATH' ) || exit;
?>
<section class="cart_totals cart-summary-block <?php echo esc_attr( WC()->customer->has_calculated_shipping() ? 'calculated_shipping' : '' ); ?>" aria-labelledby="cart-summary-title">
	<h2 id="cart-summary-title" class="cart-summary-block__title"><?php esc_html_e( 'جمع کل سبد خرید', 'almas-land' ); ?></h2>

	<aside class="cart-summary surface-panel">
		<?php do_action( 'woocommerce_before_cart_totals' ); ?>

		<table cellspacing="0" class="shop_table shop_table_responsive cart-summary__table">
			<tbody>
				<tr class="cart-subtotal">
					<th><?php esc_html_e( 'جمع جزء', 'almas-land' ); ?></th>
					<td data-title="<?php esc_attr_e( 'جمع جزء', 'almas-land' ); ?>">
						<?php wc_cart_totals_subtotal_html(); ?>
					</td>
				</tr>

				<?php foreach ( WC()->cart->get_coupons() as $code => $coupon ) : ?>
					<tr class="cart-discount coupon-<?php echo esc_attr( sanitize_title( $code ) ); ?>">
						<th><?php esc_html_e( 'تخفیف', 'almas-land' ); ?></th>
						<td data-title="<?php esc_attr_e( 'تخفیف', 'almas-land' ); ?>">
							<?php wc_cart_totals_coupon_html( $coupon ); ?>
						</td>
					</tr>
				<?php endforeach; ?>

				<?php if ( WC()->cart->needs_shipping() && WC()->cart->show_shipping() ) : ?>
					<?php do_action( 'woocommerce_cart_totals_before_shipping' ); ?>
					<?php wc_cart_totals_shipping_html(); ?>
					<?php do_action( 'woocommerce_cart_totals_after_shipping' ); ?>
				<?php elseif ( WC()->cart->needs_shipping() && 'yes' === get_option( 'woocommerce_enable_shipping_calc' ) ) : ?>
					<tr class="shipping">
						<th><?php esc_html_e( 'روش ارسال', 'almas-land' ); ?></th>
						<td data-title="<?php esc_attr_e( 'روش ارسال', 'almas-land' ); ?>"><?php woocommerce_shipping_calculator(); ?></td>
					</tr>
				<?php endif; ?>

				<?php foreach ( WC()->cart->get_fees() as $fee ) : ?>
					<tr class="fee">
						<th><?php echo esc_html( $fee->name ); ?></th>
						<td data-title="<?php echo esc_attr( $fee->name ); ?>">
							<?php wc_cart_totals_fee_html( $fee ); ?>
						</td>
					</tr>
				<?php endforeach; ?>

				<?php
				if ( wc_tax_enabled() && ! WC()->cart->display_prices_including_tax() ) {
					$taxable_address = WC()->customer->get_taxable_address();
					$estimated_text  = '';

					if ( WC()->customer->is_customer_outside_base() && ! WC()->customer->has_calculated_shipping() ) {
						$estimated_country = WC()->countries->estimated_for_prefix( $taxable_address[0] ) . ( WC()->countries->countries[ $taxable_address[0] ] ?? '' );
						$estimated_text    = sprintf(
							' <small>%s</small>',
							esc_html(
								sprintf(
									/* translators: %s: estimated tax country */
									__( '(برآورد برای %s)', 'almas-land' ),
									$estimated_country
								)
							)
						);
					}

					if ( 'itemized' === get_option( 'woocommerce_tax_total_display' ) ) {
						foreach ( WC()->cart->get_tax_totals() as $code => $tax ) { // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
							?>
							<tr class="tax-rate tax-rate-<?php echo esc_attr( sanitize_title( $code ) ); ?>">
								<th><?php echo esc_html( $tax->label ); ?><?php echo wp_kses_post( $estimated_text ); ?></th>
								<td data-title="<?php echo esc_attr( $tax->label ); ?>"><?php echo wp_kses_post( $tax->formatted_amount ); ?></td>
							</tr>
							<?php
						}
					} else {
						?>
						<tr class="tax-total">
							<th><?php echo esc_html( WC()->countries->tax_or_vat() ); ?><?php echo wp_kses_post( $estimated_text ); ?></th>
							<td data-title="<?php echo esc_attr( WC()->countries->tax_or_vat() ); ?>">
								<?php wc_cart_totals_taxes_total_html(); ?>
							</td>
						</tr>
						<?php
					}
				}
				?>

				<?php do_action( 'woocommerce_cart_totals_before_order_total' ); ?>

				<tr class="order-total">
					<th><?php esc_html_e( 'مجموع', 'almas-land' ); ?></th>
					<td data-title="<?php esc_attr_e( 'مجموع', 'almas-land' ); ?>">
						<?php wc_cart_totals_order_total_html(); ?>
					</td>
				</tr>

				<?php do_action( 'woocommerce_cart_totals_after_order_total' ); ?>
			</tbody>
		</table>

		<?php do_action( 'woocommerce_after_cart_totals' ); ?>
	</aside>

	<div class="wc-proceed-to-checkout cart-summary-block__checkout">
		<?php do_action( 'woocommerce_proceed_to_checkout' ); ?>
	</div>
</section>
