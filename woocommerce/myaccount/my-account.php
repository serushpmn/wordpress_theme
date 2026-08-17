<?php
/**
 * My account wrapper.
 *
 * @package AlmasLand
 */

defined( 'ABSPATH' ) || exit;

almasland_account_page_hero();

$current_user = wp_get_current_user();
?>
<div class="account-layout">
	<aside class="account-sidebar surface-panel" aria-label="<?php esc_attr_e( 'منوی حساب کاربری', 'almas-land' ); ?>">
		<?php if ( is_user_logged_in() ) : ?>
			<div class="account-sidebar__user">
				<span class="account-sidebar__avatar" aria-hidden="true"><?php echo esc_html( almasland_get_user_avatar_initial( $current_user ) ); ?></span>
				<div class="account-sidebar__copy">
					<strong><?php echo esc_html( $current_user->display_name ); ?></strong>
					<span><?php echo esc_html( $current_user->user_email ); ?></span>
				</div>
			</div>
		<?php endif; ?>
		<?php do_action( 'woocommerce_account_navigation' ); ?>
	</aside>
	<section class="account-content" aria-label="<?php esc_attr_e( 'محتوای حساب کاربری', 'almas-land' ); ?>">
		<?php do_action( 'woocommerce_account_content' ); ?>
	</section>
</div>
