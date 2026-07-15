<?php
/**
 * Shipping methods in cart totals.
 *
 * @package AlmasLand
 * @version 8.8.0
 */

defined( 'ABSPATH' ) || exit;

$formatted_destination    = isset( $formatted_destination ) ? $formatted_destination : WC()->countries->get_formatted_address( $package['destination'], '، ' );
$has_calculated_shipping  = ! empty( $has_calculated_shipping );
$show_shipping_calculator = ! empty( $show_shipping_calculator );
$calculator_text          = '';

/**
 * Translate common method titles for the Persian cart UI.
 *
 * @param WC_Shipping_Rate $method Shipping method.
 * @return string
 */
$almasland_shipping_label = static function ( $method ) {
	$label = wp_strip_all_tags( wc_cart_totals_shipping_method_label( $method ) );
	$lower = function_exists( 'almasland_normalize_search_text' ) ? almasland_normalize_search_text( $label ) : strtolower( $label );
	$has   = static function ( $needle ) use ( $lower ) {
		return function_exists( 'almasland_search_text_contains' ) ? almasland_search_text_contains( $lower, $needle ) : false !== strpos( $lower, $needle );
	};

	if ( $has( 'free' ) || $has( 'رایگان' ) ) {
		return __( 'ارسال رایگان', 'almas-land' );
	}

	if ( $has( 'pickup' ) || $has( 'local' ) || $has( 'حضوری' ) ) {
		return __( 'دریافت حضوری', 'almas-land' );
	}

	if ( $has( 'flat rate' ) || $has( 'standard' ) ) {
		return __( 'ارسال تیپاکس عادی (برای شهرستان)', 'almas-land' );
	}

	if ( $has( 'express' ) || $has( 'quick' ) || $has( 'پیک' ) ) {
		return __( 'ارسال با پیک (ویژه تهران)', 'almas-land' );
	}

	return almasland_persian_digits( $label );
};
?>
<tr class="woocommerce-shipping-totals shipping">
	<th><?php esc_html_e( 'روش ارسال', 'almas-land' ); ?></th>
	<td data-title="<?php esc_attr_e( 'روش ارسال', 'almas-land' ); ?>">
		<?php if ( ! empty( $available_methods ) && is_array( $available_methods ) ) : ?>
			<ul id="shipping_method" class="woocommerce-shipping-methods">
				<?php foreach ( $available_methods as $method ) : ?>
					<li>
						<?php
						$input_id = 'shipping_method_' . $index . '_' . sanitize_title( $method->id );
						if ( 1 < count( $available_methods ) ) {
							?>
							<input type="radio" name="shipping_method[<?php echo esc_attr( (string) $index ); ?>]" data-index="<?php echo esc_attr( (string) $index ); ?>" id="<?php echo esc_attr( $input_id ); ?>" value="<?php echo esc_attr( $method->id ); ?>" class="shipping_method" <?php checked( $method->id, $chosen_method ); ?> />
							<?php
						} else {
							?>
							<input type="hidden" name="shipping_method[<?php echo esc_attr( (string) $index ); ?>]" data-index="<?php echo esc_attr( (string) $index ); ?>" id="<?php echo esc_attr( $input_id ); ?>" value="<?php echo esc_attr( $method->id ); ?>" class="shipping_method" />
							<?php
						}
						printf( '<label for="%1$s">%2$s</label>', esc_attr( $input_id ), esc_html( $almasland_shipping_label( $method ) ) );
						do_action( 'woocommerce_after_shipping_rate', $method, $index );
						?>
					</li>
				<?php endforeach; ?>
			</ul>
			<?php if ( is_cart() ) : ?>
				<p class="woocommerce-shipping-destination">
					<?php
					if ( $formatted_destination ) {
						echo wp_kses_post( sprintf( __( 'ارسال به %s', 'almas-land' ), '<strong>' . esc_html( $formatted_destination ) . '</strong>' ) );
						$calculator_text = esc_html__( 'تغییر آدرس', 'almas-land' );
					} else {
						echo esc_html__( 'گزینه‌های ارسال در مرحله تسویه حساب دقیق‌تر می‌شود.', 'almas-land' );
					}
					?>
				</p>
			<?php endif; ?>
		<?php elseif ( ! $has_calculated_shipping || ! $formatted_destination ) : ?>
			<?php echo esc_html__( 'برای نمایش روش‌های ارسال، آدرس خود را وارد کنید.', 'almas-land' ); ?>
		<?php elseif ( ! is_cart() ) : ?>
			<?php echo esc_html__( 'برای این آدرس روش ارسال فعالی پیدا نشد.', 'almas-land' ); ?>
		<?php else : ?>
			<?php
			echo wp_kses_post( sprintf( __( 'برای %s روش ارسال فعالی پیدا نشد.', 'almas-land' ), '<strong>' . esc_html( $formatted_destination ) . '</strong>' ) );
			$calculator_text = esc_html__( 'ورود آدرس دیگر', 'almas-land' );
			?>
		<?php endif; ?>

		<?php if ( $show_package_details ) : ?>
			<?php echo '<p class="woocommerce-shipping-contents"><small>' . esc_html( $package_details ) . '</small></p>'; ?>
		<?php endif; ?>

		<?php if ( $show_shipping_calculator ) : ?>
			<?php woocommerce_shipping_calculator( $calculator_text ); ?>
		<?php endif; ?>
	</td>
</tr>
