/**
 * Landing Page Configuration for DifiHome
 * This file manages all URLs and links between landing page and billing app
 * VERSION: Production
 */

// ============================================
// Configuration
// ============================================
const DIFIHOME_CONFIG = {
  // Production URLs
  LANDING_URL: "https://difihome.my.id",
  BILLING_URL: "https://billing.difihome.my.id",

  // API Endpoints
  API: {
    REGISTER: "/api/customer/register",
    PACKAGES: "/api/packages/list",
    AREAS: "/api/areas/coverage",
    CHECK_COVERAGE: "/api/areas/check-coverage",
  },

  // Routes
  ROUTES: {
    LOGIN: "/",
    REGISTER: "/register",
    CUSTOMER_DASHBOARD: "/customer/dashboard",
    ADMIN_DASHBOARD: "/dashboard",
    PACKAGES: "/packages",
    CONTACT: "/contact",
  },
};

// ============================================
// Helper Functions
// ============================================

/**
 * Redirect to billing application
 */
function redirectToBilling(path = "") {
  const url = DIFIHOME_CONFIG.BILLING_URL + path;
  window.location.href = url;
}

/**
 * Open billing page in new tab
 */
function openBillingInNewTab(path = "") {
  const url = DIFIHOME_CONFIG.BILLING_URL + path;
  window.open(url, "_blank");
}

/**
 * Get full API URL
 */
function getApiUrl(endpoint) {
  return DIFIHOME_CONFIG.BILLING_URL + endpoint;
}

/**
 * Fetch data from billing API
 */
async function fetchFromBillingAPI(endpoint, options = {}) {
  const url = getApiUrl(endpoint);

  try {
    const response = await fetch(url, {
      ...options,
      headers: {
        "Content-Type": "application/json",
        Accept: "application/json",
        ...options.headers,
      },
    });

    if (!response.ok) {
      throw new Error(`API Error: ${response.status}`);
    }

    return await response.json();
  } catch (error) {
    console.error("API Fetch Error:", error);
    throw error;
  }
}

/**
 * Submit form to billing application
 */
async function submitFormToBilling(endpoint, formData) {
  const url = getApiUrl(endpoint);

  try {
    const response = await fetch(url, {
      method: "POST",
      body: formData instanceof FormData ? formData : JSON.stringify(formData),
      headers:
        formData instanceof FormData
          ? {}
          : {
              "Content-Type": "application/json",
              Accept: "application/json",
            },
    });

    const data = await response.json();

    if (!response.ok) {
      throw new Error(data.message || "Form submission failed");
    }

    return data;
  } catch (error) {
    console.error("Form Submit Error:", error);
    throw error;
  }
}

// ============================================
// Auto-Update Links on Page Load
// ============================================
document.addEventListener("DOMContentLoaded", function () {
  console.log("DifiHome Config Loaded:", DIFIHOME_CONFIG);

  // Update all links with data-action attributes
  updateActionLinks();

  // Update forms with data-billing-form attribute
  updateBillingForms();

  // Add click handlers for special buttons
  addButtonHandlers();
});

/**
 * Update all links with data-action attributes
 */
function updateActionLinks() {
  const actionLinks = document.querySelectorAll("[data-action]");

  actionLinks.forEach((link) => {
    const action = link.getAttribute("data-action");
    let targetUrl = "";

    switch (action) {
      case "login":
        targetUrl = DIFIHOME_CONFIG.BILLING_URL + DIFIHOME_CONFIG.ROUTES.LOGIN;
        break;
      case "register":
        targetUrl = DIFIHOME_CONFIG.BILLING_URL + DIFIHOME_CONFIG.ROUTES.REGISTER;
        break;
      case "customer-portal":
      case "customer-dashboard":
        targetUrl = DIFIHOME_CONFIG.BILLING_URL + DIFIHOME_CONFIG.ROUTES.CUSTOMER_DASHBOARD;
        break;
      case "admin-dashboard":
        targetUrl = DIFIHOME_CONFIG.BILLING_URL + DIFIHOME_CONFIG.ROUTES.ADMIN_DASHBOARD;
        break;
      case "packages":
        targetUrl = DIFIHOME_CONFIG.BILLING_URL + DIFIHOME_CONFIG.ROUTES.PACKAGES;
        break;
    }

    if (targetUrl) {
      link.href = targetUrl;
      console.log(`Updated link [${action}]:`, targetUrl);
    }
  });
}

/**
 * Update forms that submit to billing API
 */
function updateBillingForms() {
  const billingForms = document.querySelectorAll("[data-billing-form]");

  billingForms.forEach((form) => {
    const apiEndpoint = form.getAttribute("data-billing-form");
    const submitButton = form.querySelector('[type="submit"]');

    form.addEventListener("submit", async function (e) {
      e.preventDefault();

      if (submitButton) {
        submitButton.disabled = true;
        submitButton.textContent = "Mengirim...";
      }

      try {
        const formData = new FormData(form);
        const result = await submitFormToBilling(apiEndpoint, formData);

        // Show success message
        showNotification("success", result.message || "Berhasil!");

        // Redirect if specified
        if (result.redirect) {
          setTimeout(() => {
            window.location.href = DIFIHOME_CONFIG.BILLING_URL + result.redirect;
          }, 1500);
        }
      } catch (error) {
        showNotification("error", error.message || "Terjadi kesalahan");
      } finally {
        if (submitButton) {
          submitButton.disabled = false;
          submitButton.textContent = form.getAttribute("data-submit-text") || "Kirim";
        }
      }
    });
  });
}

/**
 * Add click handlers for special buttons
 */
function addButtonHandlers() {
  // Login buttons
  document.querySelectorAll(".btn-login, .login-btn").forEach((btn) => {
    if (!btn.hasAttribute("data-action")) {
      btn.addEventListener("click", (e) => {
        e.preventDefault();
        redirectToBilling(DIFIHOME_CONFIG.ROUTES.LOGIN);
      });
    }
  });

  // Register buttons
  document.querySelectorAll(".btn-register, .register-btn, .btn-daftar").forEach((btn) => {
    if (!btn.hasAttribute("data-action")) {
      btn.addEventListener("click", (e) => {
        e.preventDefault();
        redirectToBilling(DIFIHOME_CONFIG.ROUTES.REGISTER);
      });
    }
  });
}

/**
 * Show notification/alert
 */
function showNotification(type, message) {
  // You can customize this based on your UI framework
  if (typeof Swal !== "undefined") {
    // If using SweetAlert2
    Swal.fire({
      icon: type === "success" ? "success" : "error",
      title: type === "success" ? "Berhasil!" : "Gagal!",
      text: message,
      timer: 3000,
    });
  } else {
    // Fallback to alert
    alert(message);
  }
}

// ============================================
// Utility Functions for Package/Area Display
// ============================================

/**
 * Load and display packages from billing API
 */
async function loadPackages(containerId = "packages-container") {
  const container = document.getElementById(containerId);
  if (!container) return;

  try {
    container.innerHTML = '<div class="loading">Memuat paket...</div>';

    const data = await fetchFromBillingAPI(DIFIHOME_CONFIG.API.PACKAGES);

    if (data.success && data.packages) {
      displayPackages(data.packages, container);
    } else {
      container.innerHTML = '<div class="error">Gagal memuat paket</div>';
    }
  } catch (error) {
    container.innerHTML = '<div class="error">Gagal menghubungi server</div>';
  }
}

/**
 * Display packages in HTML
 */
function displayPackages(packages, container) {
  const html = packages
    .map(
      (pkg) => `
        <div class="package-card">
            <h3>${pkg.name}</h3>
            <div class="package-speed">${pkg.bandwidth} Mbps</div>
            <div class="package-price">Rp ${formatCurrency(pkg.price)}/bulan</div>
            <button onclick="selectPackage(${pkg.id})" class="btn btn-primary">
                Pilih Paket
            </button>
        </div>
    `
    )
    .join("");

  container.innerHTML = html;
}

/**
 * Handle package selection
 */
function selectPackage(packageId) {
  redirectToBilling(`${DIFIHOME_CONFIG.ROUTES.REGISTER}?package=${packageId}`);
}

/**
 * Format currency
 */
function formatCurrency(amount) {
  return new Intl.NumberFormat("id-ID").format(amount);
}

/**
 * Check coverage availability
 */
async function checkCoverage(address) {
  try {
    const data = await fetchFromBillingAPI(`${DIFIHOME_CONFIG.API.CHECK_COVERAGE}?address=${encodeURIComponent(address)}`);
    return data;
  } catch (error) {
    console.error("Coverage check failed:", error);
    return { success: false, message: "Gagal mengecek jangkauan" };
  }
}

// ============================================
// Export for use in other scripts
// ============================================
window.DifiHome = {
  config: DIFIHOME_CONFIG,
  redirectToBilling,
  openBillingInNewTab,
  fetchFromBillingAPI,
  submitFormToBilling,
  loadPackages,
  checkCoverage,
  formatCurrency,
};

console.log("✅ DifiHome Landing Page Configuration Loaded");
