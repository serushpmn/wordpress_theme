<?php
/**
 * Single variation display (JS template).
 *
 * @package AlmasLand
 * @version 9.3.0
 */

defined( 'ABSPATH' ) || exit;
?>
<script type="text/template" id="tmpl-variation-template">
	<div class="woocommerce-variation-description">{{{ data.variation.variation_description }}}</div>
	<div class="woocommerce-variation-availability">{{{ data.variation.availability_html }}}</div>
</script>
<script type="text/template" id="tmpl-unavailable-variation-template">
	<p class="almas-variation-unavailable" role="alert"><?php esc_html_e( 'این ترکیب موجود نیست. لطفاً گزینه‌های دیگری انتخاب کنید.', 'almas-land' ); ?></p>
</script>
