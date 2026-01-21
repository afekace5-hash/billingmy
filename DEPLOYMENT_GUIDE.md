# 🚀 Complete Production Deployment Guide

### DifiHome - Billing System & Customer Portal

**Target Domains:**

- 🌐 **Landing Page**: https://difihome.my.id
- 💼 **Admin Dashboard**: https://billing.difihome.my.id
- 🏠 **Customer Portal**: https://customer.difihome.my.id

**Estimated Time**: 60-90 minutes

---

## 📋 Table of Contents

1. [Prerequisites](#prerequisites)
2. [Step 1: DNS Configuration](#step-1-dns-configuration)
3. [Step 2: Prepare Files](#step-2-prepare-files)
4. [Step 3: Upload to Hosting](#step-3-upload-to-hosting)
5. [Step 4: Setup Subdomains](#step-4-setup-subdomains)
6. [Step 5: Database Setup](#step-5-database-setup)
7. [Step 6: Configure Applications](#step-6-configure-applications)
8. [Step 7: Install SSL Certificates](#step-7-install-ssl-certificates)
9. [Step 8: Testing](#step-8-testing)
10. [Troubleshooting](#troubleshooting)

---

## Prerequisites

### ✅ Yang Harus Sudah Ada:

- [ ] Domain: `difihome.my.id` (sudah dibeli)
- [ ] Hosting: cPanel atau VPS dengan PHP 8.1+, MySQL 5.7+
- [ ] FTP/cPanel access credentials
- [ ] Files lokal sudah siap (dari `C:\laragon\www\billingkimo\`)

### 📦 Software Requirements (Hosting):

- **PHP**: 8.1 atau lebih tinggi
- **MySQL**: 5.7+ atau MariaDB 10.3+
- **Apache/Nginx**: dengan mod_rewrite enabled
- **PHP Extensions**: intl, mbstring, json, mysqlnd, xml, curl, fileinfo, gd

### 🔧 Tools yang Diperlukan:

- **FileZilla** (FTP client) - Download: https://filezilla-project.org/
- **Text Editor** (Notepad++/VS Code) untuk edit config files
- **phpMyAdmin** (biasanya sudah ada di cPanel)

---

## Step 1: DNS Configuration

### ⏱️ Time: 5-30 minutes (DNS propagation)

### 1.1 Login ke Domain Registrar

Masuk ke dashboard domain registrar Anda (Niagahoster, Cloudflare, Rumahweb, dll)

### 1.2 Tambahkan DNS Records

**Di DNS Management / Zone Editor:**

```
Type: A
Name: @ (atau kosong untuk root domain)
Value: [IP_HOSTING_ANDA]
TTL: 3600 (atau Auto)

Type: A
Name: billing
Value: [IP_HOSTING_ANDA]
TTL: 3600

Type: A
Name: customer
Value: [IP_HOSTING_ANDA]
TTL: 3600
```

**Cara Cek IP Hosting:**

- cPanel: kanan atas ada IP Address
- Email hosting biasanya include IP
- Atau tanya support hosting

### 1.3 Verify DNS Propagation

**Tunggu 5-30 menit**, lalu cek:

**Online Tools:**

- https://dnschecker.org/#A/difihome.my.id
- https://dnschecker.org/#A/billing.difihome.my.id
- https://dnschecker.org/#A/customer.difihome.my.id

**Via Command Line:**

```bash
# Windows PowerShell
nslookup difihome.my.id
nslookup billing.difihome.my.id
nslookup customer.difihome.my.id

# Harus return IP hosting Anda
```

✅ **Lanjut jika DNS sudah resolve!**

---

## Step 2: Prepare Files

### ⏱️ Time: 10-15 minutes

### 2.1 File Structure Overview

```
/public_html/                        ← Root (Landing Page)
├── index.html                       ← Dari landing.html (RENAME!)
├── subscribe.html
├── formregistrasi.html
├── config.js                        ← EDIT URL!
└── assets/                          ← All CSS/JS/Images

/public_html/billing/                ← Admin Dashboard
├── public_html/                     ← Dari folder "public" (RENAME!)
│   ├── index.php
│   ├── .htaccess                    ← COPY dari htaccess-billing-production.txt
│   ├── uploads/
│   └── backend/
├── app/                             ← CodeIgniter app
├── system/                          ← CodeIgniter system
├── writable/                        ← Permissions: 777
├── vendor/                          ← Composer dependencies
└── .env                             ← CREATE & EDIT!

/public_html/customer/               ← Customer Portal
├── index.php
├── .htaccess
├── .env.customer                    ← EDIT!
└── public/
    └── assets/
```

### 2.2 Files yang WAJIB Di-Upload

#### ✅ Landing Page Files:

```
✅ landing.html → rename ke index.html
✅ subscribe.html
✅ formregistrasi.html
✅ config.js (EDIT dulu!)
✅ assets/ (semua isi)
```

#### ✅ Billing App Files:

```
✅ public/ → rename ke public_html/
✅ app/ (semua isi)
✅ system/ (semua isi)
✅ writable/ (semua isi)
✅ vendor/ (semua isi)
✅ composer.json
✅ composer.lock
✅ .env (CREATE dari env.example)
```

#### ✅ Customer Portal Files:

```
✅ customer_dashboard/index.php
✅ customer_dashboard/.htaccess
✅ customer_dashboard/.env.customer (EDIT!)
✅ customer_dashboard/public/ (semua isi)
```

### 2.3 Files yang TIDAK Perlu Di-Upload

```
❌ .git/
❌ .gitignore
❌ node_modules/
❌ tests/
❌ builds/
❌ *.md (README, DOCUMENTATION, dll)
❌ *.bat, *.ps1, *.sh (script files)
❌ env.example
❌ writable/cache/* (folder saja, isi dikosongkan)
❌ writable/logs/* (folder saja)
❌ writable/session/* (folder saja)
```

### 2.4 Edit Files SEBELUM Upload

#### A. `public/landing_page/config.js`

**EDIT URLS:**

```javascript
const CONFIG = {
  LANDING_URL: "https://difihome.my.id",
  BILLING_URL: "https://billing.difihome.my.id",
  CUSTOMER_URL: "https://customer.difihome.my.id",

  API_ENDPOINTS: {
    packages: "/api/packages",
    coverage: "/api/coverage-check",
    register: "/api/customers/register",
  },
};
```

#### B. Rename Files

```
✅ landing.html → index.html
✅ public/ → public_html/
✅ htaccess-billing-production.txt → copy ke .htaccess
```

#### C. Create `.env` untuk Billing

**Copy dari `env.example`**, save sebagai `.env`

**JANGAN edit dulu** - akan diedit setelah upload & database setup

### 2.5 Compress Files (RECOMMENDED)

Untuk upload lebih cepat, compress ke ZIP:

```
landing-page.zip         → semua files landing page
billing-app.zip          → semua files billing
customer-portal.zip      → semua files customer_dashboard
database-backup.sql      → export dari phpMyAdmin lokal
```

**Export Database:**

1. Buka: http://localhost/phpmyadmin
2. Pilih database: `kimonet`
3. Tab "Export" → Quick → Format: SQL
4. Download: `database-backup.sql`

---

## Step 3: Upload to Hosting

### ⏱️ Time: 15-20 minutes

### 3.1 Login to cPanel

```
URL: https://yourdomain.com:2083
atau: https://yourdomain.com/cpanel
Username: [dari email hosting]
Password: [dari email hosting]
```

### 3.2 Option A: Upload via cPanel File Manager (EASIEST)

#### Upload Landing Page:

1. **File Manager** → `/public_html/`
2. **Upload** → Select `landing-page.zip`
3. Wait upload complete
4. **Right-click ZIP** → **Extract**
5. Delete ZIP file after extraction

#### Upload Billing App:

1. **File Manager** → `/public_html/`
2. **New Folder** → Name: `billing`
3. Masuk ke folder `billing`
4. **Upload** → Select `billing-app.zip`
5. **Right-click** → **Extract**
6. Delete ZIP file

#### Upload Customer Portal:

1. **File Manager** → `/public_html/`
2. **New Folder** → Name: `customer`
3. Masuk ke folder `customer`
4. **Upload** → Select `customer-portal.zip`
5. **Right-click** → **Extract**
6. Delete ZIP file

### 3.3 Option B: Upload via FTP (FileZilla)

#### FTP Settings:

```
Host: ftp.difihome.my.id (atau IP hosting)
Username: [dari cPanel]
Password: [dari cPanel]
Port: 21 (FTP) atau 22 (SFTP)
```

#### FileZilla Settings:

- Transfer Type: **Binary**
- Timeout: **3600 seconds**
- Max connections: **3**

#### Upload Folders:

```
Local → Remote
------   ------
C:\laragon\www\billingkimo\public\landing_page\*  → /public_html/
C:\laragon\www\billingkimo\                        → /public_html/billing/
C:\laragon\www\billingkimo\customer_dashboard\*    → /public_html/customer/
```

### 3.4 Verify Upload

**Check via File Manager:**

```
✅ /public_html/index.html exists
✅ /public_html/config.js exists
✅ /public_html/assets/ exists
✅ /public_html/billing/app/ exists
✅ /public_html/billing/public_html/index.php exists
✅ /public_html/customer/index.php exists
```

### 3.5 Rename Files (Jika Belum)

**Via File Manager:**

1. `/public_html/billing/public/` → **Rename** → `public_html`
2. Copy `htaccess-billing-production.txt` content → paste ke `.htaccess` baru di `/public_html/billing/public_html/`

---

## Step 4: Setup Subdomains

### ⏱️ Time: 5-10 minutes

### 4.1 Create Billing Subdomain

**cPanel → Domains (atau Subdomains):**

```
Subdomain: billing
Domain: difihome.my.id
Document Root: /home/[username]/public_html/billing/public_html
```

**PENTING**: Document root harus point ke `public_html/` di dalam folder billing!

Klik **Create**

### 4.2 Create Customer Subdomain

```
Subdomain: customer
Domain: difihome.my.id
Document Root: /home/[username]/public_html/customer
```

Klik **Create**

### 4.3 Verify Subdomains

**cPanel → Domains** - Should see:

```
✅ difihome.my.id → /public_html
✅ billing.difihome.my.id → /public_html/billing/public_html
✅ customer.difihome.my.id → /public_html/customer
```

---

## Step 5: Database Setup

### ⏱️ Time: 10 minutes

### 5.1 Create Database

**cPanel → MySQL Databases:**

1. **Create New Database:**

   - Name: `difihome_billing` (atau [username]\_billing)
   - Klik **Create Database**

2. **Create Database User:**

   - Username: `difihome_user`
   - Password: Generate strong password (SAVE THIS!)
   - Klik **Create User**

3. **Add User to Database:**
   - User: `difihome_user`
   - Database: `difihome_billing`
   - Privileges: **ALL PRIVILEGES** ✅
   - Klik **Make Changes**

**SAVE CREDENTIALS:**

```
Database: difihome_billing (atau cpanelusername_billing)
Username: difihome_user (atau cpanelusername_user)
Password: [generated password]
Hostname: localhost
```

### 5.2 Import Database

**cPanel → phpMyAdmin:**

1. Klik database: `difihome_billing`
2. Tab **Import**
3. **Choose File** → Select `database-backup.sql`
4. Format: **SQL**
5. Klik **Go**
6. Wait for import... (might take 1-2 minutes)
7. Check **Tables** tab - should see all tables imported

**Common Tables:**

```
✅ customers
✅ invoices
✅ packages
✅ payments
✅ users
✅ ... (and more)
```

---

## Step 6: Configure Applications

### ⏱️ Time: 10-15 minutes

### 6.1 Edit Billing `.env`

**Location**: `/public_html/billing/.env`

**Via cPanel File Manager:**

1. Navigate to `/public_html/billing/`
2. Right-click `.env` → **Edit**
3. Update these values:

```env
#--------------------------------------------------------------------
# ENVIRONMENT
#--------------------------------------------------------------------
CI_ENVIRONMENT = production

#--------------------------------------------------------------------
# APP
#--------------------------------------------------------------------
app.baseURL = 'https://billing.difihome.my.id/'
app.forceGlobalSecureRequests = true

#--------------------------------------------------------------------
# DATABASE - UPDATE THESE!
#--------------------------------------------------------------------
database.default.hostname = localhost
database.default.database = difihome_billing
database.default.username = difihome_user
database.default.password = [PASSWORD_DARI_STEP_5]
database.default.DBDriver = MySQLi
database.default.DBPrefix =
database.default.port = 3306

#--------------------------------------------------------------------
# ENCRYPTION
#--------------------------------------------------------------------
# Generate baru! (lihat section di bawah)
encryption.key = hex2bin:[GENERATE_NEW_KEY]

#--------------------------------------------------------------------
# SECURITY
#--------------------------------------------------------------------
security.csrfProtection = 'session'
security.tokenRandomize = true
security.tokenName = 'csrf_token_name'
security.headerName = 'X-CSRF-TOKEN'
security.cookieName = 'csrf_cookie_name'
security.expires = 7200
security.regenerate = true
```

### 6.2 Generate Encryption Key

**Option 1 - Via Terminal SSH:**

```bash
cd /public_html/billing
php spark key:generate --show
```

**Option 2 - Via PHP File:**

Create file: `/public_html/generate_key.php`

```php
<?php
$key = bin2hex(random_bytes(32));
echo "encryption.key = hex2bin:$key";
```

Access via browser: `https://difihome.my.id/generate_key.php`

Copy key → Paste ke `.env`

**DELETE file setelah dipakai!**

### 6.3 Edit Customer Portal `.env.customer`

**Location**: `/public_html/customer/.env.customer`

```env
#--------------------------------------------------------------------
# ENVIRONMENT
#--------------------------------------------------------------------
CI_ENVIRONMENT = production

#--------------------------------------------------------------------
# APP
#--------------------------------------------------------------------
app.baseURL = 'https://customer.difihome.my.id/'
app.forceGlobalSecureRequests = true

app.sessionDriver = 'CodeIgniter\Session\Handlers\FileHandler'
app.sessionCookieName = 'customer_session'
app.sessionExpiration = 7200

app.cookiePrefix = 'customer_'
app.cookieDomain = '.difihome.my.id'
app.cookiePath = '/'
app.cookieSecure = true
app.cookieHTTPOnly = true

#--------------------------------------------------------------------
# DATABASE - SAMA dengan billing!
#--------------------------------------------------------------------
database.default.hostname = localhost
database.default.database = difihome_billing
database.default.username = difihome_user
database.default.password = [PASSWORD_YANG_SAMA]
database.default.DBDriver = MySQLi

#--------------------------------------------------------------------
# ENCRYPTION - Generate key BERBEDA dari billing!
#--------------------------------------------------------------------
encryption.key = hex2bin:[GENERATE_ANOTHER_NEW_KEY]

#--------------------------------------------------------------------
# SECURITY
#--------------------------------------------------------------------
security.csrfProtection = 'session'
security.tokenRandomize = true
```

**Generate encryption key berbeda** untuk customer portal (gunakan cara yang sama seperti billing)!

### 6.4 Update CodeIgniter App.php (Optional)

**Location**: `/public_html/billing/app/Config/App.php`

Verify `$baseURL`:

```php
public string $baseURL = 'https://billing.difihome.my.id/';
public bool $forceGlobalSecureRequests = true;
```

**Biasanya tidak perlu edit** karena `.env` override config ini.

### 6.5 Set File Permissions

**Via cPanel File Manager:**

```
writable/ folder:
- Right-click → Permissions → 777 (Read, Write, Execute - All)
- ✅ Apply to subdirectories

uploads/ folder:
- /public_html/billing/public_html/uploads/
- Permissions: 777

.env files:
- /public_html/billing/.env → 600
- /public_html/customer/.env.customer → 600
```

**Via SSH Terminal:**

```bash
chmod -R 777 /public_html/billing/writable
chmod -R 777 /public_html/billing/public_html/uploads
chmod 600 /public_html/billing/.env
chmod 600 /public_html/customer/.env.customer
```

---

## Step 7: Install SSL Certificates

### ⏱️ Time: 10-15 minutes

### 7.1 cPanel AutoSSL (EASIEST - RECOMMENDED)

**cPanel → SSL/TLS Status:**

1. Scroll down to domains list
2. Check:
   ```
   ✅ difihome.my.id
   ✅ billing.difihome.my.id
   ✅ customer.difihome.my.id
   ```
3. Klik **Run AutoSSL**
4. Wait 5-10 minutes for automatic installation
5. Refresh page - should see green checkmarks ✅

**Verify:**

- Visit: `https://difihome.my.id` - Harus ada gembok 🔒
- Visit: `https://billing.difihome.my.id` - Harus ada gembok 🔒
- Visit: `https://customer.difihome.my.id` - Harus ada gembok 🔒

### 7.2 Manual Let's Encrypt (Via SSH)

**If AutoSSL tidak available:**

```bash
# Install Certbot (if not installed)
sudo apt install certbot python3-certbot-apache

# Generate certificates
certbot certonly --webroot \
  -w /home/[username]/public_html \
  -d difihome.my.id \
  -w /home/[username]/public_html/billing/public_html \
  -d billing.difihome.my.id \
  -w /home/[username]/public_html/customer \
  -d customer.difihome.my.id

# Certificates will be saved to:
# /etc/letsencrypt/live/difihome.my.id/
```

### 7.3 Enable Force HTTPS

**Setelah SSL berhasil installed:**

#### A. Update `.htaccess` (Billing)

**Location**: `/public_html/billing/public_html/.htaccess`

**Uncomment these lines:**

```apache
# Force HTTPS
RewriteCond %{HTTPS} off
RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]
```

#### B. Update `.htaccess` (Customer)

**Location**: `/public_html/customer/.htaccess`

**Uncomment:**

```apache
RewriteCond %{HTTPS} off
RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]
```

#### C. Update Landing Page (Optional)

**Create/Edit**: `/public_html/.htaccess`

```apache
<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteCond %{HTTPS} off
    RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]
</IfModule>
```

### 7.4 Verify SSL Installation

**Check SSL Quality:**

- https://www.ssllabs.com/ssltest/analyze.html?d=difihome.my.id
- https://www.ssllabs.com/ssltest/analyze.html?d=billing.difihome.my.id

**Target**: Grade A or A+

---

## Step 8: Testing

### ⏱️ Time: 15-20 minutes

### 8.1 Landing Page Testing

**URL**: https://difihome.my.id

#### Test Checklist:

- [ ] Page loads without errors
- [ ] CSS/JS loaded correctly (no 404s in browser console F12)
- [ ] Images displayed
- [ ] Forms working (contact, subscribe)
- [ ] Links working
- [ ] Responsive design on mobile

**Check Browser Console (F12):**

```
✅ No errors
✅ No 404 for assets
✅ No CORS errors
```

### 8.2 Customer Portal Testing

**URL**: https://customer.difihome.my.id

#### Test Checklist:

- [ ] Login page accessible
- [ ] Can login with test customer account
- [ ] Dashboard loads
- [ ] Invoice list displays
- [ ] Can download invoice PDF
- [ ] Profile page working
- [ ] Logout works
- [ ] Session persists (not auto-logout)

**Test Customer Login:**

```
Username: test@customer.com (sesuai database Anda)
Password: [password dari database]
```

### 8.3 Billing Admin Testing

**URL**: https://billing.difihome.my.id

#### Test Checklist:

- [ ] Admin login page accessible
- [ ] Can login with admin account
- [ ] Dashboard loads with data
- [ ] Customer management works
- [ ] Invoice management works
- [ ] Payment recording works
- [ ] Reports accessible
- [ ] All CRUD operations working

**Test Admin Login:**

```
Username: admin (atau sesuai database)
Password: [password dari database]
```

### 8.4 Cross-Domain Testing

#### Test Navigation Links:

- [ ] Landing → Customer Portal (login button)
- [ ] Landing → Billing (admin link, if any)
- [ ] Customer Portal → Landing (back button)
- [ ] Billing → Landing (if applicable)

**Check `config.js` URLs:**

```javascript
// Verify ini di browser console (F12)
console.log(CONFIG);

// Should show:
// LANDING_URL: "https://difihome.my.id"
// BILLING_URL: "https://billing.difihome.my.id"
// CUSTOMER_URL: "https://customer.difihome.my.id"
```

### 8.5 Database Testing

**phpMyAdmin:**

- [ ] All tables exist
- [ ] Sample data present
- [ ] Can query tables
- [ ] Relationships working

**Test Query:**

```sql
-- Check customers
SELECT * FROM customers LIMIT 5;

-- Check invoices
SELECT * FROM invoices LIMIT 5;

-- Check users (admin)
SELECT * FROM users;
```

### 8.6 Email Testing

**Test email functionality:**

- [ ] Invoice email sent
- [ ] Registration confirmation sent
- [ ] Password reset working
- [ ] Notification emails working

**If emails not sending**, check SMTP config di `.env`:

```env
email.protocol = smtp
email.SMTPHost = smtp.gmail.com
email.SMTPPort = 587
email.SMTPUser = your-email@gmail.com
email.SMTPPass = your-app-password
email.SMTPCrypto = tls
```

### 8.7 Performance Testing

**Check Loading Speed:**

- https://gtmetrix.com/ (test all 3 domains)
- https://pagespeed.web.dev/ (Google PageSpeed)

**Target:**

- Load time < 3 seconds
- Performance score > 80

---

## Step 9: Post-Deployment Checklist

### 🔒 Security

- [ ] `.env` files have 600 permissions
- [ ] `writable/` has 777 permissions
- [ ] Database credentials secure
- [ ] Admin passwords changed from default
- [ ] CSRF protection enabled
- [ ] Force HTTPS enabled
- [ ] SQL injection protection (CodeIgniter Query Builder)

### 📊 Monitoring

- [ ] Enable error logging: `CI_ENVIRONMENT = production`
- [ ] Check logs: `/billing/writable/logs/`
- [ ] Setup uptime monitoring (UptimeRobot, etc.)
- [ ] Setup backup schedule (cPanel backups)

### 🔄 Maintenance

- [ ] Schedule database backups (daily/weekly)
- [ ] Schedule file backups
- [ ] Monitor disk space
- [ ] Monitor database size
- [ ] Plan for updates (CodeIgniter, PHP)

---

## Troubleshooting

### ❌ Problem: 500 Internal Server Error

**Symptoms**: White page dengan error 500

**Causes & Solutions:**

1. **Wrong file permissions**

   ```bash
   chmod -R 755 /public_html/billing
   chmod -R 777 /public_html/billing/writable
   ```

2. **Missing .htaccess or wrong config**

   - Verify `.htaccess` ada di `/public_html/billing/public_html/`
   - Check syntax error di `.htaccess`

3. **PHP version incompatible**

   - Check: cPanel → Select PHP Version
   - Set ke: **PHP 8.1** atau **8.2**

4. **Missing PHP extensions**

   - cPanel → Select PHP Version → Extensions
   - Enable: intl, mbstring, json, mysqlnd, xml

5. **Check error logs**
   ```
   cPanel → Error Logs
   atau
   /public_html/billing/writable/logs/log-[date].php
   ```

---

### ❌ Problem: Database Connection Failed

**Symptoms**: Error "Unable to connect to database"

**Solutions:**

1. **Verify database credentials di `.env`**

   ```env
   database.default.hostname = localhost  ← MUST be localhost
   database.default.database = [exact_db_name]
   database.default.username = [exact_username]
   database.default.password = [correct_password]
   ```

2. **Check database exists**

   - cPanel → phpMyAdmin
   - Database harus ada di sidebar

3. **Check user privileges**

   - cPanel → MySQL Databases
   - Verify user has ALL PRIVILEGES on database

4. **Test connection manually**
   - phpMyAdmin → Login with same credentials
   - If can't login, credentials salah

---

### ❌ Problem: 404 Not Found

**Different scenarios:**

#### A. 404 on main page (billing.difihome.my.id)

**Cause**: Subdomain document root salah

**Solution:**

```
cPanel → Domains → Click billing subdomain → Edit
Document Root harus: /public_html/billing/public_html
                      ^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^
                      WAJIB point ke public_html/ folder!
```

#### B. 404 on CSS/JS files

**Cause**: Base URL salah atau path salah

**Solution di `.env`:**

```env
app.baseURL = 'https://billing.difihome.my.id/'
              ^                               ^
              WAJIB ada trailing slash!
```

#### C. 404 on CodeIgniter routes

**Cause**: mod_rewrite tidak enabled atau .htaccess salah

**Solution:**

1. Verify `.htaccess` ada di document root
2. Check mod_rewrite: Contact hosting support
3. Verify .htaccess content:
   ```apache
   <IfModule mod_rewrite.c>
       RewriteEngine On
       RewriteBase /
       RewriteCond %{REQUEST_FILENAME} !-f
       RewriteCond %{REQUEST_FILENAME} !-d
       RewriteRule ^(.*)$ index.php/$1 [L]
   </IfModule>
   ```

---

### ❌ Problem: SSL Certificate Issues

**Symptoms**: "Your connection is not private" warning

**Solutions:**

1. **Wait for AutoSSL completion**

   - Can take up to 30 minutes
   - Check: cPanel → SSL/TLS Status

2. **Force regenerate SSL**

   ```
   cPanel → SSL/TLS Status
   Uncheck domain → Run AutoSSL → Check domain → Run AutoSSL
   ```

3. **Mixed content (HTTP/HTTPS)**

   - Check browser console (F12)
   - Semua assets harus HTTPS
   - Update hardcoded HTTP links ke HTTPS

4. **Clear browser cache**
   - Ctrl+F5 (Windows)
   - Cmd+Shift+R (Mac)

---

### ❌ Problem: Session Not Working

**Symptoms**: Auto logout, CSRF token mismatch

**Solutions:**

1. **Verify cookie domain di `.env`**

   ```env
   app.cookieDomain = '.difihome.my.id'
                      ^
                      WAJIB ada titik di depan!

   app.cookieSecure = true
   app.cookieHTTPOnly = true
   ```

2. **Check writable permissions**

   ```bash
   chmod -R 777 /public_html/billing/writable/session
   ```

3. **Session driver config**

   ```env
   app.sessionDriver = 'CodeIgniter\Session\Handlers\FileHandler'
   app.sessionSavePath = NULL
   ```

4. **Clear old sessions**
   ```
   Delete all files di: /billing/writable/session/
   ```

---

### ❌ Problem: Encryption Key Error

**Symptoms**: "Encryption key not found" atau "Invalid key"

**Solutions:**

1. **Generate new key**

   ```bash
   # Via SSH
   cd /public_html/billing
   php spark key:generate --show
   ```

2. **Format must be correct**

   ```env
   encryption.key = hex2bin:abc123def456...
                    ^^^^^^^^
                    WAJIB ada prefix hex2bin:
   ```

3. **Different keys for billing & customer**
   - Billing: `.env` → encryption.key
   - Customer: `.env.customer` → encryption.key (BERBEDA!)

---

### ❌ Problem: Email Not Sending

**Symptoms**: No emails received, errors di logs

**Solutions:**

1. **SMTP Configuration di `.env`**

   ```env
   email.protocol = smtp
   email.SMTPHost = smtp.gmail.com
   email.SMTPPort = 587
   email.SMTPUser = youremail@gmail.com
   email.SMTPPass = [app_password]  ← Bukan password email!
   email.SMTPCrypto = tls
   email.SMTPTimeout = 30

   email.fromEmail = youremail@gmail.com
   email.fromName = DifiHome Billing
   ```

2. **Gmail App Password**

   - Gmail → Security → 2-Step Verification
   - App passwords → Generate
   - Use generated password (16 chars)

3. **Test dari hosting**

   - Some hosting block port 25
   - Use port 587 (TLS) or 465 (SSL)

4. **Alternative: PHP mail()**
   ```env
   email.protocol = mail
   email.fromEmail = noreply@difihome.my.id
   ```

---

### ❌ Problem: Files Upload Failed

**Symptoms**: Can't upload files, permission denied

**Solutions:**

1. **Folder permissions**

   ```bash
   chmod -R 777 /public_html/billing/public_html/uploads
   ```

2. **PHP upload limits di `.htaccess`**

   ```apache
   php_value upload_max_filesize 20M
   php_value post_max_size 25M
   php_value max_execution_time 300
   php_value max_input_time 300
   ```

3. **Check disk space**
   ```
   cPanel → Disk Usage
   If full, delete old logs/cache
   ```

---

### ❌ Problem: CORS Errors

**Symptoms**: Browser console shows "CORS policy" errors

**Solutions:**

1. **Same cookie domain**

   ```env
   # Billing & Customer MUST have same cookie domain
   app.cookieDomain = '.difihome.my.id'
   ```

2. **Add CORS headers di `.htaccess`**

   ```apache
   <IfModule mod_headers.c>
       Header set Access-Control-Allow-Origin "https://difihome.my.id"
       Header set Access-Control-Allow-Credentials "true"
   </IfModule>
   ```

3. **Or in Controller:**
   ```php
   header('Access-Control-Allow-Origin: https://difihome.my.id');
   header('Access-Control-Allow-Credentials: true');
   ```

---

### ❌ Problem: DNS Not Resolving

**Symptoms**: "Site can't be reached"

**Solutions:**

1. **Wait longer**

   - DNS propagation: 5 minutes - 48 hours
   - Usually: 15-30 minutes

2. **Check DNS records**

   ```
   https://dnschecker.org/#A/difihome.my.id
   Should return hosting IP worldwide
   ```

3. **Clear DNS cache (local)**

   ```bash
   # Windows
   ipconfig /flushdns

   # Mac
   sudo dscacheutil -flushcache

   # Linux
   sudo systemd-resolve --flush-caches
   ```

4. **Try different DNS server**
   - Google DNS: 8.8.8.8, 8.8.4.4
   - Cloudflare: 1.1.1.1, 1.0.0.1

---

### ❌ Problem: Customer Dashboard 500 Error

**Symptoms**: Customer portal shows 500, but billing works

**Causes**: Customer dashboard uses shared CodeIgniter from billing

**Solutions:**

1. **Verify ROOTPATH di `/customer/index.php`**

   ```php
   define('ROOTPATH', realpath(__DIR__ . '/../') . DIRECTORY_SEPARATOR);

   // This should point to: /public_html/
   // So billing should be at: /public_html/billing/
   ```

2. **Check file structure**

   ```
   /public_html/
   ├── customer/           ← Customer portal
   └── billing/            ← MUST exist with app/, system/, etc
       ├── app/
       ├── system/
       └── vendor/
   ```

3. **Permissions**
   ```bash
   chmod 644 /public_html/customer/index.php
   chmod 644 /public_html/customer/.htaccess
   ```

---

### ❌ Problem: API Error 500 (api/dashboard/system-info)

**Symptoms**: Error 500 pada endpoint `api/dashboard/system-info` di browser console

**Causes**: Missing database table `lokasi_server` (MikroTik router configuration)

**Solutions:**

1. **Check if table exists**

   ```sql
   -- Via phpMyAdmin
   SHOW TABLES LIKE 'lokasi_server';

   -- If empty result, table tidak ada!
   ```

2. **Create `lokasi_server` table**

   **Via phpMyAdmin** → SQL tab → Run this:

   ```sql
   CREATE TABLE `lokasi_server` (
     `id_lokasi` int(11) NOT NULL AUTO_INCREMENT,
     `name` varchar(255) NOT NULL,
     `ip_router` varchar(255) NOT NULL,
     `username` varchar(100) NOT NULL,
     `password` varchar(255) NOT NULL,
     `port_api` int(11) DEFAULT 8728,
     `address` text,
     `is_connected` tinyint(1) DEFAULT 0,
     `ping_status` varchar(50) DEFAULT 'unknown',
     `last_checked` datetime DEFAULT NULL,
     `lokasi` varchar(255) DEFAULT NULL,
     `jenis_isolir` enum('address-list','simple-queue') DEFAULT 'address-list',
     `local_ip` varchar(100) DEFAULT NULL,
     `remote_url` varchar(255) DEFAULT NULL,
     PRIMARY KEY (`id_lokasi`)
   ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
   ```

3. **Insert default router configuration**

   ```sql
   INSERT INTO `lokasi_server`
   (`name`, `ip_router`, `username`, `password`, `port_api`, `is_connected`)
   VALUES
   ('Main Router', 'your-router-ip-or-hostname', 'admin', 'your-mikrotik-password', 8728, 1);
   ```

   **Replace with your MikroTik credentials:**

   - `your-router-ip-or-hostname`: IP atau hostname MikroTik Anda
   - `your-mikrotik-password`: Password MikroTik

4. **TEMPORARY WORKAROUND: Disable MikroTik features**

   Jika belum punya MikroTik atau belum setup, edit file controller:

   **File**: `/public_html/billing/app/Controllers/Api/Dashboard.php`

   Find method `public function systemInfo()` (around line 100) dan ganti dengan:

   ```php
   public function systemInfo()
   {
       try {
           // Temporary: Return dummy data until MikroTik is configured
           return $this->response->setJSON([
               'status' => 'success',
               'data' => [
                   'board_name' => 'Not Configured',
                   'version' => 'N/A',
                   'cpu_usage' => '0%',
                   'memory_usage' => '0%',
                   'memory_used' => '0 MB',
                   'memory_total' => '0 MB',
                   'disk_usage' => '0%',
                   'uptime' => 'N/A',
                   'router_name' => 'MikroTik Not Configured',
                   'is_fallback' => true,
                   'source' => 'temporary_dummy'
               ]
           ]);
       } catch (\Exception $e) {
           log_message('error', 'System info API error: ' . $e->getMessage());
           return $this->response->setJSON([
               'status' => 'error',
               'message' => 'Failed to get system information',
               'data' => null
           ])->setStatusCode(500);
       }
   }
   ```

5. **Check error logs for details**

   ```
   Location: /public_html/billing/writable/logs/log-[today-date].php

   Look for: "System info API error" or "MikroTik" related errors
   ```

6. **Verify MikroTik connection (if you have MikroTik)**
   - Pastikan MikroTik accessible dari hosting
   - Test ping: `ping your-mikrotik-ip`
   - Test API port: `telnet your-mikrotik-ip 8728`
   - Verify credentials di MikroTik

---

### 📞 Get Help

**If still stuck:**

1. **Check Logs**

   ```
   cPanel → Error Logs (Apache errors)
   /billing/writable/logs/log-[date].php (Application errors)
   Browser Console (F12 → Console tab)
   ```

2. **Common Log Locations**

   ```
   /var/log/apache2/error.log
   /home/[user]/logs/error.log
   /public_html/billing/writable/logs/
   ```

3. **Contact Support**
   - Hosting support untuk server issues
   - CodeIgniter forum untuk app issues
   - Stack Overflow untuk code problems

---

## 🎉 Deployment Complete!

### ✅ Final Checklist

- [ ] All 3 domains accessible via HTTPS
- [ ] SSL certificates installed (green padlock 🔒)
- [ ] Landing page loads correctly
- [ ] Customer portal login works
- [ ] Billing admin login works
- [ ] Database connected
- [ ] Files upload works
- [ ] Emails sending (if configured)
- [ ] All links working
- [ ] No errors in logs
- [ ] Performance acceptable

### 🔗 Your Live URLs

```
🌐 Landing Page:    https://difihome.my.id
🏠 Customer Portal: https://customer.difihome.my.id
💼 Admin Dashboard: https://billing.difihome.my.id
```

### 📊 Performance Optimization (Optional)

**After deployment, consider:**

1. **Enable Gzip Compression** (usually auto in cPanel)
2. **Browser Caching** (already in .htaccess)
3. **CDN** (Cloudflare free tier)
4. **Image Optimization** (compress images)
5. **Minify CSS/JS** (use build tools)

### 🔐 Security Hardening (Recommended)

1. **Change default admin passwords**
2. **Enable 2FA for admin accounts** (if available)
3. **Regular backups** (automate via cPanel)
4. **Monitor login attempts**
5. **Update CodeIgniter regularly**
6. **Keep PHP updated**

### 📈 Monitoring Setup

**Recommended Tools:**

- **Uptime**: UptimeRobot (free)
- **Performance**: Google Analytics
- **Errors**: Sentry.io (error tracking)
- **Backups**: cPanel automatic backups

---

## 📚 Additional Resources

- **CodeIgniter 4 Docs**: https://codeigniter.com/user_guide/
- **cPanel Docs**: https://docs.cpanel.net/
- **Let's Encrypt**: https://letsencrypt.org/
- **SSL Test**: https://www.ssllabs.com/ssltest/

---

**🎊 Selamat! Aplikasi Anda sudah live di production! 🎊**
