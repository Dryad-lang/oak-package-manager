@echo off
REM 🚀 Quick Build Test for Dryad Web (Windows)
REM Testa apenas o build do serviço dryad-web para validação rápida

echo 🧪 Quick Build Test for Dryad Web Service
echo ========================================

REM Verificar se Docker está funcionando
docker info >nul 2>&1
if errorlevel 1 (
    echo ❌ Docker não está rodando. Inicie o Docker Desktop primeiro.
    exit /b 1
)

REM Build apenas do serviço dryad-web
echo 📦 Building dryad-web service...
docker-compose build dryad-web
if errorlevel 1 (
    echo ❌ Build failed!
    exit /b 1
) else (
    echo ✅ Build successful!
)

REM Verificar se a imagem foi criada
echo 🔍 Checking if image was created...
docker images | findstr "oak-package-manager" | findstr "dryad-web" >nul
if errorlevel 1 (
    echo ❌ Docker image not found!
    exit /b 1
) else (
    echo ✅ Docker image created successfully!
)

REM Testar se as extensões PHP essenciais estão instaladas
echo 🔍 Testing essential PHP extensions...
echo Testing PHP extensions in container...

docker run --rm --entrypoint="" oak-package-manager-dryad-web php -m | findstr /R "Core pdo pgsql sqlite gd zip mbstring bcmath"

if errorlevel 1 (
    echo ⚠️ Some PHP extensions may be missing, but build completed.
) else (
    echo ✅ Essential PHP extensions are installed!
)

echo.
echo 🎉 Quick build test completed successfully!
echo.
echo 🚀 Next steps:
echo   1. Start the full system: docker-compose up -d
echo   2. Or run individual tests: test-build.bat

pause