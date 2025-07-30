@echo off
echo ========================================
echo   Multi-Language Web Development Showcase
echo   Admin System Quick Start
echo ========================================
echo.

echo Starting PHP built-in server...
echo.
echo Access URLs:
echo - Main Site: http://localhost:8000/
echo - Admin Test: http://localhost:8000/php/admin/test.php
echo - Admin Login: http://localhost:8000/php/admin/login.php
echo.
echo Default Admin Credentials:
echo - Username: admin
echo - Password: admin123
echo.
echo Press Ctrl+C to stop the server
echo ========================================
echo.

php -S localhost:8000

pause 