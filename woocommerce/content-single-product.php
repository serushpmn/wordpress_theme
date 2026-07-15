<?php
/**
 * Single product content.
 *
 * @package AlmasLand
 */

defined( 'ABSPATH' ) || exit;

global $product;

if ( ! $product ) {
	return;
}

$image_id         = $product->get_image_id();
$gallery_ids      = $product->get_gallery_image_ids();
$images           = array_values( array_filter( array_merge( array( $image_id ), $gallery_ids ) ) );
$main_image       = $image_id ? wp_get_attachment_image_url( $image_id, 'almasland-single' ) : ALMASLAND_URI . '/assets/images/laptop.svg';
$badges           = almasland_get_product_badges( $product );
$cta_text         = $product->get_meta( '_almas_cta_text' );
$features         = $product->get_meta( '_almas_features' );
$specs            = almasland_get_product_specs( $product );
$summary_specs    = array_slice( $specs, 0, 8, true );
$brand            = almasland_get_product_brand( $product );
$installment_text = $product->get_meta( '_almas_installment' );
$delivery_text    = $product->get_meta( '_almas_delivery' );
$sales_text       = $product->get_meta( '_almas_sales' );
$warranty_text    = $product->get_meta( '_almas_warranty' );
$subtitle         = almasland_get_product_english_name( $product );
$rating           = $product->get_average_rating();
$discount_percent = almasland_get_discount_percent( $product );
$stock_qty        = $product->get_stock_quantity();
$stock_label      = $product->is_in_stock()
	? ( $stock_qty ? sprintf( esc_html__( '%s عدد در انبار موجود است', 'almas-land' ), almasland_persian_digits( $stock_qty ) ) : esc_html__( 'آماده ارسال', 'almas-land' ) )
	: esc_html__( 'ناموجود', 'almas-land' );
$is_in_cart       = false;
if ( WC()->cart ) {
	foreach ( WC()->cart->get_cart() as $cart_item ) {
		$cart_product_id = (int) ( ! empty( $cart_item['variation_id'] ) ? $cart_item['variation_id'] : $cart_item['product_id'] );
		$cart_parent_id  = ! empty( $cart_item['product_id'] ) && (int) $cart_item['product_id'] !== $cart_product_id ? (int) $cart_item['product_id'] : 0;
		if ( (int) $product->get_id() === $cart_product_id || ( $cart_parent_id && (int) $product->get_id() === $cart_parent_id ) ) {
			$is_in_cart = true;
			break;
		}
	}
}
?>
<article id="product-<?php the_ID(); ?>" <?php wc_product_class( '', $product ); ?>>
	<?php almasland_breadcrumb(); ?>
	<div class="product-info">
			<?php if ( $badges ) : ?>
				<div class="product-badges" data-product-tags aria-label="<?php esc_attr_e( 'برچسب‌های محصول', 'almas-land' ); ?>">
					<?php foreach ( $badges as $badge ) : ?>
						<span class="product-badge" style="background-color: <?php echo esc_attr( $badge['bg'] ); ?>; color: <?php echo esc_attr( $badge['color'] ); ?>;"><?php echo esc_html( $badge['text'] ); ?></span>
					<?php endforeach; ?>
				</div>
			<?php endif; ?>

			<h1 id="product-title"><?php echo esc_html( get_the_title() ); ?></h1>

			<?php if ( $subtitle ) : ?>
				<p class="product-title-en"><?php echo esc_html( $subtitle ); ?></p>
			<?php endif; ?>


		</div>
<section class="product-wrapper-content">
	<section class="product-main-content">
		<section class="product-summary" aria-labelledby="product-title">
			<div class="product-gallery" aria-label="<?php esc_attr_e( 'تصاویر محصول', 'almas-land' ); ?>">
				<div class="product-gallery__stage">
					<div class="product-gallery__main">
						<img src="<?php echo esc_url( $main_image ); ?>" alt="<?php the_title_attribute(); ?>"data-gallery-main>
						<div class="product-gallery__actions" aria-label="<?php esc_attr_e( 'عملیات محصول', 'almas-land' ); ?>">
							<?php if ( wc_review_ratings_enabled() && $rating > 0 ) : ?>
								<div class="product-gallery__rating" aria-label="<?php esc_attr_e( 'امتیاز محصول', 'almas-land' ); ?>">
									<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 17.3 6.2 20.3l1.1-6.5L2.4 9.2l6.5-1L12 2.3l3.1 5.9 6.5 1-4.9 4.6 1.1 6.5L12 17.3Z"/></svg>
									<span><?php echo esc_html( almasland_persian_digits( number_format( $rating, 1 ) ) ); ?></span>
								</div>
							<?php endif; ?>
							
						</div>
					</div>
				</div>
				<?php if ( count( $images ) > 1 ) : ?>
					<div class="product-gallery__thumbs" role="list">
						<?php foreach ( $images as $index => $thumb_id ) : ?>
							<?php
							$thumb = wp_get_attachment_image_url( $thumb_id, 'thumbnail' );
							$full  = wp_get_attachment_image_url( $thumb_id, 'large' );
							?>
							<button type="button" class="<?php echo 0 === (int) $index ? 'is-active' : ''; ?>" data-gallery-thumb="<?php echo esc_url( $full ); ?>" aria-label="<?php esc_attr_e( 'نمای محصول', 'almas-land' ); ?>">
								<img src="<?php echo esc_url( $thumb ); ?>" alt="" width="48" height="48">
							</button>
						<?php endforeach; ?>
					</div>
				<?php endif; ?>
			</div>

			<div class="product-info">
			<?php if ( $brand ) : ?>
				<p class="product-info__brand"><?php esc_html_e( 'برند:', 'almas-land' ); ?> <strong><?php echo esc_html( $brand ); ?></strong></p>
			<?php endif; ?>
				<?php if ( $summary_specs ) : ?>
					<dl class="product-spec-list" aria-label="<?php esc_attr_e( 'مشخصات کوتاه محصول', 'almas-land' ); ?>">
						<?php foreach ( $summary_specs as $label => $value ) : ?>
							<div><dt><?php echo esc_html( $label ); ?></dt><dd><?php echo esc_html( $value ); ?></dd></div>
						<?php endforeach; ?>
					</dl>
				<?php endif; ?>

				<?php if ( count( $specs ) > count( $summary_specs ) ) : ?>
					<a class="product-more-link" href="#spec-title"><?php esc_html_e( 'مشاهده مشخصات بیشتر', 'almas-land' ); ?></a>
				<?php endif; ?>

				<?php if ( $product->get_short_description() ) : ?>
					<div class="product-excerpt entry-content">
						<?php echo wp_kses_post( wpautop( $product->get_short_description() ) ); ?>
					</div>
				<?php endif; ?>

				<section class="product-consult">
					<div class="consult-card" aria-labelledby="consult-title">
						<h2 id="consult-title"><?php echo esc_html( $cta_text ? $cta_text : __( 'برای دریافت بهترین قیمت و مشاوره خرید، با ما تماس بگیرید', 'almas-land' ) ); ?></h2>
						<div class="consult-card-button">
						<a class="consult-phone consult-phone--outline" href="tel:<?php echo esc_attr( almasland_get_phone_tel() ); ?>"><?php echo esc_html( almasland_get_option( 'phone', '۰۲۱-۸۸۸۸۶۹۵۹' ) ); ?></a>
						<a class="consult-phone consult-phone--solid" href="tel:09359897005"><?php esc_html_e( 'درخواست مشاوره', 'almas-land' ); ?></a>
						<p><?php esc_html_e( 'پاسخگویی سریع، مشاوره تخصصی، قیمت روز', 'almas-land' ); ?></p>
						</div>
					</div>
				</section>
			</div>

		</section>

		<section class="product-trust" aria-label="<?php esc_attr_e( 'مزیت‌های خرید', 'almas-land' ); ?>">
			<div><span></span><?php esc_html_e( 'امکان تحویل اکسپرس', 'almas-land' ); ?></div>
			<div><span></span><?php esc_html_e( 'پرداخت امن اینترنتی', 'almas-land' ); ?></div>
			<div><span></span><?php esc_html_e( '48 ساعت ضمانت بازگشت کالا', 'almas-land' ); ?></div>
			<div><span></span><?php esc_html_e( 'ضمانت اصل بودن کالا', 'almas-land' ); ?></div>
		</section>

		<section class="product-content">
			<?php if ( $product->get_description() ) : ?>
				<article class="content-article" aria-labelledby="review-title">
					<h2 id="review-title"><?php esc_html_e( 'نقد و بررسی', 'almas-land' ); ?></h2>
					<div class="entry-content">
						<?php the_content(); ?>
					</div>
					<?php if ( $features ) : ?>
						<h3><?php esc_html_e( 'ویژگی‌های مهم', 'almas-land' ); ?></h3>
						<div class="entry-content"><?php echo wp_kses_post( wpautop( $features ) ); ?></div>
					<?php endif; ?>
					<?php if ( $product->get_tag_ids() ) : ?>
						<div class="tag-list" aria-label="<?php esc_attr_e( 'برچسب‌ها', 'almas-land' ); ?>">
							<?php echo wp_kses_post( wc_get_product_tag_list( $product->get_id(), '', '', '' ) ); ?>
						</div>
					<?php endif; ?>
				</article>
			<?php endif; ?>

			<section class="spec-table" aria-labelledby="spec-title">
				<h2 id="spec-title"><?php esc_html_e( 'توضیحات تکمیلی', 'almas-land' ); ?></h2>
				<?php if ( $specs ) : ?>
					<dl>
						<?php foreach ( $specs as $label => $value ) : ?>
							<div><dt><?php echo esc_html( $label ); ?></dt><dd><?php echo esc_html( $value ); ?></dd></div>
						<?php endforeach; ?>
					</dl>
				<?php else : ?>
					<p class="product-empty-specs"><?php esc_html_e( 'مشخصات تکمیلی برای این محصول ثبت نشده است.', 'almas-land' ); ?></p>
				<?php endif; ?>
			</section>
		</section>
	</section>

	<aside class="buy-card" aria-label="<?php esc_attr_e( 'خرید محصول', 'almas-land' ); ?>">
			<div class="seller-status">
				<span><?php echo esc_html( $product->get_meta( '_almas_return' ) ? $product->get_meta( '_almas_return' ) : __( '۴۸ ساعت ضمانت بازگشت بی قید و شرط', 'almas-land' ) ); ?></span>
				<span class="stock <?php echo esc_attr( almasland_stock_class( $product ) ); ?>"><?php echo esc_html( $stock_label ); ?></span>
			</div>

			<div class="warranty-box">
				<span><?php esc_html_e( 'گارانتی', 'almas-land' ); ?></span>
				<strong><?php echo esc_html( $warranty_text ? $warranty_text : __( '۱۸ ماه گارانتی و ضمانت اصالت کالا', 'almas-land' ) ); ?></strong>
			</div>

			<div class="installment-box">
				<div style="display: flex; flex-direction: column; align-items: center; ">
				<span class="installment-box__logo">خرید اعتباری با دیجی پی</span>
				<strong><?php echo esc_html( $installment_text ? $installment_text : __( 'خرید اعتباری و اقساطی این کالا', 'almas-land' ) ); ?></strong>
				<a href="<?php echo esc_url( almasland_get_contact_url() ); ?>"><?php esc_html_e( 'مشاهده راهنما', 'almas-land' ); ?></a>
				</div>
				<img src="#" alt="">
			</div>

			<div class="delivery-box">
				<span><?php echo esc_html( $delivery_text ? $delivery_text : __( 'تحویل ۱ ساعته در سراسر تهران', 'almas-land' ) ); ?></span>
			</div>

			<div class="buy-card__trust" aria-label="<?php esc_attr_e( 'امکانات اعتمادساز خرید', 'almas-land' ); ?>">
				<span><?php esc_html_e( 'پرداخت امن', 'almas-land' ); ?></span>
				<span><?php esc_html_e( 'ارسال سریع', 'almas-land' ); ?></span>
				<span><?php esc_html_e( 'ضمانت بازگشت', 'almas-land' ); ?></span>
			</div>

			<div class="woocommerce-notices-wrapper buy-card__notices"></div>

			<?php if ( $is_in_cart ) : ?>
				<div class="single-product-cart-state" aria-label="<?php esc_attr_e( 'وضعیت سبد خرید', 'almas-land' ); ?>">
					<span class="single-product-cart-state__badge">✓ <?php esc_html_e( 'در سبد خرید', 'almas-land' ); ?></span>
					<div class="single-product-cart-state__actions">
						<a class="btn btn--primary" href="<?php echo esc_url( wc_get_cart_url() ); ?>"><?php esc_html_e( 'مشاهده سبد خرید', 'almas-land' ); ?></a>
						<a class="btn btn--ghost" href="<?php echo esc_url( wc_get_checkout_url() ); ?>"><?php esc_html_e( 'تکمیل خرید', 'almas-land' ); ?></a>
					</div>
				</div>
			<?php else : ?>
				<?php woocommerce_template_single_add_to_cart(); ?>
			<?php endif; ?>

			<div class="buy-card__price">
				<?php if ( $product->is_on_sale() && $product->get_regular_price() ) : ?>
					<div class="buy-card__price-meta">
						<?php if ( $discount_percent > 0 ) : ?>
							<span class="discount-badge"><?php echo esc_html( almasland_persian_digits( $discount_percent ) ); ?>٪</span>
						<?php endif; ?>
						<del><?php echo wp_kses_post( wc_price( $product->get_regular_price() ) ); ?></del>
					</div>
				<?php endif; ?>
				<strong class="buy-card__price-current"><?php echo wp_kses_post( wc_price( wc_get_price_to_display( $product ) ) ); ?></strong>
			</div>
	</aside>
</section>
	<?php do_action( 'woocommerce_after_single_product_summary' ); ?>
</article>

<div class="mobile-buy-bar" aria-label="<?php esc_attr_e( 'خرید سریع محصول', 'almas-land' ); ?>">
	<div class="mobile-buy-bar__price">
		<?php if ( $product->is_on_sale() && $product->get_regular_price() ) : ?>
			<div class="mobile-buy-bar__price-meta">
				<?php if ( $discount_percent > 0 ) : ?>
					<span class="discount-badge"><?php echo esc_html( almasland_persian_digits( $discount_percent ) ); ?>٪</span>
				<?php endif; ?>
				<del><?php echo wp_kses_post( wc_price( $product->get_regular_price() ) ); ?></del>
			</div>
		<?php endif; ?>
		<strong class="mobile-buy-bar__price-current"><?php echo wp_kses_post( wc_price( wc_get_price_to_display( $product ) ) ); ?></strong>
	</div>
	<?php if ( $is_in_cart ) : ?>
		<a class="btn btn--primary" href="<?php echo esc_url( wc_get_cart_url() ); ?>"><?php esc_html_e( 'مشاهده سبد خرید', 'almas-land' ); ?></a>
	<?php else : ?>
		<button class="btn btn--primary" type="button" data-mobile-add-to-cart><?php esc_html_e( 'افزودن به سبد خرید', 'almas-land' ); ?></button>
	<?php endif; ?>
</div>
