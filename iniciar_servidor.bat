@echo off
chcp 65001 > nul
echo ===================================================
echo     Iniciando Servidor PeriodicArt (PHP + PDO)
echo ===================================================
echo.

set PHP_EXE=C:\Users\Aluno\php\php.exe

if not exist "%PHP_EXE%" (
    set PHP_EXE=php
)

echo Iniciando servidor em http://127.0.0.1:8000 ...
echo Pressione Ctrl+C para encerrar.
echo.

start http://127.0.0.1:8000
"%PHP_EXE%" -S 127.0.0.1:8000 -t "%~dp0"
pause
