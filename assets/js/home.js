/**
 * Almas Land — front page behaviour.
 *
 * Trust-bar tooltips, one-click add to cart on offer cards, the hero and
 * special-offers Swiper sliders, and the catalog category tabs.
 */

/*
 * Shared helpers are read through the namespace rather than destructured:
 * every theme module is a classic script sharing one global lexical scope, so
 * a top-level `const` here would clash with the same name in core.js.
 */

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

  const response = await fetch(window.AlmasLand.getWcAjaxUrl("add_to_cart"), {
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

  window.AlmasLand.updateCartFragments(result.fragments);
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
    window.AlmasLand.openCartChoiceModal();

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

function initFrontPageHeroSwiper() {
  const slider = document.querySelector("[data-hero-swiper]");
  if (!slider || typeof Swiper === "undefined" || slider.swiper) {
    return;
  }

  const autoplayEnabled = slider.dataset.autoplay !== "false";
  const interval = Number(slider.dataset.interval || 5000);

  // eslint-disable-next-line no-new
  new Swiper(slider, {
    rtl: true,
    loop: true,
    speed: 650,
    slidesPerView: 1,
    spaceBetween: 0,
    watchOverflow: true,
    autoplay: autoplayEnabled
      ? {
          delay: interval,
          disableOnInteraction: false,
          pauseOnMouseEnter: true,
        }
      : false,
    navigation: {
      nextEl: slider.querySelector(".swiper-button-next"),
      prevEl: slider.querySelector(".swiper-button-prev"),
    },
    pagination: {
      el: slider.querySelector(".swiper-pagination"),
      clickable: true,
    },
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
    slidesPerView: 2,
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
      activeTab.scrollIntoView({ behavior: "smooth", inline: "center", block: "nearest" });
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
initFrontPageHeroSwiper();
initFrontPageSpecialOffersSwiper();
initFrontPageCatalogFilters();
