@echo off
REM WhatsApp Billing Notification Cron Job for Windows
REM This batch file should be scheduled to run every hour using Windows Task Scheduler

echo %date% %time% - Starting WhatsApp billing notification process...

REM Set PHP executable path (adjust according to your XAMPP installation)
set PHP_PATH=C:\xampp\php\php.exe

REM Set project directory path
set PROJECT_PATH=C:\xampp\htdocs\billingkimo

REM Change to project directory
cd /d "%PROJECT_PATH%"

REM Run the billing notification script via web request
echo Sending WhatsApp billing notifications...
curl -s "https://billing.kimonet.my.id/whatsapp/billing/send-all" > "%PROJECT_PATH%\writable\logs\billing_notification_%date:~-4,4%%date:~-10,2%%date:~-7,2%.log" 2>&1

REM Alternative: Run via CLI (uncomment if you prefer CLI execution)
REM "%PHP_PATH%" public\index.php cli whatsapp-billing send-all

echo %date% %time% - WhatsApp billing notification process completed.

REM Optional: Clean up old log files (keep only last 30 days)
forfiles /p "%PROJECT_PATH%\writable\logs" /s /m billing_notification_*.log /d -30 /c "cmd /c del @path" 2>nul

exit /b 0
