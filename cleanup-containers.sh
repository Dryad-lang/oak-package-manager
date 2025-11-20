#!/bin/bash

# 🧹 Script de Limpeza de Containers - Dryad Package Manager (Linux)
# Este script para e remove todos os containers relacionados ao projeto

set -e

echo "🧹 Dryad Package Manager - Cleanup Containers"
echo "============================================="

# Parar todos os containers do projeto
echo "🛑 Parando containers do projeto..."
docker-compose down -v 2>/dev/null || true

# Remover containers específicos do projeto
echo "🗑️ Removendo containers do projeto..."
DRYAD_CONTAINERS=$(docker ps -aq --filter "name=dryad" 2>/dev/null || true)
if [ ! -z "$DRYAD_CONTAINERS" ]; then
    echo "  - Removendo containers 'dryad'"
    echo "$DRYAD_CONTAINERS" | xargs docker rm -f 2>/dev/null || true
fi

OAK_CONTAINERS=$(docker ps -aq --filter "name=oak-package-manager" 2>/dev/null || true)
if [ ! -z "$OAK_CONTAINERS" ]; then
    echo "  - Removendo containers 'oak-package-manager'"
    echo "$OAK_CONTAINERS" | xargs docker rm -f 2>/dev/null || true
fi

# Verificar PostgreSQL na porta 5432
echo "🔍 Verificando PostgreSQL na porta 5432..."
PG_PID=$(lsof -ti:5432 2>/dev/null || true)
if [ ! -z "$PG_PID" ]; then
    echo "⚠️  Parando PostgreSQL local (PID: $PG_PID)"
    sudo kill -9 $PG_PID 2>/dev/null || true
fi

# Limpar volumes orphaned
echo "🧽 Limpando volumes órfãos..."
docker volume prune -f >/dev/null 2>&1 || true

# Limpar redes não utilizadas
echo "🌐 Limpando redes não utilizadas..."
docker network prune -f >/dev/null 2>&1 || true

echo "✅ Limpeza concluída!"
echo ""
echo "💡 Agora você pode executar:"
echo "  - ./deploy.sh (para deploy completo)"
echo "  - ./quick-build-test.sh (para teste rápido)"
echo ""