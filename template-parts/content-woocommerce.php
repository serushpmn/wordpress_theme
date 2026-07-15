<?php
/**
 * WooCommerce page content without article wrapper styles.
 *
 * @package AlmasLand
 */

$is_wc_system_page = function_exists( 'is_cart' ) && ( is_cart() || is_checkout() || is_account_page() );
?>
<div id="post-<?php the_ID(); ?>" <?php post_class( $is_wc_system_page ? 'wc-page-content wc-page-content--system' : 'wc-page-content' ); ?>>
	<?php if ( ! $is_wc_system_page ) : ?>
		<h1><?php echo esc_html( get_the_title() ); ?></h1>
	<?php endif; ?>
	<div class="entry-content">
		<?php
		if ( function_exists( 'is_cart' ) && is_cart() ) {
			echo do_shortcode( '[woocommerce_cart]' );
		} elseif ( function_exists( 'is_checkout' ) && is_checkout() ) {
			echo do_shortcode( '[woocommerce_checkout]' );
		} else {
			the_content();
		}

		wp_link_pages( almasland_wp_link_pages_args() );
		?>
	</div>
</div>
