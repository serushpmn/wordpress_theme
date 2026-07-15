<?php
/**
 * Homepage Instagram section.
 *
 * @package AlmasLand
 */

$instagram = almasland_get_panel( 'social', 'instagram', '' );

if ( ! $instagram ) {
	return;
}
?>
<section class="container home-instagram" aria-labelledby="home-instagram">
	<div class="section-heading">
		<h2 id="home-instagram"><?php esc_html_e( 'ما را در اینستاگرام دنبال کنید', 'almas-land' ); ?></h2>
		<a href="<?php echo esc_url( $instagram ); ?>" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'مشاهده صفحه', 'almas-land' ); ?></a>
	</div>
	<p><?php esc_html_e( 'جدیدترین محصولات و پیشنهادها را در اینستاگرام الماس لند ببینید.', 'almas-land' ); ?></p>
</section>
