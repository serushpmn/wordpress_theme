<?php
/**
 * Front page template.
 *
 * @package AlmasLand
 */

get_header();

$img  = trailingslashit( ALMASLAND_URI . '/assets/images' );
$shop = almasland_get_default_shop_url();
if ( ! $shop ) {
	$shop = home_url( '/' );
}

$asset_url = static function ( $file ) use ( $img ) {
	return $img . ltrim( $file, '/' );
};

$category_url = static function ( $candidates ) use ( $shop ) {
	$term = function_exists( 'almasland_get_product_category_by_candidates' ) ? almasland_get_product_category_by_candidates( (array) $candidates ) : null;

	if ( $term instanceof WP_Term ) {
		$link = get_term_link( $term );
		if ( ! is_wp_error( $link ) ) {
			return $link;
		}
	}

	return $shop;
};

$hero_title       = almasland_get_option( 'hero_title', __( 'همه چیز از MSI', 'almas-land' ) );
$hero_text        = almasland_get_option( 'hero_text', __( 'کیفیت، اصالت و پشتیبانی تخصصی الماس لند', 'almas-land' ) );
$hero_button_text = almasland_get_option( 'hero_button_text', __( 'خرید محصولات', 'almas-land' ) );
$products_title   = almasland_get_option( 'home_products_title', __( 'محصولات ویژه', 'almas-land' ) );
$hero_link        = $shop;
$hero_banner      = '';

$sliders = almasland_get_enabled_sliders();
if ( ! empty( $sliders ) ) {
	$slide = $sliders[0];
	$hero_banner = almasland_get_attachment_url( $slide['image'], 'almasland-hero' );
	if ( ! empty( $slide['link'] ) ) {
		$hero_link = $slide['link'];
	}
}

if ( ! $hero_banner ) {
	$hero_banner = almasland_get_option( 'hero_image', '' );
}

if ( ! $hero_banner ) {
	$hero_banner = $asset_url( 'promo.svg' );
}

$render_products = static function ( $args ) {
	if ( ! class_exists( 'WooCommerce' ) || ! function_exists( 'almasland_render_home_product_loop' ) ) {
		return '';
	}

	$args['swiper'] = true;

	ob_start();
	almasland_render_home_product_loop( $args );
	return trim( ob_get_clean() );
};

$featured_ids = array_filter( array_map( 'absint', (array) almasland_get_panel( 'shop', 'featured_product_ids', array() ) ) );
$featured_products = $render_products(
	$featured_ids
		? array(
			'include' => $featured_ids,
			'limit'   => 8,
			'orderby' => 'include',
		)
		: array(
			'limit' => 8,
		)
);

$category_cards = array(
	array(
		'title'      => __( 'مانیتور', 'almas-land' ),
		'image'      => 'category-monitor.svg',
		'class'      => 'category-card--monitor',
		'candidates' => array( 'monitor', 'monitors', 'مانیتور' ),
	),
	array(
		'title'      => __( 'آل این وان', 'almas-land' ),
		'image'      => 'category-ai.svg',
		'class'      => 'category-card--aio',
		'candidates' => array( 'all-in-one', 'allinone', 'aio', 'آل این وان', 'ال این وان' ),
	),
	array(
		'title'      => __( 'لپ تاپ', 'almas-land' ),
		'image'      => 'category-laptop.svg',
		'class'      => 'category-card--laptop',
		'candidates' => array( 'laptop', 'laptops', 'lap-top', 'لپتاپ', 'لپ تاپ' ),
	),
);

$popular_categories = array(
	array(
		'title'      => __( 'لپ تاپ', 'almas-land' ),
		'image'      => 'category-laptop.svg',
		'candidates' => array( 'laptop', 'laptops', 'lap-top', 'لپتاپ', 'لپ تاپ' ),
	),
	array(
		'title'      => __( 'مانیتور', 'almas-land' ),
		'image'      => 'category-monitor.svg',
		'candidates' => array( 'monitor', 'monitors', 'مانیتور' ),
	),
	array(
		'title'      => __( 'موبایل', 'almas-land' ),
		'image'      => 'category-phone.svg',
		'candidates' => array( 'mobile', 'phones', 'phone', 'گوشی', 'موبایل' ),
	),
	array(
		'title'      => __( 'آل این وان', 'almas-land' ),
		'image'      => 'category-ai.svg',
		'candidates' => array( 'all-in-one', 'allinone', 'aio', 'آل این وان', 'ال این وان' ),
	),
	array(
		'title'      => __( 'لوازم جانبی', 'almas-land' ),
		'image'      => 'accessory.svg',
		'candidates' => array( 'accessories', 'accessory', 'لوازم جانبی' ),
	),
	array(
		'title'      => __( 'کیس', 'almas-land' ),
		'image'      => 'laptop.svg',
		'candidates' => array( 'case', 'pc-case', 'کیس' ),
	),
	array(
		'title'      => __( 'پرینتر', 'almas-land' ),
		'image'      => 'promo.svg',
		'candidates' => array( 'printer', 'printers', 'پرینتر' ),
	),
	array(
		'title'      => __( 'هارد اکسترنال', 'almas-land' ),
		'image'      => 'phone.svg',
		'candidates' => array( 'external-hard', 'hard-drive', 'storage', 'هارد اکسترنال' ),
	),
);

$bestsellers = array(
	array(
		'title'      => __( 'پرفروش‌ترین مانیتور', 'almas-land' ),
		'image'      => 'monitor.svg',
		'class'      => 'home-hard-bestseller--monitor',
		'candidates' => array( 'monitor', 'monitors', 'مانیتور' ),
	),
	array(
		'title'      => __( 'پرفروش‌ترین آل این وان', 'almas-land' ),
		'image'      => 'category-ai.svg',
		'class'      => 'home-hard-bestseller--aio',
		'candidates' => array( 'all-in-one', 'allinone', 'aio', 'آل این وان', 'ال این وان' ),
	),
	array(
		'title'      => __( 'پرفروش‌ترین لپ‌تاپ', 'almas-land' ),
		'image'      => 'laptop.svg',
		'class'      => 'home-hard-bestseller--laptop',
		'candidates' => array( 'laptop', 'laptops', 'lap-top', 'لپتاپ', 'لپ تاپ' ),
	),
);

$shelves = array(
	array(
		'id'         => 'home-shelf-laptop',
		'title'      => __( 'لپ تاپ', 'almas-land' ),
		'image'      => 'laptop.svg',
		'class'      => 'shelf-banner--laptop',
		'candidates' => array( 'laptop', 'laptops', 'lap-top', 'لپتاپ', 'لپ تاپ' ),
		'query'      => array( 'limit' => 4 ),
	),
	array(
		'id'         => 'home-shelf-aio',
		'title'      => __( 'آل این وان', 'almas-land' ),
		'image'      => 'category-ai.svg',
		'class'      => 'shelf-banner--aio',
		'candidates' => array( 'all-in-one', 'allinone', 'aio', 'آل این وان', 'ال این وان' ),
		'query'      => array( 'limit' => 4 ),
	),
	array(
		'id'         => 'home-shelf-mobile',
		'title'      => __( 'موبایل', 'almas-land' ),
		'image'      => 'phone.svg',
		'class'      => 'shelf-banner--mobile',
		'candidates' => array( 'mobile', 'phones', 'phone', 'گوشی', 'موبایل' ),
		'query'      => array( 'limit' => 4 ),
	),
	array(
		'id'         => 'home-shelf-budget',
		'title'      => __( 'محصولات اقتصادی', 'almas-land' ),
		'image'      => 'accessory.svg',
		'class'      => 'shelf-banner--budget',
		'candidates' => array( 'economic', 'budget', 'اقتصادی', 'محصولات اقتصادی' ),
		'query'      => array(
			'limit'   => 4,
			'orderby' => 'price',
			'order'   => 'ASC',
		),
	),
);
?>
<div class="home-hard">
	<section class="hero-section home-hard-hero">
		<div class="container">
			<a class="hero-banner home-hard-hero__banner" href="<?php echo esc_url( $hero_link ); ?>">
				<img src="<?php echo esc_url( $hero_banner ); ?>" alt="<?php echo esc_attr( $hero_title ); ?>" width="1500" height="430" decoding="async" fetchpriority="high">
				<div class="home-hard-hero__overlay">
					<h1><?php echo esc_html( $hero_title ); ?></h1>
					<p><?php echo wp_kses_post( $hero_text ); ?></p>
					<span class="btn btn--primary"><?php echo esc_html( $hero_button_text ); ?></span>
				</div>
			</a>
		</div>
	</section>

	<?php if ( $featured_products ) : ?>
		<section class="container product-section home-hard-featured" aria-labelledby="home-featured-products">
			<div class="section-heading section-heading--accent">
				<h2 id="home-featured-products"><?php echo esc_html( $products_title ); ?></h2>
				<a href="<?php echo esc_url( $shop ); ?>"><?php esc_html_e( 'مشاهده همه', 'almas-land' ); ?></a>
			</div>
			<div class="home-product-swiper home-featured-swiper swiper" data-home-swiper="products" dir="rtl">
				<?php echo $featured_products; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
				<div class="home-swiper-controls">
					<button class="home-swiper-button home-swiper-button--prev" type="button" aria-label="<?php esc_attr_e( 'اسلاید قبلی', 'almas-land' ); ?>">
						<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M14.7 6.3 9 12l5.7 5.7 1.4-1.4-4.3-4.3 4.3-4.3-1.4-1.4Z"/></svg>
					</button>
					<button class="home-swiper-button home-swiper-button--next" type="button" aria-label="<?php esc_attr_e( 'اسلاید بعدی', 'almas-land' ); ?>">
						<svg viewBox="0 0 24 24" aria-hidden="true"><path d="m9.3 17.7 5.7-5.7-5.7-5.7-1.4 1.4 4.3 4.3-4.3 4.3 1.4 1.4Z"/></svg>
					</button>
				</div>
				<div class="home-swiper-pagination"></div>
			</div>
		</section>
	<?php endif; ?>

	<section class="container home-category-row" aria-label="<?php esc_attr_e( 'دسته‌بندی‌های ویژه', 'almas-land' ); ?>">
		<div class="section-heading">
			<h2><?php esc_html_e( 'دسته‌بندی‌های ویژه', 'almas-land' ); ?></h2>
		</div>
		<div class="home-category-row__swiper swiper" data-home-swiper="categories" dir="rtl">
			<div class="home-category-row__list swiper-wrapper">
				<?php foreach ( $category_cards as $category ) : ?>
					<a class="swiper-slide category-card category-card--hard <?php echo esc_attr( $category['class'] ); ?> home-category-card" href="<?php echo esc_url( $category_url( $category['candidates'] ) ); ?>">
						<img src="<?php echo esc_url( $asset_url( $category['image'] ) ); ?>" alt="<?php echo esc_attr( $category['title'] ); ?>" width="320" height="420" loading="lazy" decoding="async">
						<span><?php echo esc_html( $category['title'] ); ?></span>
					</a>
				<?php endforeach; ?>
			</div>
			<div class="home-swiper-controls">
				<button class="home-swiper-button home-swiper-button--prev" type="button" aria-label="<?php esc_attr_e( 'اسلاید قبلی', 'almas-land' ); ?>">
					<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M14.7 6.3 9 12l5.7 5.7 1.4-1.4-4.3-4.3 4.3-4.3-1.4-1.4Z"/></svg>
				</button>
				<button class="home-swiper-button home-swiper-button--next" type="button" aria-label="<?php esc_attr_e( 'اسلاید بعدی', 'almas-land' ); ?>">
					<svg viewBox="0 0 24 24" aria-hidden="true"><path d="m9.3 17.7 5.7-5.7-5.7-5.7-1.4 1.4 4.3 4.3-4.3 4.3 1.4 1.4Z"/></svg>
				</button>
			</div>
			<div class="home-swiper-pagination"></div>
		</div>
	</section>

	<section class="popular-strip home-hard-popular" aria-labelledby="home-popular-title">
		<div class="container">
			<div class="section-heading">
				<h2 id="home-popular-title"><?php esc_html_e( 'دسته‌بندی‌های محبوب', 'almas-land' ); ?></h2>
			</div>
			<div class="home-popular-swiper swiper" data-home-swiper="popular" dir="rtl">
				<div class="popular-grid home-hard-popular__grid swiper-wrapper">
					<?php foreach ( $popular_categories as $category ) : ?>
						<a class="swiper-slide" href="<?php echo esc_url( $category_url( $category['candidates'] ) ); ?>">
							<img src="<?php echo esc_url( $asset_url( $category['image'] ) ); ?>" alt="<?php echo esc_attr( $category['title'] ); ?>" width="80" height="80" loading="lazy" decoding="async">
							<span><?php echo esc_html( $category['title'] ); ?></span>
						</a>
					<?php endforeach; ?>
				</div>
				<div class="home-swiper-controls">
					<button class="home-swiper-button home-swiper-button--prev" type="button" aria-label="<?php esc_attr_e( 'اسلاید قبلی', 'almas-land' ); ?>">
						<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M14.7 6.3 9 12l5.7 5.7 1.4-1.4-4.3-4.3 4.3-4.3-1.4-1.4Z"/></svg>
					</button>
					<button class="home-swiper-button home-swiper-button--next" type="button" aria-label="<?php esc_attr_e( 'اسلاید بعدی', 'almas-land' ); ?>">
						<svg viewBox="0 0 24 24" aria-hidden="true"><path d="m9.3 17.7 5.7-5.7-5.7-5.7-1.4 1.4 4.3 4.3-4.3 4.3 1.4 1.4Z"/></svg>
					</button>
				</div>
				<div class="home-swiper-pagination"></div>
			</div>
		</div>
	</section>

	<section class="container home-hard-promo" aria-label="<?php esc_attr_e( 'پیشنهادها و دسته‌های منتخب', 'almas-land' ); ?>">
		<div class="section-heading">
			<h2><?php esc_html_e( 'پیشنهادهای ویژه', 'almas-land' ); ?></h2>
		</div>
		<div class="home-category-swiper swiper" data-home-swiper="categories" dir="rtl">
			<div class="home-hard-promo__slides swiper-wrapper">
				<article class="deal-card home-hard-deal swiper-slide">
					<span class="badge"><?php esc_html_e( 'پیشنهاد ویژه', 'almas-land' ); ?></span>
					<img src="<?php echo esc_url( $asset_url( 'monitor.svg' ) ); ?>" alt="" width="170" height="170" loading="lazy" decoding="async">
					<div>
						<h2><?php esc_html_e( 'مانیتور حرفه‌ای', 'almas-land' ); ?></h2>
						<p><?php esc_html_e( 'تخفیف ویژه امروز', 'almas-land' ); ?></p>
						<strong><?php esc_html_e( '۱۲,۹۹۰,۰۰۰ تومان', 'almas-land' ); ?></strong>
						<div class="home-hard-deal__timer" aria-hidden="true">
							<span>۱۲</span><span>۳۴</span><span>۵۶</span>
						</div>
					</div>
				</article>

				<?php foreach ( $category_cards as $category ) : ?>
					<a class="category-card category-card--hard <?php echo esc_attr( $category['class'] ); ?> swiper-slide" href="<?php echo esc_url( $category_url( $category['candidates'] ) ); ?>">
						<img src="<?php echo esc_url( $asset_url( $category['image'] ) ); ?>" alt="<?php echo esc_attr( $category['title'] ); ?>" width="320" height="420" loading="lazy" decoding="async">
						<span><?php echo esc_html( $category['title'] ); ?></span>
					</a>
				<?php endforeach; ?>
			</div>
			<div class="home-swiper-controls">
				<button class="home-swiper-button home-swiper-button--prev" type="button" aria-label="<?php esc_attr_e( 'اسلاید قبلی', 'almas-land' ); ?>">
					<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M14.7 6.3 9 12l5.7 5.7 1.4-1.4-4.3-4.3 4.3-4.3-1.4-1.4Z"/></svg>
				</button>
				<button class="home-swiper-button home-swiper-button--next" type="button" aria-label="<?php esc_attr_e( 'اسلاید بعدی', 'almas-land' ); ?>">
					<svg viewBox="0 0 24 24" aria-hidden="true"><path d="m9.3 17.7 5.7-5.7-5.7-5.7-1.4 1.4 4.3 4.3-4.3 4.3 1.4 1.4Z"/></svg>
				</button>
			</div>
			<div class="home-swiper-pagination"></div>
		</div>
	</section>

	<section class="container home-hard-bestsellers" aria-label="<?php esc_attr_e( 'پرفروش‌ترین‌ها', 'almas-land' ); ?>">
		<div class="home-bestseller-swiper swiper" data-home-swiper="bestsellers" dir="rtl">
			<div class="promo-row swiper-wrapper">
				<?php foreach ( $bestsellers as $item ) : ?>
					<a class="home-hard-bestseller <?php echo esc_attr( $item['class'] ); ?> swiper-slide" href="<?php echo esc_url( $category_url( $item['candidates'] ) ); ?>">
						<img src="<?php echo esc_url( $asset_url( $item['image'] ) ); ?>" alt="<?php echo esc_attr( $item['title'] ); ?>" width="88" height="88" loading="lazy" decoding="async">
						<span><?php echo esc_html( $item['title'] ); ?></span>
					</a>
				<?php endforeach; ?>
			</div>
			<div class="home-swiper-controls">
				<button class="home-swiper-button home-swiper-button--prev" type="button" aria-label="<?php esc_attr_e( 'اسلاید قبلی', 'almas-land' ); ?>">
					<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M14.7 6.3 9 12l5.7 5.7 1.4-1.4-4.3-4.3 4.3-4.3-1.4-1.4Z"/></svg>
				</button>
				<button class="home-swiper-button home-swiper-button--next" type="button" aria-label="<?php esc_attr_e( 'اسلاید بعدی', 'almas-land' ); ?>">
					<svg viewBox="0 0 24 24" aria-hidden="true"><path d="m9.3 17.7 5.7-5.7-5.7-5.7-1.4 1.4 4.3 4.3-4.3 4.3 1.4 1.4Z"/></svg>
				</button>
			</div>
			<div class="home-swiper-pagination"></div>
		</div>
	</section>

	<?php foreach ( $shelves as $shelf ) : ?>
		<?php
		$query = $shelf['query'];
		$term  = function_exists( 'almasland_get_product_category_by_candidates' ) ? almasland_get_product_category_by_candidates( $shelf['candidates'] ) : null;

		if ( $term instanceof WP_Term ) {
			$query['category'] = array( $term->slug );
		} else {
			$query['category_candidates']  = $shelf['candidates'];
			$query['allow_empty_category'] = true;
		}

		$products_html = $render_products( $query );
		if ( ! $products_html ) {
			continue;
		}
		?>
		<section class="container product-section home-hard-shelf" aria-labelledby="<?php echo esc_attr( $shelf['id'] ); ?>">
			<div class="section-heading">
				<h2 id="<?php echo esc_attr( $shelf['id'] ); ?>"><?php echo esc_html( $shelf['title'] ); ?></h2>
				<a href="<?php echo esc_url( $category_url( $shelf['candidates'] ) ); ?>"><?php esc_html_e( 'مشاهده همه', 'almas-land' ); ?></a>
			</div>
			<div class="shelf-layout">
				<a class="shelf-banner <?php echo esc_attr( $shelf['class'] ); ?>" href="<?php echo esc_url( $category_url( $shelf['candidates'] ) ); ?>">
					<img src="<?php echo esc_url( $asset_url( $shelf['image'] ) ); ?>" alt="<?php echo esc_attr( $shelf['title'] ); ?>" width="420" height="420" loading="lazy" decoding="async">
					<span><?php echo esc_html( $shelf['title'] ); ?></span>
				</a>
				<div class="home-hard-shelf__products home-product-swiper swiper" data-home-swiper="products" dir="rtl">
					<?php echo $products_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
					<div class="home-swiper-controls">
						<button class="home-swiper-button home-swiper-button--prev" type="button" aria-label="<?php esc_attr_e( 'اسلاید قبلی', 'almas-land' ); ?>">
							<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M14.7 6.3 9 12l5.7 5.7 1.4-1.4-4.3-4.3 4.3-4.3-1.4-1.4Z"/></svg>
						</button>
						<button class="home-swiper-button home-swiper-button--next" type="button" aria-label="<?php esc_attr_e( 'اسلاید بعدی', 'almas-land' ); ?>">
							<svg viewBox="0 0 24 24" aria-hidden="true"><path d="m9.3 17.7 5.7-5.7-5.7-5.7-1.4 1.4 4.3 4.3-4.3 4.3 1.4 1.4Z"/></svg>
						</button>
					</div>
					<div class="home-swiper-pagination"></div>
				</div>
			</div>
		</section>
	<?php endforeach; ?>
</div>
<?php
get_footer();
