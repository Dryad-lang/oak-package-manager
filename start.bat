@echo off
REM Dryad Package Manager - Script de Inicialização (Windows)
REM Arquitetura Simplificada com Forgejo

echo 🚀 Iniciando Dryad Package Manager (Arquitetura Simplificada)
echo =============================================================

REM Verificar se Docker está instalado
where docker >nul 2>nul
if %ERRORLEVEL% NEQ 0 (
    echo ❌ Docker não encontrado. Por favor, instale o Docker primeiro.
    pause
    exit /b 1
)

where docker-compose >nul 2>nul
if %ERRORLEVEL% NEQ 0 (
    echo ❌ Docker Compose não encontrado. Por favor, instale o Docker Compose primeiro.
    pause
    exit /b 1
)

echo ✅ Docker e Docker Compose encontrados

REM Parar containers antigos se existirem
echo 🧹 Limpando containers antigos...
docker-compose down >nul 2>nul

REM Perguntar sobre limpeza de dados
set /p cleanup="🗑️  Deseja limpar dados antigos? (y/N): "
if /i "%cleanup%"=="y" (
    echo 🗑️  Removendo volumes antigos...
    docker-compose down -v >nul 2>nul
)

REM Iniciar serviços
echo 🐳 Iniciando serviços Docker...
docker-compose up -d

echo ⏳ Aguardando serviços ficarem prontos...
timeout /t 10 /nobreak >nul

REM Verificar status dos serviços
echo 📋 Status dos serviços:
docker-compose ps

echo.
echo 🌟 Sistema iniciado com sucesso!
echo.
echo 📱 Interfaces disponíveis:
echo    - Laravel Web: http://localhost:8000
echo    - Forgejo Git: http://localhost:3000
echo    - MariaDB:     localhost:3306
echo.
echo ⚙️  Configuração inicial necessária:
echo    1. Acesse http://localhost:3000 para configurar Forgejo
echo    2. Crie uma organização chamada 'dryad-packages'
echo    3. Gere um token de API no Forgejo
echo    4. Configure FORGEJO_TOKEN no arquivo .env
echo.
echo 🔧 Para compilar o CLI:
echo    cd dryad_base ^&^& cargo build --release
echo.
echo 📦 Comandos do CLI:
echo    .\target\release\dryad.exe publish    # Publicar pacote
echo    .\target\release\dryad.exe install ^<pacote^>  # Instalar pacote
echo    .\target\release\dryad.exe list       # Listar pacotes
echo.
echo 🐛 Para verificar logs:
echo    docker-compose logs -f ^<serviço^>
echo.
echo ✨ Pronto para usar o Dryad Package Manager!
pause