document.addEventListener("DOMContentLoaded", () => {
  initializeMobileFilterSidebar();
  initializePriceRange();
});

function initializeMobileFilterSidebar() {
  const sidebar = document.querySelector("#stonesamin-product-filters");
  const openButton = document.querySelector(
    ".stonesamin-product-toolbar__filter-button"
  );
  const closeButton = document.querySelector(
    ".stonesamin-filter-panel__close"
  );
  const overlay = document.querySelector(
    ".stonesamin-product-filter-overlay"
  );

  if (!sidebar || !openButton || !closeButton || !overlay) {
    return;
  }

  const mobileBreakpoint = window.matchMedia("(max-width: 1199.98px)");

  const setSidebarAccessibility = (isOpen) => {
    sidebar.setAttribute("aria-hidden", String(!isOpen));
    openButton.setAttribute("aria-expanded", String(isOpen));

    if (isOpen) {
      sidebar.removeAttribute("inert");
    } else {
      sidebar.setAttribute("inert", "");
    }
  };

  const openSidebar = () => {
    sidebar.classList.add("is-open");
    overlay.classList.add("is-active");
    document.body.classList.add("stonesamin-filter-sidebar-open");
    setSidebarAccessibility(true);

    window.setTimeout(() => closeButton.focus(), 250);
  };

  const closeSidebar = ({ restoreFocus = true } = {}) => {
    sidebar.classList.remove("is-open");
    overlay.classList.remove("is-active");
    document.body.classList.remove("stonesamin-filter-sidebar-open");

    if (mobileBreakpoint.matches) {
      setSidebarAccessibility(false);
    }

    if (restoreFocus) {
      openButton.focus();
    }
  };

  const synchronizeSidebarState = () => {
    if (mobileBreakpoint.matches) {
      if (!sidebar.classList.contains("is-open")) {
        setSidebarAccessibility(false);
      }

      return;
    }

    closeSidebar({ restoreFocus: false });
    sidebar.setAttribute("aria-hidden", "false");
    sidebar.removeAttribute("inert");
    openButton.setAttribute("aria-expanded", "false");
  };

  openButton.addEventListener("click", openSidebar);
  closeButton.addEventListener("click", () => closeSidebar());
  overlay.addEventListener("click", () => closeSidebar());

  document.addEventListener("keydown", (event) => {
    if (event.key === "Escape" && sidebar.classList.contains("is-open")) {
      closeSidebar();
    }
  });

  mobileBreakpoint.addEventListener("change", synchronizeSidebarState);
  synchronizeSidebarState();
}

function initializePriceRange() {
  const range = document.querySelector(".stonesamin-price-range");

  if (!range) {
    return;
  }

  const form = document.querySelector("#stonesamin-product-filter-form");
  const minimumInput = range.querySelector("#stonesamin-min-price");
  const maximumInput = range.querySelector("#stonesamin-max-price");
  const minimumOutput = range.querySelector("#stonesamin-min-price-output");
  const maximumOutput = range.querySelector("#stonesamin-max-price-output");

  if (!minimumInput || !maximumInput || !minimumOutput || !maximumOutput) {
    return;
  }

  const bounds = {
    min: Number(range.dataset.min || 0),
    max: Number(range.dataset.max || 0),
  };
  const currencySymbol = range.dataset.currencySymbol || "";
  const numberFormatter = new Intl.NumberFormat("en-US", {
    maximumFractionDigits: 0,
  });

  const formatPrice = (price) =>
    `${numberFormatter.format(price)} ${currencySymbol}`.trim();

  const updateRange = (changedInput) => {
    let minimumPrice = Number(minimumInput.value);
    let maximumPrice = Number(maximumInput.value);

    if (minimumPrice > maximumPrice) {
      if (changedInput === minimumInput) {
        maximumPrice = minimumPrice;
        maximumInput.value = String(maximumPrice);
      } else {
        minimumPrice = maximumPrice;
        minimumInput.value = String(minimumPrice);
      }
    }

    minimumOutput.textContent = formatPrice(minimumPrice);
    maximumOutput.textContent = formatPrice(maximumPrice);
  };

  minimumInput.addEventListener("input", () => updateRange(minimumInput));
  maximumInput.addEventListener("input", () => updateRange(maximumInput));

  form?.addEventListener("submit", () => {
    if (
      Number(minimumInput.value) <= bounds.min &&
      Number(maximumInput.value) >= bounds.max
    ) {
      minimumInput.disabled = true;
      maximumInput.disabled = true;
    }
  });

  updateRange();
}
