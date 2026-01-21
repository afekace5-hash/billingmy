# Fix 404 Error - client.difihome.my.id

## 🚨 Problem

Akses ke `client.difihome.my.id` menampilkan **404 Not Found** dari nginx

---

## ✅ Solusi

### Step 1: Verifikasi DNS

**Cek DNS sudah resolve:**

```bash
nslookup client.difihome.my.id
```

**Harus return IP server** (bukan error atau IP lain)

**Jika DNS belum resolve:**

- Login ke domain registrar (Niagahoster/Cloudflare/dll)
- Tambahkan A Record:
  ```
  Type: A
  Name: client
  Value: [IP_HOSTING_ANDA]
  TTL: 3600
  ```
- Tunggu 5-30 menit untuk propagasi
- Cek lagi di https://dnschecker.org/#A/client.difihome.my.id

---

### Step 2: Buat Subdomain di cPanel/aaPanel

#### **Opsi A: cPanel**

1. Login cPanel
2. **Domains** atau **Subdomains**
3. Klik **Create a New Domain** atau **Create**

**Settings:**

```
Subdomain: client
Domain: difihome.my.id
Document Root: /home/[username]/public_html/customer
```

**PENTING:** Document root harus point ke folder `customer` yang berisi `index.php`!

4. Klik **Submit** atau **Create**

#### **Opsi B: aaPanel (UPDATED)**

**PENTING:** Pastikan struktur folder sudah benar dulu (lihat Step 2.5)!

1. Login aaPanel
2. **Website** → **Add site**

**Settings untuk Solusi 1 (RECOMMENDED):**

```
Domain: client.difihome.my.id
Root directory: /www/wwwroot/billing.difihome.my.id/customer_dashboard
PHP Version: 8.1 atau 8.2
Database: [pilih existing database billing]
```

**Settings untuk Solusi 2 (Terpisah):**

```
Domain: client.difihome.my.id
Root directory: /www/wwwroot/client.difihome.my.id
PHP Version: 8.1 atau 8.2
Database: [pilih existing database billing]
```

3. **Submit**
4. **SSL/TLS** → **Let's Encrypt** → Centang `client.difihome.my.id` → **Apply**
5. **Konfigurasi** → **Default Document** → Pastikan `index.php` ada di list
6. **Restart** PHP-FPM dan Nginx

#### **Opsi C: Nginx Manual Config**

**Jika pakai VPS dengan nginx langsung:**

1. Buat file config:

```bash
sudo nano /etc/nginx/sites-available/client.difihome.my.id
```

2. Paste config ini:

```nginx
server {
    listen 80;
    server_name client.difihome.my.id;

    root /var/www/customer;
    index index.php index.html;

    # Logging
    access_log /var/log/nginx/client.difihome-access.log;
    error_log /var/log/nginx/client.difihome-error.log;

    # CodeIgniter routing
    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    # PHP-FPM
    location ~ \.php$ {
        include snippets/fastcgi-php.conf;
        fastcgi_pass unix:/var/run/php/php8.1-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }

    # Security
    location ~ /\. {
        deny all;
    }

    # Disable access to sensitive files
    location ~ /(\.env|composer\.(json|lock)|package\.json)$ {
        deny all;
    }
}
```

3. Enable site:

```bash
sudo ln -s /etc/nginx/sites-available/client.difihome.my.id /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl reload nginx
```

4. Setup SSL:

```bash
sudo certbot --nginx -d client.difihome.my.id
```

---

### Step 2.5: Fix untuk aaPanel - Struktur Folder yang Benar

**PENTING untuk aaPanel:**

Error `/www/wwwroot//app/Config/Paths.php` terjadi karena struktur folder tidak sesuai.

**Solusi 1: Upload Seluruh Aplikasi (RECOMMENDED)**

Upload **seluruh folder billing** ke server, bukan hanya folder customer_dashboard:

```
/www/wwwroot/billing.difihome.my.id/
├── app/                    ← CodeIgniter app
├── system/                 ← CodeIgniter system
├── vendor/                 ← Composer dependencies
├── writable/               ← Writable folders
├── public/                 ← Public files (admin)
├── customer_dashboard/     ← Customer portal
│   ├── index.php
│   ├── .htaccess
│   └── public/
├── .env
└── composer.json
```

**Kemudian di aaPanel:**

1. **Website → Add site**
2. **Domain:** `client.difihome.my.id`
3. **Root directory:** `/www/wwwroot/billing.difihome.my.id/customer_dashboard`
4. **PHP Version:** 8.1 atau 8.2
5. **Submit**

Dengan struktur ini, `index.php` di `customer_dashboard` bisa akses parent directory (`../`) untuk menemukan folder `app/`, `system/`, dll.

**Solusi 2: Copy Files ke customer_dashboard (Alternatif)**

Jika ingin customer portal terpisah total, copy semua folder CodeIgniter:

```bash
# Via SSH/Terminal aaPanel
cd /www/wwwroot
mkdir -p client.difihome.my.id

# Copy dari folder billing
cp -r /www/wwwroot/billing.difihome.my.id/app client.difihome.my.id/
cp -r /www/wwwroot/billing.difihome.my.id/system client.difihome.my.id/
cp -r /www/wwwroot/billing.difihome.my.id/vendor client.difihome.my.id/
cp -r /www/wwwroot/billing.difihome.my.id/writable client.difihome.my.id/
cp /www/wwwroot/billing.difihome.my.id/customer_dashboard/index.php client.difihome.my.id/
cp /www/wwwroot/billing.difihome.my.id/customer_dashboard/.htaccess client.difihome.my.id/
cp /www/wwwroot/billing.difihome.my.id/customer_dashboard/.env.customer client.difihome.my.id/.env
cp -r /www/wwwroot/billing.difihome.my.id/customer_dashboard/public client.difihome.my.id/
```

**Lalu edit** `/www/wwwroot/client.difihome.my.id/index.php` line 12:

```php
// Dari ini:
define('ROOTPATH', realpath(__DIR__ . '/../') . DIRECTORY_SEPARATOR);

// Ganti jadi ini:
define('ROOTPATH', __DIR__ . DIRECTORY_SEPARATOR);
```

Karena sekarang semua file sudah di folder yang sama (tidak perlu parent directory).

**Solusi 3: Symbolic Link (Advanced)**

```bash
# Via SSH
cd /www/wwwroot/client.difihome.my.id
ln -s /www/wwwroot/billing.difihome.my.id/app app
ln -s /www/wwwroot/billing.difihome.my.id/system system
ln -s /www/wwwroot/billing.difihome.my.id/vendor vendor
ln -s /www/wwwroot/billing.difihome.my.id/writable writable
```

Lalu edit index.php seperti Solusi 2.

---

### Step 3: Verifikasi File Structure

**Pastikan struktur folder benar:**

```
/public_html/customer/               ← Document Root Subdomain
├── index.php                        ← Entry point (WAJIB!)
├── .htaccess                        ← Rewrite rules
├── .env.customer                    ← Config
└── public/
    └── assets/
        ├── css/
        ├── js/
        └── images/
```

**Cek via File Manager atau SSH:**

```bash
ls -la /home/username/public_html/customer/
# Harus ada: index.php, .htaccess
```

**Jika file tidak ada:**

- Upload folder `customer_dashboard` dari project
- Rename menjadi `customer` di hosting
- Pastikan `index.php` ada di root folder customer

---

### Step 4: Check Permissions

**Set permissions yang benar:**

#### Via cPanel File Manager:

1. Pilih folder `customer`
2. **Permissions** atau **Change Permissions**
3. Set:
   - Folders: `755` (rwxr-xr-x)
   - Files: `644` (rw-r--r--)
   - index.php: `644`

#### Via SSH:

```bash
cd /home/username/public_html/customer
find . -type d -exec chmod 755 {} \;
find . -type f -exec chmod 644 {} \;
chmod 755 index.php
```

---

### Step 5: Edit Configuration

**Edit `.env.customer`:**

Location: `/public_html/customer/.env.customer`

```env
#--------------------------------------------------------------------
# ENVIRONMENT
#--------------------------------------------------------------------
CI_ENVIRONMENT = production

#--------------------------------------------------------------------
# APP
#--------------------------------------------------------------------
app.baseURL = 'https://client.difihome.my.id/'
app.forceGlobalSecureRequests = true

#--------------------------------------------------------------------
# DATABASE
#--------------------------------------------------------------------
database.default.hostname = localhost
database.default.database = difihome_billing
database.default.username = difihome_user
database.default.password = [YOUR_DB_PASSWORD]
database.default.DBDriver = MySQLi
database.default.DBPrefix =
database.default.port = 3306

#--------------------------------------------------------------------
# ENCRYPTION
#--------------------------------------------------------------------
encryption.key = [GENERATE_NEW_KEY]
```

**Generate encryption key:**

```bash
php spark key:generate
```

---

### Step 6: Test Access

1. **Test DNS:**

   ```bash
   ping client.difihome.my.id
   ```

2. **Test HTTP:**

   ```
   http://client.difihome.my.id
   ```

   Harus redirect atau load halaman (bukan 404)

3. **Test HTTPS:**

   ```
   https://client.difihome.my.id
   ```

   Harus ada SSL certificate (hijau/secure)

4. **Check Nginx Logs** (jika masih error):

   ```bash
   # Error log
   sudo tail -f /var/log/nginx/error.log

   # Access log
   sudo tail -f /var/log/nginx/client.difihome-access.log
   ```

---

## 🔍 Troubleshooting

### Problem: Subdomain list tapi tetap 404

**Solusi:**

1. Cek document root subdomain
2. Pastikan point ke folder yang **benar** (`/public_html/customer`)
3. Bukan ke folder lain atau folder kosong

### Problem: File index.php tidak ditemukan atau Path Error

**Error seperti:**

```
/www/wwwroot//app/Config/Paths.php not found
```

**Solusi:**

```bash
# Via SSH/Terminal
cd /www/wwwroot/client.difihome.my.id
ls -la
# Pastikan ada: index.php, app/, system/, vendor/
```

**Jika tidak ada folder app/, system/, vendor/:**

**Opsi A: Gunakan struktur parent directory (RECOMMENDED)**

- Ubah document root di aaPanel ke `/www/wwwroot/billing.difihome.my.id/customer_dashboard`
- Pastikan folder billing berisi: app/, system/, vendor/, customer_dashboard/
- Restart Nginx

**Opsi B: Copy files ke folder client**

```bash
# Copy dari folder billing
cp -r /www/wwwroot/billing.difihome.my.id/{app,system,vendor,writable} /www/wwwroot/client.difihome.my.id/
```

Lalu edit `/www/wwwroot/client.difihome.my.id/index.php`:

```php
// Line 12 - Ubah dari:
define('ROOTPATH', realpath(__DIR__ . '/../') . DIRECTORY_SEPARATOR);

// Jadi:
define('ROOTPATH', __DIR__ . DIRECTORY_SEPARATOR);
```

**Opsi C: Cek realpath**

```bash
# Cek apakah realpath berhasil
php -r "echo realpath('/www/wwwroot/client.difihome.my.id') . PHP_EOL;"
```

Jika return `false` atau kosong, berarti path tidak exist atau permission salah.

### Problem: 403 Forbidden

**Solusi:**

```bash
# Fix permissions
chmod 755 /home/username/public_html/customer
chmod 644 /home/username/public_html/customer/index.php
```

### Problem: 500 Internal Server Error

**Solusi:**

1. Cek PHP version (minimal 8.1)
2. Cek `.htaccess` syntax
3. Cek error log:
   ```bash
   tail -f /var/log/nginx/error.log
   tail -f /home/username/logs/error_log
   ```

### Problem: SSL Not Secure

**Solusi:**

```bash
# Install Let's Encrypt SSL
sudo certbot --nginx -d client.difihome.my.id

# Or via cPanel
# SSL/TLS → Install SSL → Let's Encrypt → Issue
```

---

## 📋 Quick Checklist

```
✅ DNS A Record dibuat (client → IP hosting)
✅ DNS sudah propagate (cek dnschecker.org)
✅ Subdomain dibuat di cPanel/aaPanel
✅ Document root point ke /public_html/customer
✅ File index.php ada di folder customer
✅ File .htaccess ada dan syntax benar
✅ Permissions: folder 755, file 644
✅ .env.customer diedit dengan benar
✅ Database credentials benar
✅ PHP version 8.1+
✅ SSL certificate installed
```

---

## 🎯 Expected Result

Setelah semua langkah:

```
✅ http://client.difihome.my.id → Load customer portal
✅ https://client.difihome.my.id → Load dengan SSL
✅ Login page tampil
✅ No 404 error
✅ No 403 forbidden
✅ No 500 error
```

---

## 📞 Jika Masih Error

**Kumpulkan informasi ini:**

1. Hosting type: cPanel / aaPanel / VPS Nginx / VPS Apache
2. PHP version: `php -v`
3. Error log: dari cPanel atau `/var/log/nginx/error.log`
4. DNS status: hasil dari `nslookup client.difihome.my.id`
5. File check: hasil dari `ls -la /path/to/customer/`
6. Subdomain settings screenshot dari cPanel

**Common Issue:**

- Salah nama subdomain: `client` vs `customer`
- Document root salah: point ke folder lain
- File tidak diupload ke lokasi yang benar
