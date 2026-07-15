<?php
/**
 * Checkout shipping information form.
 *
 * @package AlmasLand
 */

defined( 'ABSPATH' ) || exit;
?>
<?php if ( true === WC()->cart->needs_shipping_address() ) : ?>
	<section class="woocommerce-shipping-fields checkout-field-group checkout-field-group--compact" aria-labelledby="shipping-fields-title">
		<header class="checkout-field-group__header checkout-field-group__header--compact">
			<span>۲</span>
			<h3 id="shipping-fields-title"><?php esc_html_e( 'آدرس ارسال', 'almas-land' ); ?></h3>
		</header>

		<h3 id="ship-to-different-address" class="ship-to-different-address">
			<label class="woocommerce-form__label woocommerce-form__label-for-checkbox checkbox">
				<input id="ship-to-different-address-checkbox" class="woocommerce-form__input woocommerce-form__input-checkbox input-checkbox" <?php checked( apply_filters( 'woocommerce_ship_to_different_address_checked', 'shipping' === get_option( 'woocommerce_ship_to_destination' ) ? 1 : 0 ), 1 ); ?> type="checkbox" name="ship_to_different_address" value="1">
				<span><?php esc_html_e( 'ارسال به آدرس دیگر', 'almas-land' ); ?></span>
			</label>
		</h3>

		<div class="shipping_address">
			<?php do_action( 'woocommerce_before_checkout_shipping_form', $checkout ); ?>
			<div class="woocommerce-shipping-fields__field-wrapper checkout-field-grid">
				<?php
				foreach ( $checkout->get_checkout_fields( 'shipping' ) as $key => $field ) {
					woocommerce_form_field( $key, $field, $checkout->get_value( $key ) );
				}
				?>
			</div>
			<?php do_action( 'woocommerce_after_checkout_shipping_form', $checkout ); ?>
		</div>
	</section>
<?php endif; ?>

<?php if ( apply_filters( 'woocommerce_enable_order_notes_field', 'yes' === get_option( 'woocommerce_enable_order_comments', 'yes' ) ) ) : ?>
	<section class="woocommerce-additional-fields checkout-field-group checkout-field-group--compact">
		<header class="checkout-field-group__header checkout-field-group__header--compact">
			<span>۳</span>
			<h3><?php esc_html_e( 'توضیحات سفارش', 'almas-land' ); ?></h3>
		</header>

		<?php do_action( 'woocommerce_before_order_notes', $checkout ); ?>

		<div class="woocommerce-additional-fields__field-wrapper checkout-field-grid">
			<?php foreach ( $checkout->get_checkout_fields( 'order' ) as $key => $field ) : ?>
				<?php woocommerce_form_field( $key, $field, $checkout->get_value( $key ) ); ?>
			<?php endforeach; ?>
		</div>

		<?php do_action( 'woocommerce_after_order_notes', $checkout ); ?>
	</section>
<?php endif; ?>
