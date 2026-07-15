<?php
/**
 * Proceed to checkout button.
 *
 * @package AlmasLand
 * @version 7.0.1
 */

defined( 'ABSPATH' ) || exit;
?>
<a href="<?php echo esc_url( wc_get_checkout_url() ); ?>" class="checkout-button btn btn--primary btn--block wc-forward<?php echo esc_attr( wc_wp_theme_get_element_class_name( 'button' ) ? ' ' . wc_wp_theme_get_element_class_name( 'button' ) : '' ); ?>">
	<?php esc_html_e( 'تایید و تکمیل سفارش', 'almas-land' ); ?>
</a>
