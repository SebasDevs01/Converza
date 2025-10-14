@echo off
echo ==========================================
echo INSTALADOR: Sistema de Conexiones
echo ==========================================
echo.

cd /d "%~dp0..\.."

c:\xampp\php\php.exe app\cron\install_coincidence_system.php

echo.
echo Presiona cualquier tecla para salir...
pause >nul
