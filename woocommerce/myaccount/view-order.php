<?php
/**
 * View Order.
 *
 * @package AlmasLand
 * @version 10.6.0
 */

defined( 'ABSPATH' ) || exit;

$notes = $order->get_customer_order_notes();
?>
<div class="account-card surface-panel">
	<h2><?php printf( esc_html__( 'سفارش #%s', 'almas-land' ), esc_html( almasland_persian_digits( $order->get_order_number() ) ) ); ?></h2>
	<p class="account-card__lead">
		<?php
		printf(
			esc_html__( 'ثبت‌شده در %s', 'almas-land' ),
			esc_html( almasland_persian_digits( wc_format_datetime( $order->get_date_created() ) ) )
		);
		?>
		<span class="status-label <?php echo esc_attr( almasland_order_status_label_class( $order->get_status() ) ); ?>"><?php echo esc_html( wc_get_order_status_name( $order->get_status() ) ); ?></span>
	</p>

	<?php if ( $notes ) : ?>
		<section class="account-order-notes" aria-labelledby="order-updates-title">
			<h3 id="order-updates-title"><?php esc_html_e( 'به‌روزرسانی‌های سفارش', 'almas-land' ); ?></h3>
			<ol class="account-order-notes__list">
				<?php foreach ( $notes as $note ) : ?>
					<li>
						<time datetime="<?php echo esc_attr( $note->comment_date ); ?>"><?php echo esc_html( almasland_persian_digits( date_i18n( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), strtotime( $note->comment_date ) ) ) ); ?></time>
						<div><?php echo wp_kses_post( wpautop( wptexturize( $note->comment_content ) ) ); ?></div>
					</li>
				<?php endforeach; ?>
			</ol>
		</section>
	<?php endif; ?>
</div>

<?php do_action( 'woocommerce_view_order', $order_id ); ?>
