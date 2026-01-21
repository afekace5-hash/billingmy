@echo off
REM Auto Isolir dengan VPN Configuration Script
REM Script ini akan mengatur koneksi VPN yang berbeda untuk setiap lokasi MikroTik

REM Set variables - GANTI sesuai dengan environment Anda
set "APP_PATH=C:\xampp\htdocs\billingkimo"
set "PHP_PATH=C:\xampp\php\php.exe"
set "LOG_FILE=%APP_PATH%\writable\logs\auto-isolir-vpn.log"
set "VPN_CONFIG_PATH=%APP_PATH%\vpn-configs"
set "OPENVPN_PATH=C:\Program Files\OpenVPN\bin\openvpn.exe"

REM Create timestamp
for /f "tokens=2 delims==" %%I in ('wmic os get localdatetime /value') do set datetime=%%I
set "timestamp=%datetime:~0,4%-%datetime:~4,2%-%datetime:~6,2% %datetime:~8,2%:%datetime:~10,2%:%datetime:~12,2%"

REM Log start
echo [%timestamp%] Auto Isolir VPN Script Started >> "%LOG_FILE%"

REM Check directories
if not exist "%APP_PATH%" (
    echo [%timestamp%] ERROR: Application directory not found: %APP_PATH% >> "%LOG_FILE%"
    exit /b 1
)

if not exist "%VPN_CONFIG_PATH%" (
    echo [%timestamp%] Creating VPN config directory... >> "%LOG_FILE%"
    mkdir "%VPN_CONFIG_PATH%"
)

REM Change to app directory
cd /d "%APP_PATH%"

REM Check if OpenVPN exists
if not exist "%OPENVPN_PATH%" (
    echo [%timestamp%] WARNING: OpenVPN not found at: %OPENVPN_PATH% >> "%LOG_FILE%"
    echo [%timestamp%] Please install OpenVPN or update the path >> "%LOG_FILE%"
)

REM Create logs directory if not exists
if not exist "%APP_PATH%\writable\logs" (
    mkdir "%APP_PATH%\writable\logs"
)

REM ===============================
REM 1. Setup VPN Connections for each router
REM ===============================
echo [%timestamp%] Setting up VPN connections for routers... >> "%LOG_FILE%"

REM Get active router configurations from database
"%PHP_PATH%" -r "
require_once 'vendor/autoload.php';
$config = new \Config\Database();
$db = \Config\Database::connect();

// Get all active routers with VPN config
$query = $db->query('SELECT id_lokasi, name, ip_router, vpn_config, vpn_profile FROM lokasi_server WHERE status = \"active\" AND vpn_config IS NOT NULL');
$routers = $query->getResultArray();

foreach($routers as $router) {
    echo \"Router ID: {$router['id_lokasi']}, Name: {$router['name']}, VPN: {$router['vpn_config']}\n\";
}
" >> "%LOG_FILE%" 2>&1

REM ===============================
REM 2. Connect VPN for each location
REM ===============================
echo [%timestamp%] Connecting VPN for each location... >> "%LOG_FILE%"

REM Router Location 1 - Main Office
call :ConnectVPN "location1" "id-14.hostddns.us.ovpn" "12"

REM Router Location 2 - Branch Office
call :ConnectVPN "location2" "branch-vpn.ovpn" "13"

REM Router Location 3 - Remote Site
call :ConnectVPN "location3" "remote-vpn.ovpn" "14"

REM ===============================
REM 3. Wait for VPN connections to establish
REM ===============================
echo [%timestamp%] Waiting for VPN connections to establish... >> "%LOG_FILE%"
timeout /t 15 /nobreak > nul

REM ===============================
REM 4. Run Auto Isolir with VPN routing
REM ===============================
echo [%timestamp%] Running auto isolir with VPN routing... >> "%LOG_FILE%"

"%PHP_PATH%" spark auto:isolir:vpn >> "%LOG_FILE%" 2>&1

if %ERRORLEVEL% equ 0 (
    echo [%timestamp%] Auto isolir VPN completed successfully >> "%LOG_FILE%"
) else (
    echo [%timestamp%] ERROR: Auto isolir VPN command failed with code %ERRORLEVEL% >> "%LOG_FILE%"
)

REM ===============================
REM 5. Cleanup - Disconnect VPNs (optional)
REM ===============================
echo [%timestamp%] Cleaning up VPN connections... >> "%LOG_FILE%"
call :DisconnectAllVPNs

echo [%timestamp%] Auto Isolir VPN Script Finished >> "%LOG_FILE%"

REM Clean old logs (optional)
forfiles /p "%APP_PATH%\writable\logs" /s /m *vpn*.log /d -7 /c "cmd /c del @path" 2>nul

exit /b 0

REM ===============================
REM FUNCTIONS
REM ===============================

:ConnectVPN
set "vpn_name=%~1"
set "vpn_config=%~2"
set "router_id=%~3"

echo [%timestamp%] Connecting VPN: %vpn_name% using config: %vpn_config% >> "%LOG_FILE%"

REM Check if VPN config file exists
if not exist "%VPN_CONFIG_PATH%\%vpn_config%" (
    echo [%timestamp%] WARNING: VPN config not found: %VPN_CONFIG_PATH%\%vpn_config% >> "%LOG_FILE%"
    goto :eof
)

REM Kill existing VPN connection with same name
taskkill /f /im openvpn.exe /fi "WINDOWTITLE eq %vpn_name%*" 2>nul

REM Start VPN connection in background
start /min "" "%OPENVPN_PATH%" --config "%VPN_CONFIG_PATH%\%vpn_config%" --log "%APP_PATH%\writable\logs\vpn-%vpn_name%.log"

echo [%timestamp%] VPN %vpn_name% connection initiated >> "%LOG_FILE%"
goto :eof

:DisconnectAllVPNs
echo [%timestamp%] Disconnecting all VPN connections... >> "%LOG_FILE%"
taskkill /f /im openvpn.exe 2>nul
echo [%timestamp%] All VPN connections terminated >> "%LOG_FILE%"
goto :eof