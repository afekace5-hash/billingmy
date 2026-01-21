/**
 * Menu Alignment Fix
 * This script enhances the menu appearance and fixes alignment issues
 */
document.addEventListener("DOMContentLoaded", function () {
  // Fix menu items alignment
  function fixMenuAlignment() {
    // Get all menu links
    const menuLinks = document.querySelectorAll(".vertical-menu .nav-link.menu-link");

    menuLinks.forEach((link) => {
      // Check if the link already has a span wrapper for the text
      const textContent = link.childNodes.forEach((node) => {
        if (node.nodeType === 3 && node.textContent.trim() !== "") {
          // It's a text node with content
          const span = document.createElement("span");
          span.textContent = node.textContent;
          link.replaceChild(span, node);
        }
      });
    });

    // Add proper classes to submenu indicators
    const dropdownToggles = document.querySelectorAll('.nav-link[data-bs-toggle="collapse"]');
    dropdownToggles.forEach((toggle) => {
      // Find the indicator
      const indicator = toggle.querySelector(".submenu-indicator");
      if (indicator) {
        // Already has indicator, ensure proper positioning
        indicator.style.position = "absolute";
        indicator.style.right = "15px";
      } else {
        // Create indicator if missing
        const newIndicator = document.createElement("i");
        newIndicator.className = "bx bx-chevron-right submenu-indicator";
        toggle.appendChild(newIndicator);
      }
    });
  }

  // Call the function when document is loaded
  fixMenuAlignment();

  // Add event listener for menu toggle
  const menuToggleBtn = document.querySelector(".vertical-menu-btn");
  if (menuToggleBtn) {
    menuToggleBtn.addEventListener("click", function () {
      setTimeout(fixMenuAlignment, 300); // Re-fix after animation completes
    });
  }

  // Add active class to current page
  const currentPath = window.location.pathname;
  const menuItems = document.querySelectorAll(".vertical-menu .nav-link");

  menuItems.forEach((item) => {
    const href = item.getAttribute("href");
    if (href && (currentPath === href || currentPath.startsWith(href + "/"))) {
      item.classList.add("active");

      // Expand parent if in submenu
      const parentDropdown = item.closest(".menu-dropdown");
      if (parentDropdown) {
        parentDropdown.classList.add("show");
        const parentToggle = document.querySelector(`[href="#${parentDropdown.id}"]`);
        if (parentToggle) {
          parentToggle.classList.remove("collapsed");
          parentToggle.setAttribute("aria-expanded", "true");

          const indicator = parentToggle.querySelector(".submenu-indicator");
          if (indicator) {
            indicator.style.transform = "translateY(-50%) rotate(90deg)";
          }
        }
      }
    }
  });
});
