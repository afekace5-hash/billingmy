# aaPanel Setup - client.difihome.my.id

## 🎯 Panduan Lengkap Setup Customer Portal di aaPanel

---

## Metode 1: Shared Structure (RECOMMENDED)

Customer portal menggunakan folder/file dari aplikasi billing utama.

### Step 1: Upload Aplikasi Billing

Upload **seluruh aplikasi** ke server:

```
/www/wwwroot/billing.difihome.my.id/
├── app/
├── system/
├── vendor/
├── writable/
├── public/                 ← Admin dashboard (public_html)
├── customer_dashboard/     ← Customer portal
│   ├── index.php
│   ├── .htaccess
│   ├── .env.customer
│   └── public/
├── .env
└── composer.json
```

**Upload via aaPanel File Manager atau FTP:**

1. aaPanel → **Files** → `/www/wwwroot/`
2. Upload folder `billing.difihome.my.id` (dari local project)
3. Pastikan semua folder ter-upload: app, system, vendor, customer_dashboard, dll

### Step 2: Buat Site untuk billing.difihome.my.id

**aaPanel → Website → Add site:**

```
Domain: billing.difihome.my.id
Root directory: /www/wwwroot/billing.difihome.my.id/public
PHP Version: 8.1 atau 8.2
Database: Create new → billing_db
```

Klik **Submit**

### Step 3: Buat Site untuk client.difihome.my.id

**aaPanel → Website → Add site:**

```
Domain: client.difihome.my.id
Root directory: /www/wwwroot/billing.difihome.my.id/customer_dashboard
PHP Version: 8.1 atau 8.2 (SAMA dengan billing)
Database: Use existing → billing_db (SHARE dengan billing)
```

Klik **Submit**

**PENTING:** Document root harus point ke **subdirectory** dari billing, bukan folder terpisah!

### Step 4: Verifikasi Structure

```bash
# Via SSH/Terminal
ls -la /www/wwwroot/billing.difihome.my.id/customer_dashboard/
```

**Harus ada:**

```
✅ index.php
✅ .htaccess
✅ .env.customer
✅ public/ (folder assets)
```

**Dan parent directory harus punya:**

```bash
ls -la /www/wwwroot/billing.difihome.my.id/
```

```
✅ app/
✅ system/
✅ vendor/
✅ writable/
```

### Step 5: Set Permissions

```bash
# Set ownership (ganti 'www' dengan user aaPanel Anda)
chown -R www:www /www/wwwroot/billing.difihome.my.id/

# Set permissions
find /www/wwwroot/billing.difihome.my.id/ -type d -exec chmod 755 {} \;
find /www/wwwroot/billing.difihome.my.id/ -type f -exec chmod 644 {} \;

# Writable folders
chmod -R 777 /www/wwwroot/billing.difihome.my.id/writable/
```

### Step 6: Install SSL

**aaPanel → Website → client.difihome.my.id → SSL:**

1. Tab **Let's Encrypt**
2. Centang domain: `client.difihome.my.id`
3. Klik **Apply**
4. Wait... (1-2 menit)
5. ✅ SSL Installed

**Enable Force HTTPS:**

- Toggle **Force HTTPS** → ON

### Step 7: Edit Configuration

**Edit `.env.customer`:**

aaPanel → Files → `/www/wwwroot/billing.difihome.my.id/customer_dashboard/.env.customer`

```env
CI_ENVIRONMENT = production

app.baseURL = 'https://client.difihome.my.id/'
app.forceGlobalSecureRequests = true

database.default.hostname = localhost
database.default.database = billing_db
database.default.username = billing_db
database.default.password = [PASSWORD_DARI_AAPANEL]
database.default.DBDriver = MySQLi
database.default.port = 3306

encryption.key = [GENERATE_NEW_KEY]
```

**Get database password:**

- aaPanel → Database → Click database name
- Show password

**Generate encryption key:**

```bash
cd /www/wwwroot/billing.difihome.my.id
php spark key:generate
# Copy key yang di-generate
```

### Step 8: Test Access

1. **HTTP Test:**

   ```
   http://client.difihome.my.id
   ```

   Harus auto-redirect ke HTTPS

2. **HTTPS Test:**
   ```
   https://client.difihome.my.id
   ```
   Harus tampil halaman customer portal (login page)

---

## Metode 2: Separated Structure (Alternatif)

Customer portal di folder terpisah total.

### Step 1: Upload & Copy Files

```bash
# Via SSH/Terminal aaPanel
cd /www/wwwroot
mkdir -p client.difihome.my.id

# Copy dari billing
cp -r /www/wwwroot/billing.difihome.my.id/app client.difihome.my.id/
cp -r /www/wwwroot/billing.difihome.my.id/system client.difihome.my.id/
cp -r /www/wwwroot/billing.difihome.my.id/vendor client.difihome.my.id/
cp -r /www/wwwroot/billing.difihome.my.id/writable client.difihome.my.id/

# Copy customer dashboard files
cp /www/wwwroot/billing.difihome.my.id/customer_dashboard/index.php client.difihome.my.id/
cp /www/wwwroot/billing.difihome.my.id/customer_dashboard/.htaccess client.difihome.my.id/
cp /www/wwwroot/billing.difihome.my.id/customer_dashboard/.env.customer client.difihome.my.id/.env
cp -r /www/wwwroot/billing.difihome.my.id/customer_dashboard/public client.difihome.my.id/

# Set permissions
chown -R www:www client.difihome.my.id/
chmod -R 755 client.difihome.my.id/
chmod -R 777 client.difihome.my.id/writable/
```

### Step 2: Edit index.php

**File:** `/www/wwwroot/client.difihome.my.id/index.php`

**Line 12 - Ubah ROOTPATH:**

```php
// SEBELUM (parent directory):
define('ROOTPATH', realpath(__DIR__ . '/../') . DIRECTORY_SEPARATOR);

// SESUDAH (current directory):
define('ROOTPATH', __DIR__ . DIRECTORY_SEPARATOR);
```

**Line 14-18 - Ubah env file path:**

```php
// SEBELUM:
if (file_exists(__DIR__ . '/.env.customer')) {
    $envFile = __DIR__ . '/.env.customer';
} else {
    $envFile = ROOTPATH . '.env';
}

// SESUDAH:
$envFile = ROOTPATH . '.env'; // Langsung gunakan .env
```

Karena file `.env.customer` sudah di-copy jadi `.env`

### Step 3: Buat Site di aaPanel

**aaPanel → Website → Add site:**

```
Domain: client.difihome.my.id
Root directory: /www/wwwroot/client.difihome.my.id
PHP Version: 8.1 atau 8.2
Database: Use existing → billing_db
```

### Step 4: Setup SSL & Config

(Sama seperti Metode 1 - Step 6 & 7)

---

## 🔧 Troubleshooting aaPanel

### Error: `/www/wwwroot//app/Config/Paths.php` not found

**Penyebab:** ROOTPATH kosong atau salah

**Solusi:**

1. **Cek struktur folder:**

   ```bash
   ls -la /www/wwwroot/billing.difihome.my.id/customer_dashboard/
   ls -la /www/wwwroot/billing.difihome.my.id/app/
   ```

2. **Jika folder app tidak ada di parent:**

   - Gunakan Metode 2 (copy semua files)
   - Edit index.php sesuai panduan di atas

3. **Jika document root salah:**
   - aaPanel → Website → client.difihome.my.id → Settings
   - **Root directory:** Ubah ke `/www/wwwroot/billing.difihome.my.id/customer_dashboard`
   - Save → Restart Nginx

### Error: Permission Denied

```bash
# Fix permissions via SSH
chmod -R 755 /www/wwwroot/billing.difihome.my.id/
chmod -R 777 /www/wwwroot/billing.difihome.my.id/writable/
chown -R www:www /www/wwwroot/billing.difihome.my.id/
```

### Error: 502 Bad Gateway

**Penyebab:** PHP-FPM mati atau salah config

**Solusi:**

1. **Restart PHP-FPM:**

   - aaPanel → App Store → PHP 8.1 → Service → Restart

2. **Cek PHP Version:**

   - aaPanel → Website → client.difihome.my.id → PHP Version
   - Pastikan 8.1 atau 8.2

3. **Cek error log:**
   ```bash
   tail -f /www/server/php/83/var/log/php-fpm.log
   tail -f /www/wwwlogs/client.difihome.my.id.log
   ```

### Error: Database Connection Failed

**Solusi:**

1. **Cek database exists:**

   - aaPanel → Database → Cari `billing_db`

2. **Cek credentials di .env:**

   ```bash
   cat /www/wwwroot/billing.difihome.my.id/customer_dashboard/.env.customer
   # atau
   cat /www/wwwroot/client.difihome.my.id/.env
   ```

3. **Test connection:**
   ```bash
   mysql -u billing_db -p billing_db
   # Masukkan password
   # Jika berhasil → credentials benar
   ```

### Error: nginx: [emerg] duplicate location

**Penyebab:** Konflik di nginx config

**Solusi:**

1. **aaPanel → Website → client.difihome.my.id → Config**
2. Cek section `location /`
3. Hapus duplicate atau konflik
4. Save → Restart Nginx

---

## ✅ Verification Checklist

```bash
# 1. Check files exist
ls -la /www/wwwroot/billing.difihome.my.id/customer_dashboard/index.php

# 2. Check parent folders
ls -la /www/wwwroot/billing.difihome.my.id/app/
ls -la /www/wwwroot/billing.difihome.my.id/system/
ls -la /www/wwwroot/billing.difihome.my.id/vendor/

# 3. Check permissions
stat /www/wwwroot/billing.difihome.my.id/writable/

# 4. Check PHP version
php -v

# 5. Test PHP-FPM
systemctl status php-fpm-83

# 6. Check nginx config
nginx -t

# 7. Check DNS
nslookup client.difihome.my.id

# 8. Test HTTP
curl -I http://client.difihome.my.id

# 9. Test HTTPS
curl -I https://client.difihome.my.id

# 10. Check logs
tail -f /www/wwwlogs/client.difihome.my.id.log
```

---

## 🎯 Expected Result

Setelah setup selesai:

```
✅ https://client.difihome.my.id → Customer portal login page
✅ SSL certificate valid (hijau/secure)
✅ No 404, 403, 500, atau 502 error
✅ Login form tampil
✅ Database connection OK
✅ Session working
```

---

## 📞 Support

Jika masih error, kumpulkan info ini:

1. Output dari verification checklist di atas
2. Screenshot error di browser
3. Error log: `/www/wwwlogs/client.difihome.my.id.log`
4. PHP-FPM log: `/www/server/php/83/var/log/php-fpm.log`
5. Nginx config: aaPanel → Website → Config file
6. Structure folder: `tree -L 2 /www/wwwroot/billing.difihome.my.id/`
