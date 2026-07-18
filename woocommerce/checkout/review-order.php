<?php
/**
 * Review order.
 *
 * The outer wrapper MUST use class `woocommerce-checkout-review-order-table`
 * so WooCommerce AJAX replaceWith() swaps the full block (not only the totals table).
 *
 * @package AlmasLand
 * @see WC_AJAX::update_order_review()
 */

defined( 'ABSPATH' ) || exit;
?>
<div class="checkout-review-order woocommerce-checkout-review-order-table">
	<ul class="mini-order-list checkout-review-order__items" aria-label="<?php esc_attr_e( 'اقلام سفارش', 'almas-land' ); ?>">
		<?php
		do_action( 'woocommerce_review_order_before_cart_contents' );

		foreach ( WC()->cart->get_cart() as $cart_item_key => $cart_item ) {
			$_product = apply_filters( 'woocommerce_cart_item_product', $cart_item['data'], $cart_item, $cart_item_key );

			if ( ! $_product || ! $_product->exists() || $cart_item['quantity'] <= 0 ) {
				continue;
			}
			?>
			<li>
				<div class="mini-order-list__image">
					<?php echo wp_kses_post( apply_filters( 'woocommerce_cart_item_thumbnail', $_product->get_image( 'woocommerce_thumbnail' ), $cart_item, $cart_item_key ) ); ?>
				</div>
				<div class="mini-order-list__content">
					<strong><?php echo wp_kses_post( apply_filters( 'woocommerce_cart_item_name', $_product->get_name(), $cart_item, $cart_item_key ) ); ?></strong>
					<span><?php echo esc_html( almasland_persian_digits( $cart_item['quantity'] ) ); ?> <?php esc_html_e( 'عدد', 'almas-land' ); ?></span>
				</div>
				<span class="mini-order-list__price"><?php echo wp_kses_post( apply_filters( 'woocommerce_cart_item_subtotal', WC()->cart->get_product_subtotal( $_product, $cart_item['quantity'] ), $cart_item, $cart_item_key ) ); ?></span>
			</li>
			<?php
		}

		do_action( 'woocommerce_review_order_after_cart_contents' );
		?>
	</ul>

	<table class="shop_table checkout-totals-table">
		<tbody>
			<tr class="cart-subtotal">
				<th><?php esc_html_e( 'جمع کالاها', 'almas-land' ); ?></th>
				<td><?php wc_cart_totals_subtotal_html(); ?></td>
			</tr>

			<?php foreach ( WC()->cart->get_coupons() as $code => $coupon ) : ?>
				<tr class="cart-discount coupon-<?php echo esc_attr( sanitize_title( $code ) ); ?>">
					<th><?php echo esc_html( sprintf( __( 'تخفیف: %s', 'almas-land' ), $code ) ); ?></th>
					<td><?php wc_cart_totals_coupon_html( $coupon ); ?></td>
				</tr>
			<?php endforeach; ?>

			<?php if ( WC()->cart->needs_shipping() && WC()->cart->show_shipping() ) : ?>
				<?php do_action( 'woocommerce_review_order_before_shipping' ); ?>
				<?php wc_cart_totals_shipping_html(); ?>
				<?php do_action( 'woocommerce_review_order_after_shipping' ); ?>
			<?php endif; ?>

			<?php foreach ( WC()->cart->get_fees() as $fee ) : ?>
				<tr class="fee">
					<th><?php echo esc_html( $fee->name ); ?></th>
					<td><?php wc_cart_totals_fee_html( $fee ); ?></td>
				</tr>
			<?php endforeach; ?>

			<?php if ( wc_tax_enabled() && ! WC()->cart->display_prices_including_tax() ) : ?>
				<?php if ( 'itemized' === get_option( 'woocommerce_tax_total_display' ) ) : ?>
					<?php foreach ( WC()->cart->get_tax_totals() as $code => $tax ) : // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited ?>
						<tr class="tax-rate tax-rate-<?php echo esc_attr( sanitize_title( $code ) ); ?>">
							<th><?php echo esc_html( $tax->label ); ?></th>
							<td><?php echo wp_kses_post( $tax->formatted_amount ); ?></td>
						</tr>
					<?php endforeach; ?>
				<?php else : ?>
					<tr class="tax-total">
						<th><?php esc_html_e( 'مالیات', 'almas-land' ); ?></th>
						<td><?php wc_cart_totals_taxes_total_html(); ?></td>
					</tr>
				<?php endif; ?>
			<?php endif; ?>

			<?php do_action( 'woocommerce_review_order_before_order_total' ); ?>

			<tr class="order-total">
				<th><?php esc_html_e( 'مبلغ قابل پرداخت', 'almas-land' ); ?></th>
				<td><?php wc_cart_totals_order_total_html(); ?></td>
			</tr>

			<?php do_action( 'woocommerce_review_order_after_order_total' ); ?>
		</tbody>
	</table>

	<?php get_template_part( 'template-parts/checkout/trust-badges' ); ?>
</div>
