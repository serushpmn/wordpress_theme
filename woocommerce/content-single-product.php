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
$warranty_text    = trim( wp_strip_all_tags( (string) $product->get_attribute( 'guarantee' ) ) );
$subtitle         = almasland_get_product_english_name( $product );
$rating           = $product->get_average_rating();
$stock_qty   = $product->get_stock_quantity();
$is_variable = $product->is_type( 'variable' );
$is_used_product = function_exists( 'almasland_is_used_product' ) ? almasland_is_used_product( $product ) : has_term( 'used', 'product_cat', $product->get_id() );
$apsb_specs_html  = $is_used_product && function_exists( 'apsb_render_product_specs' ) ? apsb_render_product_specs( $product->get_id() ) : '';
$stock_label = $is_variable
	? esc_html__( 'موجودی پس از انتخاب گزینه مشخص می‌شود', 'almas-land' )
	: (
		$product->is_in_stock()
			? (
				$stock_qty
					? sprintf(
						/* translators: %s: stock quantity */
						esc_html__( 'موجود در انبار — فقط %s عدد باقی مانده', 'almas-land' ),
						almasland_persian_digits( $stock_qty )
					)
					: esc_html__( 'موجود در انبار', 'almas-land' )
			)
			: esc_html__( 'ناموجود', 'almas-land' )
	);
$is_in_cart = false;

if ( ! $is_variable && WC()->cart ) {
	foreach ( WC()->cart->get_cart() as $cart_item ) {
		$cart_product_id = (int) ( ! empty( $cart_item['variation_id'] ) ? $cart_item['variation_id'] : $cart_item['product_id'] );
		$cart_parent_id  = ! empty( $cart_item['product_id'] ) && (int) $cart_item['product_id'] !== $cart_product_id ? (int) $cart_item['product_id'] : 0;
		if ( (int) $product->get_id() === $cart_product_id || ( $cart_parent_id && (int) $product->get_id() === $cart_parent_id ) ) {
			$is_in_cart = true;
			break;
		}
	}
}

$price_html       = $product->get_price_html();
$buy_price_html   = function_exists( 'almasland_get_buy_price_html' ) ? almasland_get_buy_price_html( $product ) : $price_html;
$show_price       = $buy_price_html !== '';
$has_buy_price    = function_exists( 'almasland_product_has_purchasable_price' ) ? almasland_product_has_purchasable_price( $product ) : true;
$contact_phone    = almasland_get_phone_tel();
?>
<article id="product-<?php the_ID(); ?>" <?php wc_product_class( $is_variable ? 'product--variable' : '', $product ); ?>>
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
<section class="product-wrapper-content<?php echo $is_used_product ? ' product-wrapper-content--used' : ''; ?>">
	<section class="product-main-content">
		<section class="product-summary<?php echo $is_used_product ? ' product-summary--used' : ''; ?>" aria-labelledby="product-title">
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

			<div class="product-info<?php echo $is_used_product ? ' product-info--used' : ''; ?>">
			<?php if ( $brand ) : ?>
				<p class="product-info__brand"><?php esc_html_e( 'برند:', 'almas-land' ); ?> <strong><?php echo esc_html( $brand ); ?></strong></p>
			<?php endif; ?>

			<?php if ( $is_used_product && $apsb_specs_html ) : ?>
				<section class="product-info-apsb" aria-labelledby="used-product-specs-title">
					<div class="product-info-apsb__header">
						<h2 id="used-product-specs-title"><?php esc_html_e( 'گزارش وضعیت و سلامت دستگاه', 'almas-land' ); ?></h2>
						<p><?php esc_html_e( 'دستگاه توسط کارشناسان الماس لند بررسی و تست شده است.', 'almas-land' ); ?></p>
					</div>
					<div class="product-info-apsb__body">
						<?php echo $apsb_specs_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
					</div>
					<div class="product-info-apsb__footer">
						<div class="product-info-apsb__trust">
							<span class="product-info-apsb__trust-icon" aria-hidden="true">✓</span>
							<div>
								<h3><?php esc_html_e( 'تست و بررسی شده توسط کارشناسان الماس لند', 'almas-land' ); ?></h3>
								<p><?php esc_html_e( 'ما به شفافیت در فروش اعتماد داریم. هر دستگاه قبل از فروش به صورت کامل بررسی و تست می‌شود.', 'almas-land' ); ?></p>
							</div>
						</div>
						
					</div>
				</section>
			<?php else : ?>
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
			<?php endif; ?>

				<?php if ( ! $is_used_product && $product->get_short_description() ) : ?>
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
		<span class="buy-card__stock stock <?php echo esc_attr( almasland_stock_class( $product ) ); ?>" data-product-stock>
			<span class="buy-card__stock-dot" aria-hidden="true"></span>
			<?php echo esc_html( $stock_label ); ?>
		</span>

		<div class="buy-card__feature">
			<span class="buy-card__feature-icon" aria-hidden="true">
				<svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M12 3l7 3v5.2c0 4.4-2.9 8.4-7 9.8-4.1-1.4-7-5.4-7-9.8V6l7-3Z" fill="#fff" opacity=".18"/><path d="M12 3l7 3v5.2c0 4.4-2.9 8.4-7 9.8-4.1-1.4-7-5.4-7-9.8V6l7-3Z" stroke="#fff" stroke-width="1.7" stroke-linejoin="round"/><path d="m8.8 11.6 2.2 2.2 4.4-4.4" stroke="#fff" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"/></svg>
			</span>
			<div class="buy-card__feature-body">
				<strong>
					<?php
					echo esc_html(
						$warranty_text
							? $warranty_text
							: __( 'یک هفته مهلت تست', 'almas-land' )
					);
					?>
				</strong>
			</div>
		</div>

		<?php if ( $show_price ) : ?>
			<div
				class="buy-card__price"
				data-buy-price
				data-price-default="<?php echo esc_attr( wp_strip_all_tags( $buy_price_html ) ); ?>"
			>
				<?php if ( $is_variable ) : ?>
					<div class="buy-card__price-default" data-price-default-html>
						<?php echo $buy_price_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
					</div>
					<div class="buy-card__price-selected" data-price-selected-html hidden></div>
				<?php else : ?>
					<?php echo $buy_price_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
				<?php endif; ?>
			</div>
		<?php endif; ?>

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
			<?php if ( $is_variable ) : ?>
				<p class="buy-card__choose-hint"><?php esc_html_e( 'لطفاً گزینه‌های محصول را انتخاب کنید', 'almas-land' ); ?></p>
				<?php woocommerce_template_single_add_to_cart(); ?>
			<?php elseif ( ! $has_buy_price ) : ?>
				<a class="btn btn--primary btn--block buy-card__contact-cta" href="tel:<?php echo esc_attr( $contact_phone ); ?>">
					<?php esc_html_e( 'تماس بگیرید', 'almas-land' ); ?>
				</a>
			<?php else : ?>
				<?php woocommerce_template_single_add_to_cart(); ?>
			<?php endif; ?>
		<?php endif; ?>

		<a class="buy-card__digipay" href="<?php echo esc_url( almasland_get_contact_url() ); ?>">
			<span class="buy-card__digipay-icon" aria-hidden="true">
				<svg viewBox="0 0 24 24" fill="none"><rect x="2.5" y="5" width="19" height="14" rx="2.5" stroke="currentColor" stroke-width="1.7"/><path d="M2.5 9.5h19" stroke="currentColor" stroke-width="1.7"/><path d="M7 15h4" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/></svg>
			</span>
			<span class="buy-card__digipay-text">
				<strong><?php esc_html_e( 'خرید اعتباری با دیجی‌پی', 'almas-land' ); ?></strong>
				<span><?php echo esc_html( $installment_text ? $installment_text : __( 'خرید اقساطی آسان در چند کلیک', 'almas-land' ) ); ?></span>
			</span>
			<span class="buy-card__digipay-chevron" aria-hidden="true">
				<svg viewBox="0 0 24 24" fill="none"><path d="M14.5 6.5 9 12l5.5 5.5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
			</span>
		</a>

		<div class="buy-card__trust" aria-label="<?php esc_attr_e( 'امکانات اعتمادساز خرید', 'almas-land' ); ?>">
			<div class="buy-card__trust-item">
				<span class="buy-card__trust-icon" aria-hidden="true">
					<svg viewBox="0 0 24 24" fill="none"><rect x="5" y="11" width="14" height="10" rx="2" stroke="currentColor" stroke-width="1.6"/><path d="M8 11V8a4 4 0 0 1 8 0v3" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/></svg>
				</span>
				<strong><?php esc_html_e( 'پرداخت امن', 'almas-land' ); ?></strong>
				<span><?php esc_html_e( 'درگاه بانکی معتبر', 'almas-land' ); ?></span>
			</div>
			<div class="buy-card__trust-item">
				<span class="buy-card__trust-icon" aria-hidden="true">
					<svg viewBox="0 0 24 24" fill="none"><path d="M12 3l7 3v5c0 4.2-2.8 8-7 9.4C7.8 19 5 15.2 5 11V6l7-3Z" stroke="currentColor" stroke-width="1.6"/><path d="m9 12 2 2 4-4" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/></svg>
				</span>
				<strong><?php echo esc_html( $product->get_meta( '_almas_return' ) ? $product->get_meta( '_almas_return' ) : __( 'بازگشت ۴۸ ساعته', 'almas-land' ) ); ?></strong>
				<span><?php esc_html_e( 'بی قید و شرط', 'almas-land' ); ?></span>
			</div>
			<div class="buy-card__trust-item">
				<span class="buy-card__trust-icon" aria-hidden="true">
					<svg viewBox="0 0 24 24" fill="none"><path d="M3 7h11v10H3V7Z" stroke="currentColor" stroke-width="1.6"/><path d="M14 10h4l3 3v4h-7v-7Z" stroke="currentColor" stroke-width="1.6" stroke-linejoin="round"/><circle cx="7" cy="18" r="1.7" stroke="currentColor" stroke-width="1.5"/><circle cx="17" cy="18" r="1.7" stroke="currentColor" stroke-width="1.5"/></svg>
				</span>
				<strong><?php echo esc_html( $delivery_text ? $delivery_text : __( 'تحویل ۱ ساعته', 'almas-land' ) ); ?></strong>
				<span><?php esc_html_e( 'در سراسر تهران', 'almas-land' ); ?></span>
			</div>
		</div>

		<a class="buy-card__policy" href="#spec-title">
			<?php esc_html_e( 'جزئیات شرایط ضمانت بازگشت و گارانتی', 'almas-land' ); ?>
			<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M14.5 6.5 9 12l5.5 5.5" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
		</a>

		<?php if ( $is_used_product ) : ?>
			<div class="buy-card__used">
				<div class="buy-card__used-title">
					<span class="buy-card__used-badge" aria-hidden="true">✓</span>
					<strong><?php esc_html_e( 'الماس لند، خرید مطمئن کالای کارکرده', 'almas-land' ); ?></strong>
				</div>
				<ul class="buy-card__used-list">
					<li>
						<span class="buy-card__used-icon" aria-hidden="true">
							<svg viewBox="0 0 24 24" fill="none"><circle cx="11" cy="11" r="6.5" stroke="currentColor" stroke-width="1.7"/><path d="m16 16 3.5 3.5" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/><path d="M9 11h4M11 9v4" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/></svg>
						</span>
						<span><?php esc_html_e( 'تست و بررسی تخصصی', 'almas-land' ); ?></span>
					</li>
					<li>
						<span class="buy-card__used-icon" aria-hidden="true">
							<svg viewBox="0 0 24 24" fill="none"><path d="M7 8.5V7a5 5 0 0 1 10 0v1.5" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/><rect x="5" y="8.5" width="14" height="11" rx="2.2" stroke="currentColor" stroke-width="1.7"/><path d="M12 12.5v3" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/></svg>
						</span>
						<span><?php esc_html_e( 'بدون تعمیر و باز نشده', 'almas-land' ); ?></span>
					</li>
					<li>
						<span class="buy-card__used-icon" aria-hidden="true">
							<svg viewBox="0 0 24 24" fill="none"><path d="M12 3l7 3v5.2c0 4.4-2.9 8.4-7 9.8-4.1-1.4-7-5.4-7-9.8V6l7-3Z" stroke="currentColor" stroke-width="1.7" stroke-linejoin="round"/><path d="m8.8 11.6 2.2 2.2 4.4-4.4" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/></svg>
						</span>
						<span><?php esc_html_e( 'ضمانت سلامت فنی و ظاهری', 'almas-land' ); ?></span>
					</li>
				</ul>
			</div>
		<?php endif; ?>
	</aside>
</section>
	<?php do_action( 'woocommerce_after_single_product_summary' ); ?>
</article>

<div class="mobile-buy-bar" aria-label="<?php esc_attr_e( 'خرید سریع محصول', 'almas-land' ); ?>">
	<?php if ( $show_price ) : ?>
	<div class="mobile-buy-bar__price" data-mobile-buy-price>
		<?php if ( $is_variable ) : ?>
			<div class="buy-card__price-default" data-price-default-html>
				<?php echo $buy_price_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			</div>
			<div class="buy-card__price-selected" data-price-selected-html hidden></div>
		<?php else : ?>
			<?php echo $buy_price_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
		<?php endif; ?>
	</div>
	<?php endif; ?>
	<?php if ( $is_in_cart ) : ?>
		<a class="btn btn--primary" href="<?php echo esc_url( wc_get_cart_url() ); ?>"><?php esc_html_e( 'مشاهده سبد خرید', 'almas-land' ); ?></a>
	<?php elseif ( ! $has_buy_price && ! $is_variable ) : ?>
		<a class="btn btn--primary" href="tel:<?php echo esc_attr( $contact_phone ); ?>"><?php esc_html_e( 'تماس بگیرید', 'almas-land' ); ?></a>
	<?php else : ?>
		<button class="btn btn--primary" type="button" data-mobile-add-to-cart><?php echo esc_html( $is_variable ? __( 'انتخاب و افزودن به سبد', 'almas-land' ) : __( 'افزودن به سبد خرید', 'almas-land' ) ); ?></button>
	<?php endif; ?>
</div>
