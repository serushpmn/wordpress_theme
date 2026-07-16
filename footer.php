<?php
/**
 * Footer template.
 *
 * @package AlmasLand
 */

$phone_display = almasland_get_option( 'phone', '۰۲۱-۸۸۸۸۶۹۵۹' );
$phone_tel     = almasland_get_phone_tel();
$email         = almasland_get_option( 'email', 'info@almasland.com' );
$address       = almasland_get_option( 'address', 'تهران، میدان ولیعصر، خیابان برادران مظفر، پلاک ۱۷' );
$about         = almasland_get_option( 'footer_about', 'فروشگاه تخصصی تجهیزات دیجیتال کارکرده با ضمانت، تمرکز بر کیفیت و قیمت منصفانه.' );
$copyright     = almasland_get_option( 'footer_copyright', 'تمامی حقوق این وب‌سایت محفوظ است © ۱۴۰۳ الماس لند' );
$tagline       = get_bloginfo( 'description' );
if ( ! $tagline ) {
	$tagline = __( 'تجهیزات دیجیتال با ضمانت', 'almas-land' );
}

$footer_links = almasland_get_panel_settings()['footer']['links'] ?? array();
if ( ! empty( $footer_links ) ) {
	usort(
		$footer_links,
		static function ( $a, $b ) {
			return (int) ( $a['order'] ?? 0 ) <=> (int) ( $b['order'] ?? 0 );
		}
	);
}

$categories = function_exists( 'almasland_get_home_catalog_categories' )
	? almasland_get_home_catalog_categories( 6 )
	: array();

if ( empty( $categories ) && taxonomy_exists( 'product_cat' ) ) {
	$categories = get_terms(
		array(
			'taxonomy'   => 'product_cat',
			'parent'     => 0,
			'hide_empty' => true,
			'number'     => 6,
			'orderby'    => 'menu_order',
			'order'      => 'ASC',
		)
	);
	if ( is_wp_error( $categories ) ) {
		$categories = array();
	}
}

$default_links = array(
	array(
		'title' => __( 'درباره ما', 'almas-land' ),
		'url'   => home_url( '/about/' ),
	),
	array(
		'title' => __( 'تماس با ما', 'almas-land' ),
		'url'   => almasland_get_contact_url(),
	),
	array(
		'title' => __( 'سوالات متداول', 'almas-land' ),
		'url'   => home_url( '/faq/' ),
	),
	array(
		'title' => __( 'شرایط و قوانین', 'almas-land' ),
		'url'   => home_url( '/terms/' ),
	),
	array(
		'title' => __( 'حریم خصوصی', 'almas-land' ),
		'url'   => home_url( '/privacy/' ),
	),
);

$useful_links = array();
foreach ( $footer_links as $link ) {
	if ( empty( $link['title'] ) || empty( $link['url'] ) ) {
		continue;
	}
	$useful_links[] = $link;
}
if ( empty( $useful_links ) ) {
	$useful_links = $default_links;
}

$logo_id = absint( almasland_get_panel( 'identity', 'logo_dark', 0 ) );
if ( ! $logo_id ) {
	$logo_id = almasland_get_logo_id();
}
?>
</main>

<footer class="site-footer" id="footer">
	<div class="container footer-main">
		<section class="footer-about">
			<h3 class="footer-heading"><?php esc_html_e( 'درباره الماس لند', 'almas-land' ); ?></h3>
			<p class="footer-about__text"><?php echo wp_kses_post( $about ); ?></p>
			<div class="footer-socials" aria-label="<?php esc_attr_e( 'شبکه‌های اجتماعی', 'almas-land' ); ?>">
				<?php almasland_footer_social_links(); ?>
			</div>
		</section>

		<nav class="footer-nav" aria-label="<?php esc_attr_e( 'لینک‌های مفید', 'almas-land' ); ?>">
			<h3 class="footer-heading"><?php esc_html_e( 'لینک‌های مفید', 'almas-land' ); ?></h3>
			<div class="footer-links">
				<?php foreach ( $useful_links as $link ) : ?>
					<a href="<?php echo esc_url( $link['url'] ); ?>"><?php echo esc_html( $link['title'] ); ?></a>
				<?php endforeach; ?>
			</div>
		</nav>

		<nav class="footer-nav" aria-label="<?php esc_attr_e( 'دسته‌بندی‌ها', 'almas-land' ); ?>">
			<h3 class="footer-heading"><?php esc_html_e( 'دسته‌بندی‌ها', 'almas-land' ); ?></h3>
			<div class="footer-links">
				<?php if ( ! empty( $categories ) ) : ?>
					<?php foreach ( $categories as $category ) : ?>
						<?php
						$term_link = get_term_link( $category );
						if ( is_wp_error( $term_link ) ) {
							continue;
						}
						?>
						<a href="<?php echo esc_url( $term_link ); ?>"><?php echo esc_html( $category->name ); ?></a>
					<?php endforeach; ?>
				<?php elseif ( class_exists( 'WooCommerce' ) ) : ?>
					<a href="<?php echo esc_url( wc_get_page_permalink( 'shop' ) ); ?>"><?php esc_html_e( 'فروشگاه', 'almas-land' ); ?></a>
				<?php endif; ?>
			</div>
		</nav>

		<section class="footer-contact" aria-label="<?php esc_attr_e( 'اطلاعات تماس فروشگاه', 'almas-land' ); ?>">
			<h3 class="footer-heading"><?php esc_html_e( 'اطلاعات تماس', 'almas-land' ); ?></h3>
			<ul class="footer-contact-list">
				<li class="footer-contact-item">
					<span class="footer-contact-icon" aria-hidden="true">
						<svg viewBox="0 0 24 24" fill="none"><path d="M12 21s7-5.3 7-11a7 7 0 1 0-14 0c0 5.7 7 11 7 11Z" stroke="currentColor" stroke-width="1.8"/><circle cx="12" cy="10" r="2.5" stroke="currentColor" stroke-width="1.8"/></svg>
					</span>
					<span><?php echo esc_html( $address ); ?></span>
				</li>
				<li class="footer-contact-item">
					<span class="footer-contact-icon" aria-hidden="true">
						<svg viewBox="0 0 24 24" fill="none"><path d="M6.6 10.8c1.4 2.8 3.8 5.2 6.6 6.6l2.2-2.2c.3-.3.8-.4 1.2-.3 1.3.4 2.7.6 4.1.6.7 0 1.3.6 1.3 1.3v3.5c0 .7-.6 1.3-1.3 1.3C10.4 21.6 2.4 13.6 2.4 3.3 2.4 2.6 3 2 3.7 2h3.5c.7 0 1.3.6 1.3 1.3 0 1.4.2 2.8.6 4.1.1.4 0 .9-.3 1.2l-2.2 2.2Z" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/></svg>
					</span>
					<a href="tel:<?php echo esc_attr( $phone_tel ); ?>"><?php echo esc_html( $phone_display ); ?></a>
				</li>
				<li class="footer-contact-item">
					<span class="footer-contact-icon" aria-hidden="true">
						<svg viewBox="0 0 24 24" fill="none"><rect x="3.5" y="5.5" width="17" height="13" rx="2" stroke="currentColor" stroke-width="1.8"/><path d="m4.5 7.5 7.5 6 7.5-6" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
					</span>
					<a href="mailto:<?php echo esc_attr( sanitize_email( $email ) ); ?>"><?php echo esc_html( $email ); ?></a>
				</li>
			</ul>
		</section>

		<section class="footer-brand">
			<a class="footer-brand__logo" href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home">
				<?php if ( $logo_id ) : ?>
					<?php echo wp_get_attachment_image( $logo_id, 'medium', false, array( 'class' => 'footer-brand__image', 'alt' => get_bloginfo( 'name' ) ) ); ?>
				<?php else : ?>
					<span class="footer-brand__mark" aria-hidden="true"></span>
					<span class="footer-brand__name"><?php echo esc_html( get_bloginfo( 'name' ) ); ?></span>
				<?php endif; ?>
			</a>
			<p class="footer-brand__tagline"><?php echo esc_html( $tagline ); ?></p>
		</section>
	</div>

	<div class="footer-bottom">
		<div class="container footer-bottom__inner">
			<span><?php echo esc_html( $copyright ); ?></span>
			<span class="footer-bottom__credit">
				<?php
				echo esc_html(
					apply_filters(
						'almasland_footer_credit',
						__( 'طراحی و توسعه توسط', 'almas-land' )
					)
				);
				?>
			</span>
		</div>
	</div>
</footer>

<?php wp_footer(); ?>
</body>
</html>
