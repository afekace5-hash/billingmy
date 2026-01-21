@echo off
REM ========================================
REM Generate Secret Key untuk WHATSAPP_CRON_SECRET
REM ========================================

echo.
echo ╔═══════════════════════════════════════════════════════╗
echo ║   WhatsApp Cron Secret Key Generator                 ║
echo ╚═══════════════════════════════════════════════════════╝
echo.

echo Generating random secret key...
echo.

REM Generate random 32 character hex string
set "chars=0123456789abcdef"
set "secret="
for /l %%i in (1,1,32) do (
    set /a "rand=!random! %% 16"
    for %%j in (!rand!) do set "secret=!secret!!chars:~%%j,1!"
)

REM If random doesn't work, use timestamp based
if "%secret%"=="" (
    set "secret=%random%%random%%random%%random%"
)

echo Your Generated Secret Key:
echo ═══════════════════════════════════════════════════════
echo %secret%
echo ═══════════════════════════════════════════════════════
echo.
echo Copy this key and paste into your .env file:
echo.
echo WHATSAPP_CRON_SECRET=%secret%
echo.
echo Then use this URL for your cron job:
echo https://yourdomain.com/whatsapp/billing/send-all?secret=%secret%
echo.

pause
