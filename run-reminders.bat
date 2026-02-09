@echo off
echo ===================================
echo Laravel Comment Reminders (Direct)
echo ===================================
echo.

cd /d C:\laragon\www\mypro

echo Starting Queue Worker in new window...
start "Queue Worker" cmd /k "C:\xampp\php\php.exe artisan queue:work --sleep=3 --tries=3"

echo Starting Reminders in 3 seconds...
timeout /t 3 /nobreak >nul

:loop
cls
echo [%date% %time%] Checking for reminders...
C:\xampp\php\php.exe artisan comments:send-reminders
echo.
echo Waiting 60 seconds...
timeout /t 60 /nobreak >nul
goto loop