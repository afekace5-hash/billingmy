/**
 * Menu Dropdown Fix
 * Ensures dropdown menus stay open when submenu items are clicked/active
 */
document.addEventListener("DOMContentLoaded", function () {
  // Function to keep dropdowns open when submenu items are active
  function keepDropdownsOpen() {
    // Find all active menu items
    const activeItems = document.querySelectorAll(".vertical-menu .metismenu .nav-link.active");

    activeItems.forEach(function (activeItem) {
      // Check if this active item is inside a dropdown
      const parentCollapse = activeItem.closest(".collapse");
      if (parentCollapse) {
        // Get the parent dropdown toggle
        const parentToggle = document.querySelector(`[data-bs-toggle="collapse"][href="#${parentCollapse.id}"]`);
        if (parentToggle) {
          // Expand the dropdown
          parentCollapse.classList.add("show");
          parentToggle.classList.remove("collapsed");
          parentToggle.setAttribute("aria-expanded", "true");

          // Rotate the arrow
          const indicator = parentToggle.querySelector(".submenu-indicator");
          if (indicator) {
            indicator.style.transform = "translateY(-50%) rotate(90deg)";
          }

          // If the parent is also in a dropdown, expand that too (for multi-level menus)
          const grandparentCollapse = parentToggle.closest(".collapse");
          if (grandparentCollapse) {
            const grandparentToggle = document.querySelector(`[data-bs-toggle="collapse"][href="#${grandparentCollapse.id}"]`);
            if (grandparentToggle) {
              grandparentCollapse.classList.add("show");
              grandparentToggle.classList.remove("collapsed");
              grandparentToggle.setAttribute("aria-expanded", "true");

              const grandparentIndicator = grandparentToggle.querySelector(".submenu-indicator");
              if (grandparentIndicator) {
                grandparentIndicator.style.transform = "translateY(-50%) rotate(90deg)";
              }
            }
          }
        }
      }
    });
  }

  // Check and keep dropdowns open initially
  keepDropdownsOpen();

  // Also check again after a short delay to ensure all elements are properly initialized
  setTimeout(keepDropdownsOpen, 500);

  // Add click handlers to all submenu items to keep parent dropdowns open
  const submenuItems = document.querySelectorAll(".vertical-menu .metismenu .collapse .nav-link");
  submenuItems.forEach(function (item) {
    item.addEventListener("click", function (e) {
      // Mark clicked item as active
      const allLinks = document.querySelectorAll(".vertical-menu .metismenu .nav-link");
      allLinks.forEach((link) => link.classList.remove("active"));
      this.classList.add("active");

      // Keep parent dropdown open
      const parentCollapse = this.closest(".collapse");
      if (parentCollapse) {
        // Prevent bootstrap from collapsing the dropdown
        e.stopPropagation();

        // Keep dropdown open
        parentCollapse.classList.add("show");
        const parentToggle = document.querySelector(`[data-bs-toggle="collapse"][href="#${parentCollapse.id}"]`);
        if (parentToggle) {
          parentToggle.classList.remove("collapsed");
          parentToggle.setAttribute("aria-expanded", "true");
        }
      }
    });
  });

  // Fix dropdown toggles to keep them expanded when clicked while already open
  const dropdownToggles = document.querySelectorAll('.vertical-menu .metismenu [data-bs-toggle="collapse"]');
  dropdownToggles.forEach(function (toggle) {
    toggle.addEventListener("click", function (e) {
      const target = document.querySelector(this.getAttribute("href"));
      if (target && target.classList.contains("show")) {
        // Prevent bootstrap from collapsing the dropdown if it's already open
        // and a submenu item inside is active
        const hasActiveSubmenu = target.querySelector(".nav-link.active");
        if (hasActiveSubmenu) {
          e.preventDefault();
          e.stopPropagation();
        }
      }
    });
  });

  // Monitor URL changes for SPA behavior (if applicable)
  let lastUrl = location.href;
  const observer = new MutationObserver(function () {
    if (location.href !== lastUrl) {
      lastUrl = location.href;
      setTimeout(keepDropdownsOpen, 300);
    }
  });
  observer.observe(document, { subtree: true, childList: true });

  // Additional check on window load to ensure everything is properly set up
  window.addEventListener("load", keepDropdownsOpen);
});
