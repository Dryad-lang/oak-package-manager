#!/bin/bash
set -e

# Script para criar múltiplos bancos de dados no PostgreSQL

psql -v ON_ERROR_STOP=1 --username "$POSTGRES_USER" --dbname "$POSTGRES_DB" <<-EOSQL
    -- Criar banco de dados do Gitea
    CREATE DATABASE gitea;
    
    -- Criar usuário do Gitea
    CREATE USER gitea WITH ENCRYPTED PASSWORD 'gitea_password';
    
    -- Conceder privilégios
    GRANT ALL PRIVILEGES ON DATABASE gitea TO gitea;
    
    -- Permitir que o usuário gitea crie tabelas
    \c gitea
    GRANT ALL ON SCHEMA public TO gitea;
    
    -- Voltar ao banco principal
    \c $POSTGRES_DB
    
    ECHO 'Múltiplos bancos de dados criados com sucesso!';
EOSQL