const menuToggle = document.querySelector("[data-menu-toggle]");
const siteMenu = document.querySelector("#site-menu");
const headerActions = document.querySelector(".header-actions");
const themeConfig = window.almasLandTheme || {};
const STORE_PHONE_DISPLAY = themeConfig.phoneDisplay || "۰۲۱-۸۸۸۸۶۹۵۹";
const STORE_PHONE_TEL = themeConfig.phoneTel || "02188886959";
const CONTACT_PAGE = themeConfig.contactUrl || "contact.html";

function getStoredTheme() {
  try {
    return localStorage.getItem("almas-theme");
  } catch {
    return null;
  }
}

function setStoredTheme(theme) {
  try {
    localStorage.setItem("almas-theme", theme);
  } catch {
    // Storage can be unavailable in private contexts.
  }
}

function applyTheme(theme) {
  document.documentElement.dataset.theme = theme;
}

const preferredDark = window.matchMedia?.("(prefers-color-scheme: dark)").matches;
applyTheme(getStoredTheme() || (preferredDark ? "dark" : "light"));

if (headerActions) {
  const themeToggle = document.createElement("button");
  themeToggle.className = "theme-toggle";
  themeToggle.type = "button";
  themeToggle.setAttribute("aria-label", "تغییر حالت روشن و تاریک");
  themeToggle.innerHTML = `
    <svg class="theme-toggle__moon" viewBox="0 0 24 24" aria-hidden="true"><path d="M21 14.4A8.2 8.2 0 0 1 9.6 3a8.6 8.6 0 1 0 11.4 11.4Z"/></svg>
    <svg class="theme-toggle__sun" viewBox="0 0 24 24" aria-hidden="true"><path d="M12 18a6 6 0 1 0 0-12 6 6 0 0 0 0 12Zm0 4a1 1 0 0 1-1-1v-1.2h2V21a1 1 0 0 1-1 1Zm0-17.8h-1V3a1 1 0 1 1 2 0v1.2h-1ZM4.2 13H3a1 1 0 1 1 0-2h1.2v2ZM21 13h-1.2v-2H21a1 1 0 1 1 0 2ZM6.3 7.7 5.5 6.9a1 1 0 0 1 1.4-1.4l.8.8-1.4 1.4Zm11.2 11.2-.8-.8 1.4-1.4.8.8a1 1 0 0 1-1.4 1.4Zm-.8-12.6.8-.8a1 1 0 1 1 1.4 1.4l-.8.8-1.4-1.4ZM5.5 17.5l.8-.8 1.4 1.4-.8.8a1 1 0 0 1-1.4-1.4Z"/></svg>
  `;
  headerActions.insertBefore(themeToggle, menuToggle || headerActions.firstChild);

  themeToggle.addEventListener("click", () => {
    const nextTheme = document.documentElement.dataset.theme === "dark" ? "light" : "dark";
    applyTheme(nextTheme);
    setStoredTheme(nextTheme);
  });
}

const floatingContact = document.createElement("a");
floatingContact.className = "floating-contact";
floatingContact.setAttribute("aria-label", "تماس با ما");
floatingContact.innerHTML = `
  <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M6.6 10.8c1.4 2.8 3.8 5.2 6.6 6.6l2.2-2.2c.3-.3.8-.4 1.2-.3 1.3.4 2.7.6 4.1.6.7 0 1.3.6 1.3 1.3v3.5c0 .7-.6 1.3-1.3 1.3C10.4 21.6 2.4 13.6 2.4 3.3 2.4 2.6 3 2 3.7 2h3.5c.7 0 1.3.6 1.3 1.3 0 1.4.2 2.8.6 4.1.1.4 0 .9-.3 1.2l-2.2 2.2Z"/></svg>
`;
const floatingContactLabel = document.createElement("span");
floatingContactLabel.textContent = "تماس با ما";
const floatingContactPhone = document.createElement("span");
floatingContactPhone.className = "floating-contact__phone";
floatingContactPhone.textContent = STORE_PHONE_DISPLAY;
floatingContact.append(floatingContactLabel, floatingContactPhone);
document.body.append(floatingContact);

const mobileContactQuery = window.matchMedia?.("(max-width: 720px)");

function updateFloatingContactHref() {
  const isMobile = Boolean(mobileContactQuery?.matches);
  floatingContact.href = isMobile ? `tel:${STORE_PHONE_TEL}` : CONTACT_PAGE;
}

updateFloatingContactHref();
mobileContactQuery?.addEventListener?.("change", updateFloatingContactHref);

if (menuToggle && siteMenu) {
  menuToggle.addEventListener("click", () => {
    const isOpen = siteMenu.classList.toggle("is-open");
    menuToggle.setAttribute("aria-expanded", String(isOpen));
  });
}

function initHeaderCategoriesMenu() {
  const wrap = document.querySelector(".header-categories");
  const toggle = wrap?.querySelector("[data-categories-toggle]");
  const panel = wrap?.querySelector("#header-categories-panel");

  if (!wrap || !toggle || !panel) return;

  const close = () => {
    toggle.setAttribute("aria-expanded", "false");
    panel.setAttribute("hidden", "");
  };

  const open = () => {
    toggle.setAttribute("aria-expanded", "true");
    panel.removeAttribute("hidden");
  };

  toggle.addEventListener("click", (event) => {
    event.stopPropagation();
    if (toggle.getAttribute("aria-expanded") === "true") {
      close();
    } else {
      open();
    }
  });

  document.addEventListener("click", (event) => {
    if (!wrap.contains(event.target)) {
      close();
    }
  });

  document.addEventListener("keydown", (event) => {
    if (event.key === "Escape") {
      close();
    }
  });
}

initHeaderCategoriesMenu();

document.querySelectorAll(".main-nav .menu-item-has-children > a").forEach((link) => {
  link.addEventListener("click", (event) => {
    if (!window.matchMedia("(max-width: 960px)").matches) return;
    event.preventDefault();
    const navItem = link.closest(".menu-item-has-children");
    if (!navItem) return;
    const willOpen = !navItem.classList.contains("is-open");
    document.querySelectorAll(".main-nav .menu-item-has-children").forEach((item) => {
      item.classList.remove("is-open");
    });
    if (willOpen) {
      navItem.classList.add("is-open");
    }
  });
});

const filterPanel = document.querySelector("[data-filter-panel]");
const filterOpen = document.querySelector("[data-filter-open]");
const filterClosers = document.querySelectorAll("[data-filter-close]");
const filterBackdrop = document.querySelector(".filter-backdrop");

function setFilterState(isOpen) {
  if (!filterPanel) return;
  filterPanel.classList.toggle("is-open", isOpen);
  filterBackdrop?.classList.toggle("is-open", isOpen);
  document.body.classList.toggle("shop-filter-open", isOpen);
}

filterOpen?.addEventListener("click", () => setFilterState(true));
filterClosers.forEach((button) => {
  button.addEventListener("click", () => setFilterState(false));
});

const shopProducts = document.querySelector("[data-shop-products]");
const viewSwitcher = document.querySelector("[data-view-switcher]");
const viewButtons = viewSwitcher?.querySelectorAll("[data-view-mode]") ?? [];
const productGrid = shopProducts?.querySelector(".products");
const mobileShopMedia = window.matchMedia("(max-width: 720px)");

function setShopView(mode) {
  if (!productGrid) return;
  if (mobileShopMedia.matches && mode === "list") {
    mode = "grid";
  }
  productGrid.classList.toggle("product-grid--list", mode === "list");
  viewButtons.forEach((button) => {
    button.classList.toggle("is-active", button.getAttribute("data-view-mode") === mode);
  });
  try {
    localStorage.setItem("almaslandShopView", mode);
  } catch (error) {
    // Ignore storage errors.
  }
}

function applyShopViewForViewport() {
  if (!productGrid) return;
  if (mobileShopMedia.matches) {
    setShopView("grid");
    return;
  }
  const saved = localStorage.getItem("almaslandShopView");
  setShopView(saved === "list" ? "list" : "grid");
}

const savedShopView = (() => {
  try {
    return localStorage.getItem("almaslandShopView");
  } catch (error) {
    return null;
  }
})();

applyShopViewForViewport();
mobileShopMedia.addEventListener("change", applyShopViewForViewport);

viewButtons.forEach((button) => {
  button.addEventListener("click", () => {
    setShopView(button.getAttribute("data-view-mode") || "grid");
  });
});

document.querySelectorAll(".shop-filter-toggle input, .shop-color-swatch input").forEach((input) => {
  const syncToggleState = () => {
    const label = input.closest(".shop-filter-toggle, .shop-color-swatch");
    if (!label) return;
    label.classList.toggle("is-active", input.checked);
  };
  input.addEventListener("change", syncToggleState);
  syncToggleState();
});

document.addEventListener("keydown", (event) => {
  if (event.key === "Escape") {
    setFilterState(false);
  }
});

const galleryMain = document.querySelector("[data-gallery-main]");
const galleryThumbs = document.querySelectorAll("[data-gallery-thumb]");

galleryThumbs.forEach((thumb) => {
  thumb.addEventListener("click", () => {
    const src = thumb.getAttribute("data-gallery-thumb");
    if (!galleryMain || !src) return;
    galleryMain.src = src;
    galleryThumbs.forEach((item) => item.classList.remove("is-active"));
    thumb.classList.add("is-active");
  });
});

document.querySelectorAll(".quantity-control").forEach((control) => {
  const input = control.querySelector("input");
  const minus = control.querySelector("[data-qty-minus]");
  const plus = control.querySelector("[data-qty-plus]");

  minus?.addEventListener("click", () => {
    if (!input) return;
    input.value = String(Math.max(1, Number(input.value || 1) - 1));
  });

  plus?.addEventListener("click", () => {
    if (!input) return;
    input.value = String(Number(input.value || 1) + 1);
  });
});

function parseCartCount(text) {
  if (!text) return 0;
  const normalized = String(text).replace(/[۰-۹]/g, (digit) => "۰۱۲۳۴۵۶۷۸۹".indexOf(digit));
  const parsed = Number(normalized.replace(/\D/g, ""));
  return Number.isFinite(parsed) ? parsed : 0;
}

const cartCounts = document.querySelectorAll("[data-cart-count]");
let cartCount = parseCartCount(cartCounts[0]?.textContent);

function toPersianDigits(value) {
  return String(value).replace(/\d/g, (digit) => "۰۱۲۳۴۵۶۷۸۹"[Number(digit)]);
}

document.querySelectorAll("[data-add-to-cart]").forEach((button) => {
  if (document.body.classList.contains("single-product") || document.body.classList.contains("woocommerce")) {
    return;
  }

  button.addEventListener("click", () => {
    cartCount += 1;
    cartCounts.forEach((item) => {
      item.textContent = toPersianDigits(cartCount);
    });
    button.textContent = "به سبد خرید اضافه شد";
    window.setTimeout(() => {
      button.textContent = "افزودن به سبد خرید";
    }, 1800);
  });
});

const ADD_TO_CART_BUTTON_SELECTOR =
  "button[name='add-to-cart'], .single_add_to_cart_button, button.single_add_to_cart_button";

function getWcAjaxUrl(endpoint) {
  const base = themeConfig.wcAjaxUrl || "/?wc-ajax=%%endpoint%%";
  return base.replace("%%endpoint%%", endpoint);
}

function updateCartFragments(fragments) {
  if (!fragments || typeof fragments !== "object") {
    return;
  }

  Object.entries(fragments).forEach(([selector, html]) => {
    document.querySelectorAll(selector).forEach((element) => {
      element.outerHTML = html;
    });
  });

  const refreshedCount = document.querySelector("[data-cart-count]");
  if (refreshedCount) {
    cartCount = parseCartCount(refreshedCount.textContent);
  }
}

function buildAddToCartFormData(form, button) {
  const formData = new FormData(form);
  const payload = new FormData();

  const productId =
    button?.value ||
    formData.get("add-to-cart") ||
    formData.get("product_id") ||
    form.querySelector("input[name='product_id']")?.value;

  if (productId) {
    payload.append("product_id", productId);
  }

  payload.append("quantity", formData.get("quantity") || 1);

  const variationId = formData.get("variation_id");
  if (variationId) {
    payload.append("variation_id", variationId);
  }

  formData.forEach((value, key) => {
    if (key.startsWith("attribute_")) {
      payload.append(key, value);
    }
  });

  return payload;
}

function getSingleProductCartForm() {
  return document.querySelector(".buy-card form.cart, form.cart");
}

function findSingleProductCartButton(form = getSingleProductCartForm()) {
  return form?.querySelector(ADD_TO_CART_BUTTON_SELECTOR) || null;
}

function showCartValidationMessage(form, message) {
  const wrapper =
    form?.closest(".buy-card")?.querySelector(".buy-card__notices, .woocommerce-notices-wrapper") ||
    form?.querySelector(".woocommerce-notices-wrapper");

  if (!wrapper) {
    window.alert(message);
    return;
  }

  wrapper.innerHTML = `<div class="woocommerce-error" role="alert">${message}</div>`;
}

async function addProductToCartAjax(form, button) {
  const payload = buildAddToCartFormData(form, button);

  if (!payload.get("product_id")) {
    showCartValidationMessage(form, "محصول برای افزودن به سبد شناسایی نشد.");
    return false;
  }

  const variationInput = form.querySelector("input[name='variation_id']");
  if (variationInput && (!variationInput.value || variationInput.value === "0")) {
    showCartValidationMessage(form, "لطفاً گزینه‌های محصول را انتخاب کنید.");
    return false;
  }

  const response = await fetch(getWcAjaxUrl("add_to_cart"), {
    method: "POST",
    body: payload,
    credentials: "same-origin",
  });

  if (!response.ok) {
    showCartValidationMessage(form, "افزودن به سبد خرید با خطا مواجه شد. دوباره تلاش کنید.");
    return false;
  }

  const result = await response.json();

  if (result.error && result.product_url) {
    showCartValidationMessage(form, "امکان افزودن این محصول به سبد وجود ندارد.");
    return false;
  }

  updateCartFragments(result.fragments);
  return true;
}

function getCartChoiceModal() {
  let modal = document.getElementById("cart-choice-modal");
  if (modal) {
    return modal;
  }

  modal = document.createElement("div");
  modal.id = "cart-choice-modal";
  modal.className = "modal cart-choice-modal";
  modal.setAttribute("role", "dialog");
  modal.setAttribute("aria-modal", "true");
  modal.setAttribute("aria-labelledby", "cart-choice-title");
  modal.innerHTML = `
    <div class="modal__dialog cart-choice-modal__dialog">
      <div class="cart-choice-modal__icon">✓</div>
      <h2 id="cart-choice-title">به سبد خرید اضافه شد</h2>
      <p>می‌خواهید همین حالا به سبد خرید بروید یا ادامه خرید را انجام دهید؟</p>
      <div class="cart-choice-modal__actions">
        <a class="btn btn--primary" data-cart-choice-go href="${themeConfig.cartUrl || "/cart/"}">رفتن به سبد خرید</a>
        <button class="btn btn--ghost" type="button" data-cart-choice-continue>ادامه خرید</button>
      </div>
    </div>
  `;

  modal.addEventListener("click", (event) => {
    if (event.target === modal) {
      closeCartChoiceModal();
    }
  });

  modal.querySelector("[data-cart-choice-continue]")?.addEventListener("click", closeCartChoiceModal);
  document.body.appendChild(modal);

  return modal;
}

function openCartChoiceModal() {
  const modal = getCartChoiceModal();
  modal.classList.add("is-open");
  document.body.classList.add("is-modal-open");
}

function closeCartChoiceModal() {
  const modal = document.getElementById("cart-choice-modal");
  if (!modal) {
    return;
  }
  modal.classList.remove("is-open");
  document.body.classList.remove("is-modal-open");
}

async function handleSingleAddToCart(event, button) {
  if (!button) {
    return;
  }

  let form = button.closest("form.cart");
  if (!form) {
    form = button.closest("form");
  }

  if (!form) {
    return;
  }

  event.preventDefault();
  event.stopPropagation();

  if (button.disabled || button.classList.contains("is-loading") || form.dataset.cartSubmitting === "true") {
    return;
  }

  form.dataset.cartSubmitting = "true";

  const buttonLabel = button.textContent?.trim() || "افزودن به سبد خرید";
  if (!button.dataset.originalLabel) {
    button.dataset.originalLabel = buttonLabel;
  }

  button.classList.add("is-loading");
  button.disabled = true;
  button.textContent = "در حال افزودن...";

  try {
    const added = await addProductToCartAjax(form, button);

    if (!added) {
      button.textContent = button.dataset.originalLabel;
      return;
    }

    button.textContent = "به سبد خرید اضافه شد";
    openCartChoiceModal();

    window.setTimeout(() => {
      button.textContent = button.dataset.originalLabel;
    }, 1800);
  } catch (error) {
    showCartValidationMessage(form, "افزودن به سبد خرید با خطا مواجه شد. دوباره تلاش کنید.");
    button.textContent = button.dataset.originalLabel;
  } finally {
    button.classList.remove("is-loading");
    button.disabled = false;
    delete form.dataset.cartSubmitting;
  }
}

function initSingleProductCartHandlers() {
  if (!document.body.classList.contains("single-product")) {
    return;
  }

  const form = getSingleProductCartForm();
  form?.addEventListener("submit", (event) => {
    const cartButton = findSingleProductCartButton(form);
    if (cartButton) {
      handleSingleAddToCart(event, cartButton);
    }
  });

  document.addEventListener("click", (event) => {
    const mobileButton = event.target.closest("[data-mobile-add-to-cart]");
    if (mobileButton) {
      event.preventDefault();
      const form = getSingleProductCartForm();
      const cartButton = findSingleProductCartButton(form);
      if (cartButton) {
        handleSingleAddToCart(event, cartButton);
      }
      return;
    }

    const cartButton = event.target.closest(`.buy-card ${ADD_TO_CART_BUTTON_SELECTOR}`);
    if (cartButton) {
      handleSingleAddToCart(event, cartButton);
    }
  });
}

function maybeOpenCartModalFromNotice() {
  const notice = document.querySelector(
    ".woocommerce-notices-wrapper .woocommerce-message, .woocommerce-message"
  );

  if (!notice) {
    return;
  }

  if (/سبد|اضافه/.test(notice.textContent || "")) {
    openCartChoiceModal();
  }
}

initSingleProductCartHandlers();
maybeOpenCartModalFromNotice();

document.addEventListener("keydown", (event) => {
  if (event.key === "Escape") {
    closeCartChoiceModal();
  }
});

document.querySelectorAll(".cart-item .quantity-control .quantity").forEach((quantity) => {
	if (quantity.dataset.enhanced === "true") {
		return;
	}

  const input = quantity.querySelector("input.qty");
  if (!input) {
    return;
  }

  quantity.dataset.enhanced = "true";
  quantity.classList.add("quantity-control__wc");

  const minus = document.createElement("button");
  minus.type = "button";
  minus.setAttribute("data-qty-minus", "");
  minus.setAttribute("aria-label", "کاهش تعداد");
  minus.textContent = "−";

  const plus = document.createElement("button");
  plus.type = "button";
  plus.setAttribute("data-qty-plus", "");
  plus.setAttribute("aria-label", "افزایش تعداد");
  plus.textContent = "+";

  quantity.prepend(minus);
  quantity.append(plus);

  minus.addEventListener("click", () => {
    input.value = String(Math.max(Number(input.min || 1), Number(input.value || 1) - 1));
    input.dispatchEvent(new Event("change", { bubbles: true }));
  });

  plus.addEventListener("click", () => {
    input.value = String(Number(input.value || 1) + 1);
    input.dispatchEvent(new Event("change", { bubbles: true }));
	});
});

const cartUpdateButton = document.querySelector(".woocommerce-cart-form .cart-update-button");

document.querySelectorAll(".woocommerce-cart-form input.qty").forEach((input) => {
	input.addEventListener("change", () => {
		if (cartUpdateButton) {
			cartUpdateButton.disabled = false;
		}
	});
});

const megaToggles = document.querySelectorAll("[data-mega-toggle]");

function closeMegaMenus() {
  megaToggles.forEach((toggle) => {
    toggle.closest(".nav-item")?.classList.remove("is-open");
    toggle.setAttribute("aria-expanded", "false");
  });
}

megaToggles.forEach((toggle) => {
  toggle.addEventListener("click", (event) => {
    event.stopPropagation();
    const navItem = toggle.closest(".nav-item");
    if (!navItem) return;
    const isOpen = navItem.classList.toggle("is-open");
    megaToggles.forEach((item) => {
      if (item !== toggle) {
        item.closest(".nav-item")?.classList.remove("is-open");
        item.setAttribute("aria-expanded", "false");
      }
    });
    toggle.setAttribute("aria-expanded", String(isOpen));
  });
});

document.addEventListener("click", (event) => {
  if (!event.target.closest?.(".nav-item--mega")) {
    closeMegaMenus();
  }
});

const modals = document.querySelectorAll(".modal");

function setModalState(modal, isOpen) {
  modal.classList.toggle("is-open", isOpen);
  modal.setAttribute("aria-hidden", String(!isOpen));
  document.body.style.overflow = isOpen ? "hidden" : "";
}

document.querySelectorAll("[data-modal-open]").forEach((button) => {
  button.addEventListener("click", () => {
    const modal = document.getElementById(button.getAttribute("data-modal-open"));
    if (modal) setModalState(modal, true);
  });
});

modals.forEach((modal) => {
  modal.querySelectorAll("[data-modal-close]").forEach((button) => {
    button.addEventListener("click", () => setModalState(modal, false));
  });

  modal.addEventListener("click", (event) => {
    if (event.target === modal) {
      setModalState(modal, false);
    }
  });
});

document.querySelectorAll("[data-accordion]").forEach((accordion) => {
  accordion.querySelectorAll(".accordion__trigger").forEach((trigger) => {
    trigger.addEventListener("click", () => {
      const item = trigger.closest(".accordion__item");
      item?.classList.toggle("is-open");
    });
  });
});

document.querySelectorAll("[data-tabs]").forEach((tabs) => {
  const tabButtons = tabs.querySelectorAll("[data-tab-target]");
  const tabPanels = tabs.querySelectorAll(".tabs__panel");

  tabButtons.forEach((button) => {
    button.addEventListener("click", () => {
      const target = tabs.querySelector(`#${button.getAttribute("data-tab-target")}`);
      tabButtons.forEach((item) => item.classList.remove("is-active"));
      tabPanels.forEach((panel) => panel.classList.remove("is-active"));
      button.classList.add("is-active");
      target?.classList.add("is-active");
    });
  });
});

let toastStack;

function showToast(message) {
  if (!toastStack) {
    toastStack = document.createElement("div");
    toastStack.className = "toast-stack";
    toastStack.setAttribute("aria-live", "polite");
    document.body.append(toastStack);
  }

  const toast = document.createElement("div");
  toast.className = "toast";
  toast.textContent = message;
  toastStack.append(toast);

  window.setTimeout(() => {
    toast.remove();
  }, 3200);
}

document.querySelectorAll("[data-toast]").forEach((button) => {
  button.addEventListener("click", () => {
    showToast(button.getAttribute("data-toast") || "عملیات با موفقیت انجام شد.");
  });
});

document.addEventListener("keydown", (event) => {
  if (event.key !== "Escape") return;
  closeMegaMenus();
  modals.forEach((modal) => setModalState(modal, false));
});

const notifyPopup = themeConfig.notifyPopup;
if (notifyPopup?.enabled) {
  const popupKey = "almas_notify_popup";
  const shouldSkip = notifyPopup.once && localStorage.getItem(popupKey);

  if (!shouldSkip) {
    window.setTimeout(() => {
      const popupModal = document.getElementById("theme-notify-popup");
      if (!popupModal) return;
      setModalState(popupModal, true);
      if (notifyPopup.once) {
        try {
          localStorage.setItem(popupKey, "1");
        } catch {
          // Storage can be unavailable in private contexts.
        }
      }
    }, Math.max(0, Number(notifyPopup.delay) || 0) * 1000);
  }
}

function initFrontPageTrustTooltips() {
  const containers = [
    ...document.querySelectorAll(".front-page-trust__bar, .front-page-why__stats"),
  ];

  if (!containers.length) return;

  const items = containers.flatMap((container) => [
    ...container.querySelectorAll("[data-trust-tooltip]"),
  ]);

  if (!items.length) return;

  const canHover = window.matchMedia("(hover: hover) and (pointer: fine)").matches;

  const closeAll = (except) => {
    items.forEach((item) => {
      if (item === except) return;
      item.classList.remove("is-tooltip-open", "is-tooltip-pinned");
      item.setAttribute("aria-expanded", "false");
      item.querySelector(".front-page-trust__tooltip")?.setAttribute("hidden", "");
    });
  };

  const showTooltip = (item) => {
    const tooltip = item.querySelector(".front-page-trust__tooltip");
    if (!tooltip) return;
    closeAll(item);
    item.classList.add("is-tooltip-open");
    item.setAttribute("aria-expanded", "true");
    tooltip.removeAttribute("hidden");
  };

  const hideTooltip = (item) => {
    if (item.classList.contains("is-tooltip-pinned")) return;
    item.classList.remove("is-tooltip-open");
    item.setAttribute("aria-expanded", "false");
    item.querySelector(".front-page-trust__tooltip")?.setAttribute("hidden", "");
  };

  const toggleClickTooltip = (item) => {
    if (item.classList.contains("is-tooltip-open")) {
      item.classList.remove("is-tooltip-open", "is-tooltip-pinned");
      item.setAttribute("aria-expanded", "false");
      item.querySelector(".front-page-trust__tooltip")?.setAttribute("hidden", "");
      return;
    }
    item.classList.add("is-tooltip-pinned");
    showTooltip(item);
  };

  items.forEach((item) => {
    const mode = item.dataset.trustTooltip || "click";

    if (mode === "hover-click" && canHover) {
      item.addEventListener("mouseenter", () => {
        if (!item.classList.contains("is-tooltip-pinned")) {
          showTooltip(item);
        }
      });
      item.addEventListener("mouseleave", () => hideTooltip(item));
      item.addEventListener("click", (event) => {
        event.preventDefault();
        event.stopPropagation();
        if (item.classList.contains("is-tooltip-pinned")) {
          item.classList.remove("is-tooltip-pinned");
          hideTooltip(item);
        } else {
          item.classList.add("is-tooltip-pinned");
          showTooltip(item);
        }
      });
      return;
    }

    item.addEventListener("click", (event) => {
      event.preventDefault();
      event.stopPropagation();
      toggleClickTooltip(item);
    });
  });

  document.addEventListener("click", (event) => {
    if (!event.target.closest("[data-trust-tooltip]")) {
      closeAll();
    }
  });

  document.addEventListener("keydown", (event) => {
    if (event.key !== "Escape") return;
    closeAll();
  });
}

async function addProductIdToCartAjax(productId) {
  const payload = new FormData();
  payload.append("product_id", productId);
  payload.append("quantity", "1");

  const response = await fetch(getWcAjaxUrl("add_to_cart"), {
    method: "POST",
    body: payload,
    credentials: "same-origin",
  });

  if (!response.ok) {
    return false;
  }

  const result = await response.json();

  if (result.error && result.product_url) {
    return false;
  }

  updateCartFragments(result.fragments);
  return true;
}

async function handleOfferAddToCart(event, button) {
  event.preventDefault();
  event.stopPropagation();

  const productId = button.getAttribute("data-offer-add-to-cart");
  if (!productId || button.disabled || button.classList.contains("is-loading")) {
    return;
  }

  const originalLabel = button.dataset.originalLabel || button.textContent.trim();
  button.dataset.originalLabel = originalLabel;
  button.classList.add("is-loading");
  button.disabled = true;
  button.textContent = "در حال افزودن...";

  try {
    const added = await addProductIdToCartAjax(productId);

    if (!added) {
      button.textContent = originalLabel;
      return;
    }

    button.textContent = "به سبد خرید اضافه شد";
    openCartChoiceModal();

    window.setTimeout(() => {
      button.textContent = originalLabel;
    }, 1800);
  } catch {
    button.textContent = originalLabel;
  } finally {
    button.classList.remove("is-loading");
    button.disabled = false;
  }
}

function initFrontPageOfferCartButtons() {
  document.querySelectorAll("[data-offer-add-to-cart]").forEach((button) => {
    button.addEventListener("click", (event) => {
      handleOfferAddToCart(event, button);
    });
  });
}

function initFrontPageSpecialOffersSwiper() {
  const slider = document.querySelector(".front-page-offers__slider");
  if (!slider || typeof Swiper === "undefined" || slider.swiper) {
    return;
  }

  // eslint-disable-next-line no-new
  const swiper = new Swiper(slider, {
    rtl: true,
    autoHeight: false,
    observer: false,
    observeParents: false,
    resizeObserver: false,
    updateOnWindowResize: true,
    slidesPerView: 1.12,
    spaceBetween: 12,
    watchOverflow: true,
    navigation: {
      nextEl: slider.querySelector(".front-page-offers__nav--next"),
      prevEl: slider.querySelector(".front-page-offers__nav--prev"),
    },
    pagination: {
      el: slider.querySelector(".front-page-offers__pagination"),
      clickable: true,
    },
    breakpoints: {
      520: {
        slidesPerView: 2,
        spaceBetween: 14,
      },
      820: {
        slidesPerView: 3,
        spaceBetween: 16,
      },
      1080: {
        slidesPerView: 4,
        spaceBetween: 18,
      },
    },
  });

  let resizeTimer = 0;
  window.addEventListener("resize", () => {
    window.clearTimeout(resizeTimer);
    resizeTimer = window.setTimeout(() => {
      swiper.update();
    }, 120);
  });
}

function initFrontPageCatalogFilters() {
  const section = document.querySelector(".front-page-catalog");
  if (!section) return;

  const tabs = [...section.querySelectorAll("[data-catalog-tab]")];
  const panels = [...section.querySelectorAll("[data-catalog-panel]")];
  const viewAll = section.querySelector("[data-catalog-view-all]");

  if (!tabs.length || !panels.length) return;

  const activate = (key) => {
    const activeTab = tabs.find((tab) => tab.dataset.catalogTab === key);

    tabs.forEach((tab) => {
      const isActive = tab.dataset.catalogTab === key;
      tab.classList.toggle("is-active", isActive);
      tab.setAttribute("aria-selected", String(isActive));
      tab.tabIndex = isActive ? 0 : -1;
    });

    panels.forEach((panel) => {
      const isActive = panel.dataset.catalogPanel === key;
      panel.classList.toggle("is-active", isActive);
      if (isActive) {
        panel.removeAttribute("hidden");
      } else {
        panel.setAttribute("hidden", "");
      }
    });

    if (viewAll && activeTab?.dataset.catalogUrl) {
      viewAll.setAttribute("href", activeTab.dataset.catalogUrl);
    }

    if (activeTab && typeof activeTab.scrollIntoView === "function") {
      activeTab.scrollIntoView({ behavior: "smooth", inline: "nearest", block: "nearest" });
    }
  };

  tabs.forEach((tab, index) => {
    if (index > 0) {
      tab.tabIndex = -1;
    }

    tab.addEventListener("click", () => {
      activate(tab.dataset.catalogTab);
    });

    tab.addEventListener("keydown", (event) => {
      const currentIndex = tabs.indexOf(tab);
      let nextIndex = currentIndex;

      if (event.key === "ArrowLeft") {
        nextIndex = (currentIndex + 1) % tabs.length;
      } else if (event.key === "ArrowRight") {
        nextIndex = (currentIndex - 1 + tabs.length) % tabs.length;
      } else if (event.key === "Home") {
        nextIndex = 0;
      } else if (event.key === "End") {
        nextIndex = tabs.length - 1;
      } else {
        return;
      }

      event.preventDefault();
      tabs[nextIndex]?.focus();
      activate(tabs[nextIndex].dataset.catalogTab);
    });
  });
}

initFrontPageTrustTooltips();
initFrontPageOfferCartButtons();
initFrontPageSpecialOffersSwiper();
initFrontPageCatalogFilters();

