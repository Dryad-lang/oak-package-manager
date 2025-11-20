# 🛠️ Guia de Solução de Problemas - Dryad Package Manager

## ⚠️ Problemas Comuns e Soluções

### 1. **"version is obsolete" Warning**
**Problema:** `WARN[0000] the attribute 'version' is obsolete`
**Solução:** ✅ Já corrigido! O arquivo docker-compose.yml não contém mais a linha `version`.

### 2. **Containers não sobem**
```bash
# Verificar se Docker está rodando
docker info

# Limpar tudo e tentar novamente
docker-compose down -v
docker system prune -f
docker-compose up -d
```

### 3. **Porta já em uso**
```bash
# Verificar quais processos estão usando as portas
netstat -tlnp | grep :7800
netstat -tlnp | grep :7850
netstat -tlnp | grep :7832

# No Windows:
netstat -an | findstr :7800
```

### 4. **Laravel não conecta com MariaDB**
```bash
# Aguardar MariaDB ficar pronto
docker-compose logs mariadb

# Testar conexão manualmente
docker-compose exec laravel php artisan tinker
# No tinker: DB::connection()->getPdo()

# Executar migrations
docker-compose exec laravel php artisan migrate
```

### 5. **Forgejo não inicia**
```bash
# Verificar logs do Forgejo
docker-compose logs forgejo

# Recriar container se necessário
docker-compose stop forgejo
docker-compose rm forgejo
docker-compose up -d forgejo
```

### 6. **Permissões de arquivos (Linux)**
```bash
# Corrigir permissões
sudo chown -R $USER:$USER dryad-web/storage
sudo chmod -R 755 dryad-web/storage

# Para Forgejo data
sudo chown -R 1000:1000 forgejo_data/
```

### 7. **Cache do Docker**
```bash
# Limpar cache e reconstruir
docker-compose down
docker system prune -a -f
docker-compose build --no-cache
docker-compose up -d
```

## 🔍 Comandos de Diagnóstico

### Verificar Saúde dos Serviços
```bash
# Status geral
docker-compose ps

# Logs em tempo real
docker-compose logs -f

# Verificar uso de recursos
docker stats

# Testar conectividade
curl -I http://localhost:7800
curl -I http://localhost:7850
nc -zv localhost 7832
```

### Acessar Containers
```bash
# Laravel (executar comandos Artisan)
docker-compose exec laravel bash
docker-compose exec laravel php artisan --version

# MariaDB (acessar banco)
docker-compose exec mariadb mysql -u dryad -pdryad_pass dryad

# Forgejo (verificar arquivos)
docker-compose exec forgejo sh
```

## 📊 Monitoramento

### Verificar se tudo está funcionando
```bash
# Use nosso script de verificação
./verify.sh          # Linux
verify.bat           # Windows

# Ou teste manual:
curl http://localhost:7800/api/registry/packages
curl http://localhost:7850/api/v1/version
```

### Logs Importantes
```bash
# Laravel errors
docker-compose logs laravel | grep -i error

# Forgejo startup
docker-compose logs forgejo | grep -i "server"

# MariaDB ready
docker-compose logs mariadb | grep -i "ready"
```

## 🚑 Reset Completo (Última Opção)

Se nada mais funcionar:
```bash
# Parar tudo
docker-compose down -v

# Remover imagens locais
docker rmi $(docker images "oak-package-manager*" -q) 2>/dev/null

# Limpar sistema Docker
docker system prune -a -f

# Reconstruir do zero
docker-compose build --no-cache
docker-compose up -d

# Aguardar e verificar
sleep 30
./verify.sh
```

## 📞 Suporte

Se os problemas persistirem:

1. **Colete informações:**
   ```bash
   docker --version
   docker-compose --version
   docker-compose config
   docker-compose logs > debug.log
   ```

2. **Verifique:**
   - Sistema operacional e versão
   - Recursos disponíveis (RAM, disk)
   - Outras aplicações usando as mesmas portas

3. **Teste básico:**
   ```bash
   docker run hello-world
   ```