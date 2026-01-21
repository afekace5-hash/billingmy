@echo off
REM Setup Advanced Scheduled Task untuk Auto Generate Invoice
REM Run as Administrator

echo ============================================================
echo    SETUP SCHEDULED TASK - KIMONET BILLING AUTO INVOICE
echo ============================================================

set TASK_NAME="KimonetBilling_AutoInvoice"
set SCRIPT_PATH="%CD%\writable\advanced-auto-invoice.bat"

echo.
echo Task Name: %TASK_NAME%
echo Script Path: %SCRIPT_PATH%
echo.

REM Delete existing task if exists
echo Removing existing task (if any)...
schtasks /delete /tn %TASK_NAME% /f >nul 2>&1

REM Create new scheduled task
echo Creating new scheduled task...

schtasks /create ^
    /tn %TASK_NAME% ^
    /tr "%SCRIPT_PATH% silent" ^
    /sc daily ^
    /st 06:00 ^
    /ru "SYSTEM" ^
    /rl HIGHEST ^
    /f

if %errorlevel% equ 0 (
    echo.
    echo ✓ SUCCESS: Scheduled task berhasil dibuat!
    echo.
    echo DETAIL TASK:
    echo - Nama: %TASK_NAME%
    echo - Waktu: Setiap hari jam 06:00
    echo - Script: %SCRIPT_PATH%
    echo - User: SYSTEM
    echo - Priority: HIGHEST
    echo.
    echo INFORMASI TAMBAHAN:
    echo • Task akan generate invoice otomatis setiap hari
    echo • Pada tanggal 28-31 dan 1-3, akan generate untuk bulan depan juga
    echo • Log tersimpan di: writable\logs\auto-invoice.log
    echo • Untuk melihat hasil: schtasks /query /tn %TASK_NAME%
    echo • Untuk menjalankan manual: schtasks /run /tn %TASK_NAME%
    echo • Untuk hapus task: schtasks /delete /tn %TASK_NAME% /f
    echo.
    echo CARA TEST:
    echo 1. Jalankan manual: schtasks /run /tn %TASK_NAME%
    echo 2. Atau langsung: %SCRIPT_PATH%
    echo 3. Cek log di: writable\logs\auto-invoice.log
    echo.
) else (
    echo.
    echo ✗ FAILED: Gagal membuat scheduled task
    echo.
    echo TROUBLESHOOTING:
    echo 1. Pastikan menjalankan sebagai Administrator
    echo 2. Pastikan path script benar: %SCRIPT_PATH%
    echo 3. Coba jalankan manual terlebih dahulu
    echo.
)

echo ============================================================
pause