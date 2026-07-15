<?php
/**
 * My Addresses.
 *
 * @package AlmasLand
 * @version 9.3.0
 */

defined( 'ABSPATH' ) || exit;

$customer_id = get_current_user_id();

if ( ! wc_ship_to_billing_address_only() && wc_shipping_enabled() ) {
	$get_addresses = apply_filters(
		'woocommerce_my_account_get_addresses',
		array(
			'billing'  => __( 'آدرس صورتحساب', 'almas-land' ),
			'shipping' => __( 'آدرس ارسال', 'almas-land' ),
		),
		$customer_id
	);
} else {
	$get_addresses = apply_filters(
		'woocommerce_my_account_get_addresses',
		array(
			'billing' => __( 'آدرس صورتحساب', 'almas-land' ),
		),
		$customer_id
	);
}
?>
<div class="account-card surface-panel">
	<h2><?php esc_html_e( 'آدرس‌های من', 'almas-land' ); ?></h2>
	<p class="account-card__lead"><?php echo esc_html__( 'این آدرس‌ها به‌صورت پیش‌فرض در صفحه تسویه حساب استفاده می‌شوند.', 'almas-land' ); ?></p>

	<div class="account-addresses">
		<?php foreach ( $get_addresses as $name => $address_title ) : ?>
			<?php $address = wc_get_account_formatted_address( $name ); ?>
			<article class="account-address-card">
				<header class="account-address-card__head">
					<h3><?php echo esc_html( $address_title ); ?></h3>
					<a class="text-link" href="<?php echo esc_url( wc_get_endpoint_url( 'edit-address', $name ) ); ?>">
						<?php echo $address ? esc_html__( 'ویرایش', 'almas-land' ) : esc_html__( 'افزودن آدرس', 'almas-land' ); ?>
					</a>
				</header>
				<address>
					<?php
					if ( $address ) {
						echo wp_kses_post( $address );
					} else {
						esc_html_e( 'هنوز این آدرس ثبت نشده است.', 'almas-land' );
					}
					do_action( 'woocommerce_my_account_after_my_address', $name );
					?>
				</address>
			</article>
		<?php endforeach; ?>
	</div>
</div>
