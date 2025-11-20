# 🐳 Docker Troubleshooting Guide

## Problemas Comuns e Soluções

### 1. 🚨 Erro: "Package 'sqlite3' not found"
**Problema:** Extensões PHP não conseguem ser instaladas por falta de dependências do sistema.

**Solução:**
```bash
# O Dockerfile agora inclui todas as dependências necessárias:
# - sqlite-dev (para pdo_sqlite)
# - postgresql-dev (para pdo_pgsql)  
# - libzip-dev (para zip)
# - oniguruma-dev (para mbstring)
```

### 2. 🚨 Erro: "PostgreSQL timeout"
**Problema:** Laravel não consegue conectar ao PostgreSQL.

**Solução:**
```bash
# Verificar se o PostgreSQL está rodando
docker-compose ps postgres

# Ver logs do PostgreSQL
docker-compose logs postgres

# Reiniciar apenas o PostgreSQL
docker-compose restart postgres
```

### 3. 🚨 Erro: "Migration failed"
**Problema:** Migrações falham por problemas de conexão ou schema.

**Solução:**
```bash
# Verificar status das migrações
docker exec -it dryad-web php artisan migrate:status

# Executar migrações manualmente
docker exec -it dryad-web php artisan migrate --force

# Reset completo do banco (CUIDADO: apaga dados!)
docker exec -it dryad-web php artisan migrate:fresh --seed
```

### 4. 🚨 Erro: "Build failed"
**Problema:** Docker build falha na instalação de extensões.

**Soluções:**
```bash
# 1. Limpar cache do Docker
docker system prune -f
docker-compose down -v
docker rmi $(docker images -q)

# 2. Rebuild com cache limpo
docker-compose build --no-cache dryad-web

# 3. Testar build isolado
docker build -t test-dryad ./dryad-web/
```

### 5. 🚨 Erro: "Permission denied"
**Problema:** Problemas de permissão em volumes ou arquivos.

**Solução:**
```bash
# Linux/Mac
sudo chown -R $USER:$USER ./dryad-web/storage ./dryad-web/bootstrap/cache

# Windows (executar como Admin)
icacls "dryad-web\storage" /grant Everyone:(OI)(CI)F
icacls "dryad-web\bootstrap\cache" /grant Everyone:(OI)(CI)F
```

### 6. 🚨 Erro: "Port already in use"
**Problema:** Portas já estão sendo usadas por outros serviços.

**Solução:**
```bash
# Verificar quais portas estão em uso
netstat -tulpn | grep :5432  # PostgreSQL
netstat -tulpn | grep :8000  # Laravel
netstat -tulpn | grep :4000  # Registry API

# Parar serviços conflitantes
sudo systemctl stop postgresql  # PostgreSQL local
sudo systemctl stop apache2    # Apache local
sudo systemctl stop nginx      # Nginx local
```

## 🧪 Scripts de Teste

### Build Test
```bash
# Linux/Mac
./test-build.sh

# Windows  
test-build.bat
```

### Verificação de Saúde
```bash
# Verificar todos os serviços
docker-compose ps

# Verificar logs específicos
docker-compose logs dryad-web
docker-compose logs postgres
docker-compose logs registry-api

# Testar conexão PostgreSQL
docker exec -it dryad-postgres psql -U dryad_user -d dryad_packages -c "SELECT version();"
```

## 🔧 Comandos Úteis

### Debugging
```bash
# Entrar no container Laravel
docker exec -it dryad-web bash

# Entrar no container PostgreSQL
docker exec -it dryad-postgres psql -U dryad_user -d dryad_packages

# Ver configuração PHP
docker exec -it dryad-web php -i

# Testar conexão de rede entre containers
docker exec -it dryad-web nc -zv postgres 5432
```

### Limpeza Completa
```bash
# Parar tudo e limpar volumes
docker-compose down -v

# Remover imagens do projeto
docker images | grep oak-package-manager | awk '{print $3}' | xargs docker rmi

# Limpar sistema Docker completo
docker system prune -af --volumes
```

## 📋 Checklist de Deploy

- [ ] Docker Desktop instalado e rodando
- [ ] Docker Compose versão 2.0+
- [ ] Arquivo `.env` configurado corretamente
- [ ] Portas 5432, 8000, 4000, 80 disponíveis
- [ ] Pelo menos 2GB RAM disponível
- [ ] Pelo menos 5GB espaço em disco

## 🆘 Se Nada Funcionar

1. **Backup dos dados importantes**
2. **Limpeza completa:**
   ```bash
   docker-compose down -v
   docker system prune -af --volumes
   docker network prune -f
   ```
3. **Reiniciar Docker Desktop**
4. **Executar deploy novamente:**
   ```bash
   ./deploy.sh  # ou deploy.bat
   ```

## 📞 Suporte

- **Issues:** https://github.com/Dryad-lang/oak-package-manager/issues
- **Documentação:** Ver arquivos `*.md` no repositório
- **Logs:** Sempre incluir logs completos ao reportar problemas