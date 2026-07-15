<?php
/**
 * My account wrapper.
 *
 * @package AlmasLand
 */

defined( 'ABSPATH' ) || exit;

almasland_account_page_hero();
?>
<div class="account-layout">
	<aside class="sidebar-panel surface-panel" aria-label="<?php esc_attr_e( 'منوی حساب کاربری', 'almas-land' ); ?>">
		<?php do_action( 'woocommerce_account_navigation' ); ?>
	</aside>
	<section class="account-content" aria-label="<?php esc_attr_e( 'محتوای حساب کاربری', 'almas-land' ); ?>">
		<?php do_action( 'woocommerce_account_content' ); ?>
	</section>
</div>
