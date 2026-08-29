/**
 * Almas Land — checkout behaviour.
 *
 * Mirrors the WooCommerce place-order button and order total into the sticky
 * mobile bar, keeping both in sync across `updated_checkout` refreshes.
 */

function initCheckoutStickyBar() {
  if (!document.body.classList.contains("checkout-page")) {
    return;
  }

  const placeOrder = document.querySelector("#place_order");
  const stickySubmit = document.querySelector("[data-checkout-submit]");
  const stickyTotal = document.querySelector("[data-checkout-sticky-total]");

  if (!placeOrder || !stickySubmit) {
    return;
  }

  const syncStickyTotal = () => {
    const orderTotalCell = document.querySelector(".checkout-totals-table .order-total td");
    if (orderTotalCell && stickyTotal) {
      stickyTotal.innerHTML = orderTotalCell.innerHTML;
    }
  };

  const syncStickySubmitState = () => {
    stickySubmit.disabled = placeOrder.disabled;
    stickySubmit.textContent = placeOrder.textContent.trim() || placeOrder.value;
  };

  stickySubmit.addEventListener("click", () => {
    if (!placeOrder.disabled) {
      placeOrder.click();
    }
  });

  syncStickyTotal();
  syncStickySubmitState();

  new MutationObserver(syncStickySubmitState).observe(placeOrder, {
    attributes: true,
    attributeFilter: ["disabled"],
    childList: true,
    characterData: true,
    subtree: true,
  });

  if (typeof window.jQuery !== "undefined") {
    window.jQuery(document.body).on("updated_checkout", () => {
      syncStickyTotal();
      syncStickySubmitState();
    });
  }
}

initCheckoutStickyBar();
