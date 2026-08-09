<?php
/**
 * Variable product add to cart.
 *
 * @package AlmasLand
 * @version 10.9.0
 */

defined( 'ABSPATH' ) || exit;

global $product;

$variations_json = wp_json_encode( $available_variations );
$variations_attr = function_exists( 'wc_esc_json' ) ? wc_esc_json( $variations_json ) : _wp_specialchars( $variations_json, ENT_QUOTES, 'UTF-8', true );

do_action( 'woocommerce_before_add_to_cart_form' );
?>

<form
	class="variations_form cart almas-variations-form"
	action="<?php echo esc_url( apply_filters( 'woocommerce_add_to_cart_form_action', $product->get_permalink() ) ); ?>"
	method="post"
	enctype="multipart/form-data"
	data-product_id="<?php echo absint( $product->get_id() ); ?>"
	data-product_variations="<?php echo $variations_attr; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>"
>
	<?php do_action( 'woocommerce_before_variations_form' ); ?>

	<?php if ( empty( $available_variations ) && false !== $available_variations ) : ?>
		<p class="stock out-of-stock">
			<?php echo esc_html( apply_filters( 'woocommerce_out_of_stock_message', __( 'این محصول در حال حاضر ناموجود است.', 'almas-land' ) ) ); ?>
		</p>
	<?php else : ?>
		<div class="variations almas-variations" role="group" aria-label="<?php esc_attr_e( 'انتخاب گزینه‌های محصول', 'almas-land' ); ?>">
			<?php foreach ( $attributes as $attribute_name => $options ) : ?>
				<?php
				$attribute_id    = sanitize_title( $attribute_name );
				$attribute_label = wc_attribute_label( $attribute_name );
				?>
				<div class="almas-variation">
					<label class="almas-variation__label" for="<?php echo esc_attr( $attribute_id ); ?>">
						<?php echo esc_html( $attribute_label ); ?>
					</label>
					<div class="almas-variation__control value">
						<?php
						wc_dropdown_variation_attribute_options(
							array(
								'options'   => $options,
								'attribute' => $attribute_name,
								'product'   => $product,
								'class'     => 'almas-variation__select',
								'show_option_none' => sprintf(
									/* translators: %s: attribute label */
									__( 'انتخاب %s', 'almas-land' ),
									$attribute_label
								),
							)
						);
						?>
					</div>
				</div>
			<?php endforeach; ?>

			<?php
			echo wp_kses_post(
				apply_filters(
					'woocommerce_reset_variations_link',
					'<a class="reset_variations" href="#" aria-label="' . esc_attr__( 'پاک کردن گزینه‌ها', 'almas-land' ) . '">' . esc_html__( 'پاک کردن انتخاب', 'almas-land' ) . '</a>'
				)
			);
			?>
		</div>

		<div class="reset_variations_alert screen-reader-text" role="alert" aria-live="polite" aria-relevant="all"></div>

		<?php do_action( 'woocommerce_after_variations_table' ); ?>

		<div class="single_variation_wrap">
			<?php
			do_action( 'woocommerce_before_single_variation' );
			do_action( 'woocommerce_single_variation' );
			do_action( 'woocommerce_after_single_variation' );
			?>
		</div>
	<?php endif; ?>

	<?php do_action( 'woocommerce_after_variations_form' ); ?>
</form>

<?php
do_action( 'woocommerce_after_add_to_cart_form' );
