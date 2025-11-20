#!/bin/bash

# 🔍 Script de Verificação Completa - Dryad Package Manager
# Verifica se todos os serviços estão funcionando corretamente

echo "🔍 Verificação Completa do Sistema"
echo "=================================="

# Aguardar serviços iniciarem
echo "⏳ Aguardando serviços inicializarem..."
sleep 15

echo ""
echo "📊 Status dos Containers:"
docker compose ps

echo ""
echo "🌐 Testando URLs dos Serviços:"

# Testar Laravel Web
echo -n "  🌐 Laravel Web (7800): "
if curl -s http://localhost:7800 >/dev/null 2>&1; then
    echo "✅ FUNCIONANDO"
else
    echo "❌ FALHOU"
fi

# Testar Registry API
echo -n "  🔧 Registry API (7840): "
if curl -s http://localhost:7840/api/health >/dev/null 2>&1; then
    echo "✅ FUNCIONANDO"
else
    echo "❌ FALHOU"
fi

# Testar Nginx
echo -n "  🌍 Nginx Proxy (7880): "
if curl -s http://localhost:7880 >/dev/null 2>&1; then
    echo "✅ FUNCIONANDO"
else
    echo "❌ FALHOU"
fi

# Testar Gitea
echo -n "  🔧 Gitea Server (7850): "
if curl -s http://localhost:7850 >/dev/null 2>&1; then
    echo "✅ FUNCIONANDO"
else
    echo "❌ FALHOU"
fi

echo ""
echo "🔍 Status detalhado do Registry API:"
curl -s http://localhost:7840/api/health | head -10 || echo "❌ Erro ao obter status"

echo ""
echo "🔍 Logs do Laravel (últimas 10 linhas):"
docker compose logs --tail=10 dryad-web

echo ""
echo "📊 URLs para Acesso:"
echo "   🌐 Frontend: http://localhost:7800"
echo "   🔧 API Registry: http://localhost:7840"
echo "   🌍 Nginx Proxy: http://localhost:7880"
echo "   🔧 Gitea Server: http://localhost:7850"
echo "   📊 Health Check: http://localhost:7840/api/health"
echo ""
echo "✅ Verificação concluída!"