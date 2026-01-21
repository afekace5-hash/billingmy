@echo off
REM Auto Isolir Batch Script untuk Windows Hosting/Server
REM Simpan sebagai: auto_isolir.bat

REM Set variables - GANTI sesuai dengan environment Anda
set "APP_PATH=C:\xampp\htdocs\billingkimo"
set "PHP_PATH=C:\xampp\php\php.exe"
set "LOG_FILE=%APP_PATH%\writable\logs\auto-isolir.log"

REM Create timestamp
for /f "tokens=2 delims==" %%I in ('wmic os get localdatetime /value') do set datetime=%%I
set "timestamp=%datetime:~0,4%-%datetime:~4,2%-%datetime:~6,2% %datetime:~8,2%:%datetime:~10,2%:%datetime:~12,2%"

REM Log start
echo [%timestamp%] Auto Isolir Batch Started >> "%LOG_FILE%"

REM Check if app directory exists
if not exist "%APP_PATH%" (
    echo [%timestamp%] ERROR: Application directory not found: %APP_PATH% >> "%LOG_FILE%"
    exit /b 1
)

REM Change to app directory
cd /d "%APP_PATH%"

REM Check if PHP exists
if not exist "%PHP_PATH%" (
    echo [%timestamp%] ERROR: PHP not found at: %PHP_PATH% >> "%LOG_FILE%"
    exit /b 1
)

REM Create logs directory if not exists
if not exist "%APP_PATH%\writable\logs" (
    mkdir "%APP_PATH%\writable\logs"
)

REM Run auto isolir
echo [%timestamp%] Running auto isolir command... >> "%LOG_FILE%"

"%PHP_PATH%" spark auto:isolir >> "%LOG_FILE%" 2>&1

if %ERRORLEVEL% equ 0 (
    echo [%timestamp%] Auto isolir completed successfully >> "%LOG_FILE%"
) else (
    echo [%timestamp%] ERROR: Auto isolir command failed with code %ERRORLEVEL% >> "%LOG_FILE%"
    exit /b 1
)

REM Alternative: Use cron_simple.php if spark doesn't work
REM Uncomment the lines below and comment the spark command above
REM "%PHP_PATH%" cron_simple.php >> "%LOG_FILE%" 2>&1

echo [%timestamp%] Auto Isolir Batch Finished >> "%LOG_FILE%"

REM Clean old logs (optional)
forfiles /p "%APP_PATH%\writable\logs" /s /m *.log /d -30 /c "cmd /c del @path" 2>nul

exit /b 0
