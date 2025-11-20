// crates/dryad_cli/src/main.rs
use clap::{Parser, Subcommand};
use dryad_lexer::Lexer;
use dryad_parser::Parser as DryadParser;
use dryad_runtime::Interpreter;
use dryad_lexer::Token;
use std::fs;
use std::io::{self, Write};
use std::path::Path;
use serde::{Deserialize, Serialize};

#[derive(Serialize, Deserialize)]
struct PackageJson {
    name: String,
    version: String,
    description: Option<String>,
    author: Option<String>,
    license: Option<String>,
    homepage: Option<String>,
    repository: Option<String>,
    keywords: Option<Vec<String>>,
    dependencies: Option<std::collections::HashMap<String, String>>,
}

#[derive(Parser)]
#[command(name = "dryad")]
#[command(about = "Dryad Programming Language CLI", long_about = None)]
struct Cli {
    #[command(subcommand)]
    command: Option<Commands>,
}

#[derive(Subcommand)]
enum Commands {
    /// Executa um arquivo Dryad
    Run {
        /// Arquivo .dryad para executar
        file: String,
        /// Modo verboso (mostra tokens e AST)
        #[arg(short, long)]
        verbose: bool,
    },
    /// Inicia o modo interativo (REPL)
    Repl,
    /// Valida a sintaxe de um arquivo sem executar
    Check {
        /// Arquivo .dryad para validar
        file: String,
    },
    /// Mostra os tokens de um arquivo (debug)
    Tokens {
        /// Arquivo .dryad para tokenizar
        file: String,
    },
    /// Mostra informações sobre a versão
    Version,
    /// Publica um pacote no registry
    Publish {
        /// URL do registry (opcional, usa padrão se não especificado)
        #[arg(short, long)]
        registry: Option<String>,
    },
    /// Instala um pacote do registry
    Install {
        /// Nome do pacote para instalar
        package: String,
        /// Versão específica (opcional)
        #[arg(short, long)]
        version: Option<String>,
        /// URL do registry (opcional, usa padrão se não especificado)
        #[arg(short, long)]
        registry: Option<String>,
    },
    /// Lista pacotes disponíveis no registry
    List {
        /// URL do registry (opcional, usa padrão se não especificado)
        #[arg(short, long)]
        registry: Option<String>,
    },
}

fn main() {
    let cli = Cli::parse();

    match &cli.command {
        Some(Commands::Run { file, verbose }) => {
            if let Err(e) = run_file(file, *verbose) {
                eprintln!("Erro: {}", e);
                std::process::exit(1);
            }
        }
        Some(Commands::Repl) => {
            if let Err(e) = run_repl() {
                eprintln!("Erro no REPL: {}", e);
                std::process::exit(1);
            }
        }
        Some(Commands::Check { file }) => {
            if let Err(e) = check_file(file) {
                eprintln!("Erro de sintaxe: {}", e);
                std::process::exit(1);
            } else {
                println!("✓ Sintaxe válida");
            }
        }
        Some(Commands::Tokens { file }) => {
            if let Err(e) = show_tokens(file) {
                eprintln!("Erro: {}", e);
                std::process::exit(1);
            }
        }
        Some(Commands::Version) => {
            println!("Dryad v{}", env!("CARGO_PKG_VERSION"));
            println!("Linguagem de programação moderna e expressiva");
        }
        Some(Commands::Publish { registry }) => {
            if let Err(e) = publish_package(registry.as_deref()) {
                eprintln!("Erro ao publicar pacote: {}", e);
                std::process::exit(1);
            }
        }
        Some(Commands::Install { package, version, registry }) => {
            if let Err(e) = install_package(package, version.as_deref(), registry.as_deref()) {
                eprintln!("Erro ao instalar pacote: {}", e);
                std::process::exit(1);
            }
        }
        Some(Commands::List { registry }) => {
            if let Err(e) = list_packages(registry.as_deref()) {
                eprintln!("Erro ao listar pacotes: {}", e);
                std::process::exit(1);
            }
        }
        None => {
            // Se não houver subcomando, tenta executar main.dryad
            if std::path::Path::new("main.dryad").exists() {
                if let Err(e) = run_file("main.dryad", false) {
                    eprintln!("Erro: {}", e);
                    std::process::exit(1);
                }
            } else {
                eprintln!("Uso: dryad <comando>");
                eprintln!("Tente 'dryad --help' para mais informações.");
                std::process::exit(1);
            }
        }
    }
}

fn run_file(filename: &str, verbose: bool) -> Result<(), Box<dyn std::error::Error>> {
    let source = fs::read_to_string(filename)
        .map_err(|e| format!("Erro ao ler arquivo '{}': {}", filename, e))?;

    if verbose {
        println!("=== EXECUTANDO: {} ===", filename);
    }

    let mut lexer = Lexer::new(&source);
    let mut tokens = vec![];

    // Tokenização
    loop {
        let token = lexer.next_token()?;
        if matches!(token, Token::Eof) {
            tokens.push(token);
            break;
        }
        tokens.push(token);
    }

    if verbose {
        println!("\n=== TOKENS ===");
        for (i, token) in tokens.iter().enumerate() {
            println!("{:3}: {:?}", i, token);
        }
    }

    // Parsing
    let mut parser = DryadParser::new(tokens);
    let program = parser.parse()?;

    if verbose {
        println!("\n=== AST ===");
        println!("{:#?}", program);
    }

    // Execução
    let mut interpreter = Interpreter::new();
    
    // Definir o arquivo atual para resolução de imports relativos
    interpreter.set_current_file(std::path::PathBuf::from(filename));
    
    let result = interpreter.execute(&program)?;

    if verbose {
        println!("\n=== RESULTADO ===");
        println!("{}", result);
    } else if result != "null" {
        println!("{}", result);
    }

    Ok(())
}

fn run_repl() -> Result<(), Box<dyn std::error::Error>> {
    println!("Dryad v{} - REPL Interativo", env!("CARGO_PKG_VERSION"));
    println!("Digite 'exit' para sair, 'help' para ajuda");
    
    let mut interpreter = Interpreter::new();
    
    loop {
        print!("dryad> ");
        io::stdout().flush()?;
        
        let mut input = String::new();
        io::stdin().read_line(&mut input)?;
        
        let input = input.trim();
        
        match input {
            "exit" | "quit" => {
                println!("Tchau!");
                break;
            }
            "help" => {
                println!("Comandos disponíveis:");
                println!("  exit, quit - Sair do REPL");
                println!("  help       - Mostrar esta ajuda");
                println!("  clear      - Limpar variáveis");
                println!("\nDigite código Dryad para executar.");
                continue;
            }
            "clear" => {
                interpreter = Interpreter::new();
                println!("Variáveis limpas.");
                continue;
            }
            "" => continue,
            _ => {}
        }
        
        // Processa o código
        match process_repl_input(input, &mut interpreter) {
            Ok(result) => {
                if !result.is_empty() && result != "null" {
                    println!("=> {}", result);
                }
            }
            Err(e) => println!("Erro: {}", e),
        }
    }
    
    Ok(())
}

fn process_repl_input(input: &str, interpreter: &mut Interpreter) -> Result<String, Box<dyn std::error::Error>> {
    let mut lexer = Lexer::new(input);
    let mut tokens = vec![];

    loop {
        let token = lexer.next_token()?;
        if matches!(token, Token::Eof) {
            tokens.push(token);
            break;
        }
        tokens.push(token);
    }

    let mut parser = DryadParser::new(tokens);
    let program = parser.parse()?;
    
    let result = interpreter.execute(&program)?;
    Ok(result)
}

fn check_file(filename: &str) -> Result<(), Box<dyn std::error::Error>> {
    let source = fs::read_to_string(filename)
        .map_err(|e| format!("Erro ao ler arquivo '{}': {}", filename, e))?;

    let mut lexer = Lexer::new(&source);
    let mut tokens = vec![];

    // Tokenização
    loop {
        let token = lexer.next_token()?;
        if matches!(token, Token::Eof) {
            tokens.push(token);
            break;
        }
        tokens.push(token);
    }

    // Parsing (apenas validação)
    let mut parser = DryadParser::new(tokens);
    parser.parse()?;

    Ok(())
}

fn show_tokens(filename: &str) -> Result<(), Box<dyn std::error::Error>> {
    let source = fs::read_to_string(filename)
        .map_err(|e| format!("Erro ao ler arquivo '{}': {}", filename, e))?;

    let mut lexer = Lexer::new(&source);
    let mut token_count = 0;

    println!("=== TOKENS DE: {} ===", filename);
    
    loop {
        let token = lexer.next_token()?;
        println!("{:3}: {:?}", token_count, token);
        token_count += 1;
        
        if matches!(token, Token::Eof) {
            break;
        }
    }
    
    println!("\nTotal de tokens: {}", token_count);
    Ok(())
}

// ======================================
// FUNÇÕES DE GERENCIAMENTO DE PACOTES
// ======================================

const DEFAULT_REGISTRY: &str = "http://localhost:7800/api/registry";

fn publish_package(registry_url: Option<&str>) -> Result<(), Box<dyn std::error::Error>> {
    let registry = registry_url.unwrap_or(DEFAULT_REGISTRY);
    
    println!("🚀 Publicando pacote no registry: {}", registry);
    
    // Verificar se existe package.json
    if !Path::new("package.json").exists() {
        return Err("Arquivo package.json não encontrado. Execute 'dryad init' primeiro.".into());
    }
    
    // Ler package.json
    let package_content = fs::read_to_string("package.json")?;
    let package_json: PackageJson = serde_json::from_str(&package_content)?;
    
    println!("📦 Pacote: {} v{}", package_json.name, package_json.version);
    
    // Coletar arquivos do projeto
    let mut files = Vec::new();
    collect_project_files(".", &mut files)?;
    
    println!("📁 Coletando {} arquivos...", files.len());
    
    // Preparar dados para envio
    let mut form_data = std::collections::HashMap::new();
    form_data.insert("name", package_json.name.clone());
    form_data.insert("version", package_json.version.clone());
    
    if let Some(desc) = &package_json.description {
        form_data.insert("description", desc.clone());
    }
    if let Some(author) = &package_json.author {
        form_data.insert("author", author.clone());
    }
    if let Some(license) = &package_json.license {
        form_data.insert("license", license.clone());
    }
    
    // Fazer requisição HTTP
    let client = std::process::Command::new("curl")
        .arg("-X")
        .arg("POST")
        .arg(&format!("{}/publish", registry))
        .arg("-H")
        .arg("Content-Type: application/json")
        .arg("-d")
        .arg(&serde_json::to_string(&serde_json::json!({
            "name": package_json.name,
            "version": package_json.version,
            "description": package_json.description,
            "author": package_json.author,
            "license": package_json.license,
            "homepage": package_json.homepage,
            "repository": package_json.repository,
            "keywords": package_json.keywords,
            "dependencies": package_json.dependencies,
            "files": files
        }))?)
        .output()?;
    
    if client.status.success() {
        println!("✅ Pacote publicado com sucesso!");
        let response: serde_json::Value = serde_json::from_slice(&client.stdout)?;
        if let Some(data) = response.get("data") {
            if let Some(repo_url) = data.get("repository_url") {
                println!("🔗 Repositório: {}", repo_url.as_str().unwrap_or("N/A"));
            }
        }
    } else {
        let error_msg = String::from_utf8_lossy(&client.stderr);
        return Err(format!("Falha na publicação: {}", error_msg).into());
    }
    
    Ok(())
}

fn install_package(package_name: &str, version: Option<&str>, registry_url: Option<&str>) -> Result<(), Box<dyn std::error::Error>> {
    let registry = registry_url.unwrap_or(DEFAULT_REGISTRY);
    
    println!("📥 Instalando pacote: {}", package_name);
    if let Some(v) = version {
        println!("🏷️  Versão: {}", v);
    }
    
    // Buscar informações do pacote
    let url = if let Some(v) = version {
        format!("{}/packages/{}/{}", registry, package_name, v)
    } else {
        format!("{}/packages/{}", registry, package_name)
    };
    
    let client = std::process::Command::new("curl")
        .arg("-s")
        .arg(&url)
        .output()?;
    
    if !client.status.success() {
        return Err(format!("Pacote '{}' não encontrado no registry", package_name).into());
    }
    
    let response: serde_json::Value = serde_json::from_slice(&client.stdout)?;
    
    if !response.get("success").and_then(|s| s.as_bool()).unwrap_or(false) {
        let error = response.get("error").and_then(|e| e.as_str()).unwrap_or("Erro desconhecido");
        return Err(format!("Erro do registry: {}", error).into());
    }
    
    let download_url = if version.is_some() {
        response.get("data")
            .and_then(|d| d.get("download_url"))
            .and_then(|u| u.as_str())
    } else {
        // Se não especificou versão, pegar a versão mais recente
        response.get("data")
            .and_then(|d| d.get("versions"))
            .and_then(|v| v.as_array())
            .and_then(|versions| versions.first())
            .and_then(|latest| latest.get("download_url"))
            .and_then(|u| u.as_str())
    };
    
    let download_url = download_url.ok_or("URL de download não encontrada")?;
    
    println!("⬇️  Baixando de: {}", download_url);
    
    // Criar diretório de pacotes se não existir
    fs::create_dir_all("dryad_packages")?;
    
    // Baixar e extrair o pacote
    let download_cmd = std::process::Command::new("curl")
        .arg("-L")
        .arg("-o")
        .arg(&format!("dryad_packages/{}.tar.gz", package_name))
        .arg(download_url)
        .output()?;
    
    if !download_cmd.status.success() {
        return Err("Falha no download do pacote".into());
    }
    
    // Extrair arquivo
    let extract_cmd = std::process::Command::new("tar")
        .arg("-xzf")
        .arg(&format!("dryad_packages/{}.tar.gz", package_name))
        .arg("-C")
        .arg("dryad_packages")
        .output();
    
    match extract_cmd {
        Ok(output) if output.status.success() => {
            println!("✅ Pacote '{}' instalado com sucesso em dryad_packages/", package_name);
            // Remover arquivo tar.gz
            let _ = fs::remove_file(&format!("dryad_packages/{}.tar.gz", package_name));
        }
        _ => {
            println!("⚠️  Arquivo baixado, mas falha na extração. Arquivo salvo como: dryad_packages/{}.tar.gz", package_name);
        }
    }
    
    Ok(())
}

fn list_packages(registry_url: Option<&str>) -> Result<(), Box<dyn std::error::Error>> {
    let registry = registry_url.unwrap_or(DEFAULT_REGISTRY);
    
    println!("📋 Listando pacotes do registry: {}", registry);
    
    let client = std::process::Command::new("curl")
        .arg("-s")
        .arg(&format!("{}/packages", registry))
        .output()?;
    
    if !client.status.success() {
        return Err("Falha ao conectar com o registry".into());
    }
    
    let response: serde_json::Value = serde_json::from_slice(&client.stdout)?;
    
    if !response.get("success").and_then(|s| s.as_bool()).unwrap_or(false) {
        let error = response.get("error").and_then(|e| e.as_str()).unwrap_or("Erro desconhecido");
        return Err(format!("Erro do registry: {}", error).into());
    }
    
    if let Some(packages) = response.get("data").and_then(|d| d.as_array()) {
        if packages.is_empty() {
            println!("📦 Nenhum pacote encontrado no registry.");
            return Ok(());
        }
        
        println!("\n📦 Pacotes disponíveis:");
        println!("{:<20} {:<40} {:<15}", "Nome", "Descrição", "Atualizado");
        println!("{}", "=".repeat(75));
        
        for package in packages {
            let name = package.get("name").and_then(|n| n.as_str()).unwrap_or("N/A");
            let desc = package.get("description").and_then(|d| d.as_str()).unwrap_or("N/A");
            let updated = package.get("updated_at").and_then(|u| u.as_str()).unwrap_or("N/A");
            
            println!("{:<20} {:<40} {:<15}", 
                name, 
                if desc.len() > 39 { &desc[..36] } else { desc },
                if updated.len() > 10 { &updated[..10] } else { updated }
            );
        }
    } else {
        println!("📦 Nenhum pacote encontrado.");
    }
    
    Ok(())
}

fn collect_project_files(dir: &str, files: &mut Vec<serde_json::Value>) -> Result<(), Box<dyn std::error::Error>> {
    let entries = fs::read_dir(dir)?;
    
    for entry in entries {
        let entry = entry?;
        let path = entry.path();
        
        // Ignorar arquivos/diretórios específicos
        if let Some(name) = path.file_name().and_then(|n| n.to_str()) {
            if name.starts_with('.') || name == "target" || name == "dryad_packages" {
                continue;
            }
        }
        
        if path.is_file() {
            // Verificar extensões suportadas
            if let Some(ext) = path.extension().and_then(|e| e.to_str()) {
                if ext == "dryad" || ext == "json" || ext == "md" || ext == "txt" {
                    let relative_path = path.strip_prefix(".")?.to_str().ok_or("Caminho inválido")?;
                    let content = fs::read_to_string(&path)?;
                    
                    files.push(serde_json::json!({
                        "path": relative_path.replace('\\', '/'),
                        "content": content
                    }));
                }
            }
        } else if path.is_dir() {
            collect_project_files(path.to_str().ok_or("Caminho inválido")?, files)?;
        }
    }
    
    Ok(())
}
