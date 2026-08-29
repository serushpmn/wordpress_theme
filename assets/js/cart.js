/**
 * Almas Land — cart page behaviour.
 *
 * Upgrades the WooCommerce quantity inputs with +/- controls, swaps the minus
 * button for the remove link at the minimum quantity, and enables the update
 * button when a quantity changes.
 */

function isQuantityInputLocked(input) {
  if (!input) {
    return false;
  }

  const wrap = input.closest(".quantity-control, .cart-item__quantity");
  if (input.readOnly || input.dataset.qtyLocked === "true" || wrap?.dataset.qtyLocked === "true") {
    return true;
  }

  if (wrap?.classList.contains("quantity-control--locked")) {
    return true;
  }

  const max = Number(input.max);
  const min = Number(input.min || 1);
  return max === 1 && min >= 1;
}

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

  if (isQuantityInputLocked(input)) {
    input.readOnly = true;
    input.setAttribute("aria-readonly", "true");
    quantity.classList.add("is-locked");
    quantity.closest(".cart-item__quantity")?.classList.add("quantity-control--locked");
    return;
  }

  const wrap = quantity.closest(".cart-item__quantity");
  const removeLink = wrap?.querySelector("[data-cart-remove], .cart-item__remove");

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

  const syncRemoveSlot = () => {
    const value = Number(input.value || 1);
    const min = Number(input.min || 1);
    const showRemove = value <= Math.max(1, min);
    minus.hidden = showRemove;
    if (removeLink) {
      removeLink.hidden = !showRemove;
    }
  };

  minus.addEventListener("click", () => {
    const next = Math.max(Number(input.min || 1), Number(input.value || 1) - 1);
    input.value = String(next);
    input.dispatchEvent(new Event("change", { bubbles: true }));
    syncRemoveSlot();
  });

  plus.addEventListener("click", () => {
    const max = Number(input.max || 0);
    let next = Number(input.value || 1) + 1;
    if (max > 0) {
      next = Math.min(max, next);
    }
    input.value = String(next);
    input.dispatchEvent(new Event("change", { bubbles: true }));
    syncRemoveSlot();
  });

  input.addEventListener("change", syncRemoveSlot);
  syncRemoveSlot();
});

const cartUpdateButton = document.querySelector(".woocommerce-cart-form .cart-update-button");

document.querySelectorAll(".woocommerce-cart-form input.qty").forEach((input) => {
  if (isQuantityInputLocked(input)) {
    input.readOnly = true;
    return;
  }

  input.addEventListener("change", () => {
    if (cartUpdateButton) {
      cartUpdateButton.disabled = false;
    }
  });
});
