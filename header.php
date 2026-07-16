<?php
/**
 * Header template.
 *
 * @package AlmasLand
 */

?><!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php echo esc_attr( get_bloginfo( 'charset' ) ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<a class="skip-link" href="#main"><?php esc_html_e( 'رفتن به محتوای اصلی', 'almas-land' ); ?></a>

<?php if ( almasland_is_notification_bar_visible() ) : ?>
	<?php $notify_bar = almasland_get_panel_settings()['notifications']; ?>
	<div class="theme-notify-bar" style="<?php echo esc_attr( 'background-color:' . $notify_bar['bar_color'] ); ?>">
		<div class="container theme-notify-bar__inner">
			<?php if ( ! empty( $notify_bar['bar_link'] ) ) : ?>
				<a href="<?php echo esc_url( $notify_bar['bar_link'] ); ?>"><?php echo esc_html( $notify_bar['bar_text'] ); ?></a>
			<?php else : ?>
				<span><?php echo esc_html( $notify_bar['bar_text'] ); ?></span>
			<?php endif; ?>
		</div>
	</div>
<?php endif; ?>

<header class="site-header">
	<?php if ( almasland_get_option( 'show_topbar', true ) ) : ?>
		<div class="header-top">
			<div class="container header-top__inner">
				<div class="header-top__item header-top__item--highlight">
					<span class="header-top__shield" aria-hidden="true">
						<svg viewBox="0 0 24 24" fill="none"><path d="M12 3.5 19 6.2v6.1c0 4.4-2.9 6.8-7 8.4-4.1-1.6-7-4-7-8.4V6.2L12 3.5Z" stroke="currentColor" stroke-width="1.7" stroke-linejoin="round"/></svg>
						<span>۷</span>
					</span>
					<span><?php esc_html_e( '۷ روز مهلت تست کالا', 'almas-land' ); ?></span>
				</div>

				<div class="header-top__meta">
					<div class="header-top__item">
						<span class="header-top__icon" aria-hidden="true">
							<svg viewBox="0 0 24 24" fill="none"><path d="M9 15.5v1.8a1.3 1.3 0 0 0 2.2.9l1.1-1.1" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/><path d="M7 12.5V10a5 5 0 0 1 10 0v2.5" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/><path d="M5 12.5h2M17 12.5h2" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/><path d="M6.2 12.5h11.6v4.3a1.6 1.6 0 0 1-1.6 1.6H7.8a1.6 1.6 0 0 1-1.6-1.6v-4.3Z" stroke="currentColor" stroke-width="1.7" stroke-linejoin="round"/></svg>
						</span>
						<span><?php echo esc_html( almasland_persian_digits( __( 'پشتیبانی ۲۴/۷ کشور', 'almas-land' ) ) ); ?></span>
					</div>
					<div class="header-top__item">
						<span class="header-top__icon" aria-hidden="true">
							<svg viewBox="0 0 24 24" fill="none"><path d="M12 21s7-5.3 7-11a7 7 0 1 0-14 0c0 5.7 7 11 7 11Z" stroke="currentColor" stroke-width="1.7"/><circle cx="12" cy="10" r="2.4" stroke="currentColor" stroke-width="1.7"/></svg>
						</span>
						<span><?php esc_html_e( 'مشاوره و خرید - سراسر کشور', 'almas-land' ); ?></span>
					</div>
				</div>
			</div>
		</div>
	<?php endif; ?>

	<div class="container header-main">
		<?php almasland_site_logo(); ?>

		<?php get_search_form(); ?>

		<div class="header-actions">
			<a class="header-action header-action--account" href="<?php echo esc_url( class_exists( 'WooCommerce' ) ? wc_get_page_permalink( 'myaccount' ) : wp_login_url() ); ?>">
				<svg viewBox="0 0 24 24" aria-hidden="true" fill="none"><circle cx="12" cy="8" r="3.4" stroke="currentColor" stroke-width="1.8"/><path d="M5.5 19c1.4-3.2 3.8-4.8 6.5-4.8s5.1 1.6 6.5 4.8" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>
				<span><?php esc_html_e( 'ورود / ثبت‌نام', 'almas-land' ); ?></span>
			</a>
			<?php almasland_header_cart(); ?>
			<button class="menu-toggle" type="button" data-menu-toggle aria-controls="site-menu" aria-expanded="false" aria-label="<?php esc_attr_e( 'باز کردن منو', 'almas-land' ); ?>">
				<span></span><span></span><span></span>
			</button>
		</div>
	</div>

	<div class="header-nav">
		<div class="container header-nav__inner">
			<?php
			$shop_url = class_exists( 'WooCommerce' ) ? wc_get_page_permalink( 'shop' ) : home_url( '/' );
			$nav_cats = function_exists( 'almasland_get_home_catalog_categories' ) ? almasland_get_home_catalog_categories( 8 ) : array();
			?>
			<div class="header-categories">
				<button
					type="button"
					class="header-categories__toggle"
					data-categories-toggle
					aria-expanded="false"
					aria-controls="header-categories-panel"
				>
					<svg viewBox="0 0 24 24" aria-hidden="true" fill="none"><path d="M4 7h16M4 12h16M4 17h16" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>
					<span><?php esc_html_e( 'دسته‌بندی محصولات', 'almas-land' ); ?></span>
				</button>

				<div class="header-categories__panel" id="header-categories-panel" hidden>
					<?php if ( ! empty( $nav_cats ) ) : ?>
						<?php foreach ( $nav_cats as $category ) : ?>
							<?php
							$term_link = get_term_link( $category );
							if ( is_wp_error( $term_link ) ) {
								continue;
							}
							?>
							<a href="<?php echo esc_url( $term_link ); ?>"><?php echo esc_html( $category->name ); ?></a>
						<?php endforeach; ?>
					<?php endif; ?>
					<a class="header-categories__all" href="<?php echo esc_url( $shop_url ); ?>"><?php esc_html_e( 'مشاهده همه محصولات', 'almas-land' ); ?></a>
				</div>
			</div>

			<nav class="main-nav" id="site-menu" aria-label="<?php esc_attr_e( 'منوی اصلی', 'almas-land' ); ?>">
				<?php
				wp_nav_menu(
					array(
						'theme_location' => 'primary',
						'container'      => false,
						'menu_class'     => 'main-nav__inner',
						'fallback_cb'    => 'almasland_primary_menu_fallback',
						'depth'          => 3,
					)
				);
				?>
			</nav>
		</div>
	</div>
</header>

<main id="main"<?php echo almasland_get_main_class() ? ' class="' . esc_attr( almasland_get_main_class() ) . '"' : ''; ?>>
