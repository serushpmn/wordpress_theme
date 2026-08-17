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
<div class="account-auth">
	<header class="account-auth__header">
		<p class="account-auth__eyebrow"><?php esc_html_e( 'حساب کاربری', 'almas-land' ); ?></p>
		<h1 id="account-login-title"><?php esc_html_e( 'ورود و ثبت‌نام', 'almas-land' ); ?></h1>
		<p><?php esc_html_e( 'برای پیگیری سفارش‌ها، مدیریت آدرس‌ها و اطلاعات حساب خود وارد شوید.', 'almas-land' ); ?></p>
	</header>

	<div class="account-auth__grid<?php echo $registration_enabled ? '' : ' account-auth__grid--single'; ?>">
		<form class="auth-card surface-panel woocommerce-form woocommerce-form-login login" method="post">
			<div class="auth-card__head">
				<span class="auth-card__icon" aria-hidden="true">
					<svg viewBox="0 0 24 24" fill="none"><circle cx="12" cy="8" r="3.4" stroke="currentColor" stroke-width="1.8"/><path d="M5.5 19c1.4-3.2 3.8-4.8 6.5-4.8s5.1 1.6 6.5 4.8" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>
				</span>
				<div>
					<h2><?php esc_html_e( 'ورود', 'almas-land' ); ?></h2>
					<p><?php esc_html_e( 'با حساب موجود وارد شوید', 'almas-land' ); ?></p>
				</div>
			</div>
			<?php do_action( 'woocommerce_login_form_start' ); ?>
			<label class="field"><?php esc_html_e( 'نام کاربری یا ایمیل', 'almas-land' ); ?><input type="text" name="username" autocomplete="username" value="<?php echo esc_attr( $posted_username ); ?>" required></label>
			<label class="field"><?php esc_html_e( 'رمز عبور', 'almas-land' ); ?><input type="password" name="password" autocomplete="current-password" required></label>
			<?php do_action( 'woocommerce_login_form' ); ?>
			<div class="auth-card__row">
				<label class="choice"><input name="rememberme" type="checkbox" value="forever"> <span><?php esc_html_e( 'مرا به خاطر بسپار', 'almas-land' ); ?></span></label>
				<a class="text-link auth-card__forgot" href="<?php echo esc_url( wp_lostpassword_url() ); ?>"><?php esc_html_e( 'فراموشی رمز', 'almas-land' ); ?></a>
			</div>
			<?php wp_nonce_field( 'woocommerce-login', 'woocommerce-login-nonce' ); ?>
			<button type="submit" class="btn btn--primary btn--block" name="login" value="<?php esc_attr_e( 'ورود', 'almas-land' ); ?>"><?php esc_html_e( 'ورود به حساب', 'almas-land' ); ?></button>
			<?php do_action( 'woocommerce_login_form_end' ); ?>
		</form>

		<?php if ( $registration_enabled ) : ?>
			<form method="post" class="auth-card surface-panel woocommerce-form woocommerce-form-register register">
				<div class="auth-card__head">
					<span class="auth-card__icon auth-card__icon--register" aria-hidden="true">
						<svg viewBox="0 0 24 24" fill="none"><path d="M12 12a4 4 0 1 0-4-4 4 4 0 0 0 4 4Zm-7 8a7 7 0 0 1 14 0" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/><path d="M19 8v6M22 11h-6" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>
					</span>
					<div>
						<h2><?php esc_html_e( 'ثبت‌نام', 'almas-land' ); ?></h2>
						<p><?php esc_html_e( 'حساب جدید بسازید', 'almas-land' ); ?></p>
					</div>
				</div>
				<?php do_action( 'woocommerce_register_form_start' ); ?>
				<?php if ( 'no' === get_option( 'woocommerce_registration_generate_username' ) ) : ?>
					<label class="field"><?php esc_html_e( 'نام کاربری', 'almas-land' ); ?><input type="text" name="username" autocomplete="username" value="<?php echo esc_attr( $posted_username ); ?>" required></label>
				<?php endif; ?>
				<label class="field"><?php esc_html_e( 'ایمیل', 'almas-land' ); ?><input type="email" name="email" autocomplete="email" value="<?php echo esc_attr( $posted_email ); ?>" required></label>
				<?php if ( 'no' === get_option( 'woocommerce_registration_generate_password' ) ) : ?>
					<label class="field"><?php esc_html_e( 'رمز عبور', 'almas-land' ); ?><input type="password" name="password" autocomplete="new-password" required></label>
				<?php else : ?>
					<p class="auth-card__hint"><?php esc_html_e( 'رمز عبور پس از ثبت‌نام برای شما ایمیل می‌شود.', 'almas-land' ); ?></p>
				<?php endif; ?>
				<?php do_action( 'woocommerce_register_form' ); ?>
				<?php wp_nonce_field( 'woocommerce-register', 'woocommerce-register-nonce' ); ?>
				<button type="submit" class="btn btn--primary btn--block" name="register" value="<?php esc_attr_e( 'ثبت‌نام', 'almas-land' ); ?>"><?php esc_html_e( 'ایجاد حساب', 'almas-land' ); ?></button>
				<?php do_action( 'woocommerce_register_form_end' ); ?>
			</form>
		<?php endif; ?>
	</div>
</div>
<?php do_action( 'woocommerce_after_customer_login_form' ); ?>
