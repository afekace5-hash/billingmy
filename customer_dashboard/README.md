# Customer Dashboard - Subdomain Setup

## 📁 Folder ini untuk Customer Portal Subdomain

### Akses URL

- **Subdomain**: `customer-portal.yourdomain.com`
- **Folder**: `customer_dashboard`

### Setup di Hosting

#### cPanel

1. Login cPanel → **Subdomains**
2. Subdomain: `customer-portal`
3. Document Root: `/home/username/customer_dashboard`
4. Klik Create
5. Aktifkan SSL (Let's Encrypt)

#### aaPanel

1. Website → Add Site
2. Domain: `customer-portal.yourdomain.com`
3. Root: `/www/wwwroot/customer_dashboard`
4. Enable SSL

### Konfigurasi DNS

```
Type: A
Name: customer-portal
Value: [IP Server Anda]
```

### File Penting

- `index.php` - Entry point
- `.htaccess` - Rewrite rules
- `.env.customer` - Environment config
- `public/` - Assets (CSS, JS, images)

### Setelah Upload

1. Edit `.env.customer`:
   - Sesuaikan `app.baseURL`
   - Sesuaikan database credentials
   - Generate `encryption.key` baru
2. Set permissions: `chmod 755`
3. Test akses: `https://customer-portal.yourdomain.com`

### Keamanan

✅ Session terpisah dari admin
✅ Cookie domain berbeda
✅ Rate limiting independen
✅ Environment config terpisah
