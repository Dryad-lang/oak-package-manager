#!/bin/bash

# 🐳 Test Docker Build Script
# Este script testa se o build do Docker está funcionando corretamente

set -e

echo "🧪 Testing Docker Build for Dryad Web..."
echo "========================================"

# Testar build apenas do serviço dryad-web
echo "📦 Building dryad-web service..."
docker-compose build dryad-web

# Verificar se a imagem foi criada
echo "✅ Checking if image was created..."
if docker images | grep -q "oak-package-manager-dryad-web"; then
    echo "✅ Docker image built successfully!"
else
    echo "❌ Docker image build failed!"
    exit 1
fi

# Testar se as extensões PHP estão instaladas
echo "🔍 Testing PHP extensions..."
docker run --rm oak-package-manager-dryad-web php -m | grep -E "(pdo|pgsql|sqlite|gd|zip|mbstring)"

echo ""
echo "🎉 All tests passed! Docker build is working correctly."
echo ""
echo "Now you can run the full stack with:"
echo "  docker-compose up -d"