/**
 * Custom Menu JavaScript
 * Enhances the dropdown menu functionality for better user experience
 */
document.addEventListener("DOMContentLoaded", function () {
  // Add active class to the current page's menu item
  const currentPath = window.location.pathname;
  const menuLinks = document.querySelectorAll(".nav-link");

  // Function to fix submenu alignment
  function adjustSubmenuAlignment() {
    const submenus = document.querySelectorAll(".menu-dropdown");
    submenus.forEach((submenu) => {
      // Ensure proper padding and alignment
      const menuItems = submenu.querySelectorAll(".nav-link");
      menuItems.forEach((item) => {
        // Make sure bullet points are aligned
        const bullet = item.querySelector("::before");
        if (bullet) {
          bullet.style.left = "2.2rem";
        }
      });
    });
  }

  // Run alignment fix on page load and when any dropdown is shown
  adjustSubmenuAlignment();

  menuLinks.forEach(function (link) {
    const href = link.getAttribute("href");
    if (href && currentPath.includes(href) && href !== "#") {
      link.classList.add("active");

      // If this is a submenu item, expand its parent
      const parentCollapse = link.closest(".collapse");
      if (parentCollapse) {
        parentCollapse.classList.add("show");
        const parentTrigger = document.querySelector(`[data-bs-toggle="collapse"][href="#${parentCollapse.id}"]`);
        if (parentTrigger) {
          parentTrigger.classList.remove("collapsed");
          parentTrigger.setAttribute("aria-expanded", "true");

          // Also rotate the indicator
          const indicator = parentTrigger.querySelector(".submenu-indicator");
          if (indicator) {
            indicator.style.transform = "rotate(90deg)";
          }
        }
      }
    }
  });

  // Toggle rotation of submenu indicators when dropdown is clicked
  const dropdownToggles = document.querySelectorAll('.nav-link[data-bs-toggle="collapse"]');

  dropdownToggles.forEach(function (toggle) {
    toggle.addEventListener("click", function () {
      const isExpanded = toggle.getAttribute("aria-expanded") === "true";
      const indicator = toggle.querySelector(".submenu-indicator");

      if (indicator) {
        if (isExpanded) {
          indicator.style.transform = "rotate(90deg)";
        } else {
          indicator.style.transform = "rotate(0deg)";
        }
      }

      // Call alignment fix after dropdown animation completes
      setTimeout(adjustSubmenuAlignment, 300);
    });

    // Initialize indicators based on current state
    const isExpanded = toggle.getAttribute("aria-expanded") === "true";
    const indicator = toggle.querySelector(".submenu-indicator");

    if (indicator && isExpanded) {
      indicator.style.transform = "rotate(90deg)";
    }
  });

  // Add subtle hover effects to menu items
  const allNavItems = document.querySelectorAll(".nav-item");

  allNavItems.forEach(function (item) {
    const link = item.querySelector(".nav-link");
    if (link) {
      link.addEventListener("mouseenter", function () {
        if (!link.classList.contains("menu-link")) return;
        link.style.paddingLeft = "1.6rem";
      });

      link.addEventListener("mouseleave", function () {
        if (!link.classList.contains("menu-link")) return;
        link.style.paddingLeft = "";
      });
    }
  });
});
