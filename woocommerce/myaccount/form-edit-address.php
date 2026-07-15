<?php
/**
 * Edit address form.
 *
 * @package AlmasLand
 */

defined( 'ABSPATH' ) || exit;

$page_title = ( 'billing' === $load_address ) ? __( 'آدرس صورتحساب', 'almas-land' ) : __( 'آدرس ارسال', 'almas-land' );
?>
<div class="account-card surface-panel">
<form method="post" class="form-panel woocommerce-EditAddressForm edit-address">
	<h2><?php echo esc_html( $page_title ); ?></h2>
	<?php do_action( "woocommerce_before_edit_address_form_{$load_address}" ); ?>
	<div class="form-grid">
		<?php foreach ( $address as $key => $field ) : ?>
			<?php woocommerce_form_field( $key, $field, wc_get_post_data_by_key( $key, $field['value'] ?? '' ) ); ?>
		<?php endforeach; ?>
	</div>
	<?php do_action( "woocommerce_after_edit_address_form_{$load_address}" ); ?>
	<button type="submit" class="btn btn--primary" name="save_address" value="<?php esc_attr_e( 'ذخیره آدرس', 'almas-land' ); ?>"><?php esc_html_e( 'ذخیره آدرس', 'almas-land' ); ?></button>
	<?php wp_nonce_field( 'woocommerce-edit_address', 'woocommerce-edit-address-nonce' ); ?>
	<input type="hidden" name="action" value="edit_address">
</form>
</div>
