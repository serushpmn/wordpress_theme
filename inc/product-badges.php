<?php
/**
 * Product badge meta box and helpers.
 *
 * @package AlmasLand
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Fixed badge definitions with default colors.
 *
 * @return array<string, array{text:string, bg:string, color:string}>
 */
function almasland_get_fixed_badge_definitions() {
	return array(
		'check'              => array(
			'text'  => 'قابل خرید بصورت چک',
			'bg'    => '#7c3aed',
			'color' => '#ffffff',
		),
		'installment'        => array(
			'text'  => 'قابل خرید بصورت اقساطی',
			'bg'    => '#0b8df0',
			'color' => '#ffffff',
		),
		'credit'             => array(
			'text'  => 'قابل خرید بصورت اعتباری',
			'bg'    => '#e9f1ff',
			'color' => '#1b4ca4',
		),
		'original'           => array(
			'text'  => 'کالای اصل',
			'bg'    => '#1abf77',
			'color' => '#ffffff',
		),
		'non_original'       => array(
			'text'  => 'کالای غیراصل',
			'bg'    => '#ef4444',
			'color' => '#ffffff',
		),
		'warranty'           => array(
			'text'  => 'دارای گارانتی',
			'bg'    => '#0ea5e9',
			'color' => '#ffffff',
		),
		'amazing'            => array(
			'text'  => 'پیشنهاد شگفت انگیز',
			'bg'    => '#f59e0b',
			'color' => '#ffffff',
		),
		'express_delivery'   => array(
			'text'  => 'ارسال یک ساعته 🚀',
			'bg'    => '#111827',
			'color' => '#ffffff',
		),
		'open_box'           => array(
			'text'  => 'نمونه ویترینی (اوپن باکس)',
			'bg'    => '#8b5cf6',
			'color' => '#ffffff',
		),
		'second_hand'        => array(
			'text'  => 'دست دوم (کیفیت عالی)',
			'bg'    => '#64748b',
			'color' => '#ffffff',
		),
		'with_mouse_keyboard' => array(
			'text'  => 'بهمراه موس و کیبرد',
			'bg'    => '#0369a1',
			'color' => '#ffffff',
		),
		'with_box'           => array(
			'text'  => 'همراه با جعبه (کالا+جعبه اصلی)',
			'bg'    => '#bf775f',
			'color' => '#ffffff',
		),
	);
}

/**
 * Register product badge meta box.
 */
function almasland_register_product_badge_meta_box() {
	add_meta_box(
		'almasland_product_badges',
		esc_html__( 'لیبل های سفارشی', 'almas-land' ),
		'almasland_render_product_badge_meta_box',
		'product',
		'normal',
		'high'
	);
}
add_action( 'add_meta_boxes', 'almasland_register_product_badge_meta_box' );

/**
 * Enqueue admin assets for product badge UI.
 *
 * @param string $hook Current admin page hook.
 */
function almasland_enqueue_product_badge_admin_assets( $hook ) {
	if ( ! in_array( $hook, array( 'post.php', 'post-new.php' ), true ) ) {
		return;
	}

	$screen = get_current_screen();
	if ( ! $screen || 'product' !== $screen->post_type ) {
		return;
	}

	wp_enqueue_style( 'wp-color-picker' );
	wp_enqueue_script( 'wp-color-picker' );

	wp_enqueue_style(
		'almasland-admin-product-badges',
		ALMASLAND_URI . '/assets/css/admin-product-badges.css',
		array(),
		ALMASLAND_VERSION
	);

	wp_enqueue_script(
		'almasland-admin-product-badges',
		ALMASLAND_URI . '/assets/js/admin-product-badges.js',
		array( 'jquery', 'wp-color-picker' ),
		ALMASLAND_VERSION,
		true
	);
}
add_action( 'admin_enqueue_scripts', 'almasland_enqueue_product_badge_admin_assets' );

/**
 * Render product badge meta box.
 *
 * @param WP_Post $post Current post.
 */
function almasland_render_product_badge_meta_box( $post ) {
	wp_nonce_field( 'almasland_save_product_badges', 'almasland_product_badges_nonce' );

	$product       = wc_get_product( $post->ID );
	$english_name  = $product ? $product->get_meta( '_almas_english_name' ) : '';
	$fixed_badges  = $product ? (array) $product->get_meta( '_almas_fixed_badges' ) : array();
	$custom_badges = $product ? (array) $product->get_meta( '_almas_custom_badges' ) : array();
	$definitions   = almasland_get_fixed_badge_definitions();
	?>
	<div class="almasland-product-badges-meta" dir="rtl">
		<p class="almasland-product-badges-meta__field">
			<label for="almas_english_name"><strong><?php esc_html_e( 'نام انگلیسی محصول', 'almas-land' ); ?></strong></label>
			<input type="text" id="almas_english_name" name="_almas_english_name" class="widefat" value="<?php echo esc_attr( $english_name ); ?>" placeholder="<?php esc_attr_e( 'مثال: Apple MacBook Pro 14 M3', 'almas-land' ); ?>">
		</p>

		<div class="almasland-product-badges-meta__section">
			<h4><?php esc_html_e( 'لیبل های ثابت', 'almas-land' ); ?></h4>
			<div class="almasland-fixed-badges">
				<?php foreach ( $definitions as $key => $badge ) : ?>
					<label class="almasland-fixed-badge" style="--badge-bg: <?php echo esc_attr( $badge['bg'] ); ?>; --badge-color: <?php echo esc_attr( $badge['color'] ); ?>;">
						<input type="checkbox" name="_almas_fixed_badges[]" value="<?php echo esc_attr( $key ); ?>" <?php checked( in_array( $key, $fixed_badges, true ) ); ?>>
						<span class="almasland-fixed-badge__preview"><?php echo esc_html( $badge['text'] ); ?></span>
					</label>
				<?php endforeach; ?>
			</div>
		</div>

		<div class="almasland-product-badges-meta__section">
			<h4><?php esc_html_e( 'لیبل های سفارشی', 'almas-land' ); ?></h4>
			<div class="almasland-custom-badges" data-custom-badges>
				<?php if ( $custom_badges ) : ?>
					<?php foreach ( $custom_badges as $index => $badge ) : ?>
						<?php
						$text  = isset( $badge['text'] ) ? $badge['text'] : '';
						$color = isset( $badge['color'] ) ? $badge['color'] : '#1abf77';
						?>
						<div class="almasland-custom-badge-row" data-custom-badge-row>
							<input type="text" name="_almas_custom_badges[<?php echo esc_attr( (string) $index ); ?>][text]" value="<?php echo esc_attr( $text ); ?>" placeholder="<?php esc_attr_e( 'متن لیبل', 'almas-land' ); ?>" class="almasland-custom-badge-row__text">
							<input type="text" name="_almas_custom_badges[<?php echo esc_attr( (string) $index ); ?>][color]" value="<?php echo esc_attr( $color ); ?>" class="almasland-custom-badge-row__color" data-color-picker>
							<button type="button" class="button-link-delete almasland-custom-badge-row__remove" data-remove-badge aria-label="<?php esc_attr_e( 'حذف لیبل', 'almas-land' ); ?>">&times;</button>
						</div>
					<?php endforeach; ?>
				<?php endif; ?>
			</div>

			<div class="almasland-custom-badge-add">
				<input type="text" id="almasland-new-badge-text" placeholder="<?php esc_attr_e( 'متن لیبل', 'almas-land' ); ?>" class="almasland-custom-badge-add__text">
				<input type="text" id="almasland-new-badge-color" value="#1abf77" class="almasland-custom-badge-add__color" data-color-picker>
				<button type="button" class="button button-secondary" data-add-badge><?php esc_html_e( 'افزودن لیبل', 'almas-land' ); ?></button>
			</div>
		</div>
	</div>

	<template id="almasland-custom-badge-template">
		<div class="almasland-custom-badge-row" data-custom-badge-row>
			<input type="text" name="" value="" placeholder="<?php esc_attr_e( 'متن لیبل', 'almas-land' ); ?>" class="almasland-custom-badge-row__text">
			<input type="text" name="" value="#1abf77" class="almasland-custom-badge-row__color" data-color-picker>
			<button type="button" class="button-link-delete almasland-custom-badge-row__remove" data-remove-badge aria-label="<?php esc_attr_e( 'حذف لیبل', 'almas-land' ); ?>">&times;</button>
		</div>
	</template>
	<?php
}

/**
 * Save product badge meta box data.
 *
 * @param int $post_id Post ID.
 */
function almasland_save_product_badge_meta_box( $post_id ) {
	if ( ! isset( $_POST['almasland_product_badges_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['almasland_product_badges_nonce'] ) ), 'almasland_save_product_badges' ) ) {
		return;
	}

	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}

	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}

	$product = wc_get_product( $post_id );
	if ( ! $product ) {
		return;
	}

	$english_name = isset( $_POST['_almas_english_name'] ) ? sanitize_text_field( wp_unslash( $_POST['_almas_english_name'] ) ) : '';
	$product->update_meta_data( '_almas_english_name', $english_name );
	$product->update_meta_data( '_almas_subtitle', $english_name );

	$definitions  = almasland_get_fixed_badge_definitions();
	$fixed_badges   = array();
	$posted_fixed   = isset( $_POST['_almas_fixed_badges'] ) ? (array) wp_unslash( $_POST['_almas_fixed_badges'] ) : array();
	foreach ( $posted_fixed as $badge_key ) {
		$badge_key = sanitize_key( $badge_key );
		if ( isset( $definitions[ $badge_key ] ) ) {
			$fixed_badges[] = $badge_key;
		}
	}
	$product->update_meta_data( '_almas_fixed_badges', $fixed_badges );

	$custom_badges = array();
	$posted_custom = isset( $_POST['_almas_custom_badges'] ) ? (array) wp_unslash( $_POST['_almas_custom_badges'] ) : array();
	foreach ( $posted_custom as $badge ) {
		if ( ! is_array( $badge ) ) {
			continue;
		}

		$text = isset( $badge['text'] ) ? sanitize_text_field( $badge['text'] ) : '';
		if ( ! $text ) {
			continue;
		}

		$color = isset( $badge['color'] ) ? sanitize_hex_color( $badge['color'] ) : '';
		if ( ! $color ) {
			$color = '#1abf77';
		}

		$custom_badges[] = array(
			'text'  => $text,
			'color' => $color,
		);
	}
	$product->update_meta_data( '_almas_custom_badges', $custom_badges );

	$product->save();
}
add_action( 'save_post_product', 'almasland_save_product_badge_meta_box' );

/**
 * Get product badges for frontend display.
 *
 * @param WC_Product $product Product.
 * @return array<int, array{text:string, bg:string, color:string}>
 */
function almasland_get_product_badges( $product ) {
	if ( ! $product ) {
		return array();
	}

	if ( $product->is_type( 'variation' ) ) {
		$parent = wc_get_product( $product->get_parent_id() );
		if ( $parent ) {
			$product = $parent;
		}
	}

	$badges      = array();
	$definitions = almasland_get_fixed_badge_definitions();
	$fixed       = (array) $product->get_meta( '_almas_fixed_badges' );

	foreach ( $fixed as $badge_key ) {
		if ( ! isset( $definitions[ $badge_key ] ) ) {
			continue;
		}

		$definition = $definitions[ $badge_key ];
		$badges[]   = array(
			'text'  => $definition['text'],
			'bg'    => $definition['bg'],
			'color' => $definition['color'],
		);
	}

	$custom_badges = (array) $product->get_meta( '_almas_custom_badges' );
	foreach ( $custom_badges as $badge ) {
		if ( ! is_array( $badge ) || empty( $badge['text'] ) ) {
			continue;
		}

		$bg = ! empty( $badge['color'] ) ? sanitize_hex_color( $badge['color'] ) : '#1abf77';
		if ( ! $bg ) {
			$bg = '#1abf77';
		}

		$badges[] = array(
			'text'  => sanitize_text_field( $badge['text'] ),
			'bg'    => $bg,
			'color' => almasland_badge_text_color_for_bg( $bg ),
		);
	}

	return $badges;
}

/**
 * Pick readable text color for a badge background.
 *
 * @param string $hex Background color.
 * @return string
 */
function almasland_badge_text_color_for_bg( $hex ) {
	$hex = ltrim( sanitize_hex_color( $hex ), '#' );
	if ( strlen( $hex ) !== 6 ) {
		return '#ffffff';
	}

	$r = hexdec( substr( $hex, 0, 2 ) );
	$g = hexdec( substr( $hex, 2, 2 ) );
	$b = hexdec( substr( $hex, 4, 2 ) );
	$l = ( 0.299 * $r + 0.587 * $g + 0.114 * $b ) / 255;

	return $l > 0.62 ? '#1b4ca4' : '#ffffff';
}

/**
 * Get English product name.
 *
 * @param WC_Product $product Product.
 * @return string
 */
function almasland_get_product_english_name( $product ) {
	if ( ! $product ) {
		return '';
	}

	$english_name = $product->get_meta( '_almas_english_name' );
	if ( $english_name ) {
		return $english_name;
	}

	return (string) $product->get_meta( '_almas_subtitle' );
}
