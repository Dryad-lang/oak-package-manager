@echo off
setlocal enabledelayedexpansion

REM 🚀 Deploy Final - Dryad Package Manager para Windows
REM Script completo para deploy no homelab Windows

echo 🌳 Deploy Final - Dryad Package Manager para Homelab
echo ==================================================

REM Verificações iniciais
echo 🔍 Verificando pré-requisitos...

docker --version >nul 2>&1
if errorlevel 1 (
    echo ❌ Docker não encontrado. Instale o Docker Desktop primeiro.
    pause
    exit /b 1
)

docker-compose --version >nul 2>&1
if errorlevel 1 (
    echo ❌ Docker Compose não encontrado. Instale o Docker Compose primeiro.
    pause
    exit /b 1
)

echo ✅ Docker encontrado
echo ✅ Docker Compose encontrado

REM Configurar ambiente
echo.
echo 📝 Configurando ambiente...

if not exist .env (
    copy .env.production .env >nul 2>&1
    echo ✅ Arquivo .env criado
)

REM Limpeza completa
echo.
echo 🧹 Limpeza completa do ambiente anterior...
docker-compose down -v --remove-orphans >nul 2>&1

REM Remover containers antigos
echo 🗑️ Removendo containers antigos...
for /f "tokens=1" %%i in ('docker ps -aq --filter "name=dryad" 2^>nul') do (
    docker rm -f %%i >nul 2>&1
)
for /f "tokens=1" %%i in ('docker ps -aq --filter "name=oak-package-manager" 2^>nul') do (
    docker rm -f %%i >nul 2>&1
)

REM Remover volumes órfãos
echo 💾 Limpando volumes órfãos...
docker volume prune -f >nul 2>&1

REM Build das imagens
echo.
echo 🔨 Construindo imagens Docker...
docker-compose build --no-cache
if errorlevel 1 (
    echo ❌ Erro no build das imagens
    pause
    exit /b 1
)

REM Iniciar serviços
echo.
echo 🚀 Iniciando serviços...
docker-compose up -d
if errorlevel 1 (
    echo ❌ Erro ao iniciar serviços
    pause
    exit /b 1
)

REM Aguardar inicialização
echo.
echo ⏳ Aguardando inicialização dos serviços...
echo    - PostgreSQL precisa de ~40s para estar pronto
echo    - Gitea precisa de ~60s para configuração inicial
echo    - Laravel precisa de ~60s para migrações

for /L %%i in (1,1,12) do (
    echo|set /p="."
    timeout /t 10 /nobreak >nul
)
echo.

REM Verificar status
echo.
echo 📊 Status dos containers:
docker-compose ps

echo.
echo 🔍 Verificando saúde dos serviços...

REM Testar serviços principais
echo   🌐 Laravel Web (7800):
curl -s http://localhost:7800 >nul 2>&1 && echo     ✅ OK || echo     ❌ FALHOU

echo   🔧 Registry API (7840):
curl -s http://localhost:7840/api/health >nul 2>&1 && echo     ✅ OK || echo     ❌ FALHOU

echo   🔧 Gitea Server (7850):
curl -s http://localhost:7850 >nul 2>&1 && echo     ✅ OK || echo     ❌ FALHOU

echo   🌍 Nginx Proxy (7880):
curl -s http://localhost:7880 >nul 2>&1 && echo     ✅ OK || echo     ❌ FALHOU

echo.
echo 📊 URLs do Sistema:
echo    🌐 Frontend Laravel:  http://localhost:7800
echo    🔧 Registry API:      http://localhost:7840
echo    🔧 Gitea Git Server:  http://localhost:7850
echo    🌍 Nginx Proxy:       http://localhost:7880
echo    📊 Health Check:      http://localhost:7840/api/health

echo.
echo 📝 Configurações importantes:
echo    - PostgreSQL: localhost:7832 (dryad_user/dryad_pass)
echo    - Redis Cache: localhost:7879
echo    - SSH Gitea: localhost:7822

echo.
echo 🎉 Deploy concluído!
echo.
echo 📋 Próximos passos:
echo    1. Acesse http://localhost:7800 para o frontend
echo    2. Configure o Gitea em http://localhost:7850 (primeira execução)
echo    3. Verifique a API em http://localhost:7840/api/health
echo    4. Para monitorar: docker-compose logs -f
echo    5. Para parar: docker-compose down
echo.
echo ✨ Sistema pronto para uso no seu homelab! ✨

pause