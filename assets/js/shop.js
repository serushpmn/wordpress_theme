/**
 * Almas Land — shop archive behaviour.
 *
 * Filter drawer, grid/list view switcher and filter form handling. Loaded on
 * the shop page, product taxonomy archives and product search results.
 */

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

document.querySelectorAll(".shop-filter-toggle input").forEach((input) => {
  const syncToggleState = () => {
    const label = input.closest(".shop-filter-toggle");
    if (!label) return;
    label.classList.toggle("is-active", input.checked);
  };
  input.addEventListener("change", syncToggleState);
  syncToggleState();
});

const shopFilterForm = document.querySelector(".shop-filter-form");
shopFilterForm?.addEventListener("submit", () => {
  shopFilterForm.querySelectorAll('input[name="min_price"], input[name="max_price"]').forEach((input) => {
    const value = String(input.value || "").trim();
    if (!value || Number(value) <= 0) {
      input.disabled = true;
    }
  });
});

document.addEventListener("keydown", (event) => {
  if (event.key === "Escape") {
    setFilterState(false);
  }
});
