<?php
/**
 * Admin field helpers for theme panel.
 *
 * @package AlmasLand
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Open panel form.
 *
 * @param string $page Page slug.
 */
function almasland_panel_form_open( $page ) {
	echo '<form class="almasland-panel-form" method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '">';
	wp_nonce_field( 'almasland_save_panel_' . $page, 'almasland_panel_nonce' );
	echo '<input type="hidden" name="action" value="almasland_save_theme_panel">';
	echo '<input type="hidden" name="panel_page" value="' . esc_attr( $page ) . '">';
}

/**
 * Close panel form with submit.
 */
function almasland_panel_form_close() {
	submit_button( __( 'ذخیره تغییرات', 'almas-land' ) );
	echo '</form>';
}

/**
 * Text field.
 *
 * @param string $name  Field name.
 * @param string $label Label.
 * @param mixed  $value Value.
 * @param string $type  Input type.
 */
function almasland_panel_field_text( $name, $label, $value, $type = 'text' ) {
	echo '<p class="almasland-field">';
	echo '<label for="' . esc_attr( $name ) . '"><strong>' . esc_html( $label ) . '</strong></label><br>';
	printf(
		'<input type="%1$s" class="regular-text" id="%2$s" name="%2$s" value="%3$s">',
		esc_attr( $type ),
		esc_attr( $name ),
		esc_attr( $value )
	);
	echo '</p>';
}

/**
 * Textarea field.
 *
 * @param string $name  Field name.
 * @param string $label Label.
 * @param mixed  $value Value.
 * @param int    $rows  Rows.
 */
function almasland_panel_field_textarea( $name, $label, $value, $rows = 4 ) {
	echo '<p class="almasland-field">';
	echo '<label for="' . esc_attr( $name ) . '"><strong>' . esc_html( $label ) . '</strong></label><br>';
	printf(
		'<textarea class="large-text" id="%1$s" name="%1$s" rows="%2$d">%3$s</textarea>',
		esc_attr( $name ),
		(int) $rows,
		esc_textarea( $value )
	);
	echo '</p>';
}

/**
 * Color field.
 *
 * @param string $name  Field name.
 * @param string $label Label.
 * @param mixed  $value Value.
 */
function almasland_panel_field_color( $name, $label, $value ) {
	echo '<p class="almasland-field almasland-field--color">';
	echo '<label for="' . esc_attr( $name ) . '"><strong>' . esc_html( $label ) . '</strong></label><br>';
	printf(
		'<input type="text" class="almasland-color-picker" id="%1$s" name="%1$s" value="%2$s" data-default-color="%2$s">',
		esc_attr( $name ),
		esc_attr( $value )
	);
	echo '</p>';
}

/**
 * Checkbox field.
 *
 * @param string $name  Field name.
 * @param string $label Label.
 * @param bool   $value Value.
 */
function almasland_panel_field_checkbox( $name, $label, $value ) {
	echo '<p class="almasland-field almasland-field--checkbox">';
	printf( '<input type="hidden" name="%1$s" value="0">', esc_attr( $name ) );
	printf(
		'<label><input type="checkbox" name="%1$s" value="1" %2$s> %3$s</label>',
		esc_attr( $name ),
		checked( $value, true, false ),
		esc_html( $label )
	);
	echo '</p>';
}

/**
 * Image upload field.
 *
 * @param string $name  Field name.
 * @param string $label Label.
 * @param int    $value Attachment ID.
 */
function almasland_panel_field_image( $name, $label, $value ) {
	$url = almasland_get_attachment_url( $value, 'thumbnail' );
	echo '<div class="almasland-field almasland-field--image">';
	echo '<label><strong>' . esc_html( $label ) . '</strong></label>';
	echo '<div class="almasland-image-preview">';
	if ( $url ) {
		echo '<img src="' . esc_url( $url ) . '" alt="">';
	}
	echo '</div>';
	printf( '<input type="hidden" class="almasland-image-id" name="%1$s" value="%2$d">', esc_attr( $name ), (int) $value );
	echo '<button type="button" class="button almasland-upload-image">' . esc_html__( 'انتخاب تصویر', 'almas-land' ) . '</button> ';
	echo '<button type="button" class="button almasland-remove-image">' . esc_html__( 'حذف', 'almas-land' ) . '</button>';
	echo '</div>';
}

/**
 * Card wrapper open.
 *
 * @param string $title Title.
 */
function almasland_panel_card_open( $title ) {
	echo '<div class="almasland-panel-card"><h2>' . esc_html( $title ) . '</h2>';
}

/**
 * Card wrapper close.
 */
function almasland_panel_card_close() {
	echo '</div>';
}
