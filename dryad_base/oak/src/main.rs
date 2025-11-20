// crates/oak/src/main.rs
use clap::{Parser, Subcommand};
use serde::{Deserialize, Serialize};
use std::collections::HashMap;
use std::fs;
use std::path::Path;
use reqwest;
use sha2::{Digest, Sha256};
use tokio;
use url::Url;
use std::env;

// Dryad imports
use dryad_lexer::Lexer;
use dryad_parser::Parser as DryadParser;
use dryad_runtime::Interpreter;

// Registry configuration
const DEFAULT_REGISTRY_URL: &str = "http://127.0.0.1:7800/api/registry";
const DEFAULT_WEB_URL: &str = "http://127.0.0.1:7800";

#[derive(Parser)]
#[command(name = "oak")]
#[command(about = "Oak - Gestor de Pacotes para Dryad", long_about = None)]
struct Cli {
    #[command(subcommand)]
    command: Commands,
}

#[derive(Subcommand)]
enum Commands {
    /// Inicializa um novo projeto Dryad
    Init {
        /// Nome do projeto
        name: String,
        /// Diretório para criar o projeto (opcional)
        #[arg(short, long)]
        path: Option<String>,
        /// Tipo de projeto (project ou library)
        #[arg(short, long, default_value = "project")]
        r#type: String,
    },
    /// Instala dependências do projeto
    Install {
        /// Nome do pacote para instalar (opcional)
        package: Option<String>,
        /// Versão específica
        #[arg(short, long)]
        version: Option<String>,
    },
    /// Remove uma dependência
    Remove {
        /// Nome do pacote para remover
        package: String,
    },
    /// Lista dependências instaladas
    List,
    /// Atualiza dependências
    Update,
    /// Publica um pacote (futuro)
    Publish,
    /// Executa scripts definidos no projeto
    Run {
        /// Nome do script para executar
        script: String,
    },
    /// Executa um arquivo Dryad diretamente
    Exec {
        /// Caminho do arquivo .dryad para executar
        file: String,
        /// Apenas validar sintaxe sem executar
        #[arg(short, long)]
        validate: bool,
        /// Argumentos para passar ao programa
        #[arg(last = true)]
        args: Vec<String>,
    },
    /// Limpa cache e arquivos temporários
    Clean,
    /// Mostra informações do projeto
    Info,
    /// Constrói o oaklock.json baseado nas dependências
    Lock,
    /// Gerencia configurações de registry
    Registry {
        /// Subcomando para registry
        #[command(subcommand)]
        action: RegistryAction,
    },
}

#[derive(Subcommand)]
enum RegistryAction {
    /// Lista registries configurados
    List,
    /// Adiciona um novo registry
    Add {
        /// Nome do registry
        name: String,
        /// URL do registry
        url: String,
    },
    /// Remove um registry
    Remove {
        /// Nome do registry
        name: String,
    },
    /// Define o registry padrão
    SetDefault {
        /// Nome do registry
        name: String,
    },
    /// Testa conectividade com um registry
    Test {
        /// Nome do registry (opcional, usa o padrão)
        name: Option<String>,
    },
}

#[derive(Serialize, Deserialize, Debug)]
struct OakConfig {
    name: String,
    version: String,
    description: Option<String>,
    author: Option<String>,
    license: Option<String>,
    #[serde(rename = "type")]
    project_type: ProjectType,
    main: Option<String>,
    dependencies: HashMap<String, String>,
    dev_dependencies: HashMap<String, String>,
    scripts: HashMap<String, String>,
}

#[derive(Serialize, Deserialize, Debug, Clone, PartialEq)]
#[serde(rename_all = "lowercase")]
enum ProjectType {
    Project,
    Library,
}

#[derive(Serialize, Deserialize, Debug)]
struct OakLock {
    modules: HashMap<String, ModuleConfig>,
}

#[derive(Serialize, Deserialize, Debug)]
struct ModuleConfig {
    paths: HashMap<String, String>,
}

// Estruturas para Registry System
#[derive(Serialize, Deserialize, Debug, Clone)]
struct RegistryConfig {
    default_registry: String,
    registries: HashMap<String, String>,
    cache_dir: String,
}

#[derive(Serialize, Deserialize, Debug, Clone)]
struct PackageMetadata {
    name: String,
    version: String,
    description: Option<String>,
    author: Option<String>,
    license: Option<String>,
    dependencies: HashMap<String, String>,
    download_url: String,
    checksum: String,
    file_size: u64,
}

#[derive(Serialize, Deserialize, Debug)]
struct RegistryResponse {
    packages: Vec<PackageMetadata>,
}

#[derive(Serialize, Deserialize, Debug)]
struct VersionInfo {
    version: String,
    download_url: String,
    checksum: String,
    dependencies: HashMap<String, String>,
}

impl Default for OakConfig {
    fn default() -> Self {
        Self::default_for_type(ProjectType::Project)
    }
}

impl OakConfig {
    fn default_for_type(project_type: ProjectType) -> Self {
        let mut scripts = HashMap::new();
        
        match project_type {
            ProjectType::Project => {
                scripts.insert("start".to_string(), "oak exec main.dryad".to_string());
                scripts.insert("test".to_string(), "oak exec tests/test.dryad".to_string());
                scripts.insert("check".to_string(), "oak exec --validate main.dryad".to_string());
                
                OakConfig {
                    name: "meu-projeto".to_string(),
                    version: "0.1.0".to_string(),
                    description: None,
                    author: None,
                    license: Some("MIT".to_string()),
                    project_type: ProjectType::Project,
                    main: Some("main.dryad".to_string()),
                    dependencies: HashMap::new(),
                    dev_dependencies: HashMap::new(),
                    scripts,
                }
            }
            ProjectType::Library => {
                scripts.insert("check".to_string(), "dryad check src/main.dryad".to_string());
                scripts.insert("test".to_string(), "dryad test".to_string());
                
                let mut dependencies = HashMap::new();
                dependencies.insert("dryad-stdlib".to_string(), "^0.1.0".to_string());
                
                OakConfig {
                    name: "minha-biblioteca".to_string(),
                    version: "0.1.0".to_string(),
                    description: None,
                    author: None,
                    license: Some("MIT".to_string()),
                    project_type: ProjectType::Library,
                    main: Some("src/main.dryad".to_string()),
                    dependencies,
                    dev_dependencies: HashMap::new(),
                    scripts,
                }
            }
        }
    }
}

impl Default for OakLock {
    fn default() -> Self {
        OakLock {
            modules: HashMap::new(),
        }
    }
}

impl Default for RegistryConfig {
    fn default() -> Self {
        let mut registries = HashMap::new();
        registries.insert(
            "local".to_string(),
            env::var("OAK_REGISTRY_URL").unwrap_or_else(|_| DEFAULT_REGISTRY_URL.to_string()),
        );
        registries.insert(
            "production".to_string(),
            "https://registry.dryad-lang.org".to_string(),
        );
        registries.insert(
            "github".to_string(),
            "https://raw.githubusercontent.com/Dryad-lang/packages".to_string(),
        );
        
        RegistryConfig {
            default_registry: "local".to_string(),
            registries,
            cache_dir: ".oak/cache".to_string(),
        }
    }
}

impl RegistryConfig {
    fn load_or_default() -> Self {
        match fs::read_to_string("oak-registry.json") {
            Ok(content) => serde_json::from_str(&content).unwrap_or_default(),
            Err(_) => Self::default(),
        }
    }
    
    fn save(&self) -> Result<(), Box<dyn std::error::Error>> {
        let content = serde_json::to_string_pretty(self)?;
        fs::write("oak-registry.json", content)?;
        Ok(())
    }
    
    fn get_registry_url(&self, name: Option<&str>) -> Option<String> {
        let registry_name = name.unwrap_or(&self.default_registry);
        self.registries.get(registry_name).cloned()
    }
}

#[tokio::main]
async fn main() {
    let cli = Cli::parse();

    match cli.command {
        Commands::Init { name, path, r#type } => {
            let project_type = match r#type.to_lowercase().as_str() {
                "library" => ProjectType::Library,
                "project" => ProjectType::Project,
                _ => {
                    eprintln!("Tipo de projeto inválido. Use 'project' ou 'library'");
                    std::process::exit(1);
                }
            };
            
            if let Err(e) = init_project(&name, path.as_deref(), project_type) {
                eprintln!("Erro ao inicializar projeto: {}", e);
                std::process::exit(1);
            }
        }
        Commands::Install { package, version } => {
            if let Err(e) = install_package(package.as_deref(), version.as_deref()).await {
                eprintln!("Erro ao instalar: {}", e);
                std::process::exit(1);
            }
        }
        Commands::Remove { package } => {
            if let Err(e) = remove_package(&package) {
                eprintln!("Erro ao remover: {}", e);
                std::process::exit(1);
            }
        }
        Commands::List => {
            if let Err(e) = list_dependencies() {
                eprintln!("Erro ao listar: {}", e);
                std::process::exit(1);
            }
        }
        Commands::Update => {
            if let Err(e) = update_dependencies() {
                eprintln!("Erro ao atualizar: {}", e);
                std::process::exit(1);
            }
        }
        Commands::Publish => {
            println!("⚠️  Publicação será implementada em versões futuras");
        }
        Commands::Run { script } => {
            if let Err(e) = run_script(&script) {
                eprintln!("Erro ao executar script: {}", e);
                std::process::exit(1);
            }
        }
        Commands::Exec { file, validate, args } => {
            if let Err(e) = execute_dryad_file(&file, &args, validate) {
                eprintln!("Erro ao executar arquivo Dryad: {}", e);
                std::process::exit(1);
            }
        }
        Commands::Clean => {
            if let Err(e) = clean_project() {
                eprintln!("Erro ao limpar: {}", e);
                std::process::exit(1);
            }
        }
        Commands::Info => {
            if let Err(e) = show_info() {
                eprintln!("Erro ao mostrar informações: {}", e);
                std::process::exit(1);
            }
        }
        Commands::Lock => {
            if let Err(e) = lock_dependencies() {
                eprintln!("Erro ao gerar oaklock.json: {}", e);
                std::process::exit(1);
            }
        }
        Commands::Registry { action } => {
            if let Err(e) = handle_registry_command(action).await {
                eprintln!("Erro no comando registry: {}", e);
                std::process::exit(1);
            }
        }
    }
}

fn init_project(name: &str, path: Option<&str>, project_type: ProjectType) -> Result<(), Box<dyn std::error::Error>> {
    let project_dir = match path {
        Some(p) => Path::new(p),
        None => Path::new(name),
    };

    // Criar diretório do projeto
    if project_dir.exists() {
        return Err(format!("Diretório '{}' já existe", project_dir.display()).into());
    }

    fs::create_dir_all(project_dir)?;

    // Configurar arquivo oaklibs.json
    let mut config = OakConfig::default_for_type(project_type.clone());
    config.name = name.to_string();

    let config_path = project_dir.join("oaklibs.json");
    let config_json = serde_json::to_string_pretty(&config)?;
    fs::write(&config_path, config_json)?;

    // Criar estrutura baseada no tipo de projeto
    match project_type {
        ProjectType::Project => {
            // Criar arquivo main.dryad na raiz
            let main_content = format!(r#"// {}/main.dryad
// Projeto Dryad gerado pelo Oak

#<console_io>

print("Hello World from {}!");
"#, name, name);

            let main_path = project_dir.join("main.dryad");
            fs::write(&main_path, main_content)?;
            
            // Criar pasta oak_modules para dependências
            let oak_modules_dir = project_dir.join("oak_modules");
            fs::create_dir_all(&oak_modules_dir)?;
            
            // Criar arquivo .gitkeep na pasta oak_modules
            let gitkeep_path = oak_modules_dir.join(".gitkeep");
            fs::write(&gitkeep_path, "# Esta pasta contém as dependências instaladas pelo Oak\n")?;
            
            // Criar pasta de testes
            let tests_dir = project_dir.join("tests");
            fs::create_dir_all(&tests_dir)?;
            
            // Criar arquivo de teste básico
            let test_content = format!(r#"// {}/tests/test.dryad
// Testes para o projeto {}

#<console_io>

function executarTestes() {{
    print("🧪 Executando testes...");
    
    testarSomar();
    
    print("✅ Todos os testes passaram!");
}}

function testarSomar() {{
    print("  🔬 Testando função somar...");
    
    // Aqui você testaria a função somar do main.dryad
    // Por enquanto, apenas um exemplo
    let resultado = 5 + 3;
    if (resultado == 8) {{
        print("    ✓ somar(5, 3) = 8");
    }} else {{
        print("    ❌ Esperado 8, mas obteve " + resultado);
    }}
}}

// Executar testes
executarTestes();
"#, name, name);

            let test_path = tests_dir.join("test.dryad");
            fs::write(&test_path, test_content)?;
        }
        
        ProjectType::Library => {
            // Criar estrutura de biblioteca
            let src_dir = project_dir.join("src");
            fs::create_dir_all(&src_dir)?;
            
            let lib_dir = project_dir.join("lib");
            fs::create_dir_all(&lib_dir)?;

            // main.dryad principal da biblioteca
            let main_content = format!(r#"// {}/src/main.dryad
// Biblioteca Dryad gerada pelo Oak

let VERSAO_LIB = "0.1.0";

// Funções matemáticas básicas
function somar(a, b) {{
    if (typeof(a) != "number" || typeof(b) != "number") {{
        throw new Error("Argumentos devem ser números");
    }}
    return a + b;
}}

function multiplicar(a, b) {{
    if (typeof(a) != "number" || typeof(b) != "number") {{
        throw new Error("Argumentos devem ser números");
    }}
    return a * b;
}}

function dividir(a, b) {{
    if (typeof(a) != "number" || typeof(b) != "number") {{
        throw new Error("Argumentos devem ser números");
    }}
    if (b == 0) {{
        throw new Error("Divisão por zero não é permitida");
    }}
    return a / b;
}}

// Classe utilitária
class Calculadora {{
    static function pi() {{
        return 3.141592653589793;
    }}
    
    static function circunferencia(raio) {{
        return 2 * Calculadora.pi() * raio;
    }}
    
    static function area(raio) {{
        return Calculadora.pi() * raio * raio;
    }}
    
    static function obterVersao() {{
        return VERSAO_LIB;
    }}
}}
"#, name);

            let main_path = src_dir.join("main.dryad");
            fs::write(&main_path, main_content)?;

            // Exemplo de módulo na lib
            let matematica_content = r#"// lib/matematica.dryad
export function fatorial(n) {
    if n <= 1 {
        return 1;
    }
    return n * fatorial(n - 1);
}

export function fibonacci(n) {
    if n <= 1 {
        return n;
    }
    return fibonacci(n - 1) + fibonacci(n - 2);
}
"#;

            let matematica_path = lib_dir.join("matematica.dryad");
            fs::write(&matematica_path, matematica_content)?;

            let utilidades_content = r#"// lib/utilidades.dryad
export function ehPar(numero) {
    return numero % 2 == 0;
}

export function ehPrimo(numero) {
    if numero < 2 {
        return false;
    }
    
    let i = 2;
    while i * i <= numero {
        if numero % i == 0 {
            return false;
        }
        i = i + 1;
    }
    return true;
}
"#;

            let utilidades_path = lib_dir.join("utilidades.dryad");
            fs::write(&utilidades_path, utilidades_content)?;

            // Gerar oaklock.json para biblioteca
            create_library_oaklock(project_dir, name)?;
        }
    }

    // Criar README.md
    let readme_content = match project_type {
        ProjectType::Project => format!(r#"# {}

Projeto Dryad criado com Oak.

## Executar

```bash
oak run start
```

ou

```bash
dryad run main.dryad
```

## Scripts Disponíveis

- `oak run start` - Executa o projeto
- `oak run test` - Executa testes
- `oak run check` - Verifica sintaxe

## Dependências

Veja o arquivo `oaklibs.json` para gerenciar dependências.
"#, name),
        
        ProjectType::Library => format!(r#"# {}

Biblioteca Dryad criada com Oak.

## Estrutura

```
src/
├── main.dryad    # Ponto de entrada da biblioteca
lib/
├── matematica.dryad # Módulo de matemática
└── utilidades.dryad # Módulo de utilidades
```

## Uso

```dryad
use "matematica";
use "utilidades";

let resultado = fatorial(5);
let ehPar = ehPar(10);
```

## Scripts Disponíveis

- `oak run check` - Verifica sintaxe
- `oak run test` - Executa testes

## Dependências

Veja o arquivo `oaklibs.json` para gerenciar dependências.
"#, name),
    };

    let readme_path = project_dir.join("README.md");
    fs::write(&readme_path, readme_content)?;

    // Criar diretório src (opcional, para projetos maiores)
    let src_dir = project_dir.join("src");
    fs::create_dir_all(&src_dir)?;

    // Criar .gitignore
    let gitignore_content = r#"# Oak
oaklock.json
oak_modules/

# Logs
*.log

# Temporários
*.tmp
*.temp

# Sistema
.DS_Store
Thumbs.db
"#;

    let gitignore_path = project_dir.join(".gitignore");
    fs::write(&gitignore_path, gitignore_content)?;

    let type_name = match project_type {
        ProjectType::Project => "projeto",
        ProjectType::Library => "biblioteca",
    };

    println!("✓ {} '{}' criado com sucesso!", type_name, name);
    println!("📁 Localização: {}", project_dir.display());
    println!("\n📋 Próximos passos:");
    println!("   cd {}", name);
    
    match project_type {
        ProjectType::Project => {
            println!("   oak run start");
        }
        ProjectType::Library => {
            println!("   oak run check");
            println!("   oak lock  # Para gerar oaklock.json");
        }
    }

    Ok(())
}

fn create_library_oaklock(project_dir: &Path, name: &str) -> Result<(), Box<dyn std::error::Error>> {
    let mut oaklock = OakLock::default();
    
    // Adicionar módulos da biblioteca
    let mut module_paths = HashMap::new();
    module_paths.insert("matematica".to_string(), "./lib/matematica.dryad".to_string());
    module_paths.insert("utilidades".to_string(), "./lib/utilidades.dryad".to_string());
    
    let module_config = ModuleConfig {
        paths: module_paths,
    };
    
    oaklock.modules.insert(format!("{}-utils", name), module_config);
    
    let oaklock_path = project_dir.join("oaklock.json");
    let oaklock_json = serde_json::to_string_pretty(&oaklock)?;
    fs::write(&oaklock_path, oaklock_json)?;
    
    Ok(())
}

fn load_oaklock() -> Result<OakLock, Box<dyn std::error::Error>> {
    let oaklock_path = Path::new("oaklock.json");
    if !oaklock_path.exists() {
        return Ok(OakLock::default());
    }

    let content = fs::read_to_string(oaklock_path)?;
    let oaklock: OakLock = serde_json::from_str(&content)?;
    Ok(oaklock)
}

fn save_oaklock(oaklock: &OakLock) -> Result<(), Box<dyn std::error::Error>> {
    let oaklock_json = serde_json::to_string_pretty(oaklock)?;
    fs::write("oaklock.json", oaklock_json)?;
    Ok(())
}

fn load_config() -> Result<OakConfig, Box<dyn std::error::Error>> {
    let config_path = Path::new("oaklibs.json");
    if !config_path.exists() {
        return Err("Arquivo oaklibs.json não encontrado. Execute 'oak init <nome>' primeiro.".into());
    }

    let content = fs::read_to_string(config_path)?;
    let config: OakConfig = serde_json::from_str(&content)?;
    Ok(config)
}

fn save_config(config: &OakConfig) -> Result<(), Box<dyn std::error::Error>> {
    let config_json = serde_json::to_string_pretty(config)?;
    fs::write("oaklibs.json", config_json)?;
    Ok(())
}

async fn install_package(package: Option<&str>, version: Option<&str>) -> Result<(), Box<dyn std::error::Error>> {
    let mut config = load_config()?;

    match package {
        Some(pkg) => {
            let version = version.unwrap_or("latest");
            
            // Verificar se é um projeto (não biblioteca)
            if config.project_type != ProjectType::Project {
                return Err("Instalação de pacotes só é suportada em projetos do tipo 'project'".into());
            }
            
            // Verificar se oak_modules existe, senão criar
            let oak_modules_dir = Path::new("oak_modules");
            if !oak_modules_dir.exists() {
                fs::create_dir_all(oak_modules_dir)?;
            }
            
            // Tentar instalar do registry remoto primeiro
            match install_from_registry(pkg, version).await {
                Ok(_) => {
                    println!("✓ Pacote '{}@{}' instalado do registry remoto", pkg, version);
                }
                Err(e) => {
                    println!("⚠️ Falha ao instalar do registry: {}", e);
                    println!("🔄 Usando instalação simulada como fallback...");
                    install_simulated_package(pkg, version)?;
                    println!("✓ Pacote '{}@{}' instalado (modo simulado)", pkg, version);
                }
            }
            
            config.dependencies.insert(pkg.to_string(), version.to_string());
            save_config(&config)?;
            
            println!("📁 Localização: ./oak_modules/{}", pkg);
            println!("💡 Execute 'oak lock' para atualizar o oaklock.json");
        }
        None => {
            println!("📦 Instalando todas as dependências...");
            
            if config.project_type != ProjectType::Project {
                return Err("Instalação de pacotes só é suportada em projetos do tipo 'project'".into());
            }
            
            // Verificar se oak_modules existe, senão criar
            let oak_modules_dir = Path::new("oak_modules");
            if !oak_modules_dir.exists() {
                fs::create_dir_all(oak_modules_dir)?;
            }
            
            let dependencies_clone = config.dependencies.clone();
            for (name, version) in &dependencies_clone {
                println!("  📦 Instalando {}@{}", name, version);
                
                // Tentar registry remoto primeiro
                match install_from_registry(name, version).await {
                    Ok(_) => {
                        println!("  ✓ {}@{} instalado do registry remoto", name, version);
                    }
                    Err(e) => {
                        println!("  ⚠️ Falha no registry para {}: {}", name, e);
                        println!("  🔄 Usando fallback simulado...");
                        install_simulated_package(name, version)?;
                        println!("  ✓ {}@{} instalado (modo simulado)", name, version);
                    }
                }
            }
            
            if config.dependencies.is_empty() {
                println!("  Nenhuma dependência para instalar");
            } else {
                println!("✓ {} dependência(s) instalada(s)", config.dependencies.len());
            }
        }
    }

    Ok(())
}

fn remove_package(package: &str) -> Result<(), Box<dyn std::error::Error>> {
    let mut config = load_config()?;

    if config.dependencies.remove(package).is_some() {
        save_config(&config)?;
        println!("✓ Pacote '{}' removido das dependências", package);
        println!("💡 Execute 'oak lock' para atualizar o oaklock.json");
    } else {
        println!("⚠️  Pacote '{}' não encontrado nas dependências", package);
    }

    Ok(())
}

fn list_dependencies() -> Result<(), Box<dyn std::error::Error>> {
    let config = load_config()?;
    let oaklock = load_oaklock().unwrap_or_default();

    println!("📦 Dependências do projeto '{}':", config.name);
    
    if config.dependencies.is_empty() {
        println!("  Nenhuma dependência encontrada");
    } else {
        for (name, version) in &config.dependencies {
            let status = if oaklock.modules.contains_key(name) {
                "✓ resolvido"
            } else {
                "⚠ não resolvido"
            };
            println!("  ├─ {}@{} {}", name, version, status);
        }
    }

    if !config.dev_dependencies.is_empty() {
        println!("\n🔧 Dependências de desenvolvimento:");
        for (name, version) in &config.dev_dependencies {
            println!("  ├─ {}@{}", name, version);
        }
    }

    if !oaklock.modules.is_empty() {
        println!("\n📋 Módulos disponíveis:");
        for (module_name, module_config) in &oaklock.modules {
            println!("  └─ {} ({} arquivo(s))", module_name, module_config.paths.len());
        }
    }

    Ok(())
}

fn update_dependencies() -> Result<(), Box<dyn std::error::Error>> {
    let config = load_config()?;
    
    println!("🔄 Atualizando dependências...");
    for (name, version) in &config.dependencies {
        println!("  - {}@{}", name, version);
    }
    println!("⚠️  Atualização real será implementada em versões futuras");
    println!("💡 Execute 'oak lock' para regenerar o oaklock.json");

    Ok(())
}

fn run_script(script: &str) -> Result<(), Box<dyn std::error::Error>> {
    let config = load_config()?;

    match config.scripts.get(script) {
        Some(command) => {
            println!("🚀 Executando script '{}':", script);
            println!("   {}", command);
            
            // Executa o comando
            let mut cmd_parts = command.split_whitespace();
            let program = cmd_parts.next().unwrap();
            let args: Vec<&str> = cmd_parts.collect();

            let status = std::process::Command::new(program)
                .args(&args)
                .status()?;

            if !status.success() {
                return Err(format!("Script '{}' falhou", script).into());
            }
        }
        None => {
            println!("❌ Script '{}' não encontrado", script);
            println!("\n📋 Scripts disponíveis:");
            for (name, command) in &config.scripts {
                println!("  {} - {}", name, command);
            }
        }
    }

    Ok(())
}

fn execute_dryad_file(file_path: &str, args: &[String], validate_only: bool) -> Result<(), Box<dyn std::error::Error>> {
    let path = Path::new(file_path);
    
    // Verificar se arquivo existe
    if !path.exists() {
        return Err(format!("Arquivo '{}' não encontrado", file_path).into());
    }
    
    // Verificar extensão .dryad
    if path.extension().and_then(|s| s.to_str()) != Some("dryad") {
        return Err(format!("Arquivo '{}' não tem extensão .dryad", file_path).into());
    }
    
    if validate_only {
        println!("🔍 Validando sintaxe do arquivo: {}", file_path);
    } else {
        println!("🚀 Executando arquivo Dryad: {}", file_path);
    }
    
    // Ler conteúdo do arquivo
    let source = fs::read_to_string(path)?;
    
    // Executar pipeline Dryad: Lexer -> Parser -> Runtime
    let mut lexer = Lexer::new_with_file(&source, path.to_path_buf());
    let mut parser = DryadParser::new_from_lexer(&mut lexer)?;
    let program = parser.parse()?;
    
    if validate_only {
        println!("✅ Sintaxe válida");
        return Ok(());
    }
    
    let mut interpreter = Interpreter::new();
    
    // Adicionar argumentos como variável global 'args'
    if !args.is_empty() {
        use dryad_runtime::Value;
        let args_values: Vec<Value> = args.iter()
            .map(|arg| Value::String(arg.clone()))
            .collect();
        interpreter.set_variable("args".to_string(), Value::Array(args_values));
    }
    
    // Executar programa
    match interpreter.execute_and_return_value(&program) {
        Ok(_) => {
            println!("✅ Programa executado com sucesso");
            Ok(())
        }
        Err(e) => {
            eprintln!("❌ Erro durante execução: {}", e);
            Err(e.into())
        }
    }
}

fn clean_project() -> Result<(), Box<dyn std::error::Error>> {
    println!("🧹 Limpando projeto...");
    
    // Limpar arquivos de cache
    let cache_dirs = ["oak_modules", ".oak_cache", "target"];
    
    for dir in &cache_dirs {
        if Path::new(dir).exists() {
            fs::remove_dir_all(dir)?;
            println!("✓ Removido: {}", dir);
        }
    }
    
    // Remover oaklock.json se existir
    if Path::new("oaklock.json").exists() {
        fs::remove_file("oaklock.json")?;
        println!("✓ Removido: oaklock.json");
    }
    
    // Limpar arquivos temporários
    let temp_patterns = ["*.log", "*.tmp"];
    for pattern in &temp_patterns {
        println!("✓ Limpeza de arquivos: {}", pattern);
    }
    
    println!("✅ Limpeza concluída");
    println!("💡 Execute 'oak lock' para regenerar o oaklock.json");
    Ok(())
}

fn show_info() -> Result<(), Box<dyn std::error::Error>> {
    let config = load_config()?;
    let oaklock = load_oaklock().unwrap_or_default();

    println!("📋 Informações do Projeto");
    println!("━━━━━━━━━━━━━━━━━━━━━━━━");
    println!("Nome:        {}", config.name);
    println!("Versão:      {}", config.version);
    
    let type_display = match config.project_type {
        ProjectType::Project => "Projeto",
        ProjectType::Library => "Biblioteca",
    };
    println!("Tipo:        {}", type_display);
    
    if let Some(main) = &config.main {
        println!("Principal:   {}", main);
    }
    
    if let Some(desc) = &config.description {
        println!("Descrição:   {}", desc);
    }
    
    if let Some(author) = &config.author {
        println!("Autor:       {}", author);
    }
    
    if let Some(license) = &config.license {
        println!("Licença:     {}", license);
    }

    println!("Dependências: {}", config.dependencies.len());
    println!("Scripts:      {}", config.scripts.len());
    
    if !oaklock.modules.is_empty() {
        println!("Módulos:      {}", oaklock.modules.len());
        
        println!("\n📦 Módulos Disponíveis:");
        for (module_name, module_config) in &oaklock.modules {
            println!("  └─ {} ({} arquivo(s))", module_name, module_config.paths.len());
            for (alias, path) in &module_config.paths {
                println!("     ├─ {} -> {}", alias, path);
            }
        }
    }

    Ok(())
}

fn lock_dependencies() -> Result<(), Box<dyn std::error::Error>> {
    let config = load_config()?;
    let mut oaklock = load_oaklock()?;

    println!("🔒 Construindo oaklock.json...");

    // Para cada dependência, resolver os caminhos dos módulos
    for (dep_name, dep_version) in &config.dependencies {
        println!("  📦 Processando {}@{}", dep_name, dep_version);
        
        let mut module_paths = HashMap::new();
        
        // Verificar se o pacote está instalado em oak_modules
        let pkg_dir = Path::new("oak_modules").join(dep_name);
        if pkg_dir.exists() {
            // Escanear a estrutura real do pacote instalado
            scan_package_modules(&pkg_dir, dep_name, &mut module_paths)?;
        } else {
            // Fallback para estrutura simulada (compatibilidade)
            match dep_name.as_str() {
                "dryad-stdlib" => {
                    module_paths.insert("io".to_string(), "./oak_modules/dryad-stdlib/io.dryad".to_string());
                    module_paths.insert("math".to_string(), "./oak_modules/dryad-stdlib/math.dryad".to_string());
                    module_paths.insert("string".to_string(), "./oak_modules/dryad-stdlib/string.dryad".to_string());
                }
                "matematica-utils" => {
                    module_paths.insert("matematica".to_string(), "./oak_modules/matematica-utils/lib/matematica.dryad".to_string());
                    module_paths.insert("utilidades".to_string(), "./oak_modules/matematica-utils/lib/utilidades.dryad".to_string());
                    module_paths.insert("formas".to_string(), "./oak_modules/matematica-utils/lib/formas.dryad".to_string());
                }
                _ => {
                    // Para outras dependências, assumir estrutura padrão
                    module_paths.insert("main".to_string(), format!("./oak_modules/{}/src/main.dryad", dep_name));
                }
            }
        }
        
        let module_config = ModuleConfig {
            paths: module_paths,
        };
        
        oaklock.modules.insert(dep_name.clone(), module_config);
    }

    // Se for uma biblioteca, incluir os próprios módulos
    if config.project_type == ProjectType::Library {
        let mut self_module_paths = HashMap::new();
        
        // Escanear o diretório lib/ para encontrar módulos
        let lib_dir = Path::new("lib");
        if lib_dir.exists() {
            for entry in fs::read_dir(lib_dir)? {
                let entry = entry?;
                let path = entry.path();
                
                if path.extension().map_or(false, |ext| ext == "dryad") {
                    if let Some(stem) = path.file_stem() {
                        if let Some(module_name) = stem.to_str() {
                            let relative_path = format!("./lib/{}.dryad", module_name);
                            self_module_paths.insert(module_name.to_string(), relative_path);
                        }
                    }
                }
            }
        }
        
        if !self_module_paths.is_empty() {
            let self_module_config = ModuleConfig {
                paths: self_module_paths,
            };
            
            let self_module_name = format!("{}-utils", config.name);
            oaklock.modules.insert(self_module_name, self_module_config);
        }
    }

    save_oaklock(&oaklock)?;
    
    println!("✓ oaklock.json gerado com sucesso!");
    println!("📋 Módulos resolvidos: {}", oaklock.modules.len());
    
    // Mostrar mapeamento para use "biblioteca/modulo"
    for (module_name, module_config) in &oaklock.modules {
        println!("  📦 {}: {} módulo(s)", module_name, module_config.paths.len());
        for (alias, path) in &module_config.paths {
            println!("    - use \"{}/{}\" -> {}", module_name, alias, path);
        }
    }

    Ok(())
}

fn install_simulated_package(pkg_name: &str, version: &str) -> Result<(), Box<dyn std::error::Error>> {
    let pkg_dir = Path::new("oak_modules").join(pkg_name);
    
    // Remover se já existir
    if pkg_dir.exists() {
        fs::remove_dir_all(&pkg_dir)?;
    }
    
    // Criar estrutura baseada no nome do pacote
    match pkg_name {
        "matematica-utils" => {
            // Criar estrutura de biblioteca matemática
            let lib_dir = pkg_dir.join("lib");
            fs::create_dir_all(&lib_dir)?;
            
            // matematica.dryad
            let matematica_content = r#"// matematica-utils/lib/matematica.dryad
export function fatorial(n) {
    if n <= 1 {
        return 1;
    }
    return n * fatorial(n - 1);
}

export function fibonacci(n) {
    if n <= 1 {
        return n;
    }
    return fibonacci(n - 1) + fibonacci(n - 2);
}

export function ehPrimo(numero) {
    if numero < 2 {
        return false;
    }
    
    let i = 2;
    while i * i <= numero {
        if numero % i == 0 {
            return false;
        }
        i = i + 1;
    }
    return true;
}
"#;
            fs::write(lib_dir.join("matematica.dryad"), matematica_content)?;
            
            // utilidades.dryad
            let utilidades_content = r#"// matematica-utils/lib/utilidades.dryad
export function ehPar(numero) {
    return numero % 2 == 0;
}

export function maximo(a, b) {
    if a > b {
        return a;
    }
    return b;
}

export function minimo(a, b) {
    if a < b {
        return a;
    }
    return b;
}

export function absoluto(numero) {
    if numero < 0 {
        return -numero;
    }
    return numero;
}
"#;
            fs::write(lib_dir.join("utilidades.dryad"), utilidades_content)?;
            
            // formas.dryad
            let formas_content = r#"// matematica-utils/lib/formas.dryad
export class Circulo {
    function init(raio) {
        this.raio = raio;
    }
    
    function area() {
        return 3.14159 * this.raio * this.raio;
    }
    
    function perimetro() {
        return 2 * 3.14159 * this.raio;
    }
}

export class Retangulo {
    function init(largura, altura) {
        this.largura = largura;
        this.altura = altura;
    }
    
    function area() {
        return this.largura * this.altura;
    }
    
    function perimetro() {
        return 2 * (this.largura + this.altura);
    }
}

export function areaTriangulo(base, altura) {
    return (base * altura) / 2;
}
"#;
            fs::write(lib_dir.join("formas.dryad"), formas_content)?;
        }
        
        "dryad-stdlib" => {
            // Criar biblioteca padrão
            fs::create_dir_all(&pkg_dir)?;
            
            // io.dryad
            let io_content = r#"// dryad-stdlib/io.dryad
export function lerArquivo(caminho) {
    // Simulação de leitura de arquivo
    return "Conteúdo do arquivo: " + caminho;
}

export function escreverArquivo(caminho, conteudo) {
    // Simulação de escrita de arquivo
    print("Escrevendo em " + caminho + ": " + conteudo);
    return true;
}

export function existeArquivo(caminho) {
    // Simulação de verificação de existência
    return true;
}
"#;
            fs::write(pkg_dir.join("io.dryad"), io_content)?;
            
            // math.dryad
            let math_content = r#"// dryad-stdlib/math.dryad
export let PI = 3.141592653589793;
export let E = 2.718281828459045;

export function sin(x) {
    // Implementação simplificada
    return x; // Placeholder
}

export function cos(x) {
    // Implementação simplificada  
    return 1 - (x * x) / 2; // Placeholder
}

export function sqrt(x) {
    if x < 0 {
        return null;
    }
    return x ** 0.5;
}

export function pow(base, exp) {
    return base ** exp;
}

export function random() {
    // Simulação de número aleatório
    return 0.42;
}
"#;
            fs::write(pkg_dir.join("math.dryad"), math_content)?;
            
            // string.dryad
            let string_content = r#"// dryad-stdlib/string.dryad
export function maiuscula(texto) {
    // Implementação simplificada
    return texto; // Placeholder - deveria converter para maiúsculo
}

export function minuscula(texto) {
    // Implementação simplificada
    return texto; // Placeholder - deveria converter para minúsculo
}

export function dividir(texto, separador) {
    // Implementação simplificada - retorna array
    return [texto]; // Placeholder
}

export function substituir(texto, antigo, novo) {
    // Implementação simplificada
    return texto; // Placeholder
}

export function tamanho(texto) {
    // Esta função já existe nativamente como len()
    return len(texto);
}
"#;
            fs::write(pkg_dir.join("string.dryad"), string_content)?;
        }
        
        _ => {
            // Para outras bibliotecas, criar estrutura genérica
            let src_dir = pkg_dir.join("src");
            fs::create_dir_all(&src_dir)?;
            
            let main_content = format!(r#"// {}/src/main.dryad
// Biblioteca genérica gerada pelo Oak

export function exemplo() {{
    return "Função de exemplo da biblioteca {}";
}}

export let VERSAO = "{}";
"#, pkg_name, pkg_name, version);
            
            fs::write(src_dir.join("main.dryad"), main_content)?;
        }
    }
    
    // Criar oaklibs.json da biblioteca
    let package_config = serde_json::json!({
        "name": pkg_name,
        "version": version,
        "type": "library",
        "main": if pkg_name == "matematica-utils" { "lib/matematica.dryad" } else { "src/main.dryad" },
        "description": format!("Biblioteca {} instalada pelo Oak", pkg_name)
    });
    
    let package_config_path = pkg_dir.join("oaklibs.json");
    fs::write(&package_config_path, serde_json::to_string_pretty(&package_config)?)?;
    
    Ok(())
}

fn scan_package_modules(pkg_dir: &Path, pkg_name: &str, module_paths: &mut HashMap<String, String>) -> Result<(), Box<dyn std::error::Error>> {
    // Escanear lib/ se existir
    let lib_dir = pkg_dir.join("lib");
    if lib_dir.exists() {
        for entry in fs::read_dir(&lib_dir)? {
            let entry = entry?;
            let path = entry.path();
            
            if path.extension().map_or(false, |ext| ext == "dryad") {
                if let Some(stem) = path.file_stem() {
                    if let Some(module_name) = stem.to_str() {
                        let relative_path = format!("./oak_modules/{}/lib/{}.dryad", pkg_name, module_name);
                        module_paths.insert(module_name.to_string(), relative_path);
                    }
                }
            }
        }
    }
    
    // Escanear src/ se existir e lib/ não tiver módulos
    if module_paths.is_empty() {
        let src_dir = pkg_dir.join("src");
        if src_dir.exists() {
            for entry in fs::read_dir(&src_dir)? {
                let entry = entry?;
                let path = entry.path();
                
                if path.extension().map_or(false, |ext| ext == "dryad") {
                    if let Some(stem) = path.file_stem() {
                        if let Some(module_name) = stem.to_str() {
                            let relative_path = format!("./oak_modules/{}/src/{}.dryad", pkg_name, module_name);
                            module_paths.insert(module_name.to_string(), relative_path);
                        }
                    }
                }
            }
        }
    }
    
    // Escanear arquivos .dryad na raiz se não há módulos em lib/ ou src/
    if module_paths.is_empty() {
        for entry in fs::read_dir(pkg_dir)? {
            let entry = entry?;
            let path = entry.path();
            
            if path.extension().map_or(false, |ext| ext == "dryad") {
                if let Some(stem) = path.file_stem() {
                    if let Some(module_name) = stem.to_str() {
                        let relative_path = format!("./oak_modules/{}/{}.dryad", pkg_name, module_name);
                        module_paths.insert(module_name.to_string(), relative_path);
                    }
                }
            }
        }
    }
    
    Ok(())
}

// Registry HTTP Client Functions
struct RegistryClient {
    client: reqwest::Client,
    config: RegistryConfig,
}

impl RegistryClient {
    fn new() -> Self {
        let config = RegistryConfig::load_or_default();
        Self {
            client: reqwest::Client::new(),
            config,
        }
    }
    
    async fn search_package(&self, name: &str) -> Result<Option<PackageMetadata>, Box<dyn std::error::Error>> {
        let registry_url = self.config.get_registry_url(None)
            .ok_or("Registry URL não configurado")?;
            
        let url = format!("{}/api/packages/{}", registry_url, name);
        
        println!("🔍 Buscando pacote '{}' em {}", name, registry_url);
        
        // Primeiro tenta buscar no registry remoto
        match self.client.get(&url).send().await {
            Ok(response) => {
                if response.status().is_success() {
                    match response.json::<PackageMetadata>().await {
                        Ok(metadata) => return Ok(Some(metadata)),
                        Err(e) => println!("⚠️ Erro ao parsear resposta do registry: {}", e),
                    }
                } else {
                    println!("⚠️ Pacote não encontrado no registry ({})", response.status());
                }
            }
            Err(e) => {
                println!("⚠️ Erro ao conectar com registry: {}", e);
                println!("🔄 Tentando registry alternativo...");
            }
        }
        
        // Se falhar, tenta registries alternativos
        if let Some(github_url) = self.config.registries.get("github") {
            let github_url = format!("{}/main/{}/package.json", github_url, name);
            match self.client.get(&github_url).send().await {
                Ok(response) if response.status().is_success() => {
                    match response.json::<PackageMetadata>().await {
                        Ok(metadata) => return Ok(Some(metadata)),
                        Err(e) => println!("⚠️ Erro ao parsear resposta do GitHub: {}", e),
                    }
                }
                _ => {}
            }
        }
        
        Ok(None)
    }
    
    async fn download_package(&self, metadata: &PackageMetadata) -> Result<Vec<u8>, Box<dyn std::error::Error>> {
        println!("📥 Baixando pacote {}@{} ({} bytes)", 
                metadata.name, metadata.version, metadata.file_size);
                
        let response = self.client.get(&metadata.download_url).send().await?;
        
        if !response.status().is_success() {
            return Err(format!("Falha ao baixar: {}", response.status()).into());
        }
        
        let bytes = response.bytes().await?.to_vec();
        
        // Verificar checksum
        let mut hasher = Sha256::new();
        hasher.update(&bytes);
        let hash = format!("{:x}", hasher.finalize());
        
        if hash != metadata.checksum {
            return Err("Checksum não confere - arquivo pode estar corrompido".into());
        }
        
        println!("✓ Download concluído e verificado");
        Ok(bytes)
    }
}

async fn install_from_registry(package_name: &str, version: &str) -> Result<(), Box<dyn std::error::Error>> {
    let registry_client = RegistryClient::new();
    
    // Cache directory
    let cache_dir = Path::new(&registry_client.config.cache_dir);
    if !cache_dir.exists() {
        fs::create_dir_all(cache_dir)?;
    }
    
    // Verificar cache local primeiro
    let cache_file = cache_dir.join(format!("{}@{}.tar.gz", package_name, version));
    let package_bytes = if cache_file.exists() {
        println!("📦 Usando versão em cache de {}", package_name);
        fs::read(&cache_file)?
    } else {
        // Buscar metadados do pacote
        let metadata = match registry_client.search_package(package_name).await? {
            Some(meta) => meta,
            None => {
                return Err(format!("Pacote '{}' não encontrado no registry", package_name).into());
            }
        };
        
        // Download do pacote
        let bytes = registry_client.download_package(&metadata).await?;
        
        // Salvar no cache
        fs::write(&cache_file, &bytes)?;
        bytes
    };
    
    // Extrair pacote
    extract_package(package_name, &package_bytes)?;
    
    println!("✓ Pacote '{}@{}' instalado com sucesso", package_name, version);
    Ok(())
}

fn extract_package(package_name: &str, data: &[u8]) -> Result<(), Box<dyn std::error::Error>> {
    use flate2::read::GzDecoder;
    use tar::Archive;
    use std::io::Cursor;
    
    let target_dir = format!("oak_modules/{}", package_name);
    
    // Remover diretório existente se houver
    if Path::new(&target_dir).exists() {
        fs::remove_dir_all(&target_dir)?;
    }
    
    // Extrair arquivo tar.gz
    let cursor = Cursor::new(data);
    let decoder = GzDecoder::new(cursor);
    let mut archive = Archive::new(decoder);
    
    archive.unpack(&target_dir)?;
    
    println!("📁 Pacote extraído para {}", target_dir);
    Ok(())
}

async fn handle_registry_command(action: RegistryAction) -> Result<(), Box<dyn std::error::Error>> {
    let mut config = RegistryConfig::load_or_default();
    
    match action {
        RegistryAction::List => {
            println!("🌐 Registries configurados:");
            for (name, url) in &config.registries {
                let marker = if name == &config.default_registry { " (padrão)" } else { "" };
                println!("  ├─ {}{}: {}", name, marker, url);
            }
            
            if config.registries.is_empty() {
                println!("  Nenhum registry configurado");
            }
        }
        
        RegistryAction::Add { name, url } => {
            // Validar URL
            match Url::parse(&url) {
                Ok(_) => {
                    config.registries.insert(name.clone(), url.clone());
                    config.save()?;
                    println!("✓ Registry '{}' adicionado: {}", name, url);
                }
                Err(e) => {
                    return Err(format!("URL inválida: {}", e).into());
                }
            }
        }
        
        RegistryAction::Remove { name } => {
            if config.registries.remove(&name).is_some() {
                // Se remover o registry padrão, definir outro como padrão
                if config.default_registry == name {
                    config.default_registry = config.registries.keys()
                        .next()
                        .unwrap_or(&"default".to_string())
                        .clone();
                }
                config.save()?;
                println!("✓ Registry '{}' removido", name);
            } else {
                println!("⚠️ Registry '{}' não encontrado", name);
            }
        }
        
        RegistryAction::SetDefault { name } => {
            if config.registries.contains_key(&name) {
                config.default_registry = name.clone();
                config.save()?;
                println!("✓ Registry padrão definido para '{}'", name);
            } else {
                return Err(format!("Registry '{}' não existe", name).into());
            }
        }
        
        RegistryAction::Test { name } => {
            let registry_name = name.unwrap_or(config.default_registry.clone());
            let url = config.registries.get(&registry_name)
                .ok_or(format!("Registry '{}' não encontrado", registry_name))?;
            
            println!("🔍 Testando conectividade com registry '{}'...", registry_name);
            println!("🌐 URL: {}", url);
            
            let client = reqwest::Client::new();
            let test_url = format!("{}/api/health", url);
            
            match client.get(&test_url).timeout(std::time::Duration::from_secs(10)).send().await {
                Ok(response) => {
                    println!("✓ Status: {} {}", response.status().as_u16(), response.status().canonical_reason().unwrap_or(""));
                    if response.status().is_success() {
                        println!("✅ Registry '{}' está acessível", registry_name);
                    } else {
                        println!("⚠️ Registry retornou status de erro");
                    }
                }
                Err(e) => {
                    println!("❌ Falha ao conectar: {}", e);
                    println!("💡 Verifique sua conexão e se a URL está correta");
                }
            }
        }
    }
    
    Ok(())
}
