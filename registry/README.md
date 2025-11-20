# 🌐 Registry Dryad - Repositório de Pacotes

**Versão:** 1.0  
**Status:** Estrutura de Exemplo  
**Última atualização:** 20 de novembro de 2025

---

## 📋 Visão Geral

Este é um exemplo de estrutura para o registry oficial de pacotes Dryad. O registry será hospedado externamente e acessado pelo Oak através de APIs HTTP.

## 🏗️ Estrutura do Registry

```
registry/
├── api/
│   ├── packages/           # Metadados dos pacotes
│   │   ├── matematica-utils.json
│   │   ├── dryad-stdlib.json
│   │   └── file-utils.json
│   └── health              # Endpoint de status
├── packages/               # Arquivos dos pacotes
│   ├── matematica-utils/
│   │   ├── 1.0.0.tar.gz
│   │   └── 1.1.0.tar.gz
│   ├── dryad-stdlib/
│   │   └── 0.1.0.tar.gz
│   └── file-utils/
│       └── 2.0.0.tar.gz
└── index.json             # Índice geral
```

## 📡 API Endpoints

### 🔍 Buscar Pacote
```
GET /api/packages/{nome}
```
Retorna metadados do pacote incluindo versões disponíveis.

### 📥 Download do Pacote
```
GET /packages/{nome}/{versao}.tar.gz
```
Download do arquivo compactado do pacote.

### 🏥 Health Check
```
GET /api/health
```
Verifica se o registry está operacional.

### 📋 Listar Pacotes
```
GET /api/packages
```
Lista todos os pacotes disponíveis.

## 📦 Formato de Metadados

Cada pacote tem um arquivo JSON com seus metadados:

```json
{
  "name": "matematica-utils",
  "version": "1.1.0",
  "description": "Biblioteca de utilitários matemáticos para Dryad",
  "author": "Dryad Community",
  "license": "MIT",
  "dependencies": {
    "dryad-stdlib": "^0.1.0"
  },
  "download_url": "https://registry.dryad-lang.org/packages/matematica-utils/1.1.0.tar.gz",
  "checksum": "sha256:a1b2c3d4e5f6...",
  "file_size": 15420
}
```

## 🔐 Verificação de Integridade

- Todos os pacotes têm checksums SHA256
- Downloads são verificados automaticamente pelo Oak
- Arquivos corrompidos são detectados e rejeitados

## 🚀 Como o Oak Usa o Registry

1. **Busca**: `oak install matematica-utils`
2. **Query**: GET `/api/packages/matematica-utils`
3. **Download**: GET `/packages/matematica-utils/1.1.0.tar.gz`
4. **Verificação**: Checksum SHA256
5. **Extração**: Descompacta em `oak_modules/`
6. **Cache**: Salva em `.oak/cache/` para uso futuro

## 🌍 Registries Alternativos

O Oak suporta múltiplos registries:

- **Registry Oficial**: `https://registry.dryad-lang.org`
- **GitHub Packages**: `https://raw.githubusercontent.com/Dryad-lang/packages`
- **Registry Local**: Para desenvolvimento e testes
- **Registry Privado**: Para empresas e organizações

### Configuração de Registry

```bash
# Listar registries
oak registry list

# Adicionar registry personalizado
oak registry add minha-empresa https://packages.minhaempresa.com

# Testar conectividade
oak registry test minha-empresa

# Definir como padrão
oak registry set-default minha-empresa
```

## 📁 Estrutura de Pacotes

Cada pacote `.tar.gz` contém:

```
matematica-utils/
├── oaklibs.json           # Configuração do pacote
├── src/                   # Código fonte principal
│   └── main.dryad
├── lib/                   # Módulos exportáveis
│   ├── algebra.dryad
│   ├── geometria.dryad
│   └── estatistica.dryad
├── tests/                 # Testes (opcional)
│   └── test_algebra.dryad
└── README.md              # Documentação
```

## 🔄 Versionamento Semântico

Seguimos o padrão SemVer:
- `1.0.0` - Major.Minor.Patch
- `^1.0.0` - Compatible com versões 1.x.x
- `~1.0.0` - Compatible com versões 1.0.x
- `1.0.0` - Exatamente esta versão

## 🛠️ Desenvolvimento

Para contribuir com pacotes para o registry:

1. Criar biblioteca Dryad: `oak init meu-pacote --type library`
2. Desenvolver e testar o pacote
3. Configurar metadados em `oaklibs.json`
4. Submeter para revisão da comunidade
5. Publicação no registry oficial

## 📈 Estatísticas (Exemplo)

- **Pacotes Disponíveis**: 156
- **Downloads Totais**: 12,450
- **Desenvolvedores Ativos**: 23
- **Última Atualização**: Hoje

---

**Mantido por**: Comunidade Dryad  
**Suporte**: https://github.com/Dryad-lang/registry/issues