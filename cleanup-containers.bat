@echo off
REM 🧹 Script de Limpeza de Containers - Dryad Package Manager (Windows)
REM Este script para e remove todos os containers relacionados ao projeto

echo 🧹 Dryad Package Manager - Cleanup Containers
echo =============================================

REM Parar todos os containers do projeto
echo 🛑 Parando containers do projeto...
docker-compose down -v >nul 2>&1

REM Remover containers específicos do projeto
echo 🗑️ Removendo containers do projeto...
for /f "tokens=1" %%i in ('docker ps -aq --filter "name=dryad" 2^>nul') do (
    echo   - Removendo container: %%i
    docker rm -f %%i >nul 2>&1
)

for /f "tokens=1" %%i in ('docker ps -aq --filter "name=oak-package-manager" 2^>nul') do (
    echo   - Removendo container: %%i
    docker rm -f %%i >nul 2>&1
)

REM Verificar PostgreSQL na porta 5432
echo 🔍 Verificando PostgreSQL na porta 5432...
for /f "tokens=5" %%a in ('netstat -aon ^| findstr :5432 2^>nul') do (
    echo ⚠️  Parando processo na porta 5432 (PID: %%a)
    taskkill /PID %%a /F >nul 2>&1
)

REM Limpar volumes orphaned
echo 🧽 Limpando volumes órfãos...
docker volume prune -f >nul 2>&1

REM Limpar redes não utilizadas
echo 🌐 Limpando redes não utilizadas...
docker network prune -f >nul 2>&1

echo ✅ Limpeza concluída!
echo.
echo 💡 Agora você pode executar:
echo   - deploy.bat (para deploy completo)
echo   - quick-build-test.bat (para teste rápido)
echo.
pause