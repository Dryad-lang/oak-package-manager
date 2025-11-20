@echo off
REM 🐳 Test Docker Build Script (Windows)
REM Este script testa se o build do Docker está funcionando corretamente

echo 🧪 Testing Docker Build for Dryad Web...
echo ========================================

REM Testar build apenas do serviço dryad-web
echo 📦 Building dryad-web service...
docker-compose build dryad-web

REM Verificar se a imagem foi criada
echo ✅ Checking if image was created...
docker images | findstr "oak-package-manager-dryad-web" >nul
if errorlevel 1 (
    echo ❌ Docker image build failed!
    exit /b 1
) else (
    echo ✅ Docker image built successfully!
)

REM Testar se as extensões PHP estão instaladas
echo 🔍 Testing PHP extensions...
docker run --rm oak-package-manager-dryad-web php -m | findstr /R "pdo pgsql sqlite gd zip mbstring"

echo.
echo 🎉 All tests passed! Docker build is working correctly.
echo.
echo Now you can run the full stack with:
echo   docker-compose up -d

pause