@echo off
echo ================================================
echo CLEAN PROJECT UNTUK UPLOAD KE HOSTING
echo ================================================
echo.

echo [1/5] Membersihkan writable/logs...
del /q "writable\logs\*.log" 2>nul
echo Done.

echo [2/5] Membersihkan writable/cache...
for /d %%p in ("writable\cache\*") do rmdir "%%p" /s /q 2>nul
del /q "writable\cache\*" 2>nul
echo Done.

echo [3/5] Membersihkan writable/session...
del /q "writable\session\*" 2>nul
echo Done.

echo [4/5] Membersihkan writable/debugbar...
for /d %%p in ("writable\debugbar\*") do rmdir "%%p" /s /q 2>nul
del /q "writable\debugbar\*" 2>nul
echo Done.

echo [5/5] Membuat folder uploads jika belum ada...
if not exist "public\uploads" mkdir "public\uploads"
if not exist "writable\uploads" mkdir "writable\uploads"
echo Done.

echo.
echo ================================================
echo SELESAI!
echo ================================================
echo.
echo Folder sudah bersih dan siap untuk di-upload.
echo.
echo CATATAN:
echo - Jangan lupa edit file .env sebelum upload
echo - Set CI_ENVIRONMENT = production
echo - Ganti database credentials
echo.
echo Tekan tombol apa saja untuk keluar...
pause >nul
