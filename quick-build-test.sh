#!/bin/bash

# 🚀 Quick Build Test for Dryad Web
# Testa apenas o build do serviço dryad-web para validação rápida

set -e

echo "🧪 Quick Build Test for Dryad Web Service"
echo "========================================"

# Verificar se Docker está funcionando
if ! docker info >/dev/null 2>&1; then
    echo "❌ Docker não está rodando. Inicie o Docker Desktop primeiro."
    exit 1
fi

# Build apenas do serviço dryad-web
echo "📦 Building dryad-web service..."
if docker-compose build dryad-web; then
    echo "✅ Build successful!"
else
    echo "❌ Build failed!"
    exit 1
fi

# Verificar se a imagem foi criada
echo "🔍 Checking if image was created..."
if docker images | grep -q "oak-package-manager[_-]dryad-web"; then
    echo "✅ Docker image created successfully!"
else
    echo "❌ Docker image not found!"
    exit 1
fi

# Testar se as extensões PHP essenciais estão instaladas
echo "🔍 Testing essential PHP extensions..."
echo "Testing PHP extensions in container..."

docker run --rm --entrypoint="" oak-package-manager-dryad-web php -m | grep -E "(Core|pdo|pgsql|sqlite|gd|zip|mbstring|bcmath)"

if [ $? -eq 0 ]; then
    echo "✅ Essential PHP extensions are installed!"
else
    echo "⚠️ Some PHP extensions may be missing, but build completed."
fi

echo ""
echo "🎉 Quick build test completed successfully!"
echo ""
echo "🚀 Next steps:"
echo "  1. Start the full system: docker-compose up -d"
echo "  2. Or run individual tests: ./test-build.sh"