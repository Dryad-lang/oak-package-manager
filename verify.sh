#!/bin/bash

# Script de Verificação - Dryad Package Manager
echo "🔍 Verificando status do Dryad Package Manager..."
echo "================================================="

# Verificar se Docker está rodando
if ! docker info >/dev/null 2>&1; then
    echo "❌ Docker não está rodando. Inicie o Docker primeiro."
    exit 1
fi

echo "✅ Docker está rodando"

# Verificar containers
echo ""
echo "📦 Status dos containers:"
docker-compose ps

echo ""
echo "🌐 Testando conectividade dos serviços:"

# Testar Laravel
echo -n "🔧 Laravel (7800): "
if curl -s -o /dev/null -w "%{http_code}" http://localhost:7800 | grep -q "200\|302"; then
    echo "✅ OK"
else
    echo "❌ FALHA"
fi

# Testar Forgejo
echo -n "🗃️  Forgejo (7850): "
if curl -s -o /dev/null -w "%{http_code}" http://localhost:7850 | grep -q "200\|302"; then
    echo "✅ OK"
else
    echo "❌ FALHA"
fi

# Testar MariaDB
echo -n "🗄️  MariaDB (7832): "
if nc -z localhost 7832 2>/dev/null; then
    echo "✅ OK"
else
    echo "❌ FALHA"
fi

echo ""
echo "📋 Logs dos últimos 10 eventos:"
docker-compose logs --tail=10

echo ""
echo "🔧 Para depuração:"
echo "   - Ver logs completos: docker-compose logs -f"
echo "   - Reiniciar serviços: docker-compose restart"
echo "   - Verificar recursos: docker stats"