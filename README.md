# 🚀 BillingKimo - MikroTik Billing System

![PHP](https://img.shields.io/badge/PHP-8.0+-blue)
![CodeIgniter](https://img.shields.io/badge/CodeIgniter-4.x-orange)
![License](https://img.shields.io/badge/License-MIT-green)
![Status](https://img.shields.io/badge/Status-Production%20Ready-brightgreen)

Sistem billing lengkap untuk ISP/Warnet dengan integrasi MikroTik RouterOS, WhatsApp notifications, dan payment gateway.

## ✨ Features

### 🏢 Management System

- **Customer Management** - Kelola data pelanggan dengan lengkap
- **Package Management** - Paket internet dengan berbagai pilihan
- **Invoice Generation** - Generate invoice otomatis bulanan
- **Payment Tracking** - Tracking pembayaran dengan multiple payment methods

### 🌐 MikroTik Integration

- **Router Management** - Kelola multiple MikroTik router
- **PPPoE Secret Management** - Sinkronisasi user PPPoE
- **Bandwidth Control** - Atur bandwidth secara otomatis
- **Monitoring** - Real-time monitoring koneksi dan resource
- **Auto Isolation** - Isolir otomatis customer yang nunggak

### 💬 Communication

- **WhatsApp Integration** - Notifikasi via WhatsApp API
- **Auto Notifications** - Reminder pembayaran otomatis
- **Broadcast Messages** - Kirim pengumuman ke semua customer

### 💳 Payment Gateway

- **Xendit Integration** - Payment gateway lokal Indonesia
- **Multiple Methods** - Bank transfer, e-wallet, virtual account
- **Auto Verification** - Verifikasi pembayaran otomatis

### 🔧 System Features

- **Auto Updates** - Update system via Git integration
- **Backup System** - Backup otomatis database dan files
- **Log Management** - Comprehensive logging system
- **Security** - CSRF protection, input validation
- **Responsive UI** - Mobile-friendly interface

## 🛠️ Tech Stack

- **Backend**: CodeIgniter 4 (PHP)
- **Database**: MySQL/MariaDB
- **Frontend**: Bootstrap 5, jQuery
- **APIs**: MikroTik API, WhatsApp API, Xendit API
- **Deployment**: Git, Composer

## 📋 Requirements

### System Requirements

```
PHP >= 7.4 (Recommended: PHP 8.0+)
MySQL/MariaDB >= 5.7
Apache/Nginx Web Server
Composer
Git (optional, for auto-updates)
```

### PHP Extensions

```
php-curl
php-intl
php-mbstring
php-json
php-mysqlnd
php-xml
php-zip
```

## 🚀 Quick Installation

### 1. Clone Repository

```bash
git clone https://github.com/AFIK35/billingkimo.git
cd billingkimo
```

### 2. Install Dependencies

```bash
composer install
```

### 3. Configuration

```bash
cp .env.production.example .env
# Edit .env file with your settings
```

### 4. Set Permissions

```bash
chmod -R 755 writable/
chmod -R 755 public/
```

### 5. Database Setup

```bash
# Import database or run migrations
mysql -u user -p database_name < database_backup.sql
```

### 6. Web Server Configuration

Point your web server document root to the `public/` folder.

## 📖 Detailed Documentation

- [📚 Complete Documentation](DOCUMENTATION.md) - Comprehensive system documentation including:
  - Installation & Configuration
  - Payment Gateway Integration (Flip, Midtrans, Duitku)
  - Auto Generate Invoice System
  - Auto Isolir MikroTik Integration
  - WhatsApp Notification Setup
  - Customer Dashboard
  - CRON Job Setup for Hosting
  - API Documentation
  - Troubleshooting Guide

## 🔧 Configuration

### Environment Variables

Key configurations in `.env` file:

```bash
# Database
database.default.hostname = localhost
database.default.database = billingku
database.default.username = root
database.default.password =

# APIs
XENDIT_API_KEY = xnd_development_xxx
WHATSAPP_API_KEY = your_whatsapp_key
WHATSAPP_SENDER = 628xxxxxxxxx
WHATSAPP_BASE_URL = https://api.whatsapp.com

# Security
security.tokenName = csrf_interneter
encryption.key = your-32-character-secret-key
```

## 🔒 Security Features

- CSRF Protection
- SQL Injection Prevention
- XSS Protection
- Input Validation & Sanitization
- Session Security
- File Upload Security
- IP Whitelisting for Admin Functions

## 🤝 Contributing

1. Fork the repository
2. Create your feature branch (`git checkout -b feature/AmazingFeature`)
3. Commit your changes (`git commit -m 'Add some AmazingFeature'`)
4. Push to the branch (`git push origin feature/AmazingFeature`)
5. Open a Pull Request

## 📝 License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

## 🙏 Acknowledgments

- [CodeIgniter 4](https://codeigniter.com/) - The PHP framework
- [MikroTik RouterOS](https://mikrotik.com/) - Router management
- [Xendit](https://xendit.co/) - Payment gateway
- [Bootstrap](https://getbootstrap.com/) - UI framework

## 📞 Support

- **GitHub Issues**: [Create an issue](https://github.com/AFIK35/billingkimo/issues)
- **Documentation**: [Wiki](https://github.com/AFIK35/billingkimo/wiki)

---

⭐ **Star this repository if you find it helpful!**

Made with ❤️ by [AFIK35](https://github.com/AFIK35)

---
