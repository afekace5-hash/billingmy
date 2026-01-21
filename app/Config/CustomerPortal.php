<?php

/**
 * Customer Portal Configuration
 * File: app/Config/CustomerPortal.php
 * 
 * Konfigurasi khusus untuk customer portal subdomain
 */

namespace App\Config;

use CodeIgniter\Config\BaseConfig;

class CustomerPortal extends BaseConfig
{
    /**
     * Enable customer portal
     */
    public bool $enabled = true;

    /**
     * Customer portal base URL (akan override di subdomain)
     */
    public string $baseURL = '';

    /**
     * Session configuration untuk customer
     */
    public string $sessionName = 'customer_session';
    public string $sessionPrefix = 'customer_';
    public int $sessionExpiration = 7200; // 2 hours

    /**
     * Cookie configuration untuk customer
     */
    public string $cookiePrefix = 'customer_';
    public string $cookieDomain = '';
    public string $cookiePath = '/';
    public bool $cookieSecure = true;
    public bool $cookieHTTPOnly = true;

    /**
     * Registration settings
     */
    public bool $enableRegistration = false;
    public bool $requireEmailVerification = true;
    public bool $requirePhoneVerification = false;

    /**
     * Login settings
     */
    public int $maxLoginAttempts = 5;
    public int $loginCooldownSeconds = 300; // 5 minutes
    public bool $allowLoginByEmail = true;
    public bool $allowLoginByPhone = true;
    public bool $allowLoginByServiceNumber = true;

    /**
     * Password settings (jika menggunakan password)
     */
    public bool $requirePassword = false; // Set false untuk login tanpa password
    public int $minPasswordLength = 6;
    public bool $requireStrongPassword = false;

    /**
     * Two-factor authentication
     */
    public bool $enable2FA = false;
    public string $twoFactorMethod = 'whatsapp'; // whatsapp, email, sms

    /**
     * Dashboard features
     */
    public bool $showInvoices = true;
    public bool $showPaymentHistory = true;
    public bool $showUsageStats = false;
    public bool $allowDownloadInvoice = true;
    public bool $allowPaymentOnline = true;

    /**
     * Payment gateway
     */
    public bool $enableMidtrans = false;
    public bool $enableDuitku = true;
    public bool $enableManualTransfer = true;

    /**
     * Customer support
     */
    public string $supportWhatsApp = '';
    public string $supportEmail = '';
    public string $supportPhone = '';

    /**
     * Rate limiting
     */
    public bool $enableRateLimit = true;
    public int $maxRequestsPerMinute = 60;
    public int $maxRequestsPerHour = 1000;

    /**
     * Branding
     */
    public string $portalTitle = 'Customer Portal';
    public string $portalLogo = '';
    public string $portalFavicon = '';
    public string $primaryColor = '#556ee6';
    public string $secondaryColor = '#74788d';

    /**
     * Maintenance mode
     */
    public bool $maintenanceMode = false;
    public string $maintenanceMessage = 'Portal sedang dalam pemeliharaan. Silakan coba lagi nanti.';
    public array $maintenanceAllowedIPs = [];

    /**
     * Logging
     */
    public bool $logCustomerLogin = true;
    public bool $logCustomerActivity = true;
    public bool $logPaymentAttempts = true;

    /**
     * Cache settings
     */
    public bool $enableCache = true;
    public int $cacheDuration = 300; // 5 minutes
}
