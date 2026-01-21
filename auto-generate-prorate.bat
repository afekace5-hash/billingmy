@echo off
REM Auto Generate Prorate - Run this daily via Task Scheduler

cd /d "%~dp0"

echo ========================================
echo Auto Generate Prorate
echo ========================================
echo.

php spark prorate:generate

echo.
echo ========================================
echo Process completed at %date% %time%
echo ========================================

pause
