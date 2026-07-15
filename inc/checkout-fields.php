<?php
/**
 * Checkout field labels and Persian strings.
 *
 * @package AlmasLand
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Persian labels for default address fields.
 *
 * @param array $fields Address fields.
 * @return array
 */
function almasland_default_address_fields( $fields ) {
	$labels = array(
		'first_name' => __( 'نام', 'almas-land' ),
		'last_name'  => __( 'نام خانوادگی', 'almas-land' ),
		'company'    => __( 'نام شرکت', 'almas-land' ),
		'country'    => __( 'کشور', 'almas-land' ),
		'address_1'  => __( 'آدرس', 'almas-land' ),
		'address_2'  => __( 'واحد / پلاک', 'almas-land' ),
		'city'       => __( 'شهر', 'almas-land' ),
		'state'      => __( 'استان', 'almas-land' ),
		'postcode'   => __( 'کد پستی', 'almas-land' ),
	);

	foreach ( $labels as $key => $label ) {
		if ( isset( $fields[ $key ] ) ) {
			$fields[ $key ]['label'] = $label;
		}
	}

	return $fields;
}
add_filter( 'woocommerce_default_address_fields', 'almasland_default_address_fields', 20 );

/**
 * Persian checkout fields and two-column layout classes.
 *
 * @param array $fields Checkout fields.
 * @return array
 */
function almasland_checkout_fields( $fields ) {
	$label_map = array(
		'billing_first_name'  => __( 'نام', 'almas-land' ),
		'billing_last_name'   => __( 'نام خانوادگی', 'almas-land' ),
		'billing_company'     => __( 'نام شرکت', 'almas-land' ),
		'billing_country'     => __( 'کشور', 'almas-land' ),
		'billing_address_1'   => __( 'آدرس کامل', 'almas-land' ),
		'billing_address_2'   => __( 'واحد / پلاک', 'almas-land' ),
		'billing_city'        => __( 'شهر', 'almas-land' ),
		'billing_state'       => __( 'استان', 'almas-land' ),
		'billing_postcode'    => __( 'کد پستی', 'almas-land' ),
		'billing_phone'       => __( 'شماره موبایل', 'almas-land' ),
		'billing_email'       => __( 'ایمیل', 'almas-land' ),
		'shipping_first_name' => __( 'نام گیرنده', 'almas-land' ),
		'shipping_last_name'  => __( 'نام خانوادگی گیرنده', 'almas-land' ),
		'shipping_company'    => __( 'نام شرکت', 'almas-land' ),
		'shipping_country'    => __( 'کشور', 'almas-land' ),
		'shipping_address_1'  => __( 'آدرس کامل', 'almas-land' ),
		'shipping_address_2'  => __( 'واحد / پلاک', 'almas-land' ),
		'shipping_city'       => __( 'شهر', 'almas-land' ),
		'shipping_state'      => __( 'استان', 'almas-land' ),
		'shipping_postcode'   => __( 'کد پستی', 'almas-land' ),
		'order_comments'      => __( 'توضیحات سفارش', 'almas-land' ),
		'account_username'    => __( 'نام کاربری', 'almas-land' ),
		'account_password'    => __( 'رمز عبور', 'almas-land' ),
		'account_password-2'  => __( 'تکرار رمز عبور', 'almas-land' ),
	);

	$placeholder_map = array(
		'billing_first_name'  => __( 'نام خود را وارد کنید', 'almas-land' ),
		'billing_last_name'   => __( 'نام خانوادگی', 'almas-land' ),
		'billing_address_1'   => __( 'خیابان، کوچه، پلاک', 'almas-land' ),
		'billing_city'        => __( 'مثلاً تهران', 'almas-land' ),
		'billing_postcode'    => __( 'کد پستی ۱۰ رقمی', 'almas-land' ),
		'billing_phone'       => __( '۰۹۱۲۱۲۳۴۵۶۷', 'almas-land' ),
		'billing_email'       => __( 'name@example.com', 'almas-land' ),
		'shipping_first_name' => __( 'نام گیرنده', 'almas-land' ),
		'shipping_last_name'  => __( 'نام خانوادگی گیرنده', 'almas-land' ),
		'shipping_address_1'  => __( 'آدرس تحویل سفارش', 'almas-land' ),
		'shipping_city'       => __( 'شهر', 'almas-land' ),
		'shipping_postcode'   => __( 'کد پستی', 'almas-land' ),
		'order_comments'      => __( 'نکته‌ای درباره زمان تحویل یا بسته‌بندی...', 'almas-land' ),
	);

	$wide_fields = array(
		'billing_address_1',
		'billing_address_2',
		'shipping_address_1',
		'shipping_address_2',
		'order_comments',
		'account_password',
		'account_password-2',
	);

	foreach ( $fields as $group => &$group_fields ) {
		foreach ( $group_fields as $key => &$field ) {
			if ( isset( $label_map[ $key ] ) ) {
				$field['label'] = $label_map[ $key ];
			}

			if ( isset( $placeholder_map[ $key ] ) ) {
				$field['placeholder'] = $placeholder_map[ $key ];
			}

			if ( in_array( $key, $wide_fields, true ) ) {
				$field['class'] = array( 'form-row-wide' );
			} else {
				$field['class'] = array( 'form-row-first' );
			}
		}
	}

	return $fields;
}
add_filter( 'woocommerce_checkout_fields', 'almasland_checkout_fields', 20 );

/**
 * Persian shipping package title on checkout.
 *
 * @param string $name Package name.
 * @return string
 */
function almasland_shipping_package_name( $name ) {
	return __( 'روش ارسال', 'almas-land' );
}
add_filter( 'woocommerce_shipping_package_name', 'almasland_shipping_package_name', 10, 1 );

/**
 * Persian coupon row label.
 *
 * @param string    $label  Label HTML.
 * @param WC_Coupon $coupon Coupon.
 * @return string
 */
function almasland_coupon_label( $label, $coupon ) {
	return sprintf( __( 'تخفیف: %s', 'almas-land' ), esc_html( $coupon->get_code() ) );
}
add_filter( 'woocommerce_cart_totals_coupon_label', 'almasland_coupon_label', 10, 2 );

/**
 * Translate common WooCommerce strings on checkout.
 *
 * @param string $translated Translated text.
 * @param string $text       Original text.
 * @param string $domain     Text domain.
 * @return string
 */
function almasland_checkout_gettext( $translated, $text, $domain ) {
	if ( ! function_exists( 'is_checkout' ) || ! is_checkout() || is_wc_endpoint_url( 'order-received' ) ) {
		return $translated;
	}

	if ( 'woocommerce' !== $domain && 'default' !== $domain ) {
		return $translated;
	}

	$map = array(
		'Subtotal'                         => 'جمع کالاها',
		'Shipping'                         => 'هزینه ارسال',
		'Total'                            => 'جمع کل',
		'Tax'                              => 'مالیات',
		'VAT'                              => 'مالیات بر ارزش افزوده',
		'Update totals'                      => 'بروزرسانی جمع کل',
		'Apply coupon'                       => 'اعمال کد',
		'Coupon code'                        => 'کد تخفیف',
		'Billing details'                    => 'اطلاعات صورتحساب',
		'Additional information'           => 'اطلاعات تکمیلی',
		'Your order'                         => 'سفارش شما',
		'Order notes'                        => 'یادداشت سفارش',
		'Notes about your order, e.g. special notes for delivery.' => 'توضیحات تکمیلی درباره نحوه ارسال یا تحویل.',
		'Create an account?'               => 'ایجاد حساب کاربری؟',
		'Ship to a different address?'       => 'ارسال به آدرس دیگر',
		'Have a coupon?'                     => 'کد تخفیف دارید؟',
		'Click here to enter your code'      => 'کد را وارد کنید',
		'required'                           => 'الزامی',
		'Privacy policy'                     => 'حریم خصوصی',
		'Terms and conditions'               => 'قوانین و مقررات',
		'Place order'                        => 'ثبت سفارش',
		'Return to cart'                     => 'بازگشت به سبد',
		'Payment method'                     => 'روش پرداخت',
		'(optional)'                         => '(اختیاری)',
		'optional'                           => 'اختیاری',
	);

	return isset( $map[ $text ] ) ? $map[ $text ] : $translated;
}
add_filter( 'gettext', 'almasland_checkout_gettext', 20, 3 );

/**
 * Privacy policy text in Persian.
 *
 * @return string
 */
function almasland_checkout_privacy_policy_text() {
	return __( 'اطلاعات شما فقط برای پردازش سفارش و پشتیبانی استفاده می‌شود.', 'almas-land' );
}
add_filter( 'woocommerce_checkout_privacy_policy_text', 'almasland_checkout_privacy_policy_text' );

/**
 * Terms checkbox text in Persian.
 *
 * @return string
 */
function almasland_terms_checkbox_text() {
	return __( 'قوانین و مقررات فروشگاه را مطالعه کرده و می‌پذیرم.', 'almas-land' );
}
add_filter( 'woocommerce_get_terms_and_conditions_checkbox_text', 'almasland_terms_checkbox_text' );
