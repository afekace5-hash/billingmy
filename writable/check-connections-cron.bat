@echo off
REM Batch file untuk cek koneksi server otomatis setiap jam
REM Simpan file ini sebagai check-connections-cron.bat

cd /d "c:\xampp\htdocs\billingkimo"

REM Log dengan timestamp
echo [%date% %time%] Starting connection check... >> writable/logs/connection-check.log

REM Jalankan command untuk cek koneksi
php spark check:connections >> writable/logs/connection-check.log 2>&1

REM Log completion
echo [%date% %time%] Connection check completed. >> writable/logs/connection-check.log
echo. >> writable/logs/connection-check.log
