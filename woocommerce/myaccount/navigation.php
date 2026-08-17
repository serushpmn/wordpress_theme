<?php
/**
 * My Account navigation.
 *
 * @package AlmasLand
 * @version 9.3.0
 */

defined( 'ABSPATH' ) || exit;

do_action( 'woocommerce_before_account_navigation' );
?>
<nav class="woocommerce-MyAccount-navigation account-nav" aria-label="<?php esc_attr_e( 'صفحات حساب کاربری', 'almas-land' ); ?>">
	<ul class="account-nav__list">
		<?php foreach ( wc_get_account_menu_items() as $endpoint => $label ) : ?>
			<li class="account-nav__item <?php echo esc_attr( wc_get_account_menu_item_classes( $endpoint ) ); ?>">
				<a href="<?php echo esc_url( wc_get_account_endpoint_url( $endpoint ) ); ?>" <?php echo wc_is_current_account_menu_item( $endpoint ) ? 'aria-current="page"' : ''; ?>>
					<span class="account-nav__icon" aria-hidden="true"><?php echo almasland_account_nav_icon( $endpoint ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
					<span class="account-nav__label"><?php echo esc_html( $label ); ?></span>
				</a>
			</li>
		<?php endforeach; ?>
	</ul>
</nav>
<?php
do_action( 'woocommerce_after_account_navigation' );
