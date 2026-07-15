# ✅ چک‌لیست تأیید تغییرات

## 📋 بررسی کد

### assets/js/main.js

- [x] **خط 240**: سلکتور دکمه بهبود یافته
  ```javascript
  ".buy-card button[name='add-to-cart']"
  ".buy-card .single_add_to_cart_button"
  ".buy-card button.single_add_to_cart_button"
  ```

- [x] **خط 293-351**: تابع handleSingleAddToCart بهبود یافته
  - ✓ Fallback برای form
  - ✓ Logging
  - ✓ Disable/Re-enable button
  - ✓ Quantity input check

- [x] **خط 353-368**: Mobile button handler
  - ✓ Direct form search
  - ✓ Direct button search
  - ✓ Logging

- [x] **خط 591-635**: Fallback mechanism
  - ✓ ensureCartButtonsInitialized function
  - ✓ DOMContentLoaded listener
  - ✓ Load listener
  - ✓ 500ms interval

---

## 🔧 تست Manual

### وقتی دکمه "افزودن به سبد خرید" را کلیک کنید:

1. [ ] Console لاگ "📍 Primary button clicked" یا "📍 Mobile button clicked"
2. [ ] Console لاگ "✓ Processing add to cart..."
3. [ ] دکمه disabled می‌شود
4. [ ] متن دکمه "به سبد خرید اضافه شد" می‌شود
5. [ ] Modal ظاهر می‌شود تا 900ms
6. [ ] دکمه re-enable می‌شود
7. [ ] محصول به سبد خرید اضافه می‌شود

---

## 🎯 حالات مختلف

### Case 1: دکمه اصلی کلیک شود
```javascript
✓ .buy-card button[name='add-to-cart'] پیدا می‌شود
✓ primaryAddToCart?.addEventListener فایر می‌شود
✓ handleSingleAddToCart فراخوانی می‌شود
```

### Case 2: دکمه موبایل کلیک شود
```javascript
✓ form.cart پیدا می‌شود
✓ button[name='add-to-cart'] داخل form پیدا می‌شود
✓ handleSingleAddToCart فراخوانی می‌شود
```

### Case 3: اگر دکمه ابتدا پیدا نشده باشد
```javascript
✓ ensureCartButtonsInitialized هر 500ms فراخوانی می‌شود
✓ دوباره سعی می‌کند دکمه را پیدا کند
✓ اگر پیدا شود، listener attach می‌کند
```

---

## 📱 تست Device

### Desktop
- [ ] دکمه اصلی در `.buy-card` کار می‌کند
- [ ] Modal ظاهر می‌شود

### Mobile
- [ ] دکمه موبایل در `.mobile-buy-bar` کار می‌کند
- [ ] Modal ظاهر می‌شود

---

## 🐛 Debugging Steps

اگر مشکلی وجود داشت:

1. **Console را باز کنید** (F12)
2. **صفحه را refresh کنید** (Ctrl+R)
3. **لاگ‌ها را ببینید:**
   ```
   📍 DOM Content Loaded
   📍 Window loaded
   ✓ Event listener attached
   ```
4. **دکمه را کلیک کنید**
5. **تازه لاگ‌ها ببینید:**
   ```
   📍 Primary button clicked
   ✓ Processing add to cart...
   ✓ Found quantity input: 1
   ✓ Using form.requestSubmit()
   ```

---

## 🚀 نتیجه نهایی

| مشکل | حل |
|-----|-----|
| دکمه پیدا نمی‌شد | سلکتورهای متعدد |
| Event listener attach نمی‌شد | fallback mechanism |
| Form پیدا نمی‌شد | form.cart یا form |
| Timing issue | 500ms interval |
| Debugging سخت بود | Comprehensive logging |

---

## 📌 Remember

- تمام تغییرات در **assets/js/main.js** هستند
- هیچ تغییری در template نیازی نیست
- WooCommerce AJAX خودکار handle می‌شود
- اگر مشکل هنوز هست، console logs برای debugging کمک می‌کنند
