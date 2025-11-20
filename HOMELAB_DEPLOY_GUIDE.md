# 🏠 Deploy Final para Homelab - Dryad Package Manager

## ✅ Correções Implementadas

### 🔧 **Problemas Resolvidos:**
1. **❌ DNS Resolution**: Gitea agora usa `dryad-postgres` como hostname
2. **❌ SQL Errors**: Script PostgreSQL corrigido (removido `ECHO` inválido)
3. **❌ Startup Order**: Dependências robustas com health checks
4. **❌ Network Issues**: Rede Docker configurada com subnet dedicado
5. **❌ Health Checks**: Timeouts e retries otimizados

### 🚀 **Melhorias Implementadas:**
- ✅ **PostgreSQL**: Health check robusto com 10 retries
- ✅ **Gitea**: Configuração simplificada sem scripts customizados  
- ✅ **Registry API**: Dependência apenas do PostgreSQL
- ✅ **Network**: Subnet dedicado `172.20.0.0/16`
- ✅ **Restart Policies**: `unless-stopped` para todos os serviços

## 🚀 **Deploy no seu Homelab**

### **Opção 1: Linux/macOS**
```bash
# Clone e execute
git clone <repo-url>
cd oak-package-manager
chmod +x homelab-deploy.sh
./homelab-deploy.sh
```

### **Opção 2: Windows**
```cmd
# Clone e execute
git clone <repo-url>
cd oak-package-manager
homelab-deploy.bat
```

## 🌐 **URLs do Sistema**

| Serviço | URL | Porta | Status |
|---------|-----|-------|--------|
| **🌐 Frontend Laravel** | http://localhost:7800 | 7800 | ✅ Ready |
| **🔧 Registry API** | http://localhost:7840 | 7840 | ✅ Ready |
| **🔧 Gitea Git Server** | http://localhost:7850 | 7850 | ✅ Ready |
| **🌍 Nginx Proxy** | http://localhost:7880 | 7880 | ✅ Ready |
| **🐘 PostgreSQL** | localhost:7832 | 7832 | ✅ Ready |
| **📊 Redis Cache** | localhost:7879 | 7879 | ✅ Ready |

## ⏱️ **Tempo de Inicialização**

| Serviço | Tempo | Descrição |
|---------|-------|-----------|
| PostgreSQL | ~40s | Criação de bancos + health check |
| Gitea | ~60s | Configuração inicial + DB connection |
| Laravel | ~60s | Migrações + container startup |
| Registry API | ~45s | Dependente do PostgreSQL |

## 🔧 **Configuração Inicial**

### **1. Gitea (Primeira Execução)**
1. Acesse: http://localhost:7850
2. Configure com as credenciais:
   - **Database**: PostgreSQL
   - **Host**: `dryad-postgres:5432`
   - **User**: `gitea`
   - **Password**: `gitea_password`
   - **Database**: `gitea`

### **2. Laravel Frontend**
- URL: http://localhost:7800
- **Database já configurado automaticamente**

### **3. Registry API**
- URL: http://localhost:7840/api/health
- **Integração automática com Gitea**

## 📋 **Comandos Úteis**

```bash
# Ver status dos containers
docker compose ps

# Ver logs em tempo real
docker compose logs -f

# Ver logs de um serviço específico
docker compose logs -f dryad-web
docker compose logs -f gitea
docker compose logs -f postgres

# Parar sistema
docker compose down

# Parar e remover tudo (reset completo)
docker compose down -v --remove-orphans

# Restart de um serviço específico
docker compose restart gitea
```

## 🛠️ **Troubleshooting**

### **Gitea não conecta no PostgreSQL**
```bash
# Verificar se PostgreSQL está healthy
docker compose ps postgres

# Verificar logs do Gitea
docker compose logs gitea

# Restart do Gitea (após PostgreSQL estar pronto)
docker compose restart gitea
```

### **Laravel não conecta no PostgreSQL**
```bash
# Verificar configurações
docker compose exec dryad-web cat .env

# Executar migrações manualmente
docker compose exec dryad-web php artisan migrate

# Verificar conectividade
docker compose exec dryad-web php artisan tinker
```

### **Registry API sem conectividade**
```bash
# Verificar health
curl http://localhost:7840/api/health

# Verificar logs
docker compose logs registry-api
```

## 📊 **Monitoramento**

### **Health Checks**
```bash
# Sistema completo
curl http://localhost:7840/api/health

# Gitea
curl http://localhost:7850

# Laravel
curl http://localhost:7800

# Nginx
curl http://localhost:7880
```

### **Recursos do Sistema**
```bash
# Uso de recursos
docker stats

# Espaço em disco
docker system df

# Volumes
docker volume ls
```

## 🎯 **Resultado Final**

Após executar o script de deploy, você terá:

- ✅ **Sistema completo funcionando** em ~2-3 minutos
- ✅ **PostgreSQL** com múltiplos bancos configurados
- ✅ **Gitea** para controle de versão de pacotes
- ✅ **Registry API** para gerenciamento de pacotes
- ✅ **Laravel Frontend** para interface web
- ✅ **Nginx Proxy** para roteamento
- ✅ **Redis Cache** para performance

---

**🚀 Execute `./homelab-deploy.sh` (Linux) ou `homelab-deploy.bat` (Windows) e terá um Package Manager completo rodando no seu homelab!**