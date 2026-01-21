# Landing Page DifiHome - Panduan Update Link

## 📁 File Structure

```
landing_page/
├── landing.html          # Homepage landing page
├── subscribe.html        # Form berlangganan
├── formregistrasi.html   # Form registrasi customer
├── config.js            # ⭐ File konfigurasi link (PENTING!)
└── assets/              # CSS, JS, images
```

## 🎯 Cara Update Link ke Billing Application

### Metode 1: Menggunakan Data Attributes (RECOMMENDED)

Tambahkan atribut `data-action` pada button/link:

```html
<!-- Login Button -->
<a href="#" data-action="login" class="btn btn-primary"> Login </a>

<!-- Register/Daftar Button -->
<a href="#" data-action="register" class="btn btn-success"> Daftar Sekarang </a>

<!-- Customer Portal -->
<a href="#" data-action="customer-portal" class="btn btn-info"> Customer Portal </a>

<!-- Admin Dashboard -->
<a href="#" data-action="admin-dashboard" class="btn btn-warning"> Admin Dashboard </a>
```

Link akan otomatis diupdate oleh `config.js` saat halaman dimuat!

### Metode 2: Menggunakan JavaScript Function

```html
<!-- Button dengan onclick -->
<button onclick="DifiHome.redirectToBilling('/register')" class="btn btn-primary">Daftar</button>

<!-- Link dengan onclick -->
<a href="#" onclick="DifiHome.redirectToBilling('/customer/dashboard'); return false;"> Portal Customer </a>

<!-- Open in new tab -->
<a href="#" onclick="DifiHome.openBillingInNewTab('/packages'); return false;"> Lihat Paket (New Tab) </a>
```

### Metode 3: Hard-Code URL (Tidak Disarankan)

```html
<!-- Development -->
<a href="http://billing.difihome.my.id" class="btn btn-primary">Login</a>

<!-- Production (dengan SSL) -->
<a href="https://billing.difihome.my.id" class="btn btn-primary">Login</a>
```

⚠️ **Catatan**: Metode ini harus diubah manual saat pindah environment!

---

## 📋 Form yang Submit ke Billing API

### Contoh Form Registrasi

```html
<form data-billing-form="/api/customer/register" data-submit-text="Daftar" id="registerForm">
  <input type="text" name="nama_pelanggan" placeholder="Nama Lengkap" required />
  <input type="email" name="email" placeholder="Email" required />
  <input type="tel" name="telepphone" placeholder="No. HP" required />
  <input type="password" name="password" placeholder="Password" required />
  <textarea name="address" placeholder="Alamat" required></textarea>

  <button type="submit" class="btn btn-primary">Daftar Sekarang</button>
</form>
```

Form akan otomatis submit ke billing API!

---

## 🛠️ Functions yang Tersedia dari config.js

### Redirect Functions

```javascript
// Redirect ke halaman billing
DifiHome.redirectToBilling("/register");

// Open di new tab
DifiHome.openBillingInNewTab("/packages");
```

### API Functions

```javascript
// Fetch data dari billing API
const packages = await DifiHome.fetchFromBillingAPI("/api/packages/list");

// Submit form data
const formData = new FormData(document.getElementById("myForm"));
const result = await DifiHome.submitFormToBilling("/api/customer/register", formData);

// Check coverage
const coverage = await DifiHome.checkCoverage("Jl. Merdeka No. 123");
```

### Display Functions

```javascript
// Load dan display packages
DifiHome.loadPackages("packages-container");

// Format currency
const formatted = DifiHome.formatCurrency(150000); // "150.000"
```

---

## 🔧 Konfigurasi Custom

Edit file `config.js` untuk mengubah URL:

```javascript
const DIFIHOME_CONFIG = {
  LANDING_URL: "https://difihome.my.id",
  BILLING_URL: "https://billing.difihome.my.id", // ← Ubah disini

  API: {
    REGISTER: "/api/customer/register",
    PACKAGES: "/api/packages/list",
    // ... tambah endpoint lain
  },

  ROUTES: {
    LOGIN: "/",
    REGISTER: "/register",
    // ... tambah route lain
  },
};
```

---

## 📖 Contoh Implementasi di landing.html

### Hero Section dengan CTA Button

```html
<section class="hero-section">
  <div class="container">
    <h1>Internet Super Cepat & Murah</h1>
    <p>Mulai dari Rp 50.000/bulan</p>

    <div class="cta-buttons">
      <!-- Metode 1: Data Attribute (Recommended) -->
      <a href="#" data-action="register" class="btn btn-lg btn-primary"> <i class="ri-user-add-line"></i> Daftar Sekarang </a>

      <!-- Metode 2: JavaScript Function -->
      <button onclick="DifiHome.redirectToBilling('/packages')" class="btn btn-lg btn-outline-primary"><i class="ri-list-check"></i> Lihat Paket</button>
    </div>
  </div>
</section>
```

### Packages Section

```html
<section id="packages" class="packages-section">
  <div class="container">
    <h2>Pilih Paket Internet</h2>

    <!-- Container untuk display packages dari API -->
    <div id="packages-container" class="packages-grid">
      <!-- Packages akan dimuat otomatis dari billing API -->
    </div>
  </div>
</section>

<script>
  // Load packages saat halaman dimuat
  document.addEventListener("DOMContentLoaded", function () {
    DifiHome.loadPackages("packages-container");
  });
</script>
```

### Navigation Menu

```html
<nav class="main-nav">
  <ul>
    <li><a href="#home">Home</a></li>
    <li><a href="#packages">Paket</a></li>
    <li><a href="#about">Tentang Kami</a></li>
    <li><a href="#contact">Kontak</a></li>
    <li>
      <!-- Login button -->
      <a href="#" data-action="login" class="btn btn-sm btn-primary"> Login </a>
    </li>
    <li>
      <!-- Register button -->
      <a href="#" data-action="register" class="btn btn-sm btn-success"> Daftar </a>
    </li>
  </ul>
</nav>
```

### Footer

```html
<footer>
  <div class="container">
    <div class="row">
      <div class="col-md-4">
        <h4>DifiHome</h4>
        <p>Penyedia layanan internet terpercaya</p>
      </div>
      <div class="col-md-4">
        <h4>Quick Links</h4>
        <ul>
          <li><a href="#" data-action="login">Login</a></li>
          <li><a href="#" data-action="register">Daftar</a></li>
          <li><a href="#" data-action="customer-portal">Customer Portal</a></li>
          <li><a href="#packages">Paket Internet</a></li>
        </ul>
      </div>
      <div class="col-md-4">
        <h4>Kontak</h4>
        <p>Email: info@difihome.my.id</p>
        <p>WhatsApp: 0851-8311-2127</p>
      </div>
    </div>
  </div>
</footer>
```

---

## ✅ Testing Checklist

### Local Testing

- [ ] Buka `http://difihome.my.id` di browser
- [ ] Klik tombol "Login" → redirect ke `http://billing.difihome.my.id`
- [ ] Klik tombol "Daftar" → redirect ke `http://billing.difihome.my.id/register`
- [ ] Check browser console - tidak ada error JavaScript
- [ ] Form submit berfungsi dengan baik

### Production Testing

- [ ] Landing page bisa diakses di `https://difihome.my.id`
- [ ] Billing app bisa diakses di `https://billing.difihome.my.id`
- [ ] SSL certificate valid (hijau di browser)
- [ ] Semua link redirect dengan benar
- [ ] API calls dari landing ke billing berfungsi
- [ ] No CORS errors

---

## 🚨 Common Issues & Solutions

### 1. Link tidak redirect

**Solusi**:

- Pastikan `config.js` sudah di-include di HTML
- Check browser console untuk error
- Pastikan billing application sudah running

### 2. CORS Error saat API call

**Solusi**:

- Update `app/Config/Filters.php` di billing app
- Enable CORS headers untuk difihome.my.id

### 3. Form submit tidak berhasil

**Solusi**:

- Check network tab di browser DevTools
- Pastikan endpoint API benar
- Check error response dari server

### 4. CSS/JS tidak load

**Solusi**:

- Gunakan relative path untuk assets: `assets/css/style.css`
- Atau absolute path: `https://difihome.my.id/assets/css/style.css`

---

## 📚 Resources

- **Main Guide**: `SETUP_SUBDOMAIN_GUIDE.md`
- **Virtual Host Config**: `apache-vhost-difihome.conf`
- **Setup Script**: `setup-subdomain.ps1`
- **Config File**: `config.js`

---

## 🆘 Need Help?

Jika ada masalah:

1. Baca `SETUP_SUBDOMAIN_GUIDE.md` untuk detail lengkap
2. Check log files di `writable/logs/`
3. Enable debug mode di browser (F12 → Console)
4. Test dengan curl: `curl -I http://difihome.my.id`
