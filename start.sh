#!/bin/bash

# Dryad Package Manager - Script de Inicialização
# Arquitetura Simplificada com Forgejo

echo "🚀 Iniciando Dryad Package Manager (Arquitetura Simplificada)"
echo "============================================================="

# Verificar se Docker está instalado
if ! command -v docker &> /dev/null; then
    echo "❌ Docker não encontrado. Por favor, instale o Docker primeiro."
    exit 1
fi

if ! command -v docker-compose &> /dev/null; then
    echo "❌ Docker Compose não encontrado. Por favor, instale o Docker Compose primeiro."
    exit 1
fi

echo "✅ Docker e Docker Compose encontrados"

# Parar containers antigos se existirem
echo "🧹 Limpando containers antigos..."
docker-compose down 2>/dev/null || true

# Remover volumes antigos se existirem (opcional)
read -p "🗑️  Deseja limpar dados antigos? (y/N): " -n 1 -r
echo
if [[ $REPLY =~ ^[Yy]$ ]]; then
    echo "🗑️  Removendo volumes antigos..."
    docker-compose down -v 2>/dev/null || true
fi

# Iniciar serviços
echo "🐳 Iniciando serviços Docker..."
docker-compose up -d

echo "⏳ Aguardando serviços ficarem prontos..."
sleep 10

# Verificar status dos serviços
echo "📋 Status dos serviços:"
docker-compose ps

echo ""
echo "🌟 Sistema iniciado com sucesso!"
echo ""
echo "📱 Interfaces disponíveis:"
echo "   - Laravel Web: http://localhost:8000"
echo "   - Forgejo Git: http://localhost:3000"
echo "   - MariaDB:     localhost:3306"
echo ""
echo "⚙️  Configuração inicial necessária:"
echo "   1. Acesse http://localhost:3000 para configurar Forgejo"
echo "   2. Crie uma organização chamada 'dryad-packages'"
echo "   3. Gere um token de API no Forgejo"
echo "   4. Configure FORGEJO_TOKEN no arquivo .env"
echo ""
echo "🔧 Para compilar o CLI:"
echo "   cd dryad_base && cargo build --release"
echo ""
echo "📦 Comandos do CLI:"
echo "   ./target/release/dryad publish    # Publicar pacote"
echo "   ./target/release/dryad install <pacote>  # Instalar pacote"
echo "   ./target/release/dryad list       # Listar pacotes"
echo ""
echo "🐛 Para verificar logs:"
echo "   docker-compose logs -f <serviço>"
echo ""
echo "✨ Pronto para usar o Dryad Package Manager!"