# 🔌 Portas Configuradas - Dryad Package Manager

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