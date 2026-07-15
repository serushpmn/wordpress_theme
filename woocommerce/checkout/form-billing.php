<?php
/**
 * Checkout billing information form.
 *
 * @package AlmasLand
 */

defined( 'ABSPATH' ) || exit;
?>
<section class="woocommerce-billing-fields checkout-field-group checkout-field-group--compact" aria-labelledby="billing-fields-title">
	<header class="checkout-field-group__header checkout-field-group__header--compact">
		<span>۱</span>
		<h3 id="billing-fields-title">
			<?php
			if ( wc_ship_to_billing_address_only() && WC()->cart->needs_shipping() ) {
				esc_html_e( 'اطلاعات خریدار و گیرنده', 'almas-land' );
			} else {
				esc_html_e( 'اطلاعات خریدار', 'almas-land' );
			}
			?>
		</h3>
	</header>

	<?php do_action( 'woocommerce_before_checkout_billing_form', $checkout ); ?>

	<div class="woocommerce-billing-fields__field-wrapper checkout-field-grid">
		<?php
		foreach ( $checkout->get_checkout_fields( 'billing' ) as $key => $field ) {
			woocommerce_form_field( $key, $field, $checkout->get_value( $key ) );
		}
		?>
	</div>

	<?php do_action( 'woocommerce_after_checkout_billing_form', $checkout ); ?>
</section>

<?php if ( ! is_user_logged_in() && $checkout->is_registration_enabled() ) : ?>
	<section class="woocommerce-account-fields checkout-field-group checkout-field-group--compact">
		<?php if ( ! $checkout->is_registration_required() ) : ?>
			<p class="form-row form-row-wide create-account">
				<label class="woocommerce-form__label woocommerce-form__label-for-checkbox checkbox">
					<input class="woocommerce-form__input woocommerce-form__input-checkbox input-checkbox" id="createaccount" <?php checked( ( true === $checkout->get_value( 'createaccount' ) || ( true === apply_filters( 'woocommerce_create_account_default_checked', false ) ) ), true ); ?> type="checkbox" name="createaccount" value="1">
					<span><?php esc_html_e( 'ساخت حساب کاربری برای پیگیری سفارش', 'almas-land' ); ?></span>
				</label>
			</p>
		<?php endif; ?>

		<?php do_action( 'woocommerce_before_checkout_registration_form', $checkout ); ?>

		<?php if ( $checkout->get_checkout_fields( 'account' ) ) : ?>
			<div class="create-account checkout-field-grid">
				<?php foreach ( $checkout->get_checkout_fields( 'account' ) as $key => $field ) : ?>
					<?php woocommerce_form_field( $key, $field, $checkout->get_value( $key ) ); ?>
				<?php endforeach; ?>
			</div>
		<?php endif; ?>

		<?php do_action( 'woocommerce_after_checkout_registration_form', $checkout ); ?>
	</section>
<?php endif; ?>
