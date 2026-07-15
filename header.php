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
				<span><?php echo esc_html( almasland_get_option( 'topbar_text', 'ارسال سریع، ضمانت اصالت کالا و پشتیبانی تخصصی' ) ); ?></span>
				<a href="tel:<?php echo esc_attr( almasland_get_phone_tel() ); ?>"><?php echo esc_html( almasland_get_option( 'phone', '۰۲۱-۸۸۸۸۶۹۵۹' ) ); ?></a>
			</div>
		</div>
	<?php endif; ?>

	<div class="container header-main">
		<?php almasland_site_logo(); ?>

		<?php get_search_form(); ?>

		<div class="header-actions">
			<a class="header-action" href="<?php echo esc_url( class_exists( 'WooCommerce' ) ? wc_get_page_permalink( 'myaccount' ) : wp_login_url() ); ?>" aria-label="<?php esc_attr_e( 'حساب کاربری', 'almas-land' ); ?>">
				<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 12a4.5 4.5 0 1 0 0-9 4.5 4.5 0 0 0 0 9Zm0 2c-4.3 0-7.8 2.4-7.8 5.4V21h15.6v-1.6c0-3-3.5-5.4-7.8-5.4Z"/></svg>
				<span><?php esc_html_e( 'حساب', 'almas-land' ); ?></span>
			</a>
			<?php almasland_header_cart(); ?>
			<button class="menu-toggle" type="button" data-menu-toggle aria-controls="site-menu" aria-expanded="false" aria-label="<?php esc_attr_e( 'باز کردن منو', 'almas-land' ); ?>">
				<span></span><span></span><span></span>
			</button>
		</div>
		
	</div>
		<div class="container">
			<nav class="main-nav" id="site-menu" aria-label="<?php esc_attr_e( 'منوی اصلی', 'almas-land' ); ?>">
				<?php
				wp_nav_menu(
					array(
						'theme_location' => 'primary',
						'container'      => false,
						'menu_class'     => 'container main-nav__inner',
						'fallback_cb'    => 'almasland_primary_menu_fallback',
						'depth'          => 3,
					)
				);
				?>
			</nav>
		</div>
</header>

<main id="main"<?php echo almasland_get_main_class() ? ' class="' . esc_attr( almasland_get_main_class() ) . '"' : ''; ?>>
