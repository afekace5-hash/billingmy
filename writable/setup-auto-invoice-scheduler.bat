@echo off
REM Setup scheduled task untuk auto generate invoice setiap tanggal 1
REM Run as Administrator

echo Setting up automatic invoice generation...

REM Buat scheduled task untuk generate invoice setiap tanggal 1 jam 08:00
schtasks /create /tn "BillingKimo_AutoGenerateInvoices" /tr "C:\xampp\htdocs\billingkimo\writable\cron-generate-invoices.bat" /sc monthly /d 1 /st 08:00 /f

if %errorlevel% equ 0 (
    echo ✓ Scheduled task berhasil dibuat!
    echo ✓ Invoice akan otomatis di-generate setiap tanggal 1 jam 08:00
    echo.
    echo Untuk melihat task yang telah dibuat:
    echo schtasks /query /tn "BillingKimo_AutoGenerateInvoices"
    echo.
    echo Untuk hapus task:
    echo schtasks /delete /tn "BillingKimo_AutoGenerateInvoices" /f
) else (
    echo ✗ Gagal membuat scheduled task
    echo Pastikan Anda menjalankan script ini sebagai Administrator
)

pause