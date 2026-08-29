/**
 * Almas Land — core behaviour loaded on every page.
 *
 * Theme toggle, floating contact, mobile navigation, header menus, generic UI
 * components (modals, tabs, accordions, toasts), the shared WooCommerce
 * add-to-cart plumbing and the navigation progress indicator.
 *
 * Shared helpers are published on `window.AlmasLand` so the page-specific
 * modules (shop/product/cart/checkout/home) can reuse them. Those modules are
 * registered with this script as a dependency, so this file always runs first.
 */

window.AlmasLand = window.AlmasLand || {};

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

applyTheme(getStoredTheme() || "light");

if (headerActions) {
  const themeToggle = document.createElement("button");
  themeToggle.className = "theme-toggle";
  themeToggle.type = "button";
  themeToggle.setAttribute("aria-label", "تغییر حالت روشن و تاریک");
  themeToggle.innerHTML = `
    <svg class="theme-toggle__moon" viewBox="0 0 24 24" aria-hidden="true"><path d="M21 14.4A8.2 8.2 0 0 1 9.6 3a8.6 8.6 0 1 0 11.4 11.4Z"/></svg>
    <svg class="theme-toggle__sun" viewBox="0 0 24 24" aria-hidden="true"><path d="M12 18a6 6 0 1 0 0-12 6 6 0 0 0 0 12Zm0 4a1 1 0 0 1-1-1v-1.2h2V21a1 1 0 0 1-1 1Zm0-17.8h-1V3a1 1 0 1 1 2 0v1.2h-1ZM4.2 13H3a1 1 0 1 1 0-2h1.2v2ZM21 13h-1.2v-2H21a1 1 0 1 1 0 2ZM6.3 7.7 5.5 6.9a1 1 0 0 1 1.4-1.4l.8.8-1.4 1.4Zm11.2 11.2-.8-.8 1.4-1.4.8.8a1 1 0 0 1-1.4 1.4Zm-.8-12.6.8-.8a1 1 0 1 1 1.4 1.4l-.8.8-1.4-1.4ZM5.5 17.5l.8-.8 1.4 1.4-.8.8a1 1 0 0 1-1.4-1.4Z"/></svg>
  `;
  headerActions.insertBefore(themeToggle, headerActions.firstChild);

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

floatingContact.addEventListener("pointerdown", () => {
  floatingContact.classList.remove("is-tapped");
  // Force reflow so the tap animation can replay on rapid taps.
  void floatingContact.offsetWidth;
  floatingContact.classList.add("is-tapped");
});

floatingContact.addEventListener("animationend", (event) => {
  if (event.animationName === "floating-contact-tap") {
    floatingContact.classList.remove("is-tapped");
  }
});

const mobileContactQuery = window.matchMedia?.("(max-width: 720px)");

function updateFloatingContactHref() {
  const isMobile = Boolean(mobileContactQuery?.matches);
  floatingContact.href = isMobile ? `tel:${STORE_PHONE_TEL}` : CONTACT_PAGE;
}

updateFloatingContactHref();
mobileContactQuery?.addEventListener?.("change", updateFloatingContactHref);

function setMobileNavOpen(isOpen) {
  if (!siteMenu || !menuToggle) return;

  siteMenu.classList.toggle("is-open", isOpen);
  document.body.classList.toggle("is-mobile-nav-open", isOpen);
  menuToggle.setAttribute("aria-expanded", String(isOpen));
  menuToggle.setAttribute(
    "aria-label",
    isOpen ? "بستن منو" : "باز کردن منو"
  );

  const panel = siteMenu.querySelector("#mobile-nav-panel");
  if (panel) {
    if (isOpen) {
      panel.setAttribute("role", "dialog");
      panel.setAttribute("aria-modal", "true");
      panel.setAttribute("aria-label", "منوی سایت");
    } else {
      panel.removeAttribute("role");
      panel.removeAttribute("aria-modal");
      panel.removeAttribute("aria-label");
    }
  }

  if (isOpen) {
    const closeBtn = siteMenu.querySelector(".mobile-nav-close");
    closeBtn?.focus?.({ preventScroll: true });
  } else {
    menuToggle.focus?.({ preventScroll: true });
  }
}

if (menuToggle && siteMenu) {
  menuToggle.addEventListener("click", () => {
    setMobileNavOpen(!siteMenu.classList.contains("is-open"));
  });

  siteMenu.querySelectorAll("[data-menu-close]").forEach((el) => {
    el.addEventListener("click", () => setMobileNavOpen(false));
  });

  document.addEventListener("keydown", (event) => {
    if (event.key === "Escape" && siteMenu.classList.contains("is-open")) {
      setMobileNavOpen(false);
    }
  });

  siteMenu.querySelectorAll("a[href]").forEach((link) => {
    link.addEventListener("click", () => {
      if (!window.matchMedia("(max-width: 960px)").matches) return;
      if (link.parentElement?.classList.contains("menu-item-has-children")) {
        return;
      }
      setMobileNavOpen(false);
    });
  });

  window.matchMedia("(max-width: 960px)").addEventListener?.("change", (event) => {
    if (!event.matches) {
      setMobileNavOpen(false);
    }
  });
}

function initHeaderCategoriesMenu() {
  const wrap = document.querySelector(".header-categories");
  const toggle = wrap?.querySelector("[data-categories-toggle]");
  const panel = wrap?.querySelector("#header-categories-panel");

  if (!wrap || !toggle || !panel) return;

  const isMobileNav = () => window.matchMedia("(max-width: 960px)").matches;

  const close = () => {
    wrap.classList.remove("is-open");
    toggle.setAttribute("aria-expanded", "false");
    panel.setAttribute("hidden", "");
  };

  const open = () => {
    wrap.classList.add("is-open");
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

  wrap.addEventListener("mouseenter", () => {
    if (!isMobileNav()) {
      open();
    }
  });

  wrap.addEventListener("mouseleave", () => {
    if (!isMobileNav()) {
      close();
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
  const navItem = link.parentElement;
  if (!navItem?.classList.contains("menu-item-has-children")) {
    return;
  }

  const setExpanded = (isOpen) => {
    link.setAttribute("aria-expanded", String(isOpen));
    navItem.classList.toggle("is-open", isOpen);
  };

  navItem.addEventListener("mouseenter", () => {
    if (window.matchMedia("(max-width: 960px)").matches) return;
    setExpanded(true);
  });

  navItem.addEventListener("mouseleave", () => {
    if (window.matchMedia("(max-width: 960px)").matches) return;
    setExpanded(false);
  });

  link.addEventListener("click", (event) => {
    if (!window.matchMedia("(max-width: 960px)").matches) return;
    event.preventDefault();
    const willOpen = !navItem.classList.contains("is-open");
    navItem.parentElement?.querySelectorAll(":scope > .menu-item-has-children").forEach((item) => {
      if (item !== navItem) {
        item.classList.remove("is-open");
        item.querySelector(":scope > a")?.setAttribute("aria-expanded", "false");
      }
    });
    setExpanded(willOpen);
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

let cartChoiceAutoCloseTimer = null;

function scheduleCartChoiceAutoClose() {
  if (cartChoiceAutoCloseTimer) {
    clearTimeout(cartChoiceAutoCloseTimer);
  }

  const dismissSeconds = Math.max(1, Number(themeConfig.cartChoiceDismiss) || 4);
  cartChoiceAutoCloseTimer = window.setTimeout(() => {
    closeCartChoiceModal();
  }, dismissSeconds * 1000);
}

function openCartChoiceModal() {
  const modal = getCartChoiceModal();
  modal.classList.add("is-open");
  document.body.classList.add("is-modal-open");
  scheduleCartChoiceAutoClose();
}

function closeCartChoiceModal() {
  if (cartChoiceAutoCloseTimer) {
    clearTimeout(cartChoiceAutoCloseTimer);
    cartChoiceAutoCloseTimer = null;
  }

  const modal = document.getElementById("cart-choice-modal");
  if (!modal) {
    return;
  }
  modal.classList.remove("is-open");
  document.body.classList.remove("is-modal-open");
}

document.addEventListener("keydown", (event) => {
  if (event.key === "Escape") {
    closeCartChoiceModal();
  }
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

function initProductColorTooltips() {
  const swatches = [...document.querySelectorAll("[data-color-tooltip]")];
  if (!swatches.length) {
    return;
  }

  const closeAll = (except) => {
    swatches.forEach((swatch) => {
      if (swatch === except) return;
      swatch.classList.remove("is-tooltip-open", "is-tooltip-pinned");
      swatch.setAttribute("aria-expanded", "false");
    });
  };

  const showTooltip = (swatch) => {
    closeAll(swatch);
    swatch.classList.add("is-tooltip-open");
    swatch.setAttribute("aria-expanded", "true");
  };

  const hideTooltip = (swatch) => {
    if (swatch.classList.contains("is-tooltip-pinned")) {
      return;
    }
    swatch.classList.remove("is-tooltip-open");
    swatch.setAttribute("aria-expanded", "false");
  };

  swatches.forEach((swatch) => {
    swatch.addEventListener("mouseenter", () => showTooltip(swatch));
    swatch.addEventListener("mouseleave", () => hideTooltip(swatch));
    swatch.addEventListener("focus", () => showTooltip(swatch));
    swatch.addEventListener("blur", () => {
      if (!swatch.classList.contains("is-tooltip-pinned")) {
        hideTooltip(swatch);
      }
    });
    swatch.addEventListener("click", (event) => {
      event.preventDefault();
      event.stopPropagation();
      if (swatch.classList.contains("is-tooltip-pinned")) {
        swatch.classList.remove("is-tooltip-pinned");
        hideTooltip(swatch);
        return;
      }
      swatch.classList.add("is-tooltip-pinned");
      showTooltip(swatch);
    });
    swatch.addEventListener("keydown", (event) => {
      if (event.key !== "Enter" && event.key !== " ") {
        return;
      }
      event.preventDefault();
      swatch.click();
    });
  });

  document.addEventListener("click", (event) => {
    if (event.target.closest("[data-color-tooltip]")) {
      return;
    }
    swatches.forEach((swatch) => {
      swatch.classList.remove("is-tooltip-pinned");
      hideTooltip(swatch);
    });
  });

  document.addEventListener("keydown", (event) => {
    if (event.key === "Escape") {
      closeAll();
    }
  });
}

initProductColorTooltips();

/* Lightweight page-loading indicator for internal navigations */
(function initPageLoadingIndicator() {
  let navigating = false;

  function isSkippableLink(link) {
    if (!(link instanceof HTMLAnchorElement)) {
      return true;
    }

    if (link.hasAttribute("download")) {
      return true;
    }

    const target = (link.getAttribute("target") || "").toLowerCase();
    if (target && target !== "_self") {
      return true;
    }

    const rawHref = (link.getAttribute("href") || "").trim();
    if (!rawHref || rawHref.charAt(0) === "#") {
      return true;
    }

    const protocol = rawHref.slice(0, rawHref.indexOf(":") + 1).toLowerCase();
    if (
      protocol === "mailto:" ||
      protocol === "tel:" ||
      protocol === "javascript:" ||
      protocol === "data:" ||
      protocol === "sms:" ||
      protocol === "whatsapp:"
    ) {
      return true;
    }

    if (
      link.classList.contains("ajax_add_to_cart") ||
      link.classList.contains("add_to_cart_button") ||
      link.classList.contains("remove_from_cart_button") ||
      link.classList.contains("remove") ||
      link.hasAttribute("data-add-to-cart") ||
      link.hasAttribute("data-mobile-add-to-cart")
    ) {
      return true;
    }

    if (link.hasAttribute("data-product_id") && link.classList.contains("button")) {
      return true;
    }

    let url;
    try {
      url = new URL(rawHref, window.location.href);
    } catch {
      return true;
    }

    if (url.origin !== window.location.origin) {
      return true;
    }

    const path = url.pathname.toLowerCase();
    if (path.indexOf("/wp-admin") !== -1 || path.indexOf("/wp-login.php") !== -1) {
      return true;
    }

    if (
      url.searchParams.get("action") === "logout" ||
      url.href.indexOf("customer-logout") !== -1 ||
      url.href.indexOf("wp-logout") !== -1
    ) {
      return true;
    }

    if (
      url.pathname === window.location.pathname &&
      url.search === window.location.search &&
      url.hash !== ""
    ) {
      return true;
    }

    return false;
  }

  function isNavClick(event) {
    return (
      event.button === 0 &&
      !event.defaultPrevented &&
      !event.metaKey &&
      !event.ctrlKey &&
      !event.shiftKey &&
      !event.altKey
    );
  }

  document.addEventListener("click", (event) => {
    if (!isNavClick(event)) {
      return;
    }

    const link = event.target.closest?.("a[href]");
    if (!link || isSkippableLink(link)) {
      return;
    }

    if (navigating) {
      event.preventDefault();
      return;
    }

    queueMicrotask(() => {
      if (event.defaultPrevented || navigating) {
        return;
      }
      navigating = true;
      document.documentElement.classList.add("is-page-loading");
    });
  });

  window.addEventListener("pageshow", () => {
    navigating = false;
    document.documentElement.classList.remove("is-page-loading");
  });
})();

Object.assign(window.AlmasLand, {
  config: themeConfig,
  ADD_TO_CART_BUTTON_SELECTOR,
  addProductToCartAjax,
  buildAddToCartFormData,
  closeCartChoiceModal,
  closeMegaMenus,
  findSingleProductCartButton,
  getSingleProductCartForm,
  getWcAjaxUrl,
  openCartChoiceModal,
  parseCartCount,
  setModalState,
  showCartValidationMessage,
  showToast,
  toPersianDigits,
  updateCartFragments,
});
