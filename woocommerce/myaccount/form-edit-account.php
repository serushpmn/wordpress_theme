<?php
/**
 * Edit account form.
 *
 * @package AlmasLand
 */

defined( 'ABSPATH' ) || exit;

$user = wp_get_current_user();

do_action( 'woocommerce_before_edit_account_form' );
?>
<div class="account-card surface-panel">
<form class="form-panel woocommerce-EditAccountForm edit-account" action="" method="post">
	<h2><?php esc_html_e( 'ویرایش حساب کاربری', 'almas-land' ); ?></h2>
	<?php do_action( 'woocommerce_edit_account_form_start' ); ?>
	<div class="form-grid">
		<label class="field"><?php esc_html_e( 'نام', 'almas-land' ); ?><input type="text" name="account_first_name" value="<?php echo esc_attr( $user->first_name ); ?>" autocomplete="given-name"></label>
		<label class="field"><?php esc_html_e( 'نام خانوادگی', 'almas-land' ); ?><input type="text" name="account_last_name" value="<?php echo esc_attr( $user->last_name ); ?>" autocomplete="family-name"></label>
		<label class="field"><?php esc_html_e( 'نام نمایشی', 'almas-land' ); ?><input type="text" name="account_display_name" value="<?php echo esc_attr( $user->display_name ); ?>"></label>
		<label class="field"><?php esc_html_e( 'ایمیل', 'almas-land' ); ?><input type="email" name="account_email" value="<?php echo esc_attr( $user->user_email ); ?>" autocomplete="email"></label>
		<label class="field"><?php esc_html_e( 'رمز فعلی', 'almas-land' ); ?><input type="password" name="password_current" autocomplete="off"></label>
		<label class="field"><?php esc_html_e( 'رمز جدید', 'almas-land' ); ?><input type="password" name="password_1" autocomplete="off"></label>
		<label class="field"><?php esc_html_e( 'تکرار رمز جدید', 'almas-land' ); ?><input type="password" name="password_2" autocomplete="off"></label>
	</div>
	<?php do_action( 'woocommerce_edit_account_form' ); ?>
	<?php wp_nonce_field( 'save_account_details', 'save-account-details-nonce' ); ?>
	<button type="submit" class="btn btn--primary" name="save_account_details" value="<?php esc_attr_e( 'ذخیره تغییرات', 'almas-land' ); ?>"><?php esc_html_e( 'ذخیره تغییرات', 'almas-land' ); ?></button>
	<input type="hidden" name="action" value="save_account_details">
	<?php do_action( 'woocommerce_edit_account_form_end' ); ?>
</form>
</div>
<?php do_action( 'woocommerce_after_edit_account_form' ); ?>
