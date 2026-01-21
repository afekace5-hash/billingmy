@echo off
REM Jalankan script generate invoice otomatis setiap tanggal 1
REM Pastikan path PHP dan spark sudah benar

cd /d %~dp0..

REM Generate untuk bulan berjalan jika belum ada
echo Generating invoices for current month...
php spark invoices:autogenerate %date:~0,4%-%date:~5,2%

REM Generate untuk bulan depan (otomatis pada tanggal 1)
echo Generating invoices for next month...
php spark invoices:autogenerate

REM Log hasil ke file
echo %date% %time% - Invoice generation completed >> writable\logs\invoice-generation.log

pause
