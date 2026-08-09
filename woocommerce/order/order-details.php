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

$show_purchase_note = $order->has_status( apply_filters( 'woocommerce_purchase_note_order_statuses', array( 'completed', 'processing' ) ) );
?>
<section class="order-details" aria-labelledby="order-details-title">
	<header class="order-details__header">
		<h2 id="order-details-title"><?php esc_html_e( 'جزئیات سفارش', 'almas-land' ); ?></h2>
		<p><?php esc_html_e( 'اقلام خریداری‌شده و جمع‌بندی مبلغ', 'almas-land' ); ?></p>
	</header>

	<div class="order-details__items">
		<?php foreach ( $order->get_items() as $item_id => $item ) : ?>
			<?php
			if ( ! apply_filters( 'woocommerce_order_item_visible', true, $item ) ) {
				continue;
			}

			$product = $item->get_product();
			$qty     = $item->get_quantity();
			$thumb   = $product ? $product->get_image( 'thumbnail', array( 'class' => 'order-details__thumb' ) ) : '';
			?>
			<article class="<?php echo esc_attr( apply_filters( 'woocommerce_order_item_class', 'order-details__item', $item, $order ) ); ?>">
				<div class="order-details__media">
					<?php echo $thumb ? wp_kses_post( $thumb ) : '<span class="order-details__thumb order-details__thumb--placeholder" aria-hidden="true"></span>'; ?>
				</div>
				<div class="order-details__info">
					<strong class="order-details__name">
						<?php
						$is_visible = $product && $product->is_visible();
						$product_permalink = apply_filters( 'woocommerce_order_item_permalink', $is_visible ? $product->get_permalink( $item ) : '', $item, $order );

						if ( $product_permalink ) {
							echo wp_kses_post( sprintf( '<a href="%s">%s</a>', esc_url( $product_permalink ), $item->get_name() ) );
						} else {
							echo wp_kses_post( $item->get_name() );
						}
						?>
					</strong>
					<span class="order-details__qty"><?php echo esc_html( sprintf( __( 'تعداد: %s', 'almas-land' ), almasland_persian_digits( (string) $qty ) ) ); ?></span>
					<?php
					do_action( 'woocommerce_order_item_meta_start', $item_id, $item, $order, false );
					wc_display_item_meta( $item );
					do_action( 'woocommerce_order_item_meta_end', $item_id, $item, $order, false );
					?>
					<?php if ( $show_purchase_note && $product && $product->get_purchase_note() ) : ?>
						<div class="order-details__note"><?php echo wp_kses_post( wpautop( do_shortcode( $product->get_purchase_note() ) ) ); ?></div>
					<?php endif; ?>
				</div>
				<div class="order-details__total">
					<?php echo wp_kses_post( $order->get_formatted_line_subtotal( $item ) ); ?>
				</div>
			</article>
		<?php endforeach; ?>
	</div>

	<div class="order-details__totals">
		<?php foreach ( $order->get_order_item_totals() as $key => $total ) : ?>
			<div class="order-details__total-row<?php echo 'order_total' === $key ? ' is-grand' : ''; ?>">
				<span><?php echo esc_html( $total['label'] ); ?></span>
				<strong><?php echo wp_kses_post( $total['value'] ); ?></strong>
			</div>
		<?php endforeach; ?>
	</div>

	<?php if ( $order->get_customer_note() ) : ?>
		<div class="order-details__customer-note">
			<strong><?php esc_html_e( 'یادداشت سفارش', 'almas-land' ); ?></strong>
			<p><?php echo wp_kses_post( nl2br( wptexturize( $order->get_customer_note() ) ) ); ?></p>
		</div>
	<?php endif; ?>
</section>

<?php do_action( 'woocommerce_order_details_after_order_table', $order ); ?>

<?php
$billing_address  = $order->get_formatted_billing_address();
$shipping_address = $order->get_formatted_shipping_address();
$is_owner         = is_user_logged_in() && (int) $order->get_user_id() === (int) get_current_user_id();
$is_guest_thanks  = ! $order->get_user_id() && function_exists( 'is_wc_endpoint_url' ) && is_wc_endpoint_url( 'order-received' );

if ( ( $is_owner || $is_guest_thanks ) && ( $billing_address || $shipping_address ) ) :
	?>
	<section class="order-addresses" aria-label="<?php esc_attr_e( 'آدرس‌های سفارش', 'almas-land' ); ?>">
		<?php if ( $billing_address ) : ?>
			<div class="order-addresses__card">
				<h3><?php esc_html_e( 'آدرس صورتحساب', 'almas-land' ); ?></h3>
				<address><?php echo wp_kses_post( $billing_address ); ?></address>
				<?php if ( $order->get_billing_phone() ) : ?>
					<p><?php echo esc_html( almasland_persian_digits( $order->get_billing_phone() ) ); ?></p>
				<?php endif; ?>
			</div>
		<?php endif; ?>
		<?php if ( $shipping_address ) : ?>
			<div class="order-addresses__card">
				<h3><?php esc_html_e( 'آدرس ارسال', 'almas-land' ); ?></h3>
				<address><?php echo wp_kses_post( $shipping_address ); ?></address>
			</div>
		<?php endif; ?>
	</section>
<?php endif; ?>
