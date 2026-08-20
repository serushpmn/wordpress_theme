(function () {
  "use strict";

  function normalizeHex(value) {
    const raw = String(value || "").trim();
    if (!raw) {
      return "";
    }

    let hex = raw.startsWith("#") ? raw : `#${raw}`;
    if (/^#[0-9a-fA-F]{3}$/.test(hex)) {
      const chars = hex.slice(1).split("");
      hex = `#${chars[0]}${chars[0]}${chars[1]}${chars[1]}${chars[2]}${chars[2]}`;
    }

    return /^#[0-9a-fA-F]{6}$/.test(hex) ? hex.toUpperCase() : "";
  }

  function syncColorField(hexInput, pickerInput) {
    const hex = normalizeHex(hexInput.value);
    hexInput.value = hex;
    if (hex) {
      pickerInput.value = hex;
    }
  }

  function reindexRows(container) {
    container.querySelectorAll("[data-product-color-row]").forEach((row, index) => {
      row.querySelectorAll("input[name]").forEach((input) => {
        const name = input.getAttribute("name");
        if (!name) {
          return;
        }
        input.setAttribute(
          "name",
          name.replace(/_almas_product_colors\[(?:\d+|__INDEX__)\]/, `_almas_product_colors[${index}]`)
        );
      });
    });
  }

  function initColorField(field) {
    const hexInput = field.querySelector("[data-product-color-hex]");
    const pickerInput = field.querySelector("[data-product-color-picker]");
    if (!hexInput || !pickerInput) {
      return;
    }

    syncColorField(hexInput, pickerInput);

    hexInput.addEventListener("change", () => {
      syncColorField(hexInput, pickerInput);
    });

    hexInput.addEventListener("blur", () => {
      syncColorField(hexInput, pickerInput);
    });

    pickerInput.addEventListener("input", () => {
      hexInput.value = normalizeHex(pickerInput.value);
    });
  }

  function initColorsRepeater(root) {
    const rowsWrap = root.querySelector("[data-product-colors-rows]");
    const addButton = root.querySelector("[data-product-colors-add]");
    const template = root.querySelector("[data-product-color-template]");

    if (!rowsWrap || !addButton || !template) {
      return;
    }

    rowsWrap.querySelectorAll(".almasland-product-color-field").forEach(initColorField);

    addButton.addEventListener("click", () => {
      const html = template.innerHTML.replace(/__INDEX__/g, String(rowsWrap.children.length));
      const holder = document.createElement("div");
      holder.innerHTML = html.trim();
      const row = holder.firstElementChild;
      if (!row) {
        return;
      }
      rowsWrap.appendChild(row);
      const field = row.querySelector(".almasland-product-color-field");
      if (field) {
        initColorField(field);
      }
      reindexRows(rowsWrap);
    });

    rowsWrap.addEventListener("click", (event) => {
      const removeButton = event.target.closest("[data-product-color-remove]");
      if (!removeButton) {
        return;
      }

      const row = removeButton.closest("[data-product-color-row]");
      if (!row) {
        return;
      }

      if (rowsWrap.children.length <= 1) {
        row.querySelectorAll("input").forEach((input) => {
          if (input.type === "color") {
            input.value = "#000000";
          } else {
            input.value = "";
          }
        });
        return;
      }

      row.remove();
      reindexRows(rowsWrap);
    });
  }

  document.addEventListener("DOMContentLoaded", () => {
    document.querySelectorAll("[data-product-colors]").forEach(initColorsRepeater);
  });
})();
