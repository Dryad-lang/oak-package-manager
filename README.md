# 🌳 Oak Package Manager

**Gerenciador de pacotes completo para a linguagem Dryad**

[![Rust](https://img.shields.io/badge/Rust-1.70+-orange.svg)](https://www.rust-lang.org/)
[![Laravel](https://img.shields.io/badge/Laravel-12-red.svg)](https://laravel.com/)
[![Docker](https://img.shields.io/badge/Docker-Ready-blue.svg)](https://www.docker.com/)
[![License](https://img.shields.io/badge/License-MIT-yellow.svg)](https://opensource.org/licenses/MIT)

---

## 🎯 Visão Geral

O **Oak** é o gerenciador de pacotes oficial da linguagem Dryad, oferecendo uma solução completa para desenvolvimento, distribuição e hospedagem de pacotes Dryad. Sistema integrado com CLI, Registry API e Frontend Web.

### 🚀 Componentes Principais

| Componente | Tecnologia | Porta | Status |
|------------|------------|-------|--------|
| **🛠️ Oak CLI** | Rust + Dryad Runtime | - | ✅ Funcional |
| **🌐 Registry API** | Node.js + Express | 4000 | ✅ Funcional |
| **🎨 Web Interface** | Laravel 12 + Bootstrap | 8000 | ✅ Funcional |
| **🗄️ Database** | PostgreSQL 16 | 5432 | ✅ Configurado |
| **🔄 Nginx Proxy** | Nginx Alpine | 80 | ✅ Configurado |
| **⚡ Cache** | Redis 7 | 6379 | ✅ Opcional |

## 🚀 Deploy Rápido (5 minutos)

### Pré-requisitos
- Docker Desktop instalado
- Git instalado

### Comando único
```bash
# Clone o repositório
git clone https://github.com/Dryad-lang/oak-package-manager.git
cd oak-package-manager

# Execute o deploy (Windows)
deploy.bat

# Ou no Linux/Mac
chmod +x deploy.sh
./deploy.sh
```

### URLs após o deploy
- 🌐 **Frontend**: http://localhost:8000
- 🔧 **Registry API**: http://localhost:4000
- 📊 **Health Check**: http://localhost:4000/api/health

## 🛠️ Desenvolvimento Local

### Oak CLI
```bash
cd dryad_base
cargo run --bin oak init meu-projeto
cargo run --bin oak install matematica-utils
cargo run --bin oak registry test
```

### Registry API (Standalone)
```bash
cd registry-server/registry-api
npm install
npm start
```

### Laravel Web (Standalone)
```bash
cd dryad-web
composer install
php artisan serve --port=8000
```

## 📁 Estrutura do Projeto

```
oak-package-manager/
├── 🦀 dryad_base/           # Oak CLI (Rust)
├── 🌐 dryad-web/            # Laravel Frontend
├── 🔧 registry-server/      # Node.js Registry API  
├── 📦 registry/             # Dados de exemplo
├── 🐳 docker-compose.yml    # Orquestração
├── 🚀 deploy.bat/.sh        # Scripts de deploy
└── 📖 README.md             # Este arquivo
```

## 🔧 Comandos Úteis

### Docker
```bash
# Ver logs em tempo real
docker-compose logs -f

# Status dos containers  
docker-compose ps

# Parar todos os serviços
docker-compose down

# Reiniciar serviços
docker-compose restart

# Reconstruir tudo
docker-compose up --build -d
```

### Oak CLI
```bash
# Criar novo projeto
oak init projeto-exemplo

# Instalar dependência
oak install matematica-utils --version 1.1.0

# Buscar pacotes  
oak search math

# Informações de um pacote
oak info matematica-utils

# Testar conectividade
oak registry test
```

## 🔌 Integração de Sistemas

### Oak CLI → Registry API
- **Endpoint**: `http://localhost:4000/api/packages`
- **Autenticação**: Não requerida (desenvolvimento)
- **Cache**: Configurado no Oak CLI

### Laravel → Registry API  
- **Service**: `App\Services\PackageService`
- **Cache**: Redis/Database (300s TTL)
- **Fallback**: Dados estáticos em caso de falha

### Registry API → Laravel
- **CORS**: Configurado para localhost:8000
- **Rate Limiting**: 100 req/min por IP
- **Health Check**: `/api/health`

## 🎨 Funcionalidades do Frontend

- ✅ **Homepage**: Pacotes em destaque + estatísticas
- ✅ **Busca**: Busca avançada com filtros
- ✅ **Pacotes**: Listagem e detalhes de pacotes  
- ✅ **Dashboard**: Área do desenvolvedor
- ✅ **Autenticação**: Login/registro de usuários
- ✅ **Responsivo**: Bootstrap 5 + design moderno

## 🛡️ Segurança e Performance

### Registry API
- Rate limiting (100 req/min)
- CORS configurado
- Helmet.js para headers de segurança
- Compressão gzip

### Laravel
- CSRF protection
- SQL injection protection (Eloquent)
- XSS protection (Blade templates)
- Cache de configuração

### Nginx
- Rate limiting customizado
- Headers de segurança
- Proxy reverso otimizado

## 📊 Monitoramento

### Health Checks
- **Registry API**: `GET /api/health`
- **Laravel**: Status automático via Nginx
- **Docker**: Health checks configurados

### Logs
```bash
# Todos os logs
docker-compose logs -f

# Apenas Registry API
docker-compose logs -f registry-api

# Apenas Laravel
docker-compose logs -f dryad-web
```

## 🔄 CI/CD e Deploy

### Ambientes Suportados
- ✅ **Desenvolvimento**: Docker Compose local
- ✅ **Produção**: Docker Swarm/Kubernetes ready
- ✅ **Cloud**: AWS/GCP/Azure compatível

### Variables de Ambiente
```bash
# Registry
DRYAD_REGISTRY_URL=http://localhost:4000
DRYAD_REGISTRY_TIMEOUT=10

# Laravel
APP_ENV=production  
APP_DEBUG=false
CACHE_STORE=database
```

## 🤝 Contribuindo

1. Fork o repositório
2. Crie uma branch: `git checkout -b feature/nova-feature`
3. Faça commit: `git commit -am 'Add nova feature'`
4. Push: `git push origin feature/nova-feature`
5. Abra um Pull Request

## 📜 Licença

Este projeto está licenciado sob a MIT License - veja o arquivo [LICENSE](LICENSE) para detalhes.

## 🆘 Suporte

- 📖 **Documentação**: [docs.dryad-lang.org](https://docs.dryad-lang.org)
- 🐛 **Issues**: [GitHub Issues](https://github.com/Dryad-lang/oak-package-manager/issues)
- 💬 **Discord**: [Dryad Community](https://discord.gg/dryad)
- 📧 **Email**: support@dryad-lang.org

---

⭐ **Gostou do projeto? Dê uma estrela no GitHub!**

---

## 🏗️ Estrutura do Projeto

```
oak-package-manager/
├── oak/                    # 🛠️ Cliente CLI (Rust)
│   ├── src/
│   │   └── main.rs        # Interface de linha de comando
│   └── Cargo.toml         # Dependências Rust
│
├── registry-server/       # 🌐 Servidor Registry (Docker)
│   ├── registry-api/      # API REST (Node.js)
│   ├── web-interface/     # Dashboard (React/Next.js)
│   ├── nginx/            # Proxy reverso + SSL
│   └── docker-compose.yml # Orquestração completa
│
├── registry/             # 📦 Estrutura de exemplo
│   └── api/packages/     # Metadados dos pacotes
│
└── manuals/             # 📚 Documentação técnica
    ├── SYNTAX.md        # Sintaxe da linguagem Dryad
    ├── DEVELOPER_MANUAL.md
    └── ...
```

---

## 🚀 Início Rápido

### 1. 🛠️ Instalar o Oak CLI

```bash
# Clonar repositório
git clone https://github.com/Dryad-lang/oak-package-manager.git
cd oak-package-manager/oak

# Compilar Oak
cargo build --release

# Instalar globalmente (opcional)
cargo install --path .
```

### 2. 📦 Usar o Oak

```bash
# Criar novo projeto
oak init meu-projeto --type project

# Instalar dependências
oak install matematica-utils

# Executar scripts
oak run start

# Gerenciar registry
oak registry list
oak registry add meu-registry https://my-registry.com
```

### 3. 🌐 Hospedar Registry Próprio

```bash
# Navegar para o registry server
cd ../registry-server

# Configurar ambiente
cp .env.example .env
# Editar .env com suas configurações

# Subir todos os serviços
docker-compose up -d

# Acessar interface web
open http://oak.dryadlang.org
```

---

## 🌟 Características do Oak CLI

- ✅ **Gestão de Projetos** - Criação e configuração automática
- ✅ **Resolução de Dependências** - Sistema inteligente de versionamento
- ✅ **Registry Remoto** - Download automático via HTTP/HTTPS
- ✅ **Cache Local** - Otimização de downloads repetidos
- ✅ **Múltiplos Registries** - Suporte a registries públicos e privados
- ✅ **Verificação de Integridade** - Checksums SHA256 automáticos
- ✅ **Fallback Inteligente** - Modo simulado quando registry indisponível
- ✅ **Scripts Customizados** - Sistema de tarefas flexível

## 🏢 Características do Registry Server

- ✅ **Deploy com Docker** - Configuração completa em containers
- ✅ **Interface Web** - Upload via drag & drop
- ✅ **Git Server Integrado** - Gitea para versionamento
- ✅ **API REST Completa** - Compatível com Oak CLI
- ✅ **Autenticação JWT** - Sistema seguro de usuários
- ✅ **Rate Limiting** - Proteção contra abuso
- ✅ **SSL/HTTPS** - Certificados automáticos
- ✅ **Backup Automático** - Scripts de manutenção incluídos

---

## 📊 Comandos Disponíveis

### 🛠️ Gestão de Projetos
```bash
oak init <nome> [--type project|library]    # Criar projeto
oak info                                    # Informações do projeto
oak clean                                   # Limpar cache
oak lock                                    # Gerar oaklock.json
```

### 📦 Gestão de Pacotes
```bash
oak install [pacote] [--version 1.0.0]     # Instalar dependências
oak remove <pacote>                         # Remover pacote
oak list                                    # Listar dependências
oak update                                  # Atualizar dependências
```

### 🌐 Gestão de Registry
```bash
oak registry list                           # Listar registries
oak registry add <nome> <url>              # Adicionar registry
oak registry remove <nome>                  # Remover registry
oak registry set-default <nome>            # Definir padrão
oak registry test [nome]                   # Testar conectividade
```

### ⚙️ Executar Tarefas
```bash
oak run <script>                           # Executar script
oak run start                              # Executar aplicação
oak run test                               # Executar testes
```

---

## 🔧 Configuração

### oaklibs.json (Projeto)
```json
{
  "name": "meu-projeto",
  "version": "1.0.0",
  "description": "Meu projeto Dryad",
  "author": "Seu Nome",
  "license": "MIT",
  "type": "project",
  "main": "main.dryad",
  "dependencies": {
    "matematica-utils": "^1.0.0",
    "dryad-stdlib": "^0.1.0"
  },
  "scripts": {
    "start": "dryad run main.dryad",
    "test": "dryad test",
    "check": "dryad check main.dryad"
  }
}
```

### oak-registry.json (Registry)
```json
{
  "default_registry": "oficial",
  "registries": {
    "oficial": "https://registry.dryad-lang.org",
    "github": "https://raw.githubusercontent.com/Dryad-lang/packages",
    "local": "http://oak.dryadlang.org:4000"
  },
  "cache_dir": ".oak/cache"
}
```

---

## 🌍 Ecossistema Dryad

### Registries Oficiais
- **Oficial**: `https://registry.dryad-lang.org` (Em desenvolvimento)
- **GitHub**: `https://raw.githubusercontent.com/Dryad-lang/packages`
- **Community**: Registries mantidos pela comunidade

### Pacotes Populares
- `dryad-stdlib` - Biblioteca padrão oficial
- `matematica-utils` - Utilitários matemáticos
- `file-utils` - Manipulação de arquivos
- `crypto-utils` - Funções criptográficas

---

## 🚀 Deploy em Produção

### Requisitos do Servidor
- **CPU**: 2+ cores
- **RAM**: 4GB+
- **Storage**: 50GB+ SSD
- **OS**: Ubuntu 20.04+ ou similar
- **Docker**: 20.10+
- **Docker Compose**: 2.0+

### Deploy Rápido
```bash
# Preparar servidor
sudo apt update && sudo apt upgrade -y
curl -fsSL https://get.docker.com -o get-docker.sh
sudo sh get-docker.sh

# Clonar e configurar
git clone https://github.com/Dryad-lang/oak-package-manager.git
cd oak-package-manager/registry-server
cp .env.example .env
# Editar .env com configurações de produção

# Subir serviços
docker-compose up -d

# Configurar SSL (opcional)
sudo certbot --nginx -d registry.dryad-lang.org
```

---

## 🔐 Segurança

### Registry Server
- **🔒 JWT Authentication** - Tokens seguros
- **🛡️ Rate Limiting** - Proteção DDoS
- **🔐 Password Hashing** - bcrypt com salt
- **🌐 CORS Protection** - Configuração restritiva
- **📝 Input Validation** - Joi schemas
- **🔍 Security Headers** - Helmet.js

### Oak CLI
- **✅ Checksum Verification** - SHA256 automático
- **🔒 HTTPS Only** - Conexões seguras
- **📁 Sandbox Downloads** - Isolamento de arquivos
- **🛡️ Input Sanitization** - Validação rigorosa

---

## 📈 Roadmap

### Oak CLI v0.2.0
- [ ] Suporte a workspaces
- [ ] Plugin system
- [ ] Resolução de dependências avançada
- [ ] Modo offline melhorado

### Registry Server v2.0.0
- [ ] Dashboard avançado com métricas
- [ ] Webhook support
- [ ] Package scanning (segurança)
- [ ] CDN integration
- [ ] Multi-registry federation

### Futuro
- [ ] Registry móvel (iOS/Android)
- [ ] IDE plugins (VSCode, IntelliJ)
- [ ] CI/CD integrations
- [ ] Package analytics

---

## 🤝 Contribuindo

1. **Fork** o projeto
2. **Crie** uma branch (`git checkout -b feature/nova-funcionalidade`)
3. **Commit** suas mudanças (`git commit -am 'Add: nova funcionalidade'`)
4. **Push** para a branch (`git push origin feature/nova-funcionalidade`)
5. **Abra** um Pull Request

### 🧪 Executar Testes

```bash
# Testes do Oak CLI
cd oak && cargo test

# Testes do Registry API
cd registry-server/registry-api && npm test

# Testes da Web Interface
cd registry-server/web-interface && npm test
```

---

## 📄 Licença

Este projeto está licenciado sob a licença MIT - veja o arquivo [LICENSE](LICENSE) para detalhes.

---

## 🆘 Suporte e Comunidade

- **📖 Documentação**: [docs.dryad-lang.org](https://docs.dryad-lang.org)
- **🐛 Issues**: [GitHub Issues](https://github.com/Dryad-lang/oak-package-manager/issues)
- **💬 Discord**: [discord.gg/dryad-lang](https://discord.gg/dryad-lang)
- **📧 Email**: oak@dryad-lang.org
- **🐦 Twitter**: [@DryadLang](https://twitter.com/DryadLang)

---

**Feito com ❤️ pela comunidade Dryad**

*⭐ Se este projeto te ajudou, considera dar uma estrela no GitHub!*
