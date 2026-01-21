@echo off
REM =================================================================
REM Auto Invoice Generation Setup Script for Windows
REM Created: July 6, 2025
REM Purpose: Setup Windows Task Scheduler for monthly invoice generation
REM =================================================================

echo ========================================
echo  AUTO INVOICE GENERATION SETUP
echo ========================================
echo.
echo This script will help you setup automatic invoice generation
echo that runs every 1st day of each month at 12:01 AM
echo.

REM Get current directory
set "CURRENT_DIR=%~dp0"
set "CURRENT_DIR=%CURRENT_DIR:~0,-1%"

echo Current application directory: %CURRENT_DIR%
echo.

REM Test the command first
echo [1/4] Testing auto-generate command...
echo.
cd /d "%CURRENT_DIR%"
php spark invoices:autogenerate
if %ERRORLEVEL% neq 0 (
    echo.
    echo ❌ ERROR: Command test failed!
    echo Please check:
    echo - PHP is installed and in PATH
    echo - Application files are complete
    echo - Database connection is working
    echo.
    pause
    exit /b 1
)

echo.
echo ✅ Command test successful!
echo.

REM Create the task using schtasks command
echo [2/4] Creating Windows Task Scheduler entry...
echo.

schtasks /create /tn "Auto Generate Monthly Invoice" /tr "\"%CURRENT_DIR%\writable\cron-generate-invoices.bat\"" /sc monthly /d 1 /st 00:01 /ru "SYSTEM" /f

if %ERRORLEVEL% equ 0 (
    echo ✅ Task Scheduler entry created successfully!
) else (
    echo ❌ Failed to create Task Scheduler entry.
    echo Please run this script as Administrator.
    echo.
    pause
    exit /b 1
)

echo.
echo [3/4] Setting additional task properties...

REM Set additional properties for better reliability
schtasks /change /tn "Auto Generate Monthly Invoice" /enable
schtasks /change /tn "Auto Generate Monthly Invoice" /ru "SYSTEM"

echo.
echo [4/4] Testing the scheduled task...
echo.

REM Ask user if they want to test run
set /p TEST_RUN="Do you want to test run the task now? (y/n): "
if /i "%TEST_RUN%"=="y" (
    echo Running test...
    schtasks /run /tn "Auto Generate Monthly Invoice"
    
    echo.
    echo Waiting for task to complete...
    timeout /t 10 /nobreak >nul
    
    echo.
    echo Check the log file for results:
    echo %CURRENT_DIR%\writable\logs\
    echo.
)

echo.
echo ========================================
echo           SETUP COMPLETE!
echo ========================================
echo.
echo ✅ Auto invoice generation is now scheduled to run:
echo    📅 Every 1st day of the month
echo    🕐 At 12:01 AM (00:01)
echo    💻 As SYSTEM user
echo.
echo 📋 Task Name: "Auto Generate Monthly Invoice"
echo 📂 Log Location: %CURRENT_DIR%\writable\logs\
echo.
echo To manage the task:
echo 1. Open Task Scheduler (taskschd.msc)
echo 2. Find "Auto Generate Monthly Invoice"
echo 3. Right-click to Edit, Run, or Delete
echo.
echo To test manually:
echo cd "%CURRENT_DIR%"
echo php spark invoices:autogenerate
echo.
echo ⚠️  IMPORTANT NOTES:
echo - Make sure your computer is on during the 1st of each month
echo - Check logs regularly to ensure it's working
echo - Test the system before the next month
echo.

pause
exit /b 0
