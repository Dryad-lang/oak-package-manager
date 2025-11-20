# 🐘 PostgreSQL Setup for Dryad Package Manager

Este arquivo contém instruções para configurar PostgreSQL localmente para desenvolvimento.

## 📋 Opções de Configuração

### Opção 1: Docker PostgreSQL (Recomendado)
```bash
# Iniciar apenas o PostgreSQL via Docker
docker run -d \
  --name dryad-postgres-dev \
  -p 5432:5432 \
  -e POSTGRES_DB=dryad_packages \
  -e POSTGRES_USER=dryad_user \
  -e POSTGRES_PASSWORD=dryad_pass \
  postgres:16-alpine

# Aguardar inicialização
docker logs -f dryad-postgres-dev
```

### Opção 2: PostgreSQL Local (Windows)
```powershell
# Instalar PostgreSQL via Chocolatey
choco install postgresql

# Ou baixar de: https://www.postgresql.org/download/windows/
# Depois configurar:
createdb -U postgres dryad_packages
psql -U postgres -c "CREATE USER dryad_user WITH PASSWORD 'dryad_pass';"
psql -U postgres -c "GRANT ALL PRIVILEGES ON DATABASE dryad_packages TO dryad_user;"
```

### Opção 3: Usar SQLite para Desenvolvimento
Se preferir usar SQLite para desenvolvimento local:

```env
# No arquivo .env
DB_CONNECTION=sqlite
DB_DATABASE=E:\git\oak-package-manager\dryad-web\database\database.sqlite
```

## 🧪 Testando a Configuração

Após configurar PostgreSQL:

```bash
# Navegar para o diretório Laravel
cd dryad-web

# Executar migrações
php artisan migrate

# Popular com dados de exemplo
php artisan db:seed

# Verificar status
php artisan migrate:status
```

## 🚀 Para Produção

Use o Docker Compose completo:
```bash
# A partir do diretório raiz
docker-compose up -d postgres
docker-compose up -d
```

## 📊 Estrutura do Banco

O banco incluirá as seguintes tabelas:
- `packages` - Informações dos pacotes
- `package_versions` - Versões de cada pacote  
- `package_downloads` - Log de downloads
- `users` - Usuários do sistema
- `cache` - Cache Laravel
- `jobs` - Fila de jobs