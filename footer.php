<?php
/**
 * Footer template.
 *
 * @package AlmasLand
 */

?>
</main>

<footer class="site-footer" id="footer">
	<div class="container footer-services" aria-label="<?php esc_attr_e( 'مزیت‌های خرید', 'almas-land' ); ?>">
		<div><span></span><?php esc_html_e( 'ضمانت اصالت کالا', 'almas-land' ); ?></div>
		<div><span></span><?php esc_html_e( 'ارسال سریع', 'almas-land' ); ?></div>
		<div><span></span><?php esc_html_e( 'پشتیبانی تخصصی', 'almas-land' ); ?></div>
		<div><span></span><?php esc_html_e( 'پرداخت امن', 'almas-land' ); ?></div>
	</div>

	<div class="container footer-main">
		<section class="footer-brand">
			<h2><?php echo esc_html( get_bloginfo( 'name' ) ); ?></h2>
			<p><?php echo wp_kses_post( almasland_get_option( 'footer_about', 'فروشگاه تخصصی محصولات دیجیتال با تجربه خرید ساده، شفاف و مطمئن.' ) ); ?></p>
			<div class="footer-socials" aria-label="<?php esc_attr_e( 'شبکه‌های اجتماعی', 'almas-land' ); ?>">
				<?php almasland_social_links(); ?>
			</div>
		</section>

		<nav aria-label="<?php esc_attr_e( 'دسترسی سریع', 'almas-land' ); ?>">
			<h3><?php esc_html_e( 'دسترسی سریع', 'almas-land' ); ?></h3>
			<?php
			$footer_links = almasland_get_panel_settings()['footer']['links'];
			if ( ! empty( $footer_links ) ) :
				usort(
					$footer_links,
					static function ( $a, $b ) {
						return (int) ( $a['order'] ?? 0 ) <=> (int) ( $b['order'] ?? 0 );
					}
				);
				echo '<div class="footer-links">';
				foreach ( $footer_links as $link ) {
					if ( empty( $link['title'] ) || empty( $link['url'] ) ) {
						continue;
					}
					printf(
						'<a href="%1$s">%2$s</a>',
						esc_url( $link['url'] ),
						esc_html( $link['title'] )
					);
				}
				echo '</div>';
			else :
				wp_nav_menu(
					array(
						'theme_location' => 'footer',
						'container'      => false,
						'menu_class'     => 'footer-links',
						'fallback_cb'    => false,
						'depth'          => 1,
					)
				);
				if ( ! has_nav_menu( 'footer' ) ) :
					?>
					<a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'خانه', 'almas-land' ); ?></a>
					<?php if ( class_exists( 'WooCommerce' ) ) : ?>
						<a href="<?php echo esc_url( wc_get_page_permalink( 'shop' ) ); ?>"><?php esc_html_e( 'فروشگاه', 'almas-land' ); ?></a>
						<a href="<?php echo esc_url( wc_get_cart_url() ); ?>"><?php esc_html_e( 'سبد خرید', 'almas-land' ); ?></a>
					<?php endif; ?>
					<a href="<?php echo esc_url( almasland_get_contact_url() ); ?>"><?php esc_html_e( 'تماس با ما', 'almas-land' ); ?></a>
					<a href="<?php echo esc_url( class_exists( 'WooCommerce' ) ? wc_get_page_permalink( 'myaccount' ) : wp_login_url() ); ?>"><?php esc_html_e( 'حساب کاربری', 'almas-land' ); ?></a>
					<?php
				endif;
			endif;
			?>
		</nav>

		<section class="footer-contact" aria-label="<?php esc_attr_e( 'اطلاعات تماس فروشگاه', 'almas-land' ); ?>">
			<h3><?php esc_html_e( 'اطلاعات تماس', 'almas-land' ); ?></h3>
			<div class="footer-contact-item">
				<span class="footer-contact-label"><?php esc_html_e( 'شماره تماس', 'almas-land' ); ?></span>
				<a href="tel:<?php echo esc_attr( almasland_get_phone_tel() ); ?>"><?php echo esc_html( almasland_get_option( 'phone', '۰۲۱-۸۸۸۸۶۹۵۹' ) ); ?></a>
			</div>
			<div class="footer-contact-item">
				<span class="footer-contact-label"><?php esc_html_e( 'ایمیل', 'almas-land' ); ?></span>
				<a href="mailto:<?php echo esc_attr( sanitize_email( almasland_get_option( 'email', 'support@almasland.test' ) ) ); ?>"><?php echo esc_html( almasland_get_option( 'email', 'support@almasland.test' ) ); ?></a>
			</div>
			<div class="footer-contact-item">
				<span class="footer-contact-label"><?php esc_html_e( 'آدرس', 'almas-land' ); ?></span>
				<span><?php echo esc_html( almasland_get_option( 'address', 'تهران، خیابان ولیعصر، ساختمان الماس' ) ); ?></span>
			</div>
		</section>

		<section class="footer-info" aria-label="<?php esc_attr_e( 'اطلاعات بیشتر', 'almas-land' ); ?>">
			<h3><?php esc_html_e( 'شبکه‌های اجتماعی', 'almas-land' ); ?></h3>
			<div class="footer-socials footer-socials-inline" aria-label="<?php esc_attr_e( 'شبکه‌های اجتماعی', 'almas-land' ); ?>">
				<?php almasland_social_links(); ?>
			</div>
			<h3><?php esc_html_e( 'نماد اعتماد', 'almas-land' ); ?></h3>
			<div class="footer-trust-badges" aria-label="<?php esc_attr_e( 'نمادهای اعتماد', 'almas-land' ); ?>">
				<?php
				$footer_panel = almasland_get_panel_settings()['footer'];
				$badges       = array(
					$footer_panel['trust_badge_1'] ?? 0,
					$footer_panel['trust_badge_2'] ?? 0,
					$footer_panel['samandehi'] ?? 0,
				);
				foreach ( $badges as $badge_id ) {
					$badge_url = almasland_get_attachment_url( $badge_id, 'medium' );
					if ( ! $badge_url ) {
						continue;
					}
					printf( '<img src="%s" alt="%s" width="80" height="80" loading="lazy">', esc_url( $badge_url ), esc_attr__( 'اعتماد دیجیتال', 'almas-land' ) );
				}
				?>
			</div>
		</section>
	</div>

	<div class="container footer-bottom">
		<span><?php echo esc_html( almasland_get_option( 'footer_copyright', 'تمام حقوق برای الماس لند محفوظ است.' ) ); ?></span>
		<div class="footer-trust-badges" aria-label="<?php esc_attr_e( 'نمادهای اعتماد', 'almas-land' ); ?>">
			<?php
			$footer_panel = almasland_get_panel_settings()['footer'];
			$badges       = array(
				$footer_panel['trust_badge_1'] ?? 0,
				$footer_panel['trust_badge_2'] ?? 0,
				$footer_panel['samandehi'] ?? 0,
			);
			foreach ( $badges as $badge_id ) {
				$badge_url = almasland_get_attachment_url( $badge_id, 'medium' );
				if ( ! $badge_url ) {
					continue;
				}
				printf( '<img src="%s" alt="" width="80" height="80" loading="lazy">', esc_url( $badge_url ) );
			}
			?>
		</div>
	</div>
</footer>

<?php wp_footer(); ?>
</body>
</html>
