@echo off
REM Script de Verificação - Dryad Package Manager (Windows)

echo 🔍 Verificando status do Dryad Package Manager...
echo =================================================

REM Verificar se Docker está rodando
docker info >nul 2>nul
if %ERRORLEVEL% NEQ 0 (
    echo ❌ Docker não está rodando. Inicie o Docker primeiro.
    pause
    exit /b 1
)

echo ✅ Docker está rodando

REM Verificar containers
echo.
echo 📦 Status dos containers:
docker-compose ps

echo.
echo 🌐 Testando conectividade dos serviços:

REM Testar Laravel
echo | set /p="🔧 Laravel (7800): "
curl -s -o nul -w "%%{http_code}" http://localhost:7800 | findstr "200 302" >nul
if %ERRORLEVEL% EQU 0 (
    echo ✅ OK
) else (
    echo ❌ FALHA
)

REM Testar Forgejo
echo | set /p="🗃️  Forgejo (7850): "
curl -s -o nul -w "%%{http_code}" http://localhost:7850 | findstr "200 302" >nul
if %ERRORLEVEL% EQU 0 (
    echo ✅ OK
) else (
    echo ❌ FALHA
)

REM Testar MariaDB
echo | set /p="🗄️  MariaDB (7832): "
telnet localhost 7832 2>nul | findstr "Connected" >nul
if %ERRORLEVEL% EQU 0 (
    echo ✅ OK
) else (
    echo ❌ FALHA
)

echo.
echo 📋 Logs dos últimos 10 eventos:
docker-compose logs --tail=10

echo.
echo 🔧 Para depuração:
echo    - Ver logs completos: docker-compose logs -f
echo    - Reiniciar serviços: docker-compose restart
echo    - Verificar recursos: docker stats

pause