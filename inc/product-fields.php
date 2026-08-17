<?php
/**
 * Product custom fields.
 *
 * @package AlmasLand
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Product field definitions.
 *
 * @return array
 */
function almasland_product_fields() {
	return array(
		'_almas_card_title'     => array(
			'label'       => esc_html__( 'عنوان', 'almas-land' ),
			'type'        => 'text',
			'description' => esc_html__( 'عنوان کوتاه برای کارت‌های محصول (مثلاً: msi mini case 8 / 128). اگر خالی باشد، عنوان اصلی نمایش داده می‌شود.', 'almas-land' ),
		),
		'_almas_card_specs'     => array(
			'label'       => esc_html__( 'مشخصات کارت', 'almas-land' ),
			'type'        => 'text',
			'description' => esc_html__( 'متن مشخصات روی کارت؛ مثلاً: i3 6100 / 8GB ram / 256 Gb SSD', 'almas-land' ),
		),
		'_almas_card_grade'     => array(
			'label'       => esc_html__( 'Grade', 'almas-land' ),
			'type'        => 'select',
			'description' => esc_html__( 'وضعیت ظاهری برای نمایش رنگی روی کارت محصولات دست‌دوم.', 'almas-land' ),
			'options'     => almasland_get_product_card_grade_options(),
		),
		'_almas_product_color'  => array(
			'label'       => esc_html__( 'رنگ محصول', 'almas-land' ),
			'type'        => 'color',
			'description' => esc_html__( 'رنگ را با کد hex یا انتخابگر رنگ مشخص کنید. روی کارت و صفحه محصول به‌صورت دایره نمایش داده می‌شود.', 'almas-land' ),
		),
		'_almas_brand'          => array( 'label' => esc_html__( 'برند محصول', 'almas-land' ), 'type' => 'text' ),
		'_almas_warranty'       => array( 'label' => esc_html__( 'گارانتی', 'almas-land' ), 'type' => 'text' ),
		'_almas_cosmetic'       => array( 'label' => esc_html__( 'وضعیت ظاهری کالا', 'almas-land' ), 'type' => 'text' ),
		'_almas_technical'      => array( 'label' => esc_html__( 'وضعیت فنی کالا', 'almas-land' ), 'type' => 'text' ),
		'_almas_items'          => array( 'label' => esc_html__( 'اقلام همراه', 'almas-land' ), 'type' => 'textarea' ),
		'_almas_features'       => array( 'label' => esc_html__( 'ویژگی‌های مهم محصول', 'almas-land' ), 'type' => 'textarea' ),
		'_almas_delivery'       => array( 'label' => esc_html__( 'متن ارسال و تحویل', 'almas-land' ), 'type' => 'textarea' ),
		'_almas_installment'    => array( 'label' => esc_html__( 'متن خرید اقساطی', 'almas-land' ), 'type' => 'text' ),
		'_almas_sales'          => array( 'label' => esc_html__( 'متن فروش اخیر', 'almas-land' ), 'type' => 'text' ),
		'_almas_return'         => array( 'label' => esc_html__( 'متن ضمانت بازگشت', 'almas-land' ), 'type' => 'textarea' ),
		'_almas_video'          => array( 'label' => esc_html__( 'ویدئوی محصول', 'almas-land' ), 'type' => 'url' ),
		'_almas_custom_specs'   => array( 'label' => esc_html__( 'جدول مشخصات سفارشی', 'almas-land' ), 'type' => 'textarea', 'description' => esc_html__( 'هر خط با فرمت: عنوان | مقدار', 'almas-land' ) ),
		'_almas_cta_text'       => array( 'label' => esc_html__( 'متن CTA اختصاصی محصول', 'almas-land' ), 'type' => 'textarea' ),
	);
}

/**
 * Grade options for used product cards (key => label).
 *
 * @return array<string, string>
 */
function almasland_get_product_card_grade_options() {
	return array(
		''            => esc_html__( '— انتخاب کنید —', 'almas-land' ),
		'like_new'    => esc_html__( 'مشابه نو', 'almas-land' ),
		'very_clean'  => esc_html__( 'بسیار تمیز', 'almas-land' ),
		'clean'       => esc_html__( 'تمیز', 'almas-land' ),
		'fair'        => esc_html__( 'معمولی', 'almas-land' ),
	);
}

/**
 * Grade visual definitions (green → orange).
 *
 * @return array<string, array{text: string, tone: string, bg: string, color: string}>
 */
function almasland_get_product_card_grade_definitions() {
	return array(
		'like_new'   => array(
			'text'  => 'مشابه نو',
			'tone'  => 'like-new',
			'bg'    => '#dcfce7',
			'color' => '#15803d',
		),
		'very_clean' => array(
			'text'  => 'بسیار تمیز',
			'tone'  => 'very-clean',
			'bg'    => '#d1fae5',
			'color' => '#0f766e',
		),
		'clean'      => array(
			'text'  => 'تمیز',
			'tone'  => 'clean',
			'bg'    => '#fef3c7',
			'color' => '#b45309',
		),
		'fair'       => array(
			'text'  => 'معمولی',
			'tone'  => 'fair',
			'bg'    => '#ffedd5',
			'color' => '#c2410c',
		),
	);
}

/**
 * Sanitize a product color hex value.
 *
 * @param string $value Raw color.
 * @return string
 */
function almasland_sanitize_product_color( $value ) {
	$value = trim( (string) $value );
	if ( '' === $value ) {
		return '';
	}

	if ( '#' !== $value[0] ) {
		$value = '#' . ltrim( $value, '#' );
	}

	if ( preg_match( '/^#([a-fA-F0-9]{3})$/', $value, $matches ) ) {
		$chars = str_split( $matches[1] );
		$value = sprintf( '#%s%s%s%s%s%s', $chars[0], $chars[0], $chars[1], $chars[1], $chars[2], $chars[2] );
	}

	$sanitized = sanitize_hex_color( $value );

	return $sanitized ? strtoupper( $sanitized ) : '';
}

/**
 * Get product color from meta (supports variations via parent).
 *
 * @param WC_Product|null $product Product.
 * @return string Hex color or empty string.
 */
function almasland_get_product_color( $product ) {
	if ( ! $product instanceof WC_Product ) {
		return '';
	}

	$source = $product;
	if ( $product->is_type( 'variation' ) ) {
		$parent = wc_get_product( $product->get_parent_id() );
		if ( $parent ) {
			$source = $parent;
		}
	}

	return almasland_sanitize_product_color( (string) $source->get_meta( '_almas_product_color' ) );
}

/**
 * Render product color swatch markup.
 *
 * @param WC_Product|null $product Product.
 * @param array           $args    Display args.
 * @return string
 */
function almasland_render_product_color_swatch( $product, $args = array() ) {
	$color = almasland_get_product_color( $product );
	if ( '' === $color ) {
		return '';
	}

	$args = wp_parse_args(
		$args,
		array(
			'class'      => 'product-color-swatch',
			'size'       => 'md',
			'show_label' => false,
			'label'      => __( 'رنگ', 'almas-land' ),
		)
	);

	$size_class = sanitize_html_class( 'product-color-swatch--' . $args['size'] );
	$classes    = trim( $args['class'] . ' ' . $size_class );
	$aria_label = sprintf(
		/* translators: %s: color hex code */
		__( 'رنگ محصول: %s', 'almas-land' ),
		$color
	);

	$swatch = sprintf(
		'<span class="%1$s" style="background-color:%2$s;" title="%3$s" aria-hidden="true"></span>',
		esc_attr( $classes ),
		esc_attr( $color ),
		esc_attr( $aria_label )
	);

	if ( ! $args['show_label'] ) {
		return $swatch;
	}

	return sprintf(
		'<span class="product-color-display"><span class="product-color-display__label">%1$s</span>%2$s<span class="screen-reader-text">%3$s</span></span>',
		esc_html( $args['label'] ),
		$swatch,
		esc_html( $aria_label )
	);
}

/**
 * Render admin product color field.
 *
 * @param string $value Saved color.
 */
function almasland_render_product_color_field( $value = '' ) {
	$value = almasland_sanitize_product_color( $value );
	$picker_value = $value ? $value : '#000000';
	?>
	<p class="form-field _almas_product_color_field">
		<label for="_almas_product_color"><?php esc_html_e( 'رنگ محصول', 'almas-land' ); ?></label>
		<span class="almasland-product-color-field">
			<input
				type="text"
				class="shorttext almasland-product-color-field__hex"
				name="_almas_product_color"
				id="_almas_product_color"
				value="<?php echo esc_attr( $value ); ?>"
				placeholder="#000000"
				inputmode="text"
				autocomplete="off"
				spellcheck="false"
				data-product-color-hex
			>
			<input
				type="color"
				class="almasland-product-color-field__picker"
				value="<?php echo esc_attr( $picker_value ); ?>"
				aria-label="<?php esc_attr_e( 'انتخاب رنگ', 'almas-land' ); ?>"
				data-product-color-picker
			>
		</span>
		<?php echo wc_help_tip( esc_html__( 'رنگ را با کد hex یا انتخابگر رنگ مشخص کنید. روی کارت و صفحه محصول به‌صورت دایره نمایش داده می‌شود.', 'almas-land' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
	</p>
	<?php
}

/**
 * Enqueue admin assets for product custom fields.
 *
 * @param string $hook Current admin page hook.
 */
function almasland_enqueue_product_fields_admin_assets( $hook ) {
	if ( ! in_array( $hook, array( 'post.php', 'post-new.php' ), true ) ) {
		return;
	}

	$screen = get_current_screen();
	if ( ! $screen || 'product' !== $screen->post_type ) {
		return;
	}

	wp_enqueue_style(
		'almasland-admin-product-fields',
		ALMASLAND_URI . '/assets/css/admin-product-fields.css',
		array(),
		ALMASLAND_VERSION
	);

	wp_enqueue_script(
		'almasland-admin-product-fields',
		ALMASLAND_URI . '/assets/js/admin-product-fields.js',
		array(),
		ALMASLAND_VERSION,
		true
	);
}
add_action( 'admin_enqueue_scripts', 'almasland_enqueue_product_fields_admin_assets' );

/**
 * Add fields to product edit screen.
 */
function almasland_add_product_fields() {
	if ( ! function_exists( 'woocommerce_wp_text_input' ) ) {
		return;
	}

	global $product_object;

	echo '<div class="options_group almasland-product-fields">';
	wp_nonce_field( 'almasland_save_product_fields', 'almasland_product_fields_nonce' );

	foreach ( almasland_product_fields() as $key => $field ) {
		if ( 'color' === $field['type'] ) {
			continue;
		}

		$args = array(
			'id'          => $key,
			'label'       => $field['label'],
			'desc_tip'    => ! empty( $field['description'] ),
			'description' => isset( $field['description'] ) ? $field['description'] : '',
		);

		if ( 'textarea' === $field['type'] ) {
			woocommerce_wp_textarea_input( $args );
		} elseif ( 'select' === $field['type'] ) {
			$args['options'] = isset( $field['options'] ) ? $field['options'] : array();
			woocommerce_wp_select( $args );
		} else {
			$args['type'] = 'url' === $field['type'] ? 'url' : 'text';
			woocommerce_wp_text_input( $args );
		}
	}

	$saved_color = $product_object instanceof WC_Product ? almasland_get_product_color( $product_object ) : '';
	almasland_render_product_color_field( $saved_color );
	echo '</div>';
}
add_action( 'woocommerce_product_options_general_product_data', 'almasland_add_product_fields' );

/**
 * Save product custom fields.
 *
 * @param WC_Product $product Product object.
 */
function almasland_save_product_fields( $product ) {
	if ( ! $product instanceof WC_Product ) {
		return;
	}

	if ( ! current_user_can( 'edit_product', $product->get_id() ) ) {
		return;
	}

	if ( ! isset( $_POST['almasland_product_fields_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['almasland_product_fields_nonce'] ) ), 'almasland_save_product_fields' ) ) {
		return;
	}

	foreach ( almasland_product_fields() as $key => $field ) {
		if ( ! isset( $_POST[ $key ] ) ) {
			continue;
		}

		$value = wp_unslash( $_POST[ $key ] );
		if ( 'url' === $field['type'] ) {
			$value = esc_url_raw( $value );
		} elseif ( 'textarea' === $field['type'] ) {
			$value = sanitize_textarea_field( $value );
		} elseif ( 'select' === $field['type'] ) {
			$value = sanitize_key( $value );
			$options = isset( $field['options'] ) ? $field['options'] : array();
			if ( ! array_key_exists( $value, $options ) ) {
				$value = '';
			}
		} elseif ( 'color' === $field['type'] ) {
			$value = almasland_sanitize_product_color( $value );
		} else {
			$value = sanitize_text_field( $value );
		}
		$product->update_meta_data( $key, $value );
	}

	delete_transient( 'almasland_shop_brand_options' );
}
add_action( 'woocommerce_admin_process_product_object', 'almasland_save_product_fields' );

/**
 * Card / loop display title. Falls back to the product name when empty.
 *
 * @param WC_Product|null $product Product.
 * @return string
 */
function almasland_get_product_card_title( $product ) {
	if ( ! $product instanceof WC_Product ) {
		return '';
	}

	$source = $product;
	if ( $product->is_type( 'variation' ) ) {
		$parent = wc_get_product( $product->get_parent_id() );
		if ( $parent ) {
			$source = $parent;
		}
	}

	$card_title = trim( (string) $source->get_meta( '_almas_card_title' ) );
	if ( $card_title !== '' ) {
		return $card_title;
	}

	return $product->get_name();
}

/**
 * Get WooCommerce attribute specs.
 *
 * @param WC_Product $product      Product.
 * @param bool       $visible_only Only include attributes marked visible on product page.
 * @return array<string, string>
 */
function almasland_get_product_attribute_specs( $product, $visible_only = false ) {
	if ( ! $product ) {
		return array();
	}

	if ( $product->is_type( 'variation' ) ) {
		$parent = wc_get_product( $product->get_parent_id() );
		if ( $parent ) {
			$product = $parent;
		}
	}

	$specs = array();

	foreach ( $product->get_attributes() as $attribute_name => $attribute ) {
		if ( $visible_only && ! $attribute->get_visible() ) {
			continue;
		}

		$label = wc_attribute_label( $attribute->get_name(), $product );
		if ( ! $label ) {
			$label = wc_attribute_label( $attribute_name, $product );
		}
		if ( ! $label ) {
			continue;
		}

		$value = $product->get_attribute( $attribute_name );
		if ( ! $value ) {
			if ( $attribute->is_taxonomy() ) {
				$terms = wc_get_product_terms( $product->get_id(), $attribute->get_name(), array( 'fields' => 'names' ) );
				$value = is_wp_error( $terms ) ? '' : implode( '، ', $terms );
			} else {
				$options = $attribute->get_options();
				$value   = is_array( $options ) ? implode( '، ', $options ) : (string) $options;
			}
		}

		$value = wp_strip_all_tags( (string) $value );
		if ( $value ) {
			$specs[ $label ] = $value;
		}
	}

	return $specs;
}

/**
 * Get the product brand from a brand attribute or fallback meta.
 *
 * @param WC_Product $product Product.
 * @return string
 */
function almasland_get_product_brand( $product ) {
	if ( ! $product ) {
		return '';
	}

	if ( $product->is_type( 'variation' ) ) {
		$parent = wc_get_product( $product->get_parent_id() );
		if ( $parent ) {
			$product = $parent;
		}
	}

	if ( function_exists( 'almasland_get_brand_attribute_taxonomy' ) ) {
		$taxonomy = almasland_get_brand_attribute_taxonomy();
		if ( $taxonomy ) {
			$terms = wc_get_product_terms( $product->get_id(), $taxonomy, array( 'fields' => 'names' ) );
			if ( ! is_wp_error( $terms ) && ! empty( $terms ) ) {
				return implode( '، ', $terms );
			}
		}
	}

	foreach ( $product->get_attributes() as $attribute ) {
		$name = $attribute->get_name();
		if ( false !== strpos( $name, 'brand' ) || false !== strpos( $name, 'برند' ) ) {
			$brand = $product->get_attribute( $name );
			if ( $brand ) {
				return $brand;
			}
		}
	}

	return $product->get_meta( '_almas_brand' );
}

/**
 * Get visible product specs for summary and tables.
 *
 * @param WC_Product $product Product.
 * @return array<string, string>
 */
function almasland_get_product_specs( $product ) {
	if ( ! $product ) {
		return array();
	}

	$specs = array();

	foreach ( almasland_get_product_attribute_specs( $product, false ) as $label => $value ) {
		$specs[ $label ] = $value;
	}

	if ( $product->get_sku() ) {
		$specs[ esc_html__( 'شناسه محصول', 'almas-land' ) ] = $product->get_sku();
	}

	$meta_map = array(
		esc_html__( 'برند', 'almas-land' )        => almasland_get_product_brand( $product ),
		esc_html__( 'گارانتی', 'almas-land' )     => $product->get_meta( '_almas_warranty' ),
		esc_html__( 'وضعیت ظاهری', 'almas-land' ) => $product->get_meta( '_almas_cosmetic' ),
		esc_html__( 'وضعت فنی', 'almas-land' )   => $product->get_meta( '_almas_technical' ),
		esc_html__( 'اقلام همراه', 'almas-land' ) => $product->get_meta( '_almas_items' ),
	);

	foreach ( $meta_map as $label => $value ) {
		if ( $value ) {
			$specs[ $label ] = $value;
		}
	}

	$custom_specs = $product->get_meta( '_almas_custom_specs' );
	if ( $custom_specs ) {
		$lines = preg_split( '/\r\n|\r|\n/', $custom_specs );
		foreach ( $lines as $line ) {
			$parts = array_map( 'trim', explode( '|', $line, 2 ) );
			if ( 2 === count( $parts ) && $parts[0] && $parts[1] ) {
				$specs[ $parts[0] ] = $parts[1];
			}
		}
	}

	return array_filter( $specs );
}
