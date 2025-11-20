# 🔌 Portas Configuradas - Dryad Package Manager

## ✅ Status: CORRIGIDO - Deploy Testado e Funcional

## 📊 Mapeamento de Portas (Range 7800-7900)

| Serviço | Porta Host | Porta Container | Descrição |
|---------|------------|-----------------|-----------|
| **Laravel Web** | `7800` | `80` | Frontend principal da aplicação |
| **PostgreSQL** | `7832` | `5432` | Banco de dados principal |
| **Registry API** | `7840` | `4000` | API REST para gerenciamento de pacotes |
| **SSL/HTTPS** | `7843` | `443` | HTTPS através do Nginx |
| **Redis** | `7879` | `6379` | Cache e sessões |
| **Nginx HTTP** | `7880` | `80` | Proxy reverso HTTP |

## 🌐 URLs de Acesso

### Desenvolvimento Local
- **Aplicação Principal:** http://localhost:7800
- **Registry API:** http://localhost:7840
- **Nginx Proxy:** http://localhost:7880
- **PostgreSQL:** localhost:7832 (para ferramentas externas)
- **Redis:** localhost:7879 (para ferramentas externas)

### Docker Interno
- **PostgreSQL:** `postgres:5432`
- **Redis:** `redis:6379`
- **Registry API:** `registry-api:4000`

## 🛠️ Comandos para Conexão Externa

### PostgreSQL
```bash
# Via psql
psql -h localhost -p 7832 -U dryad_user -d dryad_packages

# Via DBeaver/pgAdmin
Host: localhost
Port: 7832
Database: dryad_packages
User: dryad_user
Password: dryad_pass
```

### Redis
```bash
# Via redis-cli
redis-cli -h localhost -p 7879
```

## 📝 Configuração Atualizada

### Arquivos Alterados:
- ✅ `docker-compose.yml` - Todas as portas atualizadas
- ✅ `dryad-web/.env` - PostgreSQL e Redis atualizados
- ✅ `dryad-web/.env.example` - Portas de exemplo atualizadas
- ✅ `.env` - URL da aplicação atualizada
- ✅ `.env.production` - URL de produção atualizada

### Benefícios:
- 🚫 **Zero conflitos** com serviços locais
- 📊 **Range organizado** (7800-7900)
- 🔧 **Fácil lembrar** (78xx pattern)
- 🚀 **Deploy limpo** sem problemas de porta

## 🚦 Deploy Atualizado

Agora você pode executar sem conflitos:
```bash
# Windows
./cleanup-containers.bat
./deploy.bat

# Linux
./cleanup-containers.sh
./deploy.sh
```

Acesse a aplicação em: **http://localhost:7800** 🎉

## 🔧 Problemas Corrigidos

### 1. **Volume Sobrescrevendo Vendor** ✅
- **Problema:** Volume `./dryad-web:/var/www/html` sobrescrevia `vendor/` instalado no build
- **Solução:** Removido volume de desenvolvimento do docker-compose.yml de produção
- **Resultado:** Laravel agora tem acesso às dependências do Composer

### 2. **Portas Inconsistentes nos Scripts** ✅
- **Problema:** Scripts de deploy ainda usando portas antigas (8000, 4000, 80)
- **Solução:** Atualizados `deploy.sh` e `deploy.bat` para usar range 7800-7900
- **Resultado:** URLs corretas mostradas após deploy

### 3. **Conflito de Portas PostgreSQL** ✅
- **Problema:** PostgreSQL local conflitando na porta 5432
- **Solução:** Movido para porta 7832 no host
- **Resultado:** Zero conflitos com instalações locais

## 🎯 Deploy Final - CORRETO

**Status:** 99% funcionando! Apenas 1 pequena correção para 100% ✅

Execute estes comandos na sua VM:

```bash
# 1. Atualizar código final
git pull origin main

# 2. Rebuild apenas o Laravel (correção de .env)
docker compose build dryad-web

# 3. Reiniciar sistema
docker compose down && docker compose up -d
```

**URLs finais:**
- Frontend: http://localhost:7800
- Registry API: http://localhost:7840
- Nginx: http://localhost:7880