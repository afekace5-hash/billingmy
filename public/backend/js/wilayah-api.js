/**
 * Indonesian Regional API Utility
 * Backend proxy version using local API endpoints
 * Version: 3.1
 * Updated: July 2025
 */

class WilayahAPI {
  constructor() {
    this.cache = new Map();
    this.cacheTTL = 24 * 60 * 60 * 1000; // 24 hours in milliseconds

    // Backend proxy URLs (resolves CORS issues)
    this.baseUrl = window.location.origin + "/api/wilayah";

    // Request timeout
    this.timeout = 15000; // 15 seconds (increased for backend calls)
  }

  /**
   * Check if cached data is still valid
   */
  isCacheValid(cacheKey) {
    const cached = this.cache.get(cacheKey);
    if (!cached) return false;

    return Date.now() - cached.timestamp < this.cacheTTL;
  }

  /**
   * Get data from cache
   */
  getFromCache(cacheKey) {
    const cached = this.cache.get(cacheKey);
    return cached ? cached.data : null;
  }

  /**
   * Save data to cache
   */
  saveToCache(cacheKey, data) {
    this.cache.set(cacheKey, {
      data: data,
      timestamp: Date.now(),
    });
  }

  /**
   * Fetch with timeout support
   */
  async fetchWithTimeout(url, timeout = this.timeout, method = "GET") {
    const controller = new AbortController();
    const timeoutId = setTimeout(() => controller.abort(), timeout);

    try {
      // Get CSRF token if available
      const csrfToken = document.querySelector('meta[name="X-CSRF-TOKEN"]')?.getAttribute("content");

      const headers = {
        Accept: "application/json",
        "Content-Type": "application/json",
      };

      // Add CSRF token if available
      if (csrfToken) {
        headers["X-CSRF-TOKEN"] = csrfToken;
      }

      const options = {
        method: method,
        signal: controller.signal,
        headers: headers,
      };

      const response = await fetch(url, options);

      clearTimeout(timeoutId);

      if (!response.ok) {
        throw new Error(`HTTP ${response.status}: ${response.statusText}`);
      }

      return await response.json();
    } catch (error) {
      clearTimeout(timeoutId);
      if (error.name === "AbortError") {
        throw new Error("Request timeout");
      }
      throw error;
    }
  }

  /**
   * Get provinces
   */
  async getProvinces() {
    const cacheKey = "provinces";

    // Check cache first
    if (this.isCacheValid(cacheKey)) {
      console.log("Loading provinces from cache");
      return this.getFromCache(cacheKey);
    }

    try {
      console.log(`Fetching provinces from: ${this.baseUrl}/provinces`);
      const response = await this.fetchWithTimeout(`${this.baseUrl}/provinces`);

      // Check if response has success property (from backend)
      if (response.success === false) {
        throw new Error(response.message || "Backend error");
      }

      const data = response.data || response;
      console.log("Provinces loaded successfully:", data);

      // Validate data format
      if (!Array.isArray(data)) {
        throw new Error("Invalid data format: expected array");
      }

      // Save to cache
      this.saveToCache(cacheKey, data);
      return data;
    } catch (error) {
      console.error("Error fetching provinces:", error);
      throw new Error(`Gagal memuat data provinsi: ${error.message}`);
    }
  }

  /**
   * Get regencies/cities by province ID
   */
  async getRegencies(provinceId) {
    if (!provinceId) {
      throw new Error("Province ID is required");
    }

    const cacheKey = `regencies_${provinceId}`;

    // Check cache first
    if (this.isCacheValid(cacheKey)) {
      console.log(`Loading regencies for province ${provinceId} from cache`);
      return this.getFromCache(cacheKey);
    }

    try {
      console.log(`Fetching regencies from: ${this.baseUrl}/regencies/${provinceId}`);
      const response = await this.fetchWithTimeout(`${this.baseUrl}/regencies/${provinceId}`);

      // Check if response has success property (from backend)
      if (response.success === false) {
        throw new Error(response.message || "Backend error");
      }

      const data = response.data || response;
      console.log(`Regencies for province ${provinceId} loaded successfully:`, data);

      // Validate data format
      if (!Array.isArray(data)) {
        throw new Error("Invalid data format: expected array");
      }

      // Save to cache
      this.saveToCache(cacheKey, data);
      return data;
    } catch (error) {
      console.error(`Error fetching regencies for province ${provinceId}:`, error);
      throw new Error(`Gagal memuat data kabupaten/kota: ${error.message}`);
    }
  }

  /**
   * Get districts by regency ID
   */
  async getDistricts(regencyId) {
    if (!regencyId) {
      throw new Error("Regency ID is required");
    }

    const cacheKey = `districts_${regencyId}`;

    // Check cache first
    if (this.isCacheValid(cacheKey)) {
      console.log(`Loading districts for regency ${regencyId} from cache`);
      return this.getFromCache(cacheKey);
    }

    try {
      console.log(`Fetching districts from: ${this.baseUrl}/districts/${regencyId}`);
      const response = await this.fetchWithTimeout(`${this.baseUrl}/districts/${regencyId}`);

      // Check if response has success property (from backend)
      if (response.success === false) {
        throw new Error(response.message || "Backend error");
      }

      const data = response.data || response;
      console.log(`Districts for regency ${regencyId} loaded successfully:`, data);

      // Validate data format
      if (!Array.isArray(data)) {
        throw new Error("Invalid data format: expected array");
      }

      // Clean district names (backend should already do this, but ensure it)
      const cleanedData = data.map((district) => ({
        ...district,
        nama: SelectHelper.cleanRegionalName(district.nama, "district"),
      }));

      // Save to cache
      this.saveToCache(cacheKey, cleanedData);
      return cleanedData;
    } catch (error) {
      console.error(`Error fetching districts for regency ${regencyId}:`, error);
      throw new Error(`Gagal memuat data kecamatan: ${error.message}`);
    }
  }

  /**
   * Get villages by district ID
   */
  async getVillages(districtId) {
    if (!districtId) {
      throw new Error("District ID is required");
    }

    const cacheKey = `villages_${districtId}`;

    // Check cache first
    if (this.isCacheValid(cacheKey)) {
      console.log(`Loading villages for district ${districtId} from cache`);
      return this.getFromCache(cacheKey);
    }

    try {
      console.log(`Fetching villages from: ${this.baseUrl}/villages/${districtId}`);
      const response = await this.fetchWithTimeout(`${this.baseUrl}/villages/${districtId}`);

      // Check if response has success property (from backend)
      if (response.success === false) {
        throw new Error(response.message || "Backend error");
      }

      const data = response.data || response;
      console.log(`Villages for district ${districtId} loaded successfully:`, data);

      // Validate data format
      if (!Array.isArray(data)) {
        throw new Error("Invalid data format: expected array");
      }

      // Clean village names (backend should already do this, but ensure it)
      const cleanedData = data.map((village) => ({
        ...village,
        nama: SelectHelper.cleanRegionalName(village.nama, "village"),
      }));

      // Save to cache
      this.saveToCache(cacheKey, cleanedData);
      return cleanedData;
    } catch (error) {
      console.error(`Error fetching villages for district ${districtId}:`, error);
      throw new Error(`Gagal memuat data kelurahan/desa: ${error.message}`);
    }
  }

  /**
   * Clear all cached data (local cache only)
   */
  clearCache() {
    this.cache.clear();
    console.log("Local cache cleared");
  }

  /**
   * Clear backend cache via API
   */
  async clearBackendCache() {
    try {
      const response = await this.fetchWithTimeout(`${this.baseUrl}/clear-cache`, this.timeout, "POST");

      if (response.success === false) {
        throw new Error(response.message || "Backend error");
      }

      console.log("Backend cache cleared successfully");
      return response;
    } catch (error) {
      console.error("Error clearing backend cache:", error);
      throw new Error(`Gagal menghapus cache backend: ${error.message}`);
    }
  }

  /**
   * Clear all caches (local and backend)
   */
  async clearAllCaches() {
    // Clear local cache
    this.clearCache();

    // Clear backend cache
    await this.clearBackendCache();

    console.log("All caches cleared");
  }

  /**
   * Get cache statistics
   */
  getCacheStats() {
    return {
      size: this.cache.size,
      keys: Array.from(this.cache.keys()),
    };
  }
}

/**
 * Select Helper Class for managing dropdowns
 */
class SelectHelper {
  /**
   * Reset select with placeholder option
   */
  static resetSelect(select, placeholder) {
    if (!select) return;
    select.innerHTML = `<option value="">${placeholder}</option>`;
    select.disabled = false;
  }

  /**
   * Show loading state
   */
  static showLoadingState(select, text = "Memuat...") {
    if (!select) return;
    select.innerHTML = `<option value="">${text}</option>`;
    select.disabled = true;
  }

  /**
   * Hide loading state
   */
  static hideLoadingState(select) {
    if (!select) return;
    select.disabled = false;
  }

  /**
   * Show error state
   */
  static showErrorState(select, errorText = "Error memuat data") {
    if (!select) return;
    select.innerHTML = `<option value="">${errorText}</option>`;
    select.disabled = false;
  }

  /**
   * Clean regional name by removing common prefixes
   */
  static cleanRegionalName(name, type = "district") {
    if (!name || typeof name !== "string") return name;

    let cleanName = name.trim();

    if (type === "district") {
      // Remove common district prefixes
      const districtPrefixes = ["Kecamatan ", "Kec. ", "Kec ", "KEC. ", "KEC ", "Distrik ", "Dist. ", "Dist ", "DIST. ", "DIST "];

      for (const prefix of districtPrefixes) {
        if (cleanName.startsWith(prefix)) {
          cleanName = cleanName.substring(prefix.length);
          break;
        }
      }
    } else if (type === "village") {
      // Remove common village prefixes
      const villagePrefixes = ["Desa ", "Kelurahan ", "Kel. ", "Kel ", "KEL. ", "KEL ", "Dusun ", "Kampung ", "Kp. ", "Kp ", "KP. ", "KP ", "Nagari ", "Gampong ", "Mukim "];

      for (const prefix of villagePrefixes) {
        if (cleanName.startsWith(prefix)) {
          cleanName = cleanName.substring(prefix.length);
          break;
        }
      }
    }

    return cleanName;
  }
  /**
   * Populate select with data
   */
  static populateSelect(select, data, selectedValue = null, type = null) {
    if (!select) {
      console.error("Select element not found for populateSelect");
      return;
    }

    if (!Array.isArray(data)) {
      console.error("Data is not an array:", data);
      return;
    }

    console.log(`Populating select with ${data.length} items`, data);

    // Clear existing options except keep placeholder
    const currentPlaceholder = select.querySelector('option[value=""]');
    const placeholderText = currentPlaceholder ? currentPlaceholder.textContent : "Pilih...";

    select.innerHTML = "";

    // Re-add placeholder
    const placeholderOption = document.createElement("option");
    placeholderOption.value = "";
    placeholderOption.textContent = placeholderText;
    select.appendChild(placeholderOption);

    data.forEach((item) => {
      const option = document.createElement("option");
      option.value = item.id;

      // Get the name field (API uses 'nama' for Indonesian data)
      let displayName = item.nama || item.name || `ID: ${item.id}`;

      // Clean the name based on type
      if (type === "district" || type === "village") {
        displayName = SelectHelper.cleanRegionalName(displayName, type);
      }

      option.textContent = displayName;
      if (selectedValue && selectedValue == item.id) {
        option.selected = true;
      }
      select.appendChild(option);
    });

    console.log(`Select populated with ${select.children.length - 1} options (plus placeholder)`);
  }
}

// Create global instance
window.WilayahAPI = WilayahAPI;
window.SelectHelper = SelectHelper;

// Export for module systems
if (typeof module !== "undefined" && module.exports) {
  module.exports = { WilayahAPI, SelectHelper };
}
