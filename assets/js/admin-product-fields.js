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

  document.addEventListener("DOMContentLoaded", () => {
    document.querySelectorAll(".almasland-product-color-field").forEach(initColorField);
  });
})();
