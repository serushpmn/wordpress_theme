<?php
/**
 * Blog archive hero.
 *
 * @package AlmasLand
 *
 * @var array $args {
 *     @type string $title
 *     @type string $description
 *     @type bool   $show_chips
 * }
 */

$args        = isset( $args ) && is_array( $args ) ? $args : array();
$title       = isset( $args['title'] ) ? (string) $args['title'] : __( 'مقالات', 'almas-land' );
$description = isset( $args['description'] ) ? (string) $args['description'] : '';
$show_chips  = array_key_exists( 'show_chips', $args ) ? (bool) $args['show_chips'] : true;
?>
<header class="blog-hero">
	<div class="blog-hero__glow" aria-hidden="true"></div>
	<div class="blog-hero__copy">
		<p class="blog-hero__eyebrow"><?php esc_html_e( 'مجله الماس لند', 'almas-land' ); ?></p>
		<h1 class="blog-hero__title"><?php echo esc_html( wp_strip_all_tags( $title ) ); ?></h1>
		<?php if ( $description ) : ?>
			<div class="blog-hero__text"><?php echo wp_kses_post( $description ); ?></div>
		<?php else : ?>
			<p class="blog-hero__text"><?php esc_html_e( 'راهنماها، نقد و بررسی و نکات کاربردی برای خرید هوشمند تجهیزات دیجیتال.', 'almas-land' ); ?></p>
		<?php endif; ?>
		<?php if ( $show_chips ) : ?>
			<?php almasland_blog_category_chips( 10 ); ?>
		<?php endif; ?>
	</div>
</header>
