<?php
/**
 * Cart template.
 *
 * @package AlmasLand
 */

defined( 'ABSPATH' ) || exit;

do_action( 'woocommerce_before_cart' );
?>
<div class="cart-layout">
	<form class="woocommerce-cart-form cart-items" action="<?php echo esc_url( wc_get_cart_url() ); ?>" method="post">
		<?php do_action( 'woocommerce_before_cart_table' ); ?>

		<?php do_action( 'woocommerce_before_cart_contents' ); ?>

		<?php foreach ( WC()->cart->get_cart() as $cart_item_key => $cart_item ) : ?>
			<?php
			$_product   = apply_filters( 'woocommerce_cart_item_product', $cart_item['data'], $cart_item, $cart_item_key );
			$product_id = apply_filters( 'woocommerce_cart_item_product_id', $cart_item['product_id'], $cart_item, $cart_item_key );

			if ( ! $_product || ! $_product->exists() || $cart_item['quantity'] <= 0 || ! apply_filters( 'woocommerce_cart_item_visible', true, $cart_item, $cart_item_key ) ) {
				continue;
			}

			$product_name      = apply_filters( 'woocommerce_cart_item_name', $_product->get_name(), $cart_item, $cart_item_key );
			$product_permalink = apply_filters( 'woocommerce_cart_item_permalink', $_product->is_visible() ? $_product->get_permalink( $cart_item ) : '', $cart_item, $cart_item_key );
			$product_thumbnail = apply_filters( 'woocommerce_cart_item_thumbnail', $_product->get_image( 'woocommerce_thumbnail' ), $cart_item, $cart_item_key );
			$product_features  = function_exists( 'almasland_get_cart_item_features' ) ? almasland_get_cart_item_features( $cart_item, $_product ) : array();
			$product_price     = almasland_persian_price( apply_filters( 'woocommerce_cart_item_price', WC()->cart->get_product_price( $_product ), $cart_item, $cart_item_key ) );
			$product_subtotal  = almasland_persian_price( apply_filters( 'woocommerce_cart_item_subtotal', WC()->cart->get_product_subtotal( $_product, $cart_item['quantity'] ), $cart_item, $cart_item_key ) );
			$regular_price     = (float) $_product->get_regular_price();
			$current_price     = (float) $_product->get_price();
			$discount_percent  = $regular_price > 0 && $current_price > 0 && $current_price < $regular_price ? (int) round( ( ( $regular_price - $current_price ) / $regular_price ) * 100 ) : 0;
			$item_qty          = (int) $cart_item['quantity'];
			$quantity_locked   = almasland_is_cart_quantity_locked( $_product );
			?>
			<div class="cart-item <?php echo esc_attr( apply_filters( 'woocommerce_cart_item_class', 'cart_item', $cart_item, $cart_item_key ) ); ?>">
				<div class="cart-item__aside">
					<div class="cart-item__media">
						<?php if ( $product_permalink ) : ?>
							<a href="<?php echo esc_url( $product_permalink ); ?>">
								<?php echo wp_kses_post( $product_thumbnail ); ?>
							</a>
						<?php else : ?>
							<?php echo wp_kses_post( $product_thumbnail ); ?>
						<?php endif; ?>
					</div>

					<div class="cart-item__quantity quantity-control<?php echo $quantity_locked ? ' quantity-control--locked' : ''; ?>"<?php echo $quantity_locked ? ' data-qty-locked="true"' : ''; ?> aria-label="<?php esc_attr_e( 'تعداد محصول', 'almas-land' ); ?>">
						<?php
						echo apply_filters( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
							'woocommerce_cart_item_remove_link',
							sprintf(
								'<a role="button" href="%s" class="cart-item__remove remove-button" aria-label="%s" data-product_id="%s" data-product_sku="%s" data-cart-remove%s><svg class="cart-item__remove-icon" width="18" height="18" viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path fill="currentColor" d="M9 3h6l1 2h4v2H4V5h4l1-2Zm1 6h2v9h-2V9Zm4 0h2v9h-2V9ZM7 9h2v9H7V9Zm-1 12h12l1-12H5l1 12Z"/></svg><span class="screen-reader-text">%s</span></a>',
								esc_url( wc_get_cart_remove_url( $cart_item_key ) ),
								esc_attr( sprintf( __( 'حذف %s از سبد خرید', 'almas-land' ), wp_strip_all_tags( $product_name ) ) ),
								esc_attr( $product_id ),
								esc_attr( $_product->get_sku() ),
								$item_qty <= 1 ? '' : ' hidden',
								esc_html__( 'حذف', 'almas-land' )
							),
							$cart_item_key
						);

						if ( $quantity_locked ) {
							$min_quantity = 1;
							$max_quantity = 1;
						} else {
							$min_quantity = 0;
							$max_quantity = $_product->get_max_purchase_quantity();
						}

						$product_quantity = woocommerce_quantity_input(
							array(
								'input_name'   => "cart[{$cart_item_key}][qty]",
								'input_value'  => $cart_item['quantity'],
								'max_value'    => $max_quantity,
								'min_value'    => $min_quantity,
								'product_name' => $product_name,
								'readonly'     => $quantity_locked,
								'classes'      => $quantity_locked ? array( 'input-text', 'qty', 'text', 'qty-locked' ) : array( 'input-text', 'qty', 'text' ),
							),
							$_product,
							false
						);

						echo apply_filters( 'woocommerce_cart_item_quantity', $product_quantity, $cart_item_key, $cart_item ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
						?>
					</div>
				</div>

				<div class="cart-item__main">
					<div class="cart-item__info">
						<h2>
							<?php
							if ( $product_permalink ) {
								printf( '<a href="%s">%s</a>', esc_url( $product_permalink ), wp_kses_post( $product_name ) );
							} else {
								echo wp_kses_post( $product_name );
							}
							?>
						</h2>

						<?php do_action( 'woocommerce_after_cart_item_name', $cart_item, $cart_item_key ); ?>

						<?php if ( $product_features ) : ?>
							<ul class="cart-item__features" aria-label="<?php esc_attr_e( 'ویژگی‌های محصول', 'almas-land' ); ?>">
								<?php foreach ( $product_features as $feature ) : ?>
									<li>
										<span><?php echo esc_html( $feature['label'] ); ?></span>
										<strong><?php echo esc_html( $feature['value'] ); ?></strong>
									</li>
								<?php endforeach; ?>
							</ul>
						<?php endif; ?>

						<?php if ( $_product->backorders_require_notification() && $_product->is_on_backorder( $cart_item['quantity'] ) ) : ?>
							<?php echo wp_kses_post( apply_filters( 'woocommerce_cart_item_backorder_notification', '<p class="backorder_notification">' . esc_html__( 'این کالا پس از تامین موجودی ارسال می‌شود.', 'almas-land' ) . '</p>', $product_id ) ); ?>
						<?php endif; ?>
					</div>

					<div class="cart-item__pricing">
						<strong class="cart-item__price"><?php echo wp_kses_post( $product_subtotal ); ?></strong>
						<?php if ( $discount_percent ) : ?>
							<div class="cart-item__sale">
								<del><?php echo wp_kses_post( almasland_persian_price( wc_price( $regular_price * $cart_item['quantity'] ) ) ); ?></del>
								<span><?php echo esc_html( almasland_persian_digits( (string) $discount_percent ) ); ?>٪</span>
							</div>
						<?php endif; ?>
					</div>

					<div class="cart-item__footer">
						<a class="cart-item__later" href="<?php echo esc_url( wc_get_cart_url() ); ?>"><?php esc_html_e( 'بعدا می‌خرم', 'almas-land' ); ?></a>
					</div>
				</div>
			</div>
		<?php endforeach; ?>

		<?php do_action( 'woocommerce_cart_contents' ); ?>

		<div class="cart-actions-panel">
			<a class="cart-action-link" href="<?php echo esc_url( wc_get_cart_url() ); ?>"><?php esc_html_e( 'انتقال همه به سبد خرید آینده', 'almas-land' ); ?></a>
			<a class="cart-action-link" href="<?php echo esc_url( wp_nonce_url( add_query_arg( 'almas_empty_cart', '1', wc_get_cart_url() ), 'almas-empty-cart' ) ); ?>"><?php esc_html_e( 'حذف همه محصولات سبد خرید', 'almas-land' ); ?></a>
			<button type="submit" class="btn cart-update-button" name="update_cart" value="<?php esc_attr_e( 'بروزرسانی سبد خرید', 'almas-land' ); ?>" disabled><?php esc_html_e( 'بروزرسانی سبد خرید', 'almas-land' ); ?></button>
			<?php do_action( 'woocommerce_cart_actions' ); ?>
			<?php wp_nonce_field( 'woocommerce-cart', 'woocommerce-cart-nonce' ); ?>
		</div>

		<?php do_action( 'woocommerce_after_cart_contents' ); ?>
		<?php do_action( 'woocommerce_after_cart_table' ); ?>
	</form>

	<?php do_action( 'woocommerce_before_cart_collaterals' ); ?>

	<div class="cart-collaterals cart-layout__collaterals">
		<?php do_action( 'woocommerce_cart_collaterals' ); ?>
	</div>
</div>

<?php if ( ! WC()->cart->is_empty() ) : ?>
	<div class="cart-sticky-bar" aria-label="<?php esc_attr_e( 'ثبت سفارش سریع', 'almas-land' ); ?>">
		<div class="cart-sticky-bar__total">
			<span><?php esc_html_e( 'جمع کل', 'almas-land' ); ?></span>
			<strong data-cart-sticky-total><?php echo wp_kses_post( WC()->cart->get_total() ); ?></strong>
		</div>
		<a class="cart-sticky-bar__checkout btn btn--primary" href="<?php echo esc_url( wc_get_checkout_url() ); ?>">
			<?php esc_html_e( 'ثبت سفارش', 'almas-land' ); ?>
		</a>
	</div>
<?php endif; ?>

<?php do_action( 'woocommerce_after_cart' ); ?>
