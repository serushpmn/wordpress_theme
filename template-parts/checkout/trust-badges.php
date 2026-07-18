<?php
/**
 * Checkout trust badges below order total.
 *
 * @package AlmasLand
 */
?>
<div class="checkout-trust" aria-label="<?php esc_attr_e( 'اطمینان از خرید', 'almas-land' ); ?>">
	<section class="checkout-trust__features">
		<h3 class="checkout-trust__heading"><?php esc_html_e( 'خرید مطمئن از الماس لند', 'almas-land' ); ?></h3>

		<ul class="checkout-trust__list">
			<li class="checkout-trust__item">
				<span class="checkout-trust__icon" aria-hidden="true">
					<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round">
						<path d="M3 7h11v10H3z"/>
						<path d="M14 10h4l3 3v4h-7"/>
						<circle cx="7" cy="18" r="2"/>
						<circle cx="17" cy="18" r="2"/>
					</svg>
				</span>
				<span class="checkout-trust__label"><?php esc_html_e( 'ارسال سریع', 'almas-land' ); ?></span>
			</li>
			<li class="checkout-trust__item">
				<span class="checkout-trust__icon" aria-hidden="true">
					<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round">
						<path d="M4 14v-2a8 8 0 0 1 16 0v2"/>
						<path d="M4 14a2 2 0 0 0-2 2v1a2 2 0 0 0 2 2h1v-5H4z"/>
						<path d="M20 14a2 2 0 0 1 2 2v1a2 2 0 0 1-2 2h-1v-5h1z"/>
						<path d="M8 21h8"/>
					</svg>
				</span>
				<span class="checkout-trust__label"><?php esc_html_e( 'پشتیبانی ۲۴/۷', 'almas-land' ); ?></span>
			</li>
			<li class="checkout-trust__item">
				<span class="checkout-trust__icon" aria-hidden="true">
					<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round">
						<rect x="4" y="5" width="16" height="16" rx="2"/>
						<path d="M8 3v4M16 3v4M4 11h16"/>
						<path d="M12 14.2v4"/>
						<path d="M10.2 15.5h3.6"/>
					</svg>
				</span>
				<span class="checkout-trust__label"><?php esc_html_e( '۷ روز مهلت تست', 'almas-land' ); ?></span>
			</li>
			<li class="checkout-trust__item">
				<span class="checkout-trust__icon" aria-hidden="true">
					<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round">
						<path d="M12 3l8 3v6c0 5-3.5 8.5-8 10-4.5-1.5-8-5-8-10V6l8-3z"/>
						<path d="M9 12l2 2 4-4"/>
					</svg>
				</span>
				<span class="checkout-trust__label"><?php esc_html_e( 'ضمانت اصالت کالا', 'almas-land' ); ?></span>
			</li>
		</ul>
	</section>

	<section class="checkout-trust__security">
		<span class="checkout-trust__security-icon" aria-hidden="true">
			<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round">
				<path d="M12 3l8 3v6c0 5-3.5 8.5-8 10-4.5-1.5-8-5-8-10V6l8-3z"/>
				<path d="M9 12l2 2 4-4"/>
			</svg>
		</span>
		<div class="checkout-trust__security-text">
			<strong><?php esc_html_e( 'اطلاعات شما امن و محفوظ است', 'almas-land' ); ?></strong>
			<span><?php esc_html_e( 'ما از پروتکل SSL برای حفظ امنیت اطلاعات شما استفاده می‌کنیم.', 'almas-land' ); ?></span>
		</div>
	</section>
</div>
