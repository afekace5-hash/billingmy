# 📚 Dokumentasi Lengkap Sistem Billing Kimonet

**Versi:** 1.0  
**Tanggal:** 2 Januari 2026  
**Framework:** CodeIgniter 4.6.3

---

## 📋 Daftar Isi

1. [Pengenalan Sistem](#pengenalan-sistem)
2. [Instalasi & Konfigurasi](#instalasi--konfigurasi)
3. [Fitur Utama](#fitur-utama)
4. [Integrasi Payment Gateway](#integrasi-payment-gateway)
5. [Sistem Auto Generate Tagihan](#sistem-auto-generate-tagihan)
6. [Sistem Auto Isolir](#sistem-auto-isolir)
7. [Notifikasi WhatsApp](#notifikasi-whatsapp)
8. [Customer Dashboard](#customer-dashboard)
9. [Landing Page (Domain Terpisah)](#landing-page-domain-terpisah)
10. [Setup CRON Job](#setup-cron-job)
11. [API Documentation](#api-documentation)
12. [Troubleshooting](#troubleshooting)

---

## 🎯 Pengenalan Sistem

Sistem Billing Kimonet adalah aplikasi manajemen billing internet yang lengkap dengan fitur:

- **Manajemen Customer**: Pendaftaran, pengelolaan data pelanggan, dan paket layanan
- **Manajemen Tagihan**: Generate tagihan otomatis, prorate, dan tracking pembayaran
- **Payment Gateway**: Integrasi Flip, Midtrans, Duitku
- **Auto Isolir**: Isolasi otomatis pelanggan yang telat bayar via MikroTik
- **Notifikasi WhatsApp**: Reminder otomatis dan konfirmasi pembayaran
- **Customer Portal**: Dashboard pelanggan untuk cek tagihan dan bayar online
- **Public Billing**: Link pembayaran publik tanpa login

---

## 🛠️ Instalasi & Konfigurasi

### Persyaratan Sistem

- PHP 8.1 atau lebih tinggi
- MySQL 5.7+ atau MariaDB 10.3+
- Composer
- Extension PHP: intl, json, mbstring, mysqlnd, xml, curl

### Langkah Instalasi

1. **Clone Repository**

   ```bash
   git clone [repository-url]
   cd billingkimo
   ```

2. **Install Dependencies**

   ```bash
   composer install
   ```

3. **Konfigurasi Database**

   ```bash
   cp env.example .env
   ```

   Edit file `.env`:

   ```ini
   database.default.hostname = localhost
   database.default.database = your_database
   database.default.username = your_username
   database.default.password = your_password
   database.default.DBDriver = MySQLi
   database.default.port = 3306
   ```

4. **Run Migrasi Database**

   ```bash
   php spark migrate
   ```

5. **Seed Data (Optional)**

   ```bash
   php spark db:seed LokasiServerSeeder
   ```

6. **Set Permissions** (Linux/Mac)

   ```bash
   chmod -R 777 writable/
   ```

7. **Generate App Key**
   ```bash
   php spark key:generate
   ```

### Konfigurasi Base URL

Edit `app/Config/App.php`:

```php
public string $baseURL = 'http://localhost:8080/';
```

---

## ⚡ Fitur Utama

### 1. Dashboard Admin

- Statistik pelanggan aktif/non-aktif
- Total tagihan dan pembayaran
- Grafik pendapatan bulanan
- Quick actions untuk tugas harian

### 2. Manajemen Customer

- CRUD pelanggan lengkap
- Import data dari Excel
- Export data ke Excel/PDF
- Filter dan pencarian advanced
- History pembayaran per customer

### 3. Manajemen Paket

- Paket internet dengan bandwidth
- Harga dan durasi
- Profile MikroTik terintegrasi

### 4. Manajemen Invoice

- Generate tagihan manual/otomatis
- Sistem prorate untuk pelanggan baru
- Tracking status pembayaran
- Export invoice ke PDF
- Send invoice via WhatsApp

### 5. Manajemen Transaksi

- Record pembayaran manual
- History transaksi lengkap
- Laporan keuangan
- Reconciliation payment gateway

---

## 💳 Integrasi Payment Gateway

### Flip Integration

#### Setup Flip API

1. Buka `app/Config/PaymentGateway.php`
2. Tambahkan konfigurasi Flip:
   ```php
   public $flip = [
       'secret_key' => 'your-flip-secret-key',
       'validation_token' => 'your-validation-token',
       'environment' => 'production', // atau 'sandbox'
       'callback_url' => 'http://yoursite.com/payment/callback/flip'
   ];
   ```

#### Cara Mendapatkan API Key Flip

1. Login ke Dashboard Flip: https://flip.id
2. Buka menu **Settings** → **API Keys**
3. Copy **Secret Key** dan **Validation Token**
4. Setup callback URL di dashboard Flip

#### Fitur Flip yang Tersedia

- ✅ Virtual Account (BCA, BNI, BRI, Mandiri, Permata, CIMB, Danamon)
- ✅ E-Wallet (QRIS, LinkAja, Dana, OVO)
- ✅ Retail (Alfamart, Indomaret)
- ✅ Callback otomatis saat pembayaran sukses
- ✅ Auto generate invoice bulan depan
- ✅ Auto WhatsApp notification

#### Test Payment Flip (Sandbox)

```bash
# Environment: sandbox
# Gunakan test credentials dari dashboard Flip
```

**Test Virtual Account Numbers:**

- BCA: `12345678` (langsung success)
- BNI: `12345679` (pending → success)
- Mandiri: `12345680` (langsung expired)

### Midtrans & Duitku

Konfigurasi serupa dengan Flip, edit di `app/Config/PaymentGateway.php`

---

## 🔄 Sistem Auto Generate Tagihan

### Cara Kerja

1. System otomatis generate invoice setiap tanggal 1 bulan berikutnya
2. Berdasarkan data customer yang aktif
3. Paket dan harga sesuai dengan data customer
4. Support sistem prorate untuk customer baru

### Setup Auto Generate

#### Windows (Task Scheduler)

1. Buat file `generate-invoice.bat`:

   ```batch
   @echo off
   cd C:\laragon\www\billingkimo
   php spark invoice:generate
   ```

2. Buka **Task Scheduler**
3. Create Basic Task:
   - Name: Auto Generate Invoice
   - Trigger: Monthly, day 1, time 00:01
   - Action: Start Program → Browse ke `generate-invoice.bat`

#### Linux/Mac (CRON)

Edit crontab:

```bash
crontab -e
```

Tambahkan:

```bash
# Generate invoice setiap tanggal 1 jam 00:01
1 0 1 * * cd /path/to/billingkimo && php spark invoice:generate >> /path/to/billingkimo/writable/logs/auto-generate.log 2>&1
```

### Command Generate Invoice

```bash
# Generate invoice untuk periode tertentu
php spark invoice:generate --period=2026-01

# Generate invoice untuk semua customer
php spark invoice:generate

# Generate dengan notifikasi WhatsApp
php spark invoice:generate --notify
```

### Sistem Prorate

Untuk pelanggan baru yang berlangganan di tengah bulan:

**Formula:**

```
Tarif Prorate = (Tarif Paket / Jumlah Hari dalam Bulan) × Sisa Hari
```

**Contoh:**

- Paket: Rp 300,000/bulan
- Daftar: 15 Januari 2026
- Hari dalam bulan: 31
- Sisa hari: 17 (termasuk tanggal 15)
- Prorate: (300,000 / 31) × 17 = Rp 164,516

**Generate Prorate:**

```bash
php spark invoice:prorate
```

---

## 🚫 Sistem Auto Isolir

### Cara Kerja Auto Isolir

1. Cek customer dengan tagihan overdue (lewat tanggal tempo)
2. Ambil data MikroTik dari tabel `lokasi_server`
3. Ubah profile customer ke profile isolir
4. Set bandwidth limit (misal: 512k/512k)
5. Record isolir log
6. Send WhatsApp notification

### Setup Auto Isolir

#### Konfigurasi MikroTik

1. Buat profile isolir di MikroTik:

   - Name: `ISOLIR`
   - Rate Limit: `512k/512k`

2. Pastikan API MikroTik aktif:

   ```
   /ip service
   set api disabled=no port=8728
   ```

3. Buat user API:
   ```
   /user add name=billing password=your-password group=full
   ```

#### Konfigurasi Database

Tabel `lokasi_server` harus berisi:

```sql
id_lokasi, name, ip_address, username, password, port, isolir_profile
```

#### Setup CRON Auto Isolir

**Windows:**

```batch
@echo off
cd C:\laragon\www\billingkimo
php spark isolir:auto
```

Schedule di Task Scheduler: Daily jam 08:00

**Linux:**

```bash
# Auto isolir setiap hari jam 08:00
0 8 * * * cd /path/to/billingkimo && php spark isolir:auto >> /path/to/billingkimo/writable/logs/auto-isolir.log 2>&1
```

### Command Manual Isolir

```bash
# Isolir semua customer overdue
php spark isolir:auto

# Isolir customer tertentu
php spark isolir:customer --id=123

# Restore customer dari isolir
php spark isolir:restore --id=123
```

### Setup Auto Un-Isolir

Otomatis terjadi saat:

1. Customer bayar via payment gateway (callback)
2. Admin input pembayaran manual
3. Profile otomatis dikembalikan ke normal

---

## 📱 Notifikasi WhatsApp

### Setup WhatsApp API

Sistem menggunakan WhatsApp API: `wazero.difihome.my.id`

#### Konfigurasi

1. Buka menu **WhatsApp → Settings**
2. Isi API Configuration:

   - **Device ID**: Nomor device Anda
   - **API Key**: Your API key
   - **API URL**: `https://wazero.difihome.my.id`

3. Generate QR Code untuk koneksi WhatsApp

#### Fitur Notifikasi WhatsApp

1. **Reminder Tagihan**

   - H-7 sebelum jatuh tempo
   - H-3 sebelum jatuh tempo
   - H-1 sebelum jatuh tempo
   - Hari H jatuh tempo

2. **Notifikasi Isolir**

   - Saat customer diisolir
   - Peringatan akan diisolir

3. **Konfirmasi Pembayaran**

   - Otomatis kirim saat pembayaran sukses
   - Include receipt/struk pembayaran

4. **Invoice Bulanan**
   - Send invoice baru setiap bulan
   - Include link pembayaran

### Setup CRON WhatsApp Notification

```bash
# Send reminder daily jam 09:00
0 9 * * * cd /path/to/billingkimo && php spark whatsapp:reminder >> /path/to/logs/whatsapp.log 2>&1
```

### Template Pesan WhatsApp

Edit di `app/Views/whatsapp/templates/`:

- `reminder.php` - Template reminder tagihan
- `payment_confirmation.php` - Template konfirmasi bayar
- `isolir_warning.php` - Template peringatan isolir

**Contoh Template:**

```php
Halo *{nama_pelanggan}*,

Tagihan internet bulan {periode}:
📋 No. Invoice: {invoice_no}
💰 Tagihan: Rp {total}
📅 Jatuh Tempo: {due_date}

Bayar sekarang:
{payment_link}

Terima kasih! 🙏
```

---

## 👤 Customer Dashboard

### Akses Customer Portal

URL: `http://yoursite.com/customer-portal`

**Login:**

- Username: Nomor Layanan
- Password: Default (set saat registrasi)

### Fitur Customer Dashboard

1. **Dashboard Overview**

   - Total tagihan bulan ini
   - Status pembayaran
   - History pembayaran 6 bulan terakhir

2. **Lihat Invoice**

   - List invoice per bulan
   - Download PDF invoice
   - Filter by status

3. **Bayar Online**

   - Pilih invoice yang mau dibayar
   - Pilih metode pembayaran (Flip/Midtrans/Duitku)
   - Get payment code/VA number
   - Auto update saat bayar sukses

4. **Profile**
   - Update data pribadi
   - Ubah password
   - Update nomor telepon

### Setup Customer Subdomain (Optional)

Untuk memberikan URL khusus per customer: `{nomor_layanan}.yourdomain.com`

#### Setup di cPanel

1. Buat wildcard subdomain: `*.yourdomain.com` → `/public_html/customer_dashboard`
2. Edit `.htaccess`:
   ```apache
   RewriteEngine On
   RewriteCond %{HTTP_HOST} ^(.+)\.yourdomain\.com$ [NC]
   RewriteRule ^(.*)$ index.php?customer=%1 [QSA,L]
   ```

#### Setup di Nginx

```nginx
server {
    server_name ~^(?<customer>.+)\.yourdomain\.com$;
    root /var/www/billingkimo/customer_dashboard;

    location / {
        try_files $uri $uri/ /index.php?customer=$customer&$query_string;
    }
}
```

---

## 🌐 Landing Page (Domain Terpisah)

### Overview

Landing page sistem billing ini di-upload terpisah dengan domain sendiri menggunakan **difihome.my.id** untuk keperluan marketing dan informasi publik.

### Struktur Folder

Folder `public/landing_page` berisi:

- `index.html` - Homepage landing page
- `assets/` - CSS, JS, images, icon fonts
- `css/` - Bootstrap, custom styles
- `js/` - Scripts dan interaktivity
- `images/` - Logo, banner, illustrations

### Upload ke Domain Terpisah

#### Menggunakan difihome.my.id

1. **Akses Panel difihome.my.id**

   - Login ke dashboard difihome.my.id
   - Pilih menu "My Websites" atau "Hosting"

2. **Upload Files**

   ```bash
   # Compress folder terlebih dahulu
   cd public/landing_page
   zip -r landing_page.zip *
   ```

3. **Upload via File Manager**

   - Buka File Manager di panel difihome.my.id
   - Upload `landing_page.zip`
   - Extract di root directory (public_html)

4. **Setup Domain**
   - Akses: `https://yourdomain.difihome.my.id`
   - Atau custom domain jika sudah pointing

#### Setup Custom Domain (Optional)

Jika menggunakan domain sendiri yang di-pointing ke difihome.my.id:

1. **DNS Configuration**

   ```
   Type: A Record
   Name: @ atau www
   Value: [IP difihome.my.id]
   TTL: 3600
   ```

2. **Update BaseURL di Landing Page**

   Edit file `landing_page/index.html` dan update semua link:

   ```html
   <!-- Ganti semua relative path ke absolute URL -->
   <link href="https://yourdomain.difihome.my.id/assets/css/style.css">
   <script src="https://yourdomain.difihome.my.id/assets/js/main.js">
   ```

3. **SSL Certificate**
   - Aktifkan SSL Let's Encrypt di panel difihome.my.id
   - Redirect HTTP ke HTTPS

#### Menghubungkan ke Sistem Billing

Update link registrasi di landing page untuk mengarah ke sistem billing:

```html
<!-- Tombol Daftar / Registrasi -->
<a href="https://billing.yourdomain.com/register" class="btn btn-primary"> Daftar Sekarang </a>

<!-- Link Cek Tagihan -->
<a href="https://billing.yourdomain.com/customer-portal" class="btn btn-outline"> Customer Portal </a>

<!-- Link Bayar Tagihan -->
<a href="https://billing.yourdomain.com/billing/{nomor_layanan}" class="btn btn-success"> Bayar Tagihan </a>
```

### Maintenance Landing Page

**Update Konten:**

1. Edit `index.html` untuk update text, pricing, promo
2. Update gambar di folder `assets/images/`
3. Modify CSS di `assets/css/style.css`
4. Upload ulang file yang diubah

**Best Practices:**

- Backup file sebelum update
- Test di local terlebih dahulu
- Optimize images (compress) untuk loading cepat
- Use CDN untuk library (Bootstrap, jQuery)
- Monitor loading speed dengan GTmetrix/PageSpeed

### Integration dengan Sistem Billing

Landing page bisa mengakses API billing untuk:

**1. Cek Status Jaringan:**

```javascript
fetch("https://billing.yourdomain.com/api/network-status")
  .then((response) => response.json())
  .then((data) => {
    document.getElementById("status").innerHTML = data.status;
  });
```

**2. Form Registrasi:**

```html
<form action="https://billing.yourdomain.com/api/register" method="POST">
  <input type="text" name="nama" placeholder="Nama Lengkap" />
  <input type="email" name="email" placeholder="Email" />
  <input type="tel" name="telepon" placeholder="No. Telepon" />
  <button type="submit">Daftar</button>
</form>
```

**3. Cek Tagihan Publik:**

```html
<form action="https://billing.yourdomain.com/billing" method="GET">
  <input type="text" name="nomor_layanan" placeholder="Nomor Layanan" />
  <button type="submit">Cek Tagihan</button>
</form>
```

---

## ⏰ Setup CRON Job (Hosting)

### File CRON untuk Hosting

Buat file `cron.php` di root:

```php
<?php
// Set execution time limit
set_time_limit(300);

// Define path
define('ROOTPATH', __DIR__);
require ROOTPATH . '/vendor/autoload.php';

// Bootstrap CodeIgniter
require_once ROOTPATH . '/app/Config/Paths.php';
$paths = new Config\Paths();

// Get command from query string
$command = $_GET['command'] ?? '';

switch($command) {
    case 'generate-invoice':
        shell_exec('cd ' . ROOTPATH . ' && php spark invoice:generate');
        echo "Invoice generated\n";
        break;

    case 'auto-isolir':
        shell_exec('cd ' . ROOTPATH . ' && php spark isolir:auto');
        echo "Auto isolir executed\n";
        break;

    case 'whatsapp-reminder':
        shell_exec('cd ' . ROOTPATH . ' && php spark whatsapp:reminder');
        echo "WhatsApp reminder sent\n";
        break;

    default:
        echo "Invalid command\n";
}
```

### Setup di cPanel CRON

1. Login cPanel → **Cron Jobs**
2. Tambahkan CRON:

**Generate Invoice (Monthly - 1st day 00:01):**

```bash
1 0 1 * * /usr/bin/php /home/username/public_html/cron.php?command=generate-invoice
```

**Auto Isolir (Daily 08:00):**

```bash
0 8 * * * /usr/bin/php /home/username/public_html/cron.php?command=auto-isolir
```

**WhatsApp Reminder (Daily 09:00):**

```bash
0 9 * * * /usr/bin/php /home/username/public_html/cron.php?command=whatsapp-reminder
```

### Setup dengan Wget (Alternative)

```bash
# Generate Invoice
1 0 1 * * wget -q -O /dev/null https://yoursite.com/cron.php?command=generate-invoice

# Auto Isolir
0 8 * * * wget -q -O /dev/null https://yoursite.com/cron.php?command=auto-isolir

# WhatsApp Reminder
0 9 * * * wget -q -O /dev/null https://yoursite.com/cron.php?command=whatsapp-reminder
```

---

## 📡 API Documentation

### Public Billing API

#### Get Payment Methods

```http
GET /api/payment-methods?gateway=flip
```

**Response:**

```json
{
  "success": true,
  "data": [
    {
      "code": "bni_va",
      "name": "BNI Virtual Account",
      "type": "virtual_account",
      "fee": 0
    }
  ]
}
```

#### Check Billing

```http
POST /billing/check
Content-Type: application/json

{
  "customer_number": "CUST001"
}
```

**Response:**

```json
{
  "success": true,
  "customer": {
    "nomor_layanan": "CUST001",
    "nama_pelanggan": "John Doe",
    "telepphone": "081234567890"
  },
  "invoices": [
    {
      "invoice_no": "INV-001",
      "periode": "2026-01",
      "bill": 300000,
      "status": "pending"
    }
  ]
}
```

#### Create Payment

```http
POST /billing/pay
Content-Type: application/json

{
  "invoice_id": 123,
  "gateway": "flip",
  "method": "bni_va"
}
```

**Response:**

```json
{
  "success": true,
  "payment_url": "https://payment.flip.id/xxx",
  "payment_code": "8001234567890",
  "transaction_id": "PGPWF10217671650669703904"
}
```

### Payment Callback

#### Flip Callback

```http
POST /payment/callback/flip
Content-Type: application/json

{
  "id": "PGPWF10217671650669703904",
  "bill_link_id": "abc123",
  "status": "SUCCESSFUL",
  "amount": 300000,
  "sender_bank": "bni",
  "created_at": "2026-01-02 10:00:00"
}
```

**Process:**

1. Validate signature
2. Update invoice status → "paid"
3. Update customer status → "Lunas"
4. Set due date → +1 month
5. Generate next month invoice
6. Un-isolir if needed
7. Send WhatsApp confirmation
8. Return response 200 OK

---

## 🔧 Troubleshooting

### Invoice Generation Issues

**Problem:** Invoice tidak ter-generate otomatis

**Solution:**

1. Cek CRON job berjalan:
   ```bash
   grep "invoice:generate" /var/log/cron
   ```
2. Test manual:
   ```bash
   php spark invoice:generate
   ```
3. Cek log error:
   ```bash
   tail -f writable/logs/log-*.log
   ```

### Payment Gateway Issues

**Problem:** Payment tidak redirect ke gateway

**Solution:**

1. Cek API key valid
2. Cek callback URL sudah terdaftar di dashboard gateway
3. Test dengan sandbox environment dulu
4. Enable debug mode di `.env`:
   ```ini
   CI_ENVIRONMENT = development
   ```

### Auto Isolir Not Working

**Problem:** Customer tidak ter-isolir otomatis

**Solution:**

1. Cek koneksi ke MikroTik:
   ```bash
   php spark mikrotik:test --server=1
   ```
2. Pastikan profile isolir ada di MikroTik
3. Cek username/password API MikroTik
4. Verify port 8728 open
5. Check log isolir:
   ```bash
   tail -f writable/logs/isolir-*.log
   ```

### WhatsApp Not Sending

**Problem:** WhatsApp notification tidak terkirim

**Solution:**

1. Scan ulang QR Code di menu WhatsApp
2. Cek device masih connected:
   ```bash
   curl https://wazero.difihome.my.id/status?device=xxx&api_key=xxx
   ```
3. Test send manual dari menu WhatsApp
4. Cek log WhatsApp:
   ```bash
   tail -f writable/logs/whatsapp-*.log
   ```

### Database Connection Error

**Problem:** Error connecting to database

**Solution:**

1. Verify credentials di `.env`
2. Test koneksi MySQL:
   ```bash
   mysql -u username -p -h localhost database_name
   ```
3. Pastikan user MySQL punya akses
4. Check port MySQL (default 3306)

### Performance Issues

**Problem:** Aplikasi lambat

**Solution:**

1. Enable caching:
   ```php
   // app/Config/Cache.php
   public string $handler = 'file'; // atau 'redis'
   ```
2. Optimize database indexes
3. Enable query caching
4. Use CDN untuk assets static
5. Enable OPcache di PHP

---

## 📝 Best Practices

### Security

1. **Password Hashing**: Gunakan password_hash() untuk semua password
2. **CSRF Protection**: Enable di semua form
3. **XSS Prevention**: Escape output dengan esc()
4. **SQL Injection**: Gunakan Query Builder, jangan raw query
5. **API Authentication**: Validate API key dan token

### Database

1. **Backup Regular**: Minimal 1x per hari
2. **Index Optimization**: Index di kolom yang sering di-query
3. **Clean Old Data**: Archive data > 1 tahun
4. **Monitor Performance**: Check slow queries

### Logging

1. **Error Logging**: Enable production error logging
2. **Access Logging**: Track payment dan sensitive actions
3. **Log Rotation**: Rotate logs setiap minggu
4. **Monitor Logs**: Setup alert untuk error critical

---

## 📞 Support

Untuk bantuan lebih lanjut:

- **Email**: support@kimonet.com
- **WhatsApp**: +62 xxx xxxx xxxx
- **Documentation**: https://docs.kimonet.com

---

**© 2026 Kimonet Billing System. All Rights Reserved.**
