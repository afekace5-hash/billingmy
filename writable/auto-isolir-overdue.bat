@echo off
REM ===============================================
REM Auto Isolir Overdue Customers - Billing System
REM ===============================================

echo Starting Auto Isolir Process...
echo Date: %date% %time%
echo.

REM Change to the application directory
cd /d "c:\xampp\htdocs\billingkimo"

REM Run the auto isolir command
echo Running auto isolir for customers overdue more than 7 days...
php spark auto:isolir-overdue 7

echo.
echo Auto Isolir Process Completed at %date% %time%
echo.

REM Optional: Log the execution
echo %date% %time% - Auto Isolir Executed >> writable\logs\auto-isolir.log

pause