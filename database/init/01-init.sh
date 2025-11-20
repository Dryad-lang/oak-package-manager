#!/bin/bash
set -e

# Script de inicialização do PostgreSQL para o Dryad Package Manager
# Este script é executado automaticamente quando o container PostgreSQL é criado

echo "🐘 Inicializando banco de dados PostgreSQL para Dryad Package Manager..."

# Criar extensões úteis
psql -v ON_ERROR_STOP=1 --username "$POSTGRES_USER" --dbname "$POSTGRES_DB" <<-EOSQL
    -- Extensões úteis para o Laravel
    CREATE EXTENSION IF NOT EXISTS "uuid-ossp";
    CREATE EXTENSION IF NOT EXISTS "pgcrypto";
    
    -- Configurações de performance
    ALTER SYSTEM SET shared_preload_libraries = 'pg_stat_statements';
    ALTER SYSTEM SET max_connections = 200;
    ALTER SYSTEM SET shared_buffers = '128MB';
    ALTER SYSTEM SET effective_cache_size = '512MB';
    
    -- Configurações para Laravel
    ALTER SYSTEM SET timezone = 'UTC';
    
    SELECT pg_reload_conf();
EOSQL

echo "✅ Banco de dados PostgreSQL configurado com sucesso!"
echo "📊 Estatísticas: $(psql --username "$POSTGRES_USER" --dbname "$POSTGRES_DB" -c "SELECT version();" -t)"