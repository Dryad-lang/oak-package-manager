# Configuração de Portas - Dryad Package Manager
# Range: 7800-7900

## Serviços Principais
- Laravel Web Frontend: 7800
- Forgejo Git Server: 7850  
- Forgejo SSH: 7822
- MariaDB: 7832

## URLs de Acesso
- Frontend: http://localhost:7800
- Forgejo: http://localhost:7850
- Registry API: http://localhost:7800/api/registry

## Para desenvolvimento local
Se estiver desenvolvendo localmente sem Docker:
- Laravel: php artisan serve --host=0.0.0.0 --port=7800
- MariaDB: Usar porta 7832 ou padrão 3306

## Comandos úteis
```bash
# Verificar portas em uso
netstat -tlnp | grep :78

# Testar conectividade
curl http://localhost:7800
curl http://localhost:7850
nc -zv localhost 7832
```