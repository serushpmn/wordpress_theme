<?php
/**
 * Homepage newsletter section.
 *
 * @package AlmasLand
 */
?>
<section class="container home-newsletter" aria-labelledby="home-newsletter">
	<div class="home-newsletter__inner surface-panel ui-card">
		<h2 id="home-newsletter"><?php esc_html_e( 'عضویت در خبرنامه', 'almas-land' ); ?></h2>
		<p><?php esc_html_e( 'برای دریافت تخفیف‌ها و اخبار جدید، ایمیل خود را وارد کنید.', 'almas-land' ); ?></p>
		<form class="home-newsletter__form" action="#" method="post" onsubmit="return false;">
			<label class="screen-reader-text" for="home-newsletter-email"><?php esc_html_e( 'ایمیل', 'almas-land' ); ?></label>
			<input id="home-newsletter-email" type="email" name="email" placeholder="<?php esc_attr_e( 'ایمیل شما', 'almas-land' ); ?>" required>
			<button class="btn btn--primary" type="submit"><?php esc_html_e( 'ثبت‌نام', 'almas-land' ); ?></button>
		</form>
	</div>
</section>
