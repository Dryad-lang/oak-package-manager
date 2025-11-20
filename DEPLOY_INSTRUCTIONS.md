# 🚀 Deploy Corrigido - Instruções para VM

## 📝 Correções Implementadas

### ✅ Problemas Resolvidos:
1. **Portas padronizadas** para range 7800-7900
2. **Volume removido** que sobrescrevia vendor/
3. **Scripts atualizados** com portas corretas
4. **Conflitos eliminados** com serviços locais

## 🎯 Execute na sua VM:

```bash
# 1. Baixar correções
cd ~/dryad/oak-package-manager
git pull origin main

# 2. Limpar ambiente anterior
docker compose down -v

# 3. Opcional: Verificar mudanças
cat PORTS_CONFIGURATION.md | head -20

# 4. Deploy com correções
./deploy.sh
```

## 🌐 URLs Corretas Após Deploy:

- **Frontend Principal:** http://localhost:7800
- **Registry API:** http://localhost:7840  
- **Nginx Proxy:** http://localhost:7880
- **PostgreSQL:** localhost:7832 (para ferramentas externas)

## 🔍 Verificações Esperadas:

### ✅ Deve Funcionar:
- ✅ Laravel sem erro de vendor/autoload.php
- ✅ PostgreSQL na porta 7832 (sem conflito)
- ✅ Registry API respondendo na 7840
- ✅ Frontend acessível na 7800

### 📊 Logs Esperados:
```
✅ Registry API está funcionando (porta 7840)
✅ Laravel Web está funcionando (porta 7800)
✅ PostgreSQL conectado
✅ Migrações executadas com sucesso
```

## 🆘 Se Houver Problemas:

```bash
# Ver logs específicos
docker compose logs dryad-web
docker compose logs dryad-postgres

# Reiniciar um serviço específico
docker compose restart dryad-web

# Status completo
docker compose ps
```

Execute o deploy e me informe o resultado! 🚀