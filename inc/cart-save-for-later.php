<?php
/**
 * Save-for-later / future cart storage and actions.
 *
 * @package AlmasLand
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Session key for saved cart items.
 */
const ALMASLAND_SAVED_CART_SESSION_KEY = 'almas_saved_for_later';

/**
 * Get saved cart items from the WooCommerce session.
 *
 * @return array<string, array<string, mixed>>
 */
function almasland_get_saved_cart_items() {
	if ( ! class_exists( 'WooCommerce' ) || ! WC()->session ) {
		return array();
	}

	$items = WC()->session->get( ALMASLAND_SAVED_CART_SESSION_KEY, array() );

	return is_array( $items ) ? $items : array();
}

/**
 * Persist saved cart items.
 *
 * @param array<string, array<string, mixed>> $items Saved items.
 * @return void
 */
function almasland_set_saved_cart_items( array $items ) {
	if ( ! WC()->session ) {
		return;
	}

	WC()->session->set( ALMASLAND_SAVED_CART_SESSION_KEY, $items );
}

/**
 * Count saved items.
 *
 * @return int
 */
function almasland_get_saved_cart_count() {
	$count = 0;

	foreach ( almasland_get_saved_cart_items() as $item ) {
		$count += isset( $item['quantity'] ) ? (int) $item['quantity'] : 0;
	}

	return $count;
}

/**
 * Whether the cart page is showing the saved-for-later view.
 *
 * @return bool
 */
function almasland_is_saved_cart_view() {
	// phpcs:ignore WordPress.Security.NonceVerification.Recommended
	return isset( $_GET['saved'] ) && '1' === sanitize_text_field( wp_unslash( $_GET['saved'] ) );
}

/**
 * Future cart URL.
 *
 * @return string
 */
function almasland_get_saved_cart_url() {
	return add_query_arg( 'saved', '1', wc_get_cart_url() );
}

/**
 * Build a stable key for a saved cart row.
 *
 * @param array<string, mixed> $item Saved item payload.
 * @return string
 */
function almasland_get_saved_cart_item_key( array $item ) {
	return md5(
		wp_json_encode(
			array(
				'product_id'     => (int) ( $item['product_id'] ?? 0 ),
				'variation_id'   => (int) ( $item['variation_id'] ?? 0 ),
				'variation'      => $item['variation'] ?? array(),
				'cart_item_data' => $item['cart_item_data'] ?? array(),
			)
		)
	);
}

/**
 * Move one cart item into the saved list.
 *
 * @param string $cart_item_key Cart item key.
 * @return bool
 */
function almasland_save_cart_item_for_later( $cart_item_key ) {
	if ( ! WC()->cart ) {
		return false;
	}

	$cart_item = WC()->cart->get_cart_item( $cart_item_key );
	if ( ! $cart_item ) {
		return false;
	}

	$new_item = array(
		'product_id'     => (int) $cart_item['product_id'],
		'variation_id'   => (int) ( $cart_item['variation_id'] ?? 0 ),
		'quantity'       => (int) $cart_item['quantity'],
		'variation'      => $cart_item['variation'] ?? array(),
		'cart_item_data' => $cart_item['cart_item_data'] ?? array(),
	);

	$saved   = almasland_get_saved_cart_items();
	$item_key = almasland_get_saved_cart_item_key( $new_item );

	if ( isset( $saved[ $item_key ] ) ) {
		$saved[ $item_key ]['quantity'] += $new_item['quantity'];
	} else {
		$saved[ $item_key ] = $new_item;
	}

	almasland_set_saved_cart_items( $saved );
	WC()->cart->remove_cart_item( $cart_item_key );

	return true;
}

/**
 * Move all cart items into the saved list.
 *
 * @return int Number of moved items.
 */
function almasland_save_all_cart_items_for_later() {
	if ( ! WC()->cart ) {
		return 0;
	}

	$moved = 0;

	foreach ( array_keys( WC()->cart->get_cart() ) as $cart_item_key ) {
		if ( almasland_save_cart_item_for_later( $cart_item_key ) ) {
			++$moved;
		}
	}

	return $moved;
}

/**
 * Move a saved item back into the cart.
 *
 * @param string $saved_item_key Saved item key.
 * @return bool
 */
function almasland_move_saved_item_to_cart( $saved_item_key ) {
	if ( ! WC()->cart ) {
		return false;
	}

	$saved = almasland_get_saved_cart_items();
	if ( ! isset( $saved[ $saved_item_key ] ) ) {
		return false;
	}

	$item = $saved[ $saved_item_key ];

	$added = WC()->cart->add_to_cart(
		(int) $item['product_id'],
		(int) $item['quantity'],
		(int) ( $item['variation_id'] ?? 0 ),
		$item['variation'] ?? array(),
		$item['cart_item_data'] ?? array()
	);

	if ( ! $added ) {
		return false;
	}

	unset( $saved[ $saved_item_key ] );
	almasland_set_saved_cart_items( $saved );

	return true;
}

/**
 * Remove one item from the saved list.
 *
 * @param string $saved_item_key Saved item key.
 * @return bool
 */
function almasland_remove_saved_cart_item( $saved_item_key ) {
	$saved = almasland_get_saved_cart_items();

	if ( ! isset( $saved[ $saved_item_key ] ) ) {
		return false;
	}

	unset( $saved[ $saved_item_key ] );
	almasland_set_saved_cart_items( $saved );

	return true;
}

/**
 * Handle save-for-later cart actions.
 *
 * @return void
 */
function almasland_handle_save_for_later_actions() {
	if ( ! is_cart() || ! class_exists( 'WooCommerce' ) || ! WC()->cart ) {
		return;
	}

	// phpcs:ignore WordPress.Security.NonceVerification.Recommended
	if ( isset( $_GET['almas_save_item'] ) ) {
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$cart_item_key = sanitize_text_field( wp_unslash( $_GET['almas_save_item'] ) );

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		if ( ! isset( $_GET['_wpnonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_GET['_wpnonce'] ) ), 'almas-save-item-' . $cart_item_key ) ) {
			return;
		}

		if ( almasland_save_cart_item_for_later( $cart_item_key ) ) {
			wc_add_notice( __( 'محصول به سبد خرید آینده منتقل شد.', 'almas-land' ), 'success' );
		} else {
			wc_add_notice( __( 'انتقال محصول به سبد خرید آینده انجام نشد.', 'almas-land' ), 'error' );
		}

		wp_safe_redirect( wc_get_cart_url() );
		exit;
	}

	// phpcs:ignore WordPress.Security.NonceVerification.Recommended
	if ( isset( $_GET['almas_save_all'] ) && '1' === sanitize_text_field( wp_unslash( $_GET['almas_save_all'] ) ) ) {
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		if ( ! isset( $_GET['_wpnonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_GET['_wpnonce'] ) ), 'almas-save-all' ) ) {
			return;
		}

		$moved = almasland_save_all_cart_items_for_later();

		if ( $moved > 0 ) {
			wc_add_notice( __( 'همه محصولات به سبد خرید آینده منتقل شدند.', 'almas-land' ), 'success' );
			wp_safe_redirect( almasland_get_saved_cart_url() );
		} else {
			wc_add_notice( __( 'محصولی برای انتقال وجود نداشت.', 'almas-land' ), 'notice' );
			wp_safe_redirect( wc_get_cart_url() );
		}

		exit;
	}

	// phpcs:ignore WordPress.Security.NonceVerification.Recommended
	if ( isset( $_GET['almas_move_to_cart'] ) ) {
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$saved_item_key = sanitize_text_field( wp_unslash( $_GET['almas_move_to_cart'] ) );

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		if ( ! isset( $_GET['_wpnonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_GET['_wpnonce'] ) ), 'almas-move-to-cart-' . $saved_item_key ) ) {
			return;
		}

		if ( almasland_move_saved_item_to_cart( $saved_item_key ) ) {
			wc_add_notice( __( 'محصول به سبد خرید اضافه شد.', 'almas-land' ), 'success' );
		} else {
			wc_add_notice( __( 'افزودن محصول به سبد خرید انجام نشد.', 'almas-land' ), 'error' );
		}

		wp_safe_redirect( wc_get_cart_url() );
		exit;
	}

	// phpcs:ignore WordPress.Security.NonceVerification.Recommended
	if ( isset( $_GET['almas_remove_saved'] ) ) {
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$saved_item_key = sanitize_text_field( wp_unslash( $_GET['almas_remove_saved'] ) );

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		if ( ! isset( $_GET['_wpnonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_GET['_wpnonce'] ) ), 'almas-remove-saved-' . $saved_item_key ) ) {
			return;
		}

		if ( almasland_remove_saved_cart_item( $saved_item_key ) ) {
			wc_add_notice( __( 'محصول از سبد خرید آینده حذف شد.', 'almas-land' ), 'success' );
		}

		wp_safe_redirect( almasland_get_saved_cart_url() );
		exit;
	}
}
add_action( 'template_redirect', 'almasland_handle_save_for_later_actions', 20 );
