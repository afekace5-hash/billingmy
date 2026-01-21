/**
 * Menu Alignment Helper
 * Ensures menu items and submenu items are perfectly aligned
 */
document.addEventListener("DOMContentLoaded", function () {
  // Apply specific fixes for menu alignment
  function applyMenuAlignmentFixes() {
    // Main menu items
    const mainMenuItems = document.querySelectorAll(".vertical-menu .metismenu > li > .menu-link");

    mainMenuItems.forEach((item) => {
      // Ensure every main menu item has proper structure
      const icon = item.querySelector("i:not(.submenu-indicator)");
      const textNodes = Array.from(item.childNodes).filter((node) => node.nodeType === 3 && node.textContent.trim() !== "");

      // If there's plain text, wrap it in a span
      if (textNodes.length > 0) {
        const span = document.createElement("span");
        span.textContent = textNodes[0].textContent.trim();
        item.replaceChild(span, textNodes[0]);
      }

      // Ensure submenu indicators are properly positioned
      if (item.hasAttribute("data-bs-toggle")) {
        let indicator = item.querySelector(".submenu-indicator");

        if (!indicator) {
          indicator = document.createElement("i");
          indicator.className = "bx bx-chevron-right submenu-indicator";
          item.appendChild(indicator);
        }

        // Fix for arrow position when submenu is expanded
        item.addEventListener("click", function () {
          // Use setTimeout to allow Bootstrap to toggle aria-expanded
          setTimeout(() => {
            const isExpanded = this.getAttribute("aria-expanded") === "true";
            const arrow = this.querySelector(".submenu-indicator");

            if (arrow) {
              // Ensure position stays fixed when rotated
              arrow.style.right = "1rem";
              arrow.style.top = "50%";
              arrow.style.transformOrigin = "center";

              if (isExpanded) {
                arrow.style.transform = "translateY(-50%) rotate(90deg)";
              } else {
                arrow.style.transform = "translateY(-50%)";
              }
            }
          }, 10);
        });
      }
    });

    // Submenu items - ensure they have proper indentation
    const submenuItems = document.querySelectorAll(".vertical-menu .metismenu .mm-collapse .nav-item .nav-link");

    submenuItems.forEach((item) => {
      // Set explicit padding
      item.style.paddingLeft = "3rem";

      // Check if it has the bullet point
      if (!item.querySelector(".bullet-point")) {
        const bullet = document.createElement("span");
        bullet.className = "bullet-point";
        bullet.style.position = "absolute";
        bullet.style.left = "1.7rem";
        bullet.style.top = "50%";
        bullet.style.transform = "translateY(-50%)";
        bullet.style.width = "6px";
        bullet.style.height = "6px";
        bullet.style.borderRadius = "50%";
        bullet.style.backgroundColor = "rgba(108, 117, 125, 0.5)";
        item.prepend(bullet);
      }
    });
  }

  // Initial application
  applyMenuAlignmentFixes();

  // Apply again when DOM might change (like after Bootstrap initializes)
  setTimeout(applyMenuAlignmentFixes, 500);

  // Listen for submenu toggle events to reapply fixes
  const submenuToggleButtons = document.querySelectorAll('[data-bs-toggle="collapse"]');
  submenuToggleButtons.forEach((button) => {
    button.addEventListener("click", () => {
      setTimeout(applyMenuAlignmentFixes, 300);
    });
  });
});
