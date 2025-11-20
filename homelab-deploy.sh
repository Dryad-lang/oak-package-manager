#!/bin/bash

# 🚀 Deploy Final - Dryad Package Manager
# Script completo para deploy no homelab

set -e

echo "🌳 Deploy Final - Dryad Package Manager para Homelab"
echo "=================================================="

# Verificações iniciais
echo "🔍 Verificando pré-requisitos..."

if ! command -v docker &> /dev/null; then
    echo "❌ Docker não encontrado. Instale o Docker primeiro."
    exit 1
fi

if ! command -v docker compose &> /dev/null && ! command -v docker-compose &> /dev/null; then
    echo "❌ Docker Compose não encontrado. Instale o Docker Compose primeiro."
    exit 1
fi

# Usar docker compose ou docker-compose
DOCKER_COMPOSE_CMD="docker compose"
if ! command -v docker &> /dev/null || ! docker compose version &> /dev/null; then
    DOCKER_COMPOSE_CMD="docker-compose"
fi

echo "✅ Docker encontrado"
echo "✅ Docker Compose encontrado ($DOCKER_COMPOSE_CMD)"

# Configurar ambiente
echo ""
echo "📝 Configurando ambiente..."

if [ ! -f .env ]; then
    cp .env.production .env
    echo "✅ Arquivo .env criado"
fi

# Limpeza completa
echo ""
echo "🧹 Limpeza completa do ambiente anterior..."
$DOCKER_COMPOSE_CMD down -v --remove-orphans 2>/dev/null || true

# Remover containers antigos
echo "🗑️ Removendo containers antigos..."
docker ps -aq --filter "name=dryad" | xargs -r docker rm -f 2>/dev/null || true
docker ps -aq --filter "name=oak-package-manager" | xargs -r docker rm -f 2>/dev/null || true

# Remover volumes órfãos
echo "💾 Limpando volumes órfãos..."
docker volume prune -f 2>/dev/null || true

# Build das imagens
echo ""
echo "🔨 Construindo imagens Docker..."
$DOCKER_COMPOSE_CMD build --no-cache

# Iniciar serviços
echo ""
echo "🚀 Iniciando serviços..."
$DOCKER_COMPOSE_CMD up -d

# Aguardar inicialização
echo ""
echo "⏳ Aguardando inicialização dos serviços..."
echo "   - PostgreSQL precisa de ~40s para estar pronto"
echo "   - Gitea precisa de ~60s para configuração inicial"
echo "   - Laravel precisa de ~60s para migrações"

for i in {1..12}; do
    echo -n "."
    sleep 10
done
echo ""

# Verificar status
echo ""
echo "📊 Status dos containers:"
$DOCKER_COMPOSE_CMD ps

echo ""
echo "🔍 Verificando saúde dos serviços..."

# Função para testar serviços
test_service() {
    local name=$1
    local url=$2
    local max_attempts=10
    
    echo -n "  🔧 $name: "
    
    for attempt in $(seq 1 $max_attempts); do
        if curl -s --connect-timeout 5 "$url" >/dev/null 2>&1; then
            echo "✅ OK"
            return 0
        fi
        sleep 3
    done
    
    echo "❌ FALHOU (após ${max_attempts} tentativas)"
    return 1
}

# Testar serviços
test_service "PostgreSQL Health" "http://localhost:7832" || true
test_service "Laravel Web" "http://localhost:7800" || true
test_service "Registry API" "http://localhost:7840/api/health" || true
test_service "Gitea Server" "http://localhost:7850" || true
test_service "Nginx Proxy" "http://localhost:7880" || true

echo ""
echo "📊 URLs do Sistema:"
echo "   🌐 Frontend Laravel:  http://localhost:7800"
echo "   🔧 Registry API:      http://localhost:7840"
echo "   🔧 Gitea Git Server:  http://localhost:7850"
echo "   🌍 Nginx Proxy:       http://localhost:7880"
echo "   📊 Health Check:      http://localhost:7840/api/health"

echo ""
echo "📝 Configurações importantes:"
echo "   - PostgreSQL: localhost:7832 (dryad_user/dryad_pass)"
echo "   - Redis Cache: localhost:7879"
echo "   - SSH Gitea: localhost:7822"

# Verificar logs de erros
echo ""
echo "🔍 Verificando logs de erros recentes..."
if $DOCKER_COMPOSE_CMD logs --tail=5 2>/dev/null | grep -i error | head -3; then
    echo "⚠️  Erros encontrados. Verifique os logs com: $DOCKER_COMPOSE_CMD logs"
else
    echo "✅ Nenhum erro crítico nos logs recentes"
fi

echo ""
echo "🎉 Deploy concluído!"
echo ""
echo "📋 Próximos passos:"
echo "   1. Acesse http://localhost:7800 para o frontend"
echo "   2. Configure o Gitea em http://localhost:7850 (primeira execução)"
echo "   3. Verifique a API em http://localhost:7840/api/health"
echo "   4. Para monitorar: $DOCKER_COMPOSE_CMD logs -f"
echo "   5. Para parar: $DOCKER_COMPOSE_CMD down"
echo ""
echo "✨ Sistema pronto para uso no seu homelab! ✨"