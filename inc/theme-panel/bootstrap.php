<?php
/**
 * Theme panel bootstrap.
 *
 * @package AlmasLand
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require ALMASLAND_DIR . '/inc/theme-panel/defaults.php';
require ALMASLAND_DIR . '/inc/theme-panel/settings.php';

// Field renderers and the settings screen are wp-admin only.
if ( is_admin() ) {
	require ALMASLAND_DIR . '/inc/theme-panel/fields.php';
	require ALMASLAND_DIR . '/inc/theme-panel/admin.php';
}
