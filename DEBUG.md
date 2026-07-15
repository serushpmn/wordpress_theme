# راهنمای ترافل شوتینگ سبد خرید

## برای بررسی کار کردن دکمه، این مراحل را دنبال کنید:

### 1. باز کردن Developer Tools
- در براوزر، `F12` یا `Ctrl+Shift+I` را فشار دهید
- به تب **Console** برید

### 2. صفحه را ریفرش کنید
- `Ctrl+R` یا `Cmd+R` را فشار دهید
- در Console انتظار بروید و لاگ‌های زیر را بحث کنید:

```
📍 DOM Content Loaded - Initializing cart buttons
📍 Window loaded - Re-initializing cart buttons
✓ Event listener attached to main add-to-cart button
✓ Event listener attached to mobile add-to-cart button
```

### 3. دکمه "افزودن به سبد خرید" را کلیک کنید

متوقع است این لاگ‌ها ظاهر شوند:
```
📍 Mobile button clicked (اگر از دکمه موبایل استفاده کردید)
📍 Primary button clicked (اگر از دکمه اصلی استفاده کردید)
✓ Processing add to cart... 
✓ Found quantity input:
✓ Using form.requestSubmit() (یا form.submit())
```

### 4. مشکلات ممکنه:

| لاگ | معنی | راه حل |
|-----|------|-------|
| ❌ Main add-to-cart button not found | دکمه WooCommerce پیدا نشد | بررسی کنید که `woocommerce_template_single_add_to_cart()` درست فراخوانی شود |
| ❌ Cart form not found | فرم `form.cart` وجود ندارد | حتما WooCommerce فعال است و دکمه داخل فرم است |
| هیچ لاگی نمی‌آید | رویدادها اصلاً فایر نمی‌شوند | ممکن است مشکل CSS (hidden) یا JavaScript error باشد |

## فایل‌های تغییر یافته:

1. **assets/js/main.js** - بهبود event handlers و اضافه کردن logging
   - سلکتور دکمه بهتر شد
   - Fallback mechanism اضافه شد
   - Logging comprehensive اضافه شد

## اگر مشکل حل نشد:

1. بررسی کنید که دکمه‌ها disabled نیستند
2. بررسی کنید که فرم `action` و `method` درست دارد
3. بررسی کنید WooCommerce AJAX فعال است
4. بررسی کنید که نیازی به additional classes نیست

## نکات مهم:

- هر 500ms کد تابع `ensureCartButtonsInitialized` فراخوانی می‌شود
- اگر دکمه‌ها پیدا نشوند، event listener دوباره اتصال برقرار می‌کند
- همه لاگ‌های مهم در Console قابل مشاهده هستند
