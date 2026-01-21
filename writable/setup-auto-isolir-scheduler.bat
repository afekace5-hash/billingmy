@echo off
REM ===============================================
REM Setup Auto Isolir Windows Task Scheduler
REM ===============================================

echo Setting up Windows Task Scheduler for Auto Isolir...
echo.

REM Create a scheduled task to run auto isolir daily at 2 AM
schtasks /create /tn "BillingKimo-AutoIsolir" /tr "c:\xampp\htdocs\billingkimo\writable\auto-isolir-overdue.bat" /sc daily /st 02:00 /f

if %errorlevel% equ 0 (
    echo.
    echo ✓ Successfully created scheduled task: BillingKimo-AutoIsolir
    echo   - Runs daily at 2:00 AM
    echo   - Isolates customers overdue more than 7 days
    echo.
    echo To modify the schedule, use Windows Task Scheduler or:
    echo schtasks /change /tn "BillingKimo-AutoIsolir" /st [NEW_TIME]
    echo.
    echo To delete the task:
    echo schtasks /delete /tn "BillingKimo-AutoIsolir" /f
) else (
    echo.
    echo ✗ Failed to create scheduled task
    echo Please run this script as Administrator
)

echo.
echo Current scheduled tasks for BillingKimo:
schtasks /query /tn "BillingKimo*"

echo.
pause