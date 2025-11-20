#!/bin/bash

# 🌳 Dryad Package Manager - Deploy Script
# Este script configura e executa todo o sistema

set -e

echo "🌳 Dryad Package Manager - Deploy Script"
echo "========================================"

# Verificar se Docker está instalado
if ! command -v docker &> /dev/null; then
    echo "❌ Docker não está instalado. Por favor, instale o Docker primeiro."
    exit 1
fi

if ! command -v docker-compose &> /dev/null; then
    echo "❌ Docker Compose não está instalado. Por favor, instale o Docker Compose primeiro."
    exit 1
fi

# Configurar ambiente
echo "📝 Configurando ambiente..."
if [ ! -f .env ]; then
    cp .env.production .env
    echo "✅ Arquivo .env criado"
fi

# Parar containers existentes
echo "🛑 Parando containers existentes..."
docker-compose down -v 2>/dev/null || true

# Limpar volumes antigos (opcional)
read -p "🗑️  Deseja limpar todos os volumes? (y/N): " -n 1 -r
echo
if [[ $REPLY =~ ^[Yy]$ ]]; then
    docker-compose down -v
    docker volume prune -f
    echo "✅ Volumes limpos"
fi

# Instalar dependências PHP
echo "📦 Instalando dependências do Laravel..."
cd dryad-web
if command -v composer &> /dev/null; then
    composer install --optimize-autoloader --no-dev
else
    echo "⚠️  Composer não encontrado. Dependências serão instaladas no container."
fi
cd ..

# Construir e iniciar os serviços
echo "🚀 Construindo e iniciando serviços..."
docker-compose up --build -d

# Aguardar serviços ficarem prontos
echo "⏳ Aguardando serviços ficarem prontos..."
sleep 30

# Verificar status dos serviços
echo "🔍 Verificando status dos serviços..."

# Verificar Registry API
if curl -s http://localhost:4000/api/health > /dev/null; then
    echo "✅ Registry API está funcionando (porta 4000)"
else
    echo "❌ Registry API não está respondendo"
fi

# Verificar Laravel Web
if curl -s http://localhost:8000 > /dev/null; then
    echo "✅ Laravel Web está funcionando (porta 8000)"
else
    echo "❌ Laravel Web não está respondendo"
fi

# Verificar Nginx (se configurado)
if curl -s http://localhost > /dev/null; then
    echo "✅ Nginx está funcionando (porta 80)"
else
    echo "⚠️  Nginx não está configurado ou não está respondendo"
fi

echo ""
echo "🎉 Deploy concluído!"
echo ""
echo "📌 URLs de Acesso:"
echo "   🌐 Frontend: http://localhost:8000"
echo "   🔧 API Registry: http://localhost:4000" 
echo "   📊 API Health: http://localhost:4000/api/health"
echo ""
echo "📋 Comandos úteis:"
echo "   docker-compose logs -f          # Ver logs em tempo real"
echo "   docker-compose ps               # Ver status dos containers"
echo "   docker-compose down             # Parar todos os serviços"
echo "   docker-compose restart          # Reiniciar serviços"
echo ""
echo "🔧 Para testar o Oak CLI:"
echo "   cd dryad_base && cargo run --bin oak registry test"
echo ""

# Mostrar logs por alguns segundos
echo "📄 Últimos logs dos serviços:"
docker-compose logs --tail=20