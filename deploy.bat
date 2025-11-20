@echo off
setlocal enabledelayedexpansion

echo 🌳 Dryad Package Manager - Deploy Script (Windows)
echo =================================================

REM Verificar se Docker está instalado
docker --version >nul 2>&1
if errorlevel 1 (
    echo ❌ Docker não está instalado. Por favor, instale o Docker Desktop primeiro.
    pause
    exit /b 1
)

docker-compose --version >nul 2>&1
if errorlevel 1 (
    echo ❌ Docker Compose não está instalado. Por favor, instale o Docker Compose primeiro.
    pause
    exit /b 1
)

REM Configurar ambiente
echo 📝 Configurando ambiente...
if not exist .env (
    copy .env.production .env >nul 2>&1
    echo ✅ Arquivo .env criado
)

REM Parar containers existentes e limpar completamente
echo 🛑 Parando containers existentes...
docker-compose down -v >nul 2>&1

REM Limpar todos os containers relacionados ao projeto
echo 🧹 Limpando containers do projeto...
for /f "tokens=1" %%i in ('docker ps -aq --filter "name=dryad" 2^>nul') do docker rm -f %%i >nul 2>&1
for /f "tokens=1" %%i in ('docker ps -aq --filter "name=oak-package-manager" 2^>nul') do docker rm -f %%i >nul 2>&1

REM Parar PostgreSQL local se estiver rodando na porta 5432
echo 🛑 Verificando PostgreSQL local na porta 5432...
for /f "tokens=5" %%a in ('netstat -aon ^| findstr :5432 2^>nul') do (
    echo ⚠️  Parando processo na porta 5432 (PID: %%a)
    taskkill /PID %%a /F >nul 2>&1
)

REM Perguntar sobre limpeza de volumes
set /p cleanup="🗑️  Deseja limpar todos os volumes? (y/N): "
if /i "%cleanup%"=="y" (
    docker-compose down -v
    docker volume prune -f
    echo ✅ Volumes limpos
)

REM Instalar dependências PHP
echo 📦 Instalando dependências do Laravel...
cd dryad-web
if exist composer.phar (
    php composer.phar install --optimize-autoloader --no-dev
) else (
    composer install --optimize-autoloader --no-dev
)
cd ..

REM Construir e iniciar serviços
echo 🚀 Construindo e iniciando serviços...
docker-compose up --build -d

REM Aguardar serviços ficarem prontos
echo ⏳ Aguardando serviços ficarem prontos...
timeout /t 30 /nobreak >nul

REM Verificar status dos serviços
echo 🔍 Verificando status dos serviços...

REM Verificar Registry API
curl -s http://localhost:4000/api/health >nul 2>&1
if %errorlevel%==0 (
    echo ✅ Registry API está funcionando (porta 4000)
) else (
    echo ❌ Registry API não está respondendo
)

REM Verificar Laravel Web
curl -s http://localhost:8000 >nul 2>&1
if %errorlevel%==0 (
    echo ✅ Laravel Web está funcionando (porta 8000)
) else (
    echo ❌ Laravel Web não está respondendo
)

REM Verificar Nginx
curl -s http://localhost >nul 2>&1
if %errorlevel%==0 (
    echo ✅ Nginx está funcionando (porta 80)
) else (
    echo ⚠️  Nginx não está configurado ou não está respondendo
)

echo.
echo 🎉 Deploy concluído!
echo.
echo 📌 URLs de Acesso:
echo    🌐 Frontend: http://localhost:8000
echo    🔧 API Registry: http://localhost:4000
echo    📊 API Health: http://localhost:4000/api/health
echo.
echo 📋 Comandos úteis:
echo    docker-compose logs -f          # Ver logs em tempo real
echo    docker-compose ps               # Ver status dos containers
echo    docker-compose down             # Parar todos os serviços
echo    docker-compose restart          # Reiniciar serviços
echo.
echo 🔧 Para testar o Oak CLI:
echo    cd dryad_base ^&^& cargo run --bin oak registry test
echo.

REM Mostrar logs por alguns segundos
echo 📄 Últimos logs dos serviços:
docker-compose logs --tail=20

echo.
echo Pressione qualquer tecla para continuar...
pause >nul