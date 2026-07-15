<?php
/**
 * Login/register form.
 *
 * @package AlmasLand
 */

defined( 'ABSPATH' ) || exit;

do_action( 'woocommerce_before_customer_login_form' );

$registration_enabled = 'yes' === get_option( 'woocommerce_enable_myaccount_registration' );
$posted_username      = isset( $_POST['username'] ) ? sanitize_text_field( wp_unslash( $_POST['username'] ) ) : '';
$posted_email         = isset( $_POST['email'] ) ? sanitize_email( wp_unslash( $_POST['email'] ) ) : '';

almasland_account_login_hero();
?>
<div class="account-layout account-layout--auth">
	<form class="form-panel surface-panel woocommerce-form woocommerce-form-login login" method="post">
		<h2><?php esc_html_e( 'ورود', 'almas-land' ); ?></h2>
		<?php do_action( 'woocommerce_login_form_start' ); ?>
		<label class="field"><?php esc_html_e( 'نام کاربری یا ایمیل', 'almas-land' ); ?><input type="text" name="username" autocomplete="username" value="<?php echo esc_attr( $posted_username ); ?>"></label>
		<label class="field"><?php esc_html_e( 'رمز عبور', 'almas-land' ); ?><input type="password" name="password" autocomplete="current-password"></label>
		<?php do_action( 'woocommerce_login_form' ); ?>
		<label class="choice"><input name="rememberme" type="checkbox" value="forever"> <span><?php esc_html_e( 'مرا به خاطر بسپار', 'almas-land' ); ?></span></label>
		<?php wp_nonce_field( 'woocommerce-login', 'woocommerce-login-nonce' ); ?>
		<button type="submit" class="btn btn--primary" name="login" value="<?php esc_attr_e( 'ورود', 'almas-land' ); ?>"><?php esc_html_e( 'ورود', 'almas-land' ); ?></button>
		<a class="text-link" href="<?php echo esc_url( wp_lostpassword_url() ); ?>"><?php esc_html_e( 'فراموشی رمز عبور', 'almas-land' ); ?></a>
		<?php do_action( 'woocommerce_login_form_end' ); ?>
	</form>

	<?php if ( $registration_enabled ) : ?>
		<form method="post" class="form-panel surface-panel woocommerce-form woocommerce-form-register register">
			<h2><?php esc_html_e( 'ثبت‌نام', 'almas-land' ); ?></h2>
			<?php do_action( 'woocommerce_register_form_start' ); ?>
			<?php if ( 'no' === get_option( 'woocommerce_registration_generate_username' ) ) : ?>
				<label class="field"><?php esc_html_e( 'نام کاربری', 'almas-land' ); ?><input type="text" name="username" autocomplete="username" value="<?php echo esc_attr( $posted_username ); ?>"></label>
			<?php endif; ?>
			<label class="field"><?php esc_html_e( 'ایمیل', 'almas-land' ); ?><input type="email" name="email" autocomplete="email" value="<?php echo esc_attr( $posted_email ); ?>"></label>
			<?php if ( 'no' === get_option( 'woocommerce_registration_generate_password' ) ) : ?>
				<label class="field"><?php esc_html_e( 'رمز عبور', 'almas-land' ); ?><input type="password" name="password" autocomplete="new-password"></label>
			<?php endif; ?>
			<?php do_action( 'woocommerce_register_form' ); ?>
			<?php wp_nonce_field( 'woocommerce-register', 'woocommerce-register-nonce' ); ?>
			<button type="submit" class="btn btn--primary" name="register" value="<?php esc_attr_e( 'ثبت‌نام', 'almas-land' ); ?>"><?php esc_html_e( 'ثبت‌نام', 'almas-land' ); ?></button>
			<?php do_action( 'woocommerce_register_form_end' ); ?>
		</form>
	<?php endif; ?>
</div>
<?php do_action( 'woocommerce_after_customer_login_form' ); ?>
