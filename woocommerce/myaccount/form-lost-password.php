<?php
/**
 * Lost password form.
 *
 * @package AlmasLand
 * @version 9.2.0
 */

defined( 'ABSPATH' ) || exit;

do_action( 'woocommerce_before_lost_password_form' );
?>
<div class="account-card surface-panel account-card--narrow">
	<h2><?php esc_html_e( 'بازیابی رمز عبور', 'almas-land' ); ?></h2>
	<p class="account-card__lead"><?php echo esc_html__( 'نام کاربری یا ایمیل خود را وارد کنید. لینک ساخت رمز جدید برای شما ایمیل می‌شود.', 'almas-land' ); ?></p>

	<form method="post" class="woocommerce-ResetPassword lost_reset_password form-panel">
		<p class="woocommerce-form-row form-row form-row-wide">
			<label for="user_login"><?php esc_html_e( 'نام کاربری یا ایمیل', 'almas-land' ); ?>&nbsp;<span class="required" aria-hidden="true">*</span></label>
			<input class="woocommerce-Input woocommerce-Input--text input-text" type="text" name="user_login" id="user_login" autocomplete="username" required aria-required="true">
		</p>

		<?php do_action( 'woocommerce_lostpassword_form' ); ?>

		<p class="woocommerce-form-row form-row">
			<input type="hidden" name="wc_reset_password" value="true">
			<button type="submit" class="btn btn--primary" value="<?php esc_attr_e( 'بازیابی رمز', 'almas-land' ); ?>"><?php esc_html_e( 'بازیابی رمز', 'almas-land' ); ?></button>
		</p>

		<?php wp_nonce_field( 'lost_password', 'woocommerce-lost-password-nonce' ); ?>
	</form>

	<p><a class="text-link" href="<?php echo esc_url( wc_get_page_permalink( 'myaccount' ) ); ?>"><?php esc_html_e( 'بازگشت به ورود', 'almas-land' ); ?></a></p>
</div>
<?php
do_action( 'woocommerce_after_lost_password_form' );
