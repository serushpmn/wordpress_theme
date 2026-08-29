/**
 * Almas Land — single product behaviour.
 *
 * Gallery thumbnails, AJAX add to cart from the buy card and mobile bar, and
 * the variable-product price/stock/image sync.
 */

/*
 * Shared helpers are read through the namespace rather than destructured:
 * every theme module is a classic script sharing one global lexical scope, so
 * a top-level `const` here would clash with the same name in core.js.
 */

const galleryMain = document.querySelector("[data-gallery-main]");
const galleryThumbs = document.querySelectorAll("[data-gallery-thumb]");
let galleryMainFadeTimeout = null;
let galleryMainLoadHandler = null;

const updateGalleryMainSrc = (src) => {
  if (!galleryMain || !src) return;
  if (galleryMain.src === src) {
    galleryMain.style.opacity = "1";
    return;
  }

  galleryMain.style.opacity = "0";
  if (galleryMainFadeTimeout) {
    clearTimeout(galleryMainFadeTimeout);
  }

  if (galleryMainLoadHandler) {
    galleryMain.removeEventListener("load", galleryMainLoadHandler);
  }

  galleryMainLoadHandler = () => {
    galleryMain.style.opacity = "1";
    galleryMain.removeEventListener("load", galleryMainLoadHandler);
    galleryMainLoadHandler = null;
  };

  galleryMain.addEventListener("load", galleryMainLoadHandler);
  galleryMainFadeTimeout = setTimeout(() => {
    galleryMain.src = src;
  }, 120);
};

galleryThumbs.forEach((thumb) => {
  thumb.addEventListener("click", () => {
    const src = thumb.getAttribute("data-gallery-thumb");
    updateGalleryMainSrc(src);
    galleryThumbs.forEach((item) => item.classList.remove("is-active"));
    thumb.classList.add("is-active");
  });
});

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
    const added = await window.AlmasLand.addProductToCartAjax(form, button);

    if (!added) {
      button.textContent = button.dataset.originalLabel;
      return;
    }

    button.textContent = "به سبد خرید اضافه شد";
    window.AlmasLand.openCartChoiceModal();

    window.setTimeout(() => {
      button.textContent = button.dataset.originalLabel;
    }, 1800);
  } catch (error) {
    window.AlmasLand.showCartValidationMessage(form, "افزودن به سبد خرید با خطا مواجه شد. دوباره تلاش کنید.");
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

  const form = window.AlmasLand.getSingleProductCartForm();
  form?.addEventListener("submit", (event) => {
    const cartButton = window.AlmasLand.findSingleProductCartButton(form);
    if (cartButton) {
      handleSingleAddToCart(event, cartButton);
    }
  });

  document.addEventListener("click", (event) => {
    const mobileButton = event.target.closest("[data-mobile-add-to-cart]");
    if (mobileButton) {
      event.preventDefault();
      const form = window.AlmasLand.getSingleProductCartForm();
      const cartButton = window.AlmasLand.findSingleProductCartButton(form);
      if (cartButton) {
        handleSingleAddToCart(event, cartButton);
      }
      return;
    }

    const cartButton = event.target.closest(`.buy-card ${window.AlmasLand.ADD_TO_CART_BUTTON_SELECTOR}`);
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
    window.AlmasLand.openCartChoiceModal();
  }
}

initSingleProductCartHandlers();
maybeOpenCartModalFromNotice();
initVariableProductUI();

function setPriceBlocks(html, isSelected) {
  document.querySelectorAll("[data-price-default-html]").forEach((el) => {
    el.hidden = Boolean(isSelected);
  });
  document.querySelectorAll("[data-price-selected-html]").forEach((el) => {
    if (isSelected && html) {
      el.innerHTML = html;
      el.hidden = false;
    } else {
      el.innerHTML = "";
      el.hidden = true;
    }
  });
}

function setProductStockLabel(html) {
  const stock = document.querySelector("[data-product-stock]");
  if (!stock) return;

  if (!html) {
    stock.textContent = stock.dataset.defaultStock || stock.textContent;
    return;
  }

  const temp = document.createElement("div");
  temp.innerHTML = html;
  const text = (temp.textContent || "").trim();
  if (text) {
    stock.textContent = text;
  }
}

function setVariationImage(variation) {
  const mainImage = document.querySelector("[data-gallery-main]");
  if (!mainImage) return;

  if (!mainImage.dataset.defaultSrc) {
    mainImage.dataset.defaultSrc = mainImage.getAttribute("src") || "";
  }

  const imageSrc =
    variation?.image?.full_src ||
    variation?.image?.src ||
    variation?.image?.url ||
    "";

  if (imageSrc) {
    mainImage.setAttribute("src", imageSrc);
    mainImage.setAttribute("srcset", variation?.image?.srcset || "");
    return;
  }

  if (mainImage.dataset.defaultSrc) {
    mainImage.setAttribute("src", mainImage.dataset.defaultSrc);
    mainImage.removeAttribute("srcset");
  }
}

function setChooseHintVisible(isVisible) {
  const hint = document.querySelector(".buy-card__choose-hint");
  if (hint) {
    hint.hidden = !isVisible;
  }
}

function initVariableProductUI() {
  if (!document.body.classList.contains("single-product")) {
    return;
  }

  const form = document.querySelector("form.variations_form");
  const stock = document.querySelector("[data-product-stock]");
  if (stock && !stock.dataset.defaultStock) {
    stock.dataset.defaultStock = stock.textContent.trim();
  }

  if (!form || typeof window.jQuery === "undefined") {
    return;
  }

  const $form = window.jQuery(form);

  $form.on("found_variation", (_event, variation) => {
    setPriceBlocks(variation?.almas_price_html || variation?.price_html || "", true);
    setProductStockLabel(variation?.availability_html || "");
    setVariationImage(variation);
    setChooseHintVisible(false);
  });

  $form.on("reset_data hide_variation", () => {
    setPriceBlocks("", false);
    setProductStockLabel("");
    setVariationImage(null);
    setChooseHintVisible(true);
  });
}
