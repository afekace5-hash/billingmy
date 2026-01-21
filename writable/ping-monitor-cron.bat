@echo off
REM Ping monitoring cron job - runs every 5 minutes
REM Add this to Windows Task Scheduler to run every 5 minutes

cd /d "c:\xampp\htdocs\billingkimo"
php spark monitor:ping >> "writable/logs/ping-monitor.log" 2>&1

REM Log the execution
echo [%date% %time%] Ping monitoring executed >> "writable/logs/cron-ping.log"
