/**
 * Enhanced Preloader Manager
 * Mengatasi masalah preloader yang mengganggu proses update dan insert
 * Author: Fixed by GitHub Copilot
 * Date: October 2025
 */

class PreloaderManager {
  constructor() {
    this.preloader = null;
    this.isInitialized = false;
    this.activeRequests = new Set();
    this.modalStates = new Map();
    this.init();
  }

  init() {
    if (this.isInitialized) return;

    this.preloader = document.getElementById("preloader");
    this.setupEventListeners();
    this.isInitialized = true;

    // Auto-hide preloader after page load
    this.autoHideOnLoad();
  }

  setupEventListeners() {
    // Modal event listeners untuk semua modal
    document.addEventListener("show.bs.modal", (e) => {
      this.handleModalShow(e.target);
    });

    document.addEventListener("shown.bs.modal", (e) => {
      this.handleModalShown(e.target);
    });

    document.addEventListener("hide.bs.modal", (e) => {
      this.handleModalHide(e.target);
    });

    document.addEventListener("hidden.bs.modal", (e) => {
      this.handleModalHidden(e.target);
    });

    // AJAX event listeners
    $(document).ajaxStart(() => {
      // Jangan otomatis show preloader untuk semua AJAX
      // Biarkan masing-masing handler mengatur sendiri
    });

    $(document).ajaxStop(() => {
      // Safety: hide preloader setelah semua AJAX selesai
      setTimeout(() => {
        if (this.activeRequests.size === 0) {
          this.hide();
        }
      }, 300);
    });

    $(document).ajaxError(() => {
      this.hide();
    });
  }

  /**
   * Show preloader dengan kontrol yang lebih baik
   */
  show(requestId = null) {
    if (requestId) {
      this.activeRequests.add(requestId);
    }

    // Jangan show preloader jika ada modal yang terbuka
    if (this.hasOpenModal()) {
      // Log removed
      return false;
    }

    if (this.preloader) {
      this.preloader.style.display = "flex";
      this.preloader.style.opacity = "1";
      this.preloader.style.visibility = "visible";
      document.body.classList.add("preloader-active");
    }
    // Preloader spinner dinonaktifkan
    return false;
  }

  /**
   * Hide preloader dengan cleanup
   */
  hide(requestId = null) {
    if (requestId) {
      this.activeRequests.delete(requestId);
    }

    // Jika masih ada active requests, jangan hide
    if (this.activeRequests.size > 0) {
      return false;
    }

    if (this.preloader) {
      this.preloader.classList.add("preloader-fade-out");
      setTimeout(() => {
        this.preloader.style.display = "none";
        this.preloader.style.opacity = "0";
        this.preloader.style.visibility = "hidden";
        document.body.classList.remove("preloader-active");
        this.preloader.classList.remove("preloader-fade-out");
      }, 300);
    }
    return true;
  }

  /**
   * Force hide preloader tanpa mempertimbangkan active requests
   */
  forceHide() {
    this.activeRequests.clear();
    this.hide();

    // Extra cleanup
    if (this.preloader) {
      this.preloader.style.display = "none !important";
      this.preloader.style.opacity = "0 !important";
      this.preloader.style.visibility = "hidden !important";
    }
    document.body.classList.remove("preloader-active");
  }

  /**
   * Check apakah ada modal yang terbuka
   */
  hasOpenModal() {
    const modals = document.querySelectorAll('.modal.show, .modal[style*="display: block"]');
    return modals.length > 0;
  }

  /**
   * Handle modal show event
   */
  handleModalShow(modal) {
    const modalId = modal.id || "unknown";
    this.modalStates.set(modalId, "showing");

    // Hide preloader saat modal akan dibuka
    this.forceHide();
  }

  /**
   * Handle modal shown event
   */
  handleModalShown(modal) {
    const modalId = modal.id || "unknown";
    this.modalStates.set(modalId, "shown");

    // Pastikan preloader hidden dan bersihkan loading states
    this.forceHide();
    this.cleanupLoadingStates(modal);
  }

  /**
   * Handle modal hide event
   */
  handleModalHide(modal) {
    const modalId = modal.id || "unknown";
    this.modalStates.set(modalId, "hiding");
  }

  /**
   * Handle modal hidden event
   */
  handleModalHidden(modal) {
    const modalId = modal.id || "unknown";
    this.modalStates.delete(modalId);

    // Reset form states jika ada
    this.resetModalForms(modal);
  }

  /**
   * Cleanup loading states dalam modal
   */
  cleanupLoadingStates(modal) {
    if (!modal) return;

    // Remove loading elements
    const loadingElements = modal.querySelectorAll('.loading, .spinner, .spin, [class*="spin"], [class*="loading"]');
    loadingElements.forEach((el) => el.remove());

    // Remove loading classes
    const allElements = modal.querySelectorAll("*");
    allElements.forEach((el) => {
      el.classList.remove("loading", "spinning");
    });
  }

  /**
   * Reset form states dalam modal
   */
  resetModalForms(modal) {
    if (!modal) return;

    const forms = modal.querySelectorAll("form");
    forms.forEach((form) => {
      // Reset validation states
      const invalidElements = form.querySelectorAll(".is-invalid");
      invalidElements.forEach((el) => el.classList.remove("is-invalid"));

      // Hide error messages
      const errorElements = form.querySelectorAll('.invalid-feedback, [id*="error"]');
      errorElements.forEach((el) => {
        if (el.style) el.style.display = "none";
      });

      // Reset button states
      const buttons = form.querySelectorAll('button[type="submit"]');
      buttons.forEach((btn) => {
        btn.disabled = false;
        // Reset button text jika ada data-original-text
        const originalText = btn.getAttribute("data-original-text");
        if (originalText) {
          btn.innerHTML = originalText;
        }
      });
    });
  }

  /**
   * Auto hide preloader setelah page load
   */
  autoHideOnLoad() {
    // Hide setelah DOM ready
    document.addEventListener("DOMContentLoaded", () => {
      setTimeout(() => {
        if (!this.hasOpenModal()) {
          this.hide();
        }
      }, 1000);
    });

    // Hide setelah window load
    window.addEventListener("load", () => {
      setTimeout(() => {
        if (!this.hasOpenModal()) {
          this.forceHide();
        }
      }, 500);
    });
  }
  ajaxWrapper(options) {
    const requestId = "ajax_" + Date.now() + "_" + Math.random();
    const originalSuccess = options.success;
    const originalError = options.error;
    const originalComplete = options.complete;

    // Show preloader jika tidak ada modal terbuka
    if (options.showPreloader !== false) {
      this.show(requestId);
    }

    // Override success callback
    options.success = (data, textStatus, xhr) => {
      this.hide(requestId);
      if (originalSuccess) {
        originalSuccess(data, textStatus, xhr);
      }
    };

    // Override error callback
    options.error = (xhr, textStatus, errorThrown) => {
      this.hide(requestId);
      if (originalError) {
        originalError(xhr, textStatus, errorThrown);
      }
    };

    // Override complete callback
    options.complete = (xhr, textStatus) => {
      this.hide(requestId);
      if (originalComplete) {
        originalComplete(xhr, textStatus);
      }
    };

    return $.ajax(options);
  }

  /**
   * Helper untuk form submission dengan preloader
   */
  handleFormSubmission(form, options = {}) {
    const $form = $(form);
    const submitBtn = $form.find('button[type="submit"]');
    const originalText = submitBtn.html();

    // Store original text untuk reset nanti
    submitBtn.attr("data-original-text", originalText);

    // Set loading state
    if (options.loadingText) {
      submitBtn.html(options.loadingText);
    }
    submitBtn.prop("disabled", true);

    // Don't show global preloader untuk form dalam modal
    const isInModal = $form.closest(".modal").length > 0;
    return {
      resetButton: () => {
        submitBtn.html(originalText);
        submitBtn.prop("disabled", false);
      },
      isInModal: isInModal,
    };
  }
}

// Initialize global preloader manager
window.PreloaderManager = new PreloaderManager();

// Expose helper functions globally
window.showPreloader = function (requestId) {
  return window.PreloaderManager.show(requestId);
};

window.hidePreloader = function (requestId) {
  return window.PreloaderManager.hide(requestId);
};

window.forceHidePreloader = function () {
  return window.PreloaderManager.forceHide();
};

// jQuery plugin untuk AJAX dengan preloader management
if (typeof $ !== "undefined") {
  $.ajaxPreloader = function (options) {
    return window.PreloaderManager.ajaxWrapper(options);
  };
}

// Log removed
