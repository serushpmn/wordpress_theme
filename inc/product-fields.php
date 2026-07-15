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
 * Add fields to product edit screen.
 */
function almasland_add_product_fields() {
	if ( ! function_exists( 'woocommerce_wp_text_input' ) ) {
		return;
	}

	echo '<div class="options_group">';
	wp_nonce_field( 'almasland_save_product_fields', 'almasland_product_fields_nonce' );

	foreach ( almasland_product_fields() as $key => $field ) {
		$args = array(
			'id'          => $key,
			'label'       => $field['label'],
			'desc_tip'    => ! empty( $field['description'] ),
			'description' => isset( $field['description'] ) ? $field['description'] : '',
		);

		if ( 'textarea' === $field['type'] ) {
			woocommerce_wp_textarea_input( $args );
		} else {
			$args['type'] = 'url' === $field['type'] ? 'url' : 'text';
			woocommerce_wp_text_input( $args );
		}
	}
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
		} else {
			$value = sanitize_text_field( $value );
		}
		$product->update_meta_data( $key, $value );
	}

	delete_transient( 'almasland_shop_brand_options' );
}
add_action( 'woocommerce_admin_process_product_object', 'almasland_save_product_fields' );

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
