/**
 * Enhanced Form Handler
 * Utility untuk menangani form submission dengan loading states yang proper
 * dan tidak mengganggu preloader
 */

class FormHandler {
  constructor() {
    this.activeSubmissions = new Map();
    this.init();
  }

  init() {
    this.setupGlobalFormHandlers();
    this.setupModalHandlers();
  }

  /**
   * Setup global form event handlers
   */
  setupGlobalFormHandlers() {
    // Handle semua form dengan class 'ajax-form'
    $(document).on("submit", ".ajax-form", (e) => {
      e.preventDefault();
      this.handleAjaxForm(e.target);
    });

    // Handle form dengan attribute data-ajax="true"
    $(document).on("submit", 'form[data-ajax="true"]', (e) => {
      e.preventDefault();
      this.handleAjaxForm(e.target);
    });
  }

  /**
   * Setup modal-specific handlers
   */
  setupModalHandlers() {
    // Reset form state ketika modal dibuka
    $(document).on("show.bs.modal", ".modal", (e) => {
      this.resetModalForms(e.target);
    });

    // Cleanup ketika modal ditutup
    $(document).on("hidden.bs.modal", ".modal", (e) => {
      this.cleanupModal(e.target);
    });
  }

  /**
   * Handle AJAX form submission
   */
  handleAjaxForm(form) {
    const $form = $(form);
    const formId = form.id || "form_" + Date.now();
    const config = this.getFormConfig($form);

    // Prevent double submission
    if (this.activeSubmissions.has(formId)) {
      console.log("Form submission already in progress:", formId);
      return false;
    }

    // Mark as active
    this.activeSubmissions.set(formId, true);

    // Setup loading state
    const buttonState = this.setLoadingState($form, config);

    // Prepare form data
    const formData = this.prepareFormData($form);

    // Make AJAX request
    const ajaxOptions = {
      url: config.url || $form.attr("action"),
      type: config.method || $form.attr("method") || "POST",
      data: formData,
      dataType: config.dataType || "json",
      processData: config.processData !== false,
      contentType: config.contentType || "application/x-www-form-urlencoded; charset=UTF-8",
      showPreloader: config.showPreloader !== false && !this.isInModal($form),
      success: (response) => {
        this.handleSuccess($form, response, config, buttonState);
      },
      error: (xhr, status, error) => {
        this.handleError($form, xhr, config, buttonState);
      },
      complete: () => {
        this.handleComplete($form, formId, buttonState);
      },
    };

    // Use PreloaderManager AJAX wrapper if available
    if (window.PreloaderManager) {
      return window.PreloaderManager.ajaxWrapper(ajaxOptions);
    } else {
      return $.ajax(ajaxOptions);
    }
  }

  /**
   * Get form configuration from data attributes
   */
  getFormConfig($form) {
    const config = {};

    // Read config from data attributes
    const dataAttrs = ["url", "method", "dataType", "successCallback", "errorCallback", "loadingText", "showPreloader", "resetOnSuccess", "closeModalOnSuccess"];

    dataAttrs.forEach((attr) => {
      const value = $form.data(attr);
      if (value !== undefined) {
        config[attr] = value;
      }
    });

    return config;
  }

  /**
   * Check if form is inside a modal
   */
  isInModal($form) {
    return $form.closest(".modal").length > 0;
  }

  /**
   * Set loading state for form and button
   */
  setLoadingState($form, config) {
    const $submitBtn = $form.find('button[type="submit"]');
    const originalText = $submitBtn.html();
    const loadingText = config.loadingText || '<i class="bx bx-loader-alt bx-spin me-1"></i>Loading...';

    // Store original state
    $submitBtn.attr("data-original-text", originalText);

    // Set loading state
    $submitBtn.html(loadingText).prop("disabled", true).addClass("btn-loading");

    // Add form loading class if not in modal
    if (!this.isInModal($form)) {
      $form.addClass("form-loading");
    }

    return {
      $button: $submitBtn,
      originalText: originalText,
      reset: () => {
        $submitBtn.html(originalText).prop("disabled", false).removeClass("btn-loading");
        $form.removeClass("form-loading");
      },
    };
  }

  /**
   * Prepare form data with CSRF and file handling
   */
  prepareFormData($form) {
    const hasFileInput = $form.find('input[type="file"]').length > 0;

    if (hasFileInput) {
      const formData = new FormData($form[0]);

      // Add CSRF token if not present
      const csrfTokenName = $('meta[name="csrf-token-name"]').attr("content");
      const csrfToken = $('meta[name="csrf-token"]').attr("content");

      if (csrfTokenName && csrfToken && !formData.has(csrfTokenName)) {
        formData.append(csrfTokenName, csrfToken);
      }

      return formData;
    } else {
      return $form.serialize();
    }
  }

  /**
   * Handle successful form submission
   */
  handleSuccess($form, response, config, buttonState) {
    // Clear validation errors
    this.clearValidationErrors($form);

    // Call custom success callback
    if (config.successCallback && typeof window[config.successCallback] === "function") {
      window[config.successCallback](response, $form);
    }

    // Default success handling
    if (response.status === "success" || response.success) {
      // Show success notification
      this.showNotification("success", response.message || "Data berhasil disimpan");

      // Reset form if configured
      if (config.resetOnSuccess !== false) {
        $form[0].reset();
      }

      // Close modal if configured
      if (config.closeModalOnSuccess !== false && this.isInModal($form)) {
        const modal = $form.closest(".modal");
        const modalInstance = bootstrap.Modal.getOrCreateInstance(modal[0]);
        modalInstance.hide();
      }

      // Reload DataTable if present
      this.reloadDataTable();
    } else {
      this.showNotification("error", response.message || "Terjadi kesalahan");
    }
  }

  /**
   * Handle form submission error
   */
  handleError($form, xhr, config, buttonState) {
    console.error("Form submission error:", xhr);

    // Handle validation errors
    if (xhr.status === 422 && xhr.responseJSON && xhr.responseJSON.errors) {
      this.displayValidationErrors($form, xhr.responseJSON.errors);
    } else {
      // Show general error
      const errorMessage = xhr.responseJSON?.message || "Terjadi kesalahan saat menyimpan data";
      this.showNotification("error", errorMessage);
    }

    // Call custom error callback
    if (config.errorCallback && typeof window[config.errorCallback] === "function") {
      window[config.errorCallback](xhr, $form);
    }
  }

  /**
   * Handle form submission complete
   */
  handleComplete($form, formId, buttonState) {
    // Reset button state
    buttonState.reset();

    // Remove from active submissions
    this.activeSubmissions.delete(formId);
  }

  /**
   * Clear validation errors from form
   */
  clearValidationErrors($form) {
    $form.find(".is-invalid").removeClass("is-invalid");
    $form.find(".invalid-feedback").hide();
    $form.find('[id*="error"]').hide();
  }

  /**
   * Display validation errors
   */
  displayValidationErrors($form, errors) {
    Object.keys(errors).forEach((field) => {
      const $field = $form.find(`[name="${field}"]`);
      const $errorElement = $form.find(`#error${field}, #error_${field}, #error-${field}`);

      if ($field.length) {
        $field.addClass("is-invalid");
      }

      if ($errorElement.length) {
        $errorElement.show().find("strong").html(errors[field]);
      } else {
        // Create error element if not exists
        const $newError = $(`<div class="invalid-feedback">${errors[field]}</div>`);
        $field.after($newError);
      }
    });
  }

  /**
   * Show notification
   */
  showNotification(type, message, title = null) {
    if (typeof toastr !== "undefined") {
      toastr[type](message, title);
    } else if (typeof Swal !== "undefined") {
      Swal.fire({
        icon: type === "success" ? "success" : "error",
        title: title || (type === "success" ? "Berhasil" : "Error"),
        text: message,
        timer: 3000,
        showConfirmButton: false,
      });
    } else {
      alert(message);
    }
  }

  /**
   * Reload DataTable jika ada
   */
  reloadDataTable() {
    // Try berbagai selector untuk DataTable
    const tableSelectors = [".my_datatable", ".datatable", 'table[id*="table"]', ".dataTable"];

    for (const selector of tableSelectors) {
      const $table = $(selector);
      if ($table.length && $.fn.DataTable && $.fn.DataTable.isDataTable($table[0])) {
        $table.DataTable().ajax.reload(null, false);
        break;
      }
    }
  }

  /**
   * Reset modal forms
   */
  resetModalForms(modal) {
    const $modal = $(modal);
    const $forms = $modal.find("form");

    $forms.each((index, form) => {
      const $form = $(form);

      // Reset form data
      form.reset();

      // Clear validation states
      this.clearValidationErrors($form);

      // Reset button states
      const $submitBtn = $form.find('button[type="submit"]');
      const originalText = $submitBtn.attr("data-original-text");
      if (originalText) {
        $submitBtn.html(originalText);
      }
      $submitBtn.prop("disabled", false).removeClass("btn-loading");
    });
  }

  /**
   * Cleanup modal after close
   */
  cleanupModal(modal) {
    const $modal = $(modal);

    // Remove any loading states
    $modal.find(".form-loading").removeClass("form-loading");
    $modal.find(".btn-loading").removeClass("btn-loading");

    // Reset forms
    this.resetModalForms(modal);

    // Clear any timers or intervals
    // (Implementation specific)
  }

  /**
   * Public method untuk manual form submission
   */
  submitForm(formSelector, options = {}) {
    const $form = $(formSelector);
    if ($form.length === 0) {
      console.error("Form not found:", formSelector);
      return false;
    }

    // Merge options dengan data attributes
    const config = { ...this.getFormConfig($form), ...options };

    // Trigger form submission
    return this.handleAjaxForm($form[0]);
  }
}

// Initialize global form handler
window.FormHandler = new FormHandler();

// Expose helper functions
window.submitForm = function (formSelector, options) {
  return window.FormHandler.submitForm(formSelector, options);
};

console.log("FormHandler initialized successfully");
