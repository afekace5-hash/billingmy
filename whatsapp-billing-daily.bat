@echo off
REM ========================================
REM WhatsApp Billing Notifications - Daily
REM ========================================
REM Run this via Windows Task Scheduler
REM Recommended: Daily at 09:00 AM
REM ========================================

echo.
echo ╔═══════════════════════════════════════════════════════╗
echo ║   WhatsApp Billing Notification - Auto Runner        ║
echo ╚═══════════════════════════════════════════════════════╝
echo.

cd /d "C:\laragon\www\billingkimo"

echo [%date% %time%] Starting WhatsApp Billing Notifications...
echo.

REM Run the command
php spark whatsapp:billing:send-all

REM Log the execution
echo [%date% %time%] Completed WhatsApp Billing Notifications >> writable\logs\billing-notifications.log

echo.
echo ✅ Done! Check writable\logs\ for details.
echo.

REM Optional: Keep window open for 5 seconds to see results
timeout /t 5 /nobreak >nul
