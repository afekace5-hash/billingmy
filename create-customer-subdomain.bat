@echo off
echo ================================================
echo CREATE CUSTOMER PORTAL SUBDOMAIN FILES
echo ================================================
echo.

set SUBDOMAIN_DIR=customer_portal

echo [1/5] Membuat folder customer_portal...
if not exist "%SUBDOMAIN_DIR%" mkdir "%SUBDOMAIN_DIR%"
if not exist "%SUBDOMAIN_DIR%\public" mkdir "%SUBDOMAIN_DIR%\public"
if not exist "%SUBDOMAIN_DIR%\public\assets" mkdir "%SUBDOMAIN_DIR%\public\assets"
echo Done.

echo.
echo [2/5] Membuat index.php untuk subdomain...
(
echo ^<?php
echo.
echo /**
echo  * Customer Portal Entry Point
echo  * Subdomain: customer.domain.com
echo  */
echo.
echo // Load environment dari file terpisah
echo if ^(file_exists^(__DIR__ . '/.env.customer'^)^) {
echo     $envFile = __DIR__ . '/.env.customer';
echo } else {
echo     $envFile = __DIR__ . '/../.env'; // Fallback ke root
echo }
echo.
echo // Set paths
echo define^('CUSTOMER_PORTAL', true^);
echo $pathsConfig = ROOTPATH . 'app/Config/Paths.php';
echo require realpath^($pathsConfig^) ?: $pathsConfig;
echo.
echo // Define root path ^(parent directory^)
echo define^('ROOTPATH', realpath^(__DIR__ . '/../'^) . DIRECTORY_SEPARATOR^);
echo.
echo // Load CodeIgniter paths
echo $paths = new Config\Paths^(^);
echo.
echo // Location of the framework bootstrap file
echo $bootstrap = rtrim^($paths-^>systemDirectory, '\\/ '^) . DIRECTORY_SEPARATOR . 'bootstrap.php';
echo require realpath^($bootstrap^) ?: $bootstrap;
echo.
echo // Load environment variables
echo require_once SYSTEMPATH . 'Config/DotEnv.php';
echo ^(new CodeIgniter\Config\DotEnv^(ROOTPATH, $envFile^)^)-^>load^(^);
echo.
echo // Grab our CodeIgniter instance
echo $app = Config\Services::codeigniter^(^);
echo $app-^>initialize^(^);
echo.
echo // Override routes untuk customer portal only
echo $routes = service^('routes'^);
echo $routes-^>setDefaultNamespace^('App\Controllers'^);
echo $routes-^>setDefaultController^('CustomerDashboard'^);
echo $routes-^>setDefaultMethod^('index'^);
echo.
echo // Run!
echo $app-^>run^(^);
) > "%SUBDOMAIN_DIR%\index.php"
echo Done.

echo.
echo [3/5] Membuat .htaccess untuk subdomain...
(
echo # Customer Portal - Subdomain Configuration
echo.
echo ^<IfModule mod_rewrite.c^>
echo     RewriteEngine On
echo     
echo     # Redirect to index.php
echo     RewriteCond %%{REQUEST_FILENAME} !-f
echo     RewriteCond %%{REQUEST_FILENAME} !-d
echo     RewriteRule ^^(.*)$ index.php/$1 [L]
echo ^</IfModule^>
echo.
echo # Disable directory browsing
echo Options -Indexes
echo.
echo # Prevent access to hidden files
echo ^<FilesMatch "^^\.">
echo     Order allow,deny
echo     Deny from all
echo ^</FilesMatch^>
echo.
echo # Security headers
echo ^<IfModule mod_headers.c^>
echo     Header set X-Content-Type-Options "nosniff"
echo     Header set X-Frame-Options "SAMEORIGIN"
echo     Header set X-XSS-Protection "1; mode=block"
echo ^</IfModule^>
) > "%SUBDOMAIN_DIR%\.htaccess"
echo Done.

echo.
echo [4/5] Membuat .env.customer...
(
echo # CUSTOMER PORTAL ENVIRONMENT
echo # Subdomain: customer.domain.com
echo.
echo #--------------------------------------------------------------------
echo # ENVIRONMENT
echo #--------------------------------------------------------------------
echo.
echo CI_ENVIRONMENT = production
echo.
echo #--------------------------------------------------------------------
echo # APP
echo #--------------------------------------------------------------------
echo.
echo app.baseURL = 'https://customer.yourdomain.com/'
echo # app.forceGlobalSecureRequests = true
echo.
echo app.sessionDriver = 'CodeIgniter\Session\Handlers\FileHandler'
echo app.sessionCookieName = 'customer_session'
echo app.sessionExpiration = 7200
echo app.sessionSavePath = NULL
echo app.sessionMatchIP = false
echo app.sessionTimeToUpdate = 300
echo app.sessionRegenerateDestroy = false
echo.
echo app.cookiePrefix = 'customer_'
echo app.cookieDomain = '.yourdomain.com'
echo app.cookiePath = '/'
echo app.cookieSecure = true
echo app.cookieHTTPOnly = true
echo.
echo #--------------------------------------------------------------------
echo # DATABASE - GUNAKAN DATABASE YANG SAMA DENGAN ADMIN
echo #--------------------------------------------------------------------
echo.
echo database.default.hostname = localhost
echo database.default.database = your_database
echo database.default.username = your_username  
echo database.default.password = your_password
echo database.default.DBDriver = MySQLi
echo database.default.DBPrefix =
echo database.default.port = 3306
echo.
echo #--------------------------------------------------------------------
echo # CUSTOMER PORTAL SPECIFIC
echo #--------------------------------------------------------------------
echo.
echo customer.enableRegistration = false
echo customer.requireEmailVerification = true
echo customer.maxLoginAttempts = 5
echo customer.loginCooldown = 300
echo.
echo #--------------------------------------------------------------------
echo # PAYMENT GATEWAY - SAMA DENGAN ADMIN
echo #--------------------------------------------------------------------
echo.
echo # Copy dari .env utama
) > "%SUBDOMAIN_DIR%\.env.customer"
echo Done.

echo.
echo [5/5] Membuat README...
(
echo # Customer Portal Subdomain
echo.
echo ## Upload ke Hosting
echo.
echo 1. Upload folder `customer_portal/` ke root server
echo 2. Edit `.env.customer` dengan credentials hosting
echo 3. Setup subdomain di cPanel:
echo    - Subdomain: customer
echo    - Document Root: /home/username/customer_portal
echo 4. Akses: https://customer.yourdomain.com
echo.
echo ## File Structure
echo ```
echo customer_portal/
echo ├── index.php           # Entry point
echo ├── .htaccess          # Rewrite rules
echo ├── .env.customer      # Environment
echo └── public/            # Assets
echo ```
echo.
echo ## Shared dengan Admin
echo - Database: Same database
echo - App folder: ../app/ 
echo - System folder: ../vendor/
echo - Routes: Filter customer-portal/*
) > "%SUBDOMAIN_DIR%\README.md"
echo Done.

echo.
echo ================================================
echo SELESAI!
echo ================================================
echo.
echo Folder "customer_portal" sudah dibuat dengan:
echo - index.php
echo - .htaccess  
echo - .env.customer
echo - README.md
echo.
echo NEXT STEPS:
echo 1. Edit customer_portal\.env.customer
echo 2. Upload folder customer_portal ke hosting
echo 3. Setup subdomain di cPanel
echo 4. Test akses: customer.yourdomain.com
echo.
pause
