# 🐳 Dryad Registry Server - Deploy Guide

**Versão:** 1.0  
**Data:** 20 de novembro de 2025  
**Autor:** Equipe Dryad  

---

## 📋 Visão Geral

Esta é uma solução completa dockerizada para hospedar o Registry oficial de pacotes Dryad. Inclui:

- **🌐 Registry API** - API REST para gerenciar pacotes
- **🎨 Interface Web** - Dashboard para upload e gerenciamento
- **📦 Git Server** - Gitea para versionamento de pacotes
- **🔒 Proxy Reverso** - Nginx com SSL e rate limiting
- **💾 Bancos de Dados** - PostgreSQL e Redis

## 🏗️ Arquitetura

```
┌─────────────────────────────────────────────────────────────┐
│                        INTERNET                             │
└──────────────────┬──────────────────────────────────────────┘
                   │
┌──────────────────▼──────────────────────────────────────────┐
│                    NGINX (Port 80/443)                     │
│  ┌─────────────┐ ┌─────────────┐ ┌─────────────────────┐    │
│  │ Web Interface│ │Registry API │ │   Gitea (Git)       │    │
│  │  (React)    │ │ (Node.js)   │ │                     │    │
│  └─────────────┘ └─────────────┘ └─────────────────────┘    │
└─────────────────────────────────────────────────────────────┘
                   │
┌──────────────────▼──────────────────────────────────────────┐
│                   DATA LAYER                                │
│  ┌─────────────┐ ┌─────────────┐ ┌─────────────────────┐    │
│  │ PostgreSQL  │ │   Redis     │ │   File Storage      │    │
│  │ (Metadados) │ │  (Cache)    │ │   (Packages)        │    │
│  └─────────────┘ └─────────────┘ └─────────────────────┘    │
└─────────────────────────────────────────────────────────────┘
```

## 🚀 Deploy Rápido

### 1. Preparação do Servidor

```bash
# Atualizar sistema
sudo apt update && sudo apt upgrade -y

# Instalar Docker e Docker Compose
curl -fsSL https://get.docker.com -o get-docker.sh
sudo sh get-docker.sh
sudo usermod -aG docker $USER

# Instalar Docker Compose
sudo curl -L "https://github.com/docker/compose/releases/latest/download/docker-compose-$(uname -s)-$(uname -m)" -o /usr/local/bin/docker-compose
sudo chmod +x /usr/local/bin/docker-compose

# Verificar instalação
docker --version
docker-compose --version
```

### 2. Preparar Ambiente

```bash
# Clonar o repositório
git clone https://github.com/Dryad-lang/oak-package-manager.git
cd oak-package-manager/registry-server

# Criar arquivo de ambiente
cp .env.example .env

# Editar configurações
nano .env
```

### 3. Configurar Variáveis de Ambiente

Crie o arquivo `.env`:

```bash
# Database Configuration
POSTGRES_USER=gitea
POSTGRES_PASSWORD=your_secure_password_here
POSTGRES_DB=gitea

# Registry Database
REGISTRY_DB_USER=registry
REGISTRY_DB_PASSWORD=another_secure_password
REGISTRY_DB_NAME=dryad_registry

# JWT Secret (generate with: openssl rand -hex 32)
JWT_SECRET=your_jwt_secret_key_here

# Gitea Configuration
GITEA_SECRET_KEY=your_gitea_secret_key_here
GITEA_TOKEN=your_gitea_api_token

# Domain Configuration
DOMAIN=registry.dryad-lang.org
SSL_EMAIL=admin@dryad-lang.org

# File Upload Limits
MAX_PACKAGE_SIZE=100MB
UPLOAD_TIMEOUT=300s

# Redis Configuration
REDIS_PASSWORD=redis_password_here
```

### 4. Executar Deploy

```bash
# Subir todos os serviços
docker-compose up -d

# Verificar status
docker-compose ps

# Ver logs
docker-compose logs -f
```

## 📊 Monitoramento e Logs

### Verificar Serviços

```bash
# Status dos containers
docker-compose ps

# Logs em tempo real
docker-compose logs -f [service_name]

# Logs específicos
docker-compose logs registry-api
docker-compose logs web-interface
docker-compose logs gitea
docker-compose logs nginx
```

### Endpoints de Health Check

- **API Health**: `http://your-domain/api/health`
- **Web Interface**: `http://your-domain/`
- **Gitea**: `http://your-domain/git/`
- **Nginx Status**: `http://your-domain/health`

## 🔧 Configuração Pós-Deploy

### 1. Configurar Gitea (Primeira vez)

```bash
# Acessar Gitea
open http://your-domain/git/

# Configurações iniciais:
# - Database: PostgreSQL
# - Host: postgres:5432
# - User: gitea
# - Password: [sua senha do .env]
# - Database Name: gitea
```

### 2. Criar Usuário Admin

```bash
# Executar no container da API
docker-compose exec registry-api node scripts/create-admin.js

# Ou via curl
curl -X POST http://your-domain/api/auth/register \
  -H "Content-Type: application/json" \
  -d '{
    "username": "admin",
    "email": "admin@dryad-lang.org",
    "password": "your_secure_password",
    "full_name": "Registry Administrator"
  }'
```

### 3. Configurar SSL (Opcional mas Recomendado)

```bash
# Instalar Certbot
sudo apt install certbot python3-certbot-nginx -y

# Obter certificado SSL
sudo certbot --nginx -d registry.dryad-lang.org

# Configurar renovação automática
sudo crontab -e
# Adicionar: 0 12 * * * /usr/bin/certbot renew --quiet
```

## 🔐 Configuração de Segurança

### Firewall

```bash
# Configurar UFW
sudo ufw allow 22    # SSH
sudo ufw allow 80    # HTTP
sudo ufw allow 443   # HTTPS
sudo ufw enable
```

### Rate Limiting

O Nginx já inclui rate limiting:
- API geral: 10 req/s
- Upload: 2 req/s
- Burst permitido para picos de tráfego

### Backup Automático

```bash
# Criar script de backup
cat > /opt/registry-backup.sh << 'EOF'
#!/bin/bash
DATE=$(date +%Y%m%d_%H%M%S)
BACKUP_DIR="/opt/backups/registry"

mkdir -p $BACKUP_DIR

# Backup dos bancos de dados
docker-compose exec -T postgres pg_dump -U gitea gitea > $BACKUP_DIR/gitea_$DATE.sql
docker-compose exec -T postgres-registry pg_dump -U registry dryad_registry > $BACKUP_DIR/registry_$DATE.sql

# Backup dos pacotes
tar -czf $BACKUP_DIR/packages_$DATE.tar.gz /var/lib/docker/volumes/registry-server_registry-packages

# Manter apenas os últimos 7 backups
find $BACKUP_DIR -name "*.sql" -mtime +7 -delete
find $BACKUP_DIR -name "*.tar.gz" -mtime +7 -delete
EOF

chmod +x /opt/registry-backup.sh

# Agendar backup diário
echo "0 2 * * * /opt/registry-backup.sh" | sudo crontab -
```

## 🔄 Manutenção e Updates

### Atualizar Serviços

```bash
# Baixar novas imagens
docker-compose pull

# Reiniciar serviços
docker-compose up -d --force-recreate

# Limpar imagens antigas
docker image prune -f
```

### Monitorar Recursos

```bash
# Uso de recursos
docker stats

# Espaço em disco
df -h
docker system df

# Limpeza de espaço
docker system prune -f
```

## 📈 Otimização de Performance

### Configuração PostgreSQL

Crie `postgres-config/postgresql.conf`:

```sql
# Otimizações para SSD
shared_buffers = 256MB
effective_cache_size = 1GB
maintenance_work_mem = 64MB
checkpoint_completion_target = 0.9
wal_buffers = 16MB
default_statistics_target = 100
random_page_cost = 1.1
effective_io_concurrency = 200
```

### Cache Redis

```bash
# Configurar cache Redis
docker-compose exec redis redis-cli CONFIG SET maxmemory 512mb
docker-compose exec redis redis-cli CONFIG SET maxmemory-policy allkeys-lru
```

## 🚨 Troubleshooting

### Problemas Comuns

**1. Container não inicia:**
```bash
# Verificar logs
docker-compose logs [service_name]

# Verificar portas
netstat -tulpn | grep :80
```

**2. Upload falhando:**
```bash
# Verificar limites de arquivo
docker-compose exec nginx cat /etc/nginx/nginx.conf | grep client_max_body_size

# Verificar espaço em disco
df -h
```

**3. Database connection error:**
```bash
# Verificar status do PostgreSQL
docker-compose exec postgres pg_isready

# Testar conexão
docker-compose exec postgres psql -U gitea -d gitea -c "SELECT 1;"
```

**4. Performance lenta:**
```bash
# Verificar recursos
htop
docker stats

# Verificar logs de erro
docker-compose logs | grep -i error
```

## 📞 Suporte

- **Issues**: https://github.com/Dryad-lang/oak-package-manager/issues
- **Documentação**: https://docs.dryad-lang.org/registry
- **Discord**: https://discord.gg/dryad-lang
- **Email**: registry@dryad-lang.org

---

**⚠️ Importante**: Sempre teste o deploy em ambiente de desenvolvimento antes de aplicar em produção!

**📝 Nota**: Esta configuração suporta facilmente 1000+ pacotes e 10000+ downloads diários em um servidor moderno (2+ CPU cores, 4GB+ RAM).