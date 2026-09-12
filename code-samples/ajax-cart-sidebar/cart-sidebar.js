(function ($) {
  "use strict";

  const drawer = document.querySelector("#stonesamin-cart-sidebar");

  if (!drawer) {
    return;
  }

  const documentBody = document.body;
  const panel = drawer.querySelector(".stonesamin-cart-sidebar__panel");
  const closeButton = drawer.querySelector(".stonesamin-cart-sidebar__close");
  const overlay = drawer.querySelector(".stonesamin-cart-sidebar__overlay");
  const statusRegion = drawer.querySelector("[data-stonesamin-cart-status]");
  const triggers = Array.from(
    document.querySelectorAll("[data-stonesamin-cart-toggle]")
  );
  const focusableSelector = [
    "a[href]",
    "button:not([disabled])",
    "input:not([disabled])",
    "select:not([disabled])",
    "textarea:not([disabled])",
    '[tabindex]:not([tabindex="-1"])',
  ].join(",");

  let lastFocusedElement = null;

  const isOpen = () => drawer.classList.contains("is-open");

  const announce = (message) => {
    if (!statusRegion) {
      return;
    }

    statusRegion.textContent = "";
    window.setTimeout(() => {
      statusRegion.textContent = message;
    }, 50);
  };

  const setTriggerState = (expanded) => {
    triggers.forEach((trigger) => {
      trigger.setAttribute("aria-expanded", String(expanded));
    });
  };

  const openDrawer = () => {
    if (isOpen()) {
      return;
    }

    lastFocusedElement = document.activeElement;
    drawer.classList.add("is-open");
    drawer.setAttribute("aria-hidden", "false");
    documentBody.classList.add("stonesamin-cart-sidebar-open");
    setTriggerState(true);

    window.setTimeout(() => closeButton?.focus(), 200);
  };

  const closeDrawer = () => {
    if (!isOpen()) {
      return;
    }

    drawer.classList.remove("is-open");
    drawer.setAttribute("aria-hidden", "true");
    documentBody.classList.remove("stonesamin-cart-sidebar-open");
    setTriggerState(false);

    if (lastFocusedElement instanceof HTMLElement) {
      lastFocusedElement.focus();
    }
  };

  const trapFocus = (event) => {
    if (event.key !== "Tab" || !isOpen() || !panel) {
      return;
    }

    const focusableElements = Array.from(
      panel.querySelectorAll(focusableSelector)
    ).filter((element) => !element.hasAttribute("hidden"));

    if (!focusableElements.length) {
      event.preventDefault();
      return;
    }

    const firstElement = focusableElements[0];
    const lastElement = focusableElements[focusableElements.length - 1];

    if (event.shiftKey && document.activeElement === firstElement) {
      event.preventDefault();
      lastElement.focus();
    } else if (!event.shiftKey && document.activeElement === lastElement) {
      event.preventDefault();
      firstElement.focus();
    }
  };

  triggers.forEach((trigger) => {
    trigger.addEventListener("click", (event) => {
      event.preventDefault();
      isOpen() ? closeDrawer() : openDrawer();
    });
  });

  closeButton?.addEventListener("click", closeDrawer);
  overlay?.addEventListener("click", closeDrawer);

  document.addEventListener("keydown", (event) => {
    if (event.key === "Escape" && isOpen()) {
      closeDrawer();
      return;
    }

    trapFocus(event);
  });

  const updateAccessibleLabels = () => {
    const countElement = document.querySelector(
      ".stonesamin-cart-count-badge"
    );
    const count = Number.parseInt(countElement?.textContent || "0", 10) || 0;

    triggers.forEach((trigger) => {
      trigger.setAttribute(
        "aria-label",
        count > 0 ? `سبد خرید، ${count} محصول` : "سبد خرید، خالی"
      );
    });
  };

  $(documentBody).on("added_to_cart", () => {
    updateAccessibleLabels();
    announce("محصول به سبد خرید اضافه شد.");
    openDrawer();
  });

  $(documentBody).on("removed_from_cart", () => {
    updateAccessibleLabels();
    announce("محصول از سبد خرید حذف شد.");
  });

  $(documentBody).on(
    "wc_fragments_refreshed wc_fragments_loaded",
    updateAccessibleLabels
  );

  updateAccessibleLabels();
})(jQuery);
