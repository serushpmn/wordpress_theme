<?php
/**
 * 404 template.
 *
 * @package AlmasLand
 */

get_header();
?>
<div class="container">
	<section class="error-page">
		<div>
			<div class="error-page__code">404</div>
			<h1><?php esc_html_e( 'صفحه مورد نظر پیدا نشد', 'almas-land' ); ?></h1>
			<p><?php esc_html_e( 'ممکن است آدرس تغییر کرده باشد. از جستجو یا لینک‌های فروشگاه استفاده کنید.', 'almas-land' ); ?></p>
			<?php get_search_form(); ?>
			<p><a class="btn btn--primary" href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'بازگشت به خانه', 'almas-land' ); ?></a></p>
		</div>
	</section>
</div>
<?php
get_footer();
