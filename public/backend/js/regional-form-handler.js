/**
 * Regional Form Handler
 * Handles province, city, district, village dropdowns with enhanced API support
 * Requires: wilayah-api.js
 * Version: 2.0
 * Updated: July 2025
 */

class RegionalFormHandler {
  constructor(options = {}) {
    // Default options
    this.options = {
      provinceSelectId: "province",
      citySelectId: "city",
      districtSelectId: "district",
      villageSelectId: "village",
      loadingIndicators: {
        province: "pv-load",
        city: "ct-load",
        district: "dt-load",
        village: "vl-load",
      },
      autoLoad: true,
      selectedValues: {
        province: null,
        city: null,
        district: null,
        village: null,
      },
      ...options,
    };

    // Initialize API and elements
    this.api = new WilayahAPI();
    this.initializeElements();

    if (this.options.autoLoad) {
      this.loadProvinces();
    }

    this.setupEventListeners();
  }

  /**
   * Initialize DOM elements
   */
  initializeElements() {
    this.elements = {
      province: document.getElementById(this.options.provinceSelectId),
      city: document.getElementById(this.options.citySelectId),
      district: document.getElementById(this.options.districtSelectId),
      village: document.getElementById(this.options.villageSelectId),
    };

    // Validate required elements
    if (!this.elements.province) {
      console.error(`Province select element with ID '${this.options.provinceSelectId}' not found`);
    }
  }

  /**
   * Show loading indicator
   */
  showLoading(type) {
    const loaderId = this.options.loadingIndicators[type];
    if (loaderId) {
      const loader = document.getElementById(loaderId);
      if (loader) {
        loader.style.display = "block";
      }
    }
  }

  /**
   * Hide loading indicator
   */
  hideLoading(type) {
    const loaderId = this.options.loadingIndicators[type];
    if (loaderId) {
      const loader = document.getElementById(loaderId);
      if (loader) {
        loader.style.display = "none";
      }
    }
  }

  /**
   * Setup event listeners for cascading selects
   */
  setupEventListeners() {
    if (this.elements.province) {
      this.elements.province.addEventListener("change", (e) => {
        this.loadCities(e.target.value);
      });
    }

    if (this.elements.city) {
      this.elements.city.addEventListener("change", (e) => {
        this.loadDistricts(e.target.value);
      });
    }

    if (this.elements.district) {
      this.elements.district.addEventListener("change", (e) => {
        this.loadVillages(e.target.value);
      });
    }
  }

  /**
   * Load provinces
   */
  async loadProvinces() {
    if (!this.elements.province) return;

    try {
      this.showLoading("province");
      SelectHelper.showLoadingState(this.elements.province, "Memuat provinsi...");

      console.log("Starting to load provinces...");
      const provinces = await this.api.getProvinces();
      console.log("Provinces loaded, count:", provinces.length);

      SelectHelper.resetSelect(this.elements.province, "Pilih Provinsi");
      SelectHelper.populateSelect(this.elements.province, provinces, this.options.selectedValues.province, "province");

      this.hideLoading("province");

      // Auto-load cities if province is pre-selected
      if (this.options.selectedValues.province) {
        await this.loadCities(this.options.selectedValues.province);
      }
    } catch (error) {
      console.error("Error loading provinces:", error);
      SelectHelper.showErrorState(this.elements.province, "Error memuat provinsi");
      this.hideLoading("province");
      this.showUserError("Gagal memuat data provinsi", error.message);
    }
  }

  /**
   * Load cities/regencies
   */
  async loadCities(provinceId) {
    // Reset dependent selects
    if (this.elements.city) {
      SelectHelper.resetSelect(this.elements.city, "Pilih Kota/Kabupaten");
    }
    if (this.elements.district) {
      SelectHelper.resetSelect(this.elements.district, "Pilih Kecamatan");
    }
    if (this.elements.village) {
      SelectHelper.resetSelect(this.elements.village, "Pilih Desa");
    }

    if (!provinceId || !this.elements.city) return;

    try {
      this.showLoading("city");
      SelectHelper.showLoadingState(this.elements.city, "Memuat kota/kabupaten...");

      const cities = await this.api.getRegencies(provinceId);

      SelectHelper.resetSelect(this.elements.city, "Pilih Kota/Kabupaten");
      SelectHelper.populateSelect(this.elements.city, cities, this.options.selectedValues.city, "city");

      this.hideLoading("city");

      // Auto-load districts if city is pre-selected
      if (this.options.selectedValues.city) {
        await this.loadDistricts(this.options.selectedValues.city);
      }
    } catch (error) {
      console.error("Error loading cities:", error);
      SelectHelper.showErrorState(this.elements.city, "Error memuat kota");
      this.hideLoading("city");
    }
  }

  /**
   * Load districts
   */
  async loadDistricts(cityId) {
    // Reset dependent selects
    if (this.elements.district) {
      SelectHelper.resetSelect(this.elements.district, "Pilih Kecamatan");
    }
    if (this.elements.village) {
      SelectHelper.resetSelect(this.elements.village, "Pilih Desa");
    }

    if (!cityId || !this.elements.district) return;

    try {
      this.showLoading("district");
      SelectHelper.showLoadingState(this.elements.district, "Memuat kecamatan...");

      const districts = await this.api.getDistricts(cityId);

      SelectHelper.resetSelect(this.elements.district, "Pilih Kecamatan");
      SelectHelper.populateSelect(this.elements.district, districts, this.options.selectedValues.district, "district");

      this.hideLoading("district");

      // Auto-load villages if district is pre-selected
      if (this.options.selectedValues.district) {
        await this.loadVillages(this.options.selectedValues.district);
      }
    } catch (error) {
      console.error("Error loading districts:", error);
      SelectHelper.showErrorState(this.elements.district, "Error memuat kecamatan");
      this.hideLoading("district");
    }
  }

  /**
   * Load villages
   */
  async loadVillages(districtId) {
    // Reset village select
    if (this.elements.village) {
      SelectHelper.resetSelect(this.elements.village, "Pilih Desa");
    }

    if (!districtId || !this.elements.village) return;

    try {
      this.showLoading("village");
      SelectHelper.showLoadingState(this.elements.village, "Memuat desa/kelurahan...");

      const villages = await this.api.getVillages(districtId);

      SelectHelper.resetSelect(this.elements.village, "Pilih Desa");
      SelectHelper.populateSelect(this.elements.village, villages, this.options.selectedValues.village, "village");

      this.hideLoading("village");
    } catch (error) {
      console.error("Error loading villages:", error);
      SelectHelper.showErrorState(this.elements.village, "Error memuat desa");
      this.hideLoading("village");
    }
  }

  /**
   * Show user-friendly error message
   */
  showUserError(title, message) {
    // Use SweetAlert if available, otherwise use alert
    if (typeof Swal !== "undefined") {
      Swal.fire({
        title: title,
        text: message,
        icon: "error",
        confirmButtonText: "OK",
      });
    } else {
      alert(`${title}: ${message}`);
    }
  }

  /**
   * Set selected values and trigger cascade loading
   */
  async setSelectedValues(values) {
    this.options.selectedValues = { ...this.options.selectedValues, ...values };

    // Reload provinces with new selected values
    await this.loadProvinces();
  }

  /**
   * Get current selected values
   */
  getSelectedValues() {
    return {
      province: this.elements.province?.value || "",
      city: this.elements.city?.value || "",
      district: this.elements.district?.value || "",
      village: this.elements.village?.value || "",
    };
  }

  /**
   * Clear all selections
   */
  clearSelections() {
    SelectHelper.resetSelect(this.elements.province, "Pilih Provinsi");
    SelectHelper.resetSelect(this.elements.city, "Pilih Kota/Kabupaten");
    SelectHelper.resetSelect(this.elements.district, "Pilih Kecamatan");
    SelectHelper.resetSelect(this.elements.village, "Pilih Desa");
  }

  /**
   * Validate that required fields are selected
   */
  validate(requiredFields = ["province", "city", "district"]) {
    const errors = [];
    const values = this.getSelectedValues();

    requiredFields.forEach((field) => {
      if (!values[field]) {
        const fieldNames = {
          province: "Provinsi",
          city: "Kota/Kabupaten",
          district: "Kecamatan",
          village: "Desa",
        };
        errors.push(`${fieldNames[field]} harus dipilih`);
      }
    });

    return {
      isValid: errors.length === 0,
      errors: errors,
    };
  }

  /**
   * Clear cache and reload
   */
  async refresh() {
    this.api.clearCache();
    await this.loadProvinces();
  }

  /**
   * Get API cache statistics
   */
  getCacheStats() {
    return this.api.getCacheStats();
  }
}

// Make globally available
window.RegionalFormHandler = RegionalFormHandler;
