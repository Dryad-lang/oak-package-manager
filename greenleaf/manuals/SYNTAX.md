# 🚀 Sintaxe da Linguagem Dryad v1.0

**Status**: ✅ **Implementado e Testado**  
**Versão**: 1.0  
**Data**: Janeiro 2025  
**Compatibilidade**: Dryad Runtime v0.1+

> 📋 **Nota**: Esta documentação cobre apenas funcionalidades **implementadas e funcionais**. Features futuras são marcadas claramente como **"🔮 Planejado"**.

---

## 📋 Índice

1. [Tipos de Dados](#-tipos-de-dados)
2. [Operadores](#-operadores)  
3. [Estruturas de Controle](#-estruturas-de-controle)
4. [Funções](#-funções)
5. [Classes](#-classes)
6. [Módulos Nativos](#-módulos-nativos)
7. [Comentários](#-comentários)
8. [Palavras Reservadas](#-palavras-reservadas)

---

## 🏷️ Tipos de Dados

### ✅ Tipos Implementados

#### Number (Número)
Todos os números são de ponto flutuante 64-bit (f64).

```dryad
let idade = 25;
let altura = 1.75;
let negativo = -42;
let cientifico = 1.23e-4;
```

#### String (Texto)
Strings com escape sequences suportados.

```dryad
let nome = "João Silva";
let multilinhas = "Linha 1\nLinha 2\tTabulação";
let aspas = 'Também funciona com aspas simples';
let escape = "Aspas \"dentro\" de string";
```

**Escape sequences suportados:**
- `\n` - Nova linha
- `\t` - Tabulação  
- `\"` - Aspas duplas
- `\'` - Aspas simples
- `\\` - Barra invertida

#### Boolean (Booleano)
```dryad
let ativo = true;
let inativo = false;
```

#### Null
Representa ausência de valor.

```dryad
let vazio = null;
```

### 🔮 **Planejado para v0.2**
- [ ] Arrays nativos: `[1, 2, 3]`
- [ ] Objects/Maps: `{key: value}`
- [ ] Tuples: `(1, "hello", true)`

---

## ⚡ Operadores

### ✅ Aritméticos Básicos
```dryad
let a = 10;
let b = 3;

a + b    // 13 - Soma
a - b    // 7  - Subtração
a * b    // 30 - Multiplicação
a / b    // 3.333... - Divisão
a % b    // 1  - Módulo (resto)
```

### ✅ Aritméticos Avançados (Únicos do Dryad)
```dryad
// Exponenciação
2 ** 3     // 8 (2³)

// Módulo seguro (sempre positivo)
-5 %% 3    // 1 (ao invés de -2)

// Raiz enésima
27 ^^ (1/3)  // 3 (raiz cúbica de 27)

// Potência base 10
10 ## 3    // 1000 (10³)
```

### ✅ Atribuição
```dryad
let x = 10;
x += 5;    // x = x + 5
x -= 2;    // x = x - 2
x *= 3;    // x = x * 3
x /= 2;    // x = x / 2
x %= 4;    // x = x % 4
```

### ✅ Incremento/Decremento
```dryad
let contador = 0;
contador++;  // Incrementa (pós-fixo)
contador--;  // Decrementa (pós-fixo)
```

### ✅ Comparação
```dryad
a == b     // Igual
a != b     // Diferente
a < b      // Menor que
a <= b     // Menor ou igual
a > b      // Maior que
a >= b     // Maior ou igual
```

### ✅ Lógicos
```dryad
true && false   // false (E lógico)
true || false   // true  (OU lógico)
!true          // false (NÃO lógico)
```

### ✅ Bitwise
```dryad
let a = 5;  // 101 em binário
let b = 3;  // 011 em binário

a & b      // 1   (AND bitwise)
a | b      // 7   (OR bitwise)
a ^ b      // 6   (XOR bitwise)
~a         // -6  (NOT bitwise)
a << 1     // 10  (left shift)
a >> 1     // 2   (right shift)
a >>> 1    // 2   (unsigned right shift)
a <<< 1    // 10  (symmetric left shift)
```

---

## 🔄 Estruturas de Controle

> 🚨 **Importante**: Dryad segue o **padrão ANSI C** - parênteses são **obrigatórios** em todas as condições.

### ✅ If/Else
```dryad
if (idade >= 18) {
    println("Maior de idade");
} else if (idade >= 16) {
    println("Pode trabalhar");
} else {
    println("Menor de idade");
}
```

### ✅ While
```dryad
let i = 0;
while (i < 5) {
    println("Contagem: " + i);
    i++;
}
```

### ✅ Do-While
```dryad
let j = 0;
do {
    println("Executa pelo menos uma vez: " + j);
    j++;
} while (j < 3);
```

### ✅ For (Padrão C)
```dryad
// Sintaxe obrigatória: for (init; condition; update)
for (let k = 0; k < 10; k++) {
    println("For loop: " + k);
}

// Step personalizado
for (let countdown = 10; countdown >= 0; countdown -= 2) {
    println("T-" + countdown);
}
```

### ✅ Break e Continue
```dryad
for (let n = 1; n <= 10; n++) {
    if (n == 3) {
        continue; // Pula iteração
    }
    if (n == 8) {
        break;    // Sai do loop
    }
    println(n);
}
```

### ✅ Try/Catch/Finally
```dryad
try {
    // Código que pode gerar erro
    let resultado = operacao_perigosa();
} catch (erro) {
    println("Erro capturado: " + erro);
} finally {
    println("Sempre executa");
}
```

### ✅ Throw
```dryad
function validar_idade(idade) {
    if (idade < 0) {
        throw "Idade não pode ser negativa";
    }
    return idade;
}
```

---

## 🔧 Funções

### ✅ Declaração Básica
```dryad
function somar(a, b) {
    return a + b;
}

let resultado = somar(5, 3); // 8
```

### ✅ Funções sem Retorno
```dryad
function cumprimentar(nome) {
    println("Olá, " + nome + "!");
}

cumprimentar("Maria");
```

### ✅ Recursão
```dryad
function fatorial(n) {
    if (n <= 1) {
        return 1;
    }
    return n * fatorial(n - 1);
}

println(fatorial(5)); // 120
```

### ✅ Funções Assíncronas
```dryad
async function processar_dados() {
    let dados = await carregar_dados();
    return processar(dados);
}
```

### ✅ Funções de Thread
```dryad
thread function tarefa_paralela() {
    // Executa em thread separada
    return calcular_algo_pesado();
}
```

### 🔮 **Planejado para v0.2**
- [ ] Arrow functions: `(x) => x * 2`
- [ ] Closures avançadas
- [ ] Generators

---

## 🏛️ Classes

### ✅ Declaração de Classe
```dryad
class Pessoa {
    constructor(nome, idade) {
        this.nome = nome;
        this.idade = idade;
    }
    
    function apresentar() {
        println("Sou " + this.nome + ", " + this.idade + " anos");
    }
    
    function envelhecer() {
        this.idade++;
    }
}
```

### ✅ Instanciação
```dryad
let pessoa = new Pessoa("Ana", 25);
pessoa.apresentar();
pessoa.envelhecer();
```

### ✅ Herança
```dryad
class Estudante extends Pessoa {
    constructor(nome, idade, curso) {
        super(nome, idade);
        this.curso = curso;
    }
    
    function estudar() {
        println(this.nome + " está estudando " + this.curso);
    }
}

let estudante = new Estudante("Carlos", 20, "Engenharia");
estudante.apresentar();
estudante.estudar();
```

### ✅ Métodos e Propriedades
```dryad
class ContaBancaria {
    constructor(saldo_inicial) {
        this.saldo = saldo_inicial;
    }
    
    function depositar(valor) {
        this.saldo += valor;
    }
    
    function sacar(valor) {
        if (valor <= this.saldo) {
            this.saldo -= valor;
            return true;
        }
        return false;
    }
    
    function get_saldo() {
        return this.saldo;
    }
}
```

### 🔮 **Planejado para v0.2**
- [ ] Propriedades privadas: `#private`
- [ ] Métodos estáticos: `static method()`
- [ ] Interfaces: `interface Name { ... }`

---

## 📦 Módulos Nativos

### ✅ Sistema de Diretivas
Dryad usa diretivas `#<module>` para carregar módulos nativos:

```dryad
#<console_io>    // Entrada/saída do console
#<file_io>       // Manipulação de arquivos
#<http_client>   // Cliente HTTP
#<tcp>           // Networking TCP
```

### ✅ Módulos Implementados

#### Console I/O
```dryad
#<console_io>

println("Olá mundo!");
print("Sem quebra de linha");
let entrada = input();
let caractere = input_char();
flush(); // Força saída
```

#### File I/O  
```dryad
#<file_io>

write_file("teste.txt", "Conteúdo");
let conteudo = read_file("teste.txt");
append_file("teste.txt", "Mais texto");
delete_file("teste.txt");

if (file_exists("arquivo.txt")) {
    println("Arquivo existe!");
}

mkdir("nova_pasta");
let arquivos = list_dir(".");
```

#### HTTP Client
```dryad
#<http_client>

let resposta = http_get("https://api.exemplo.com/dados");
let resultado = http_post("https://api.exemplo.com/send", '{"dados": "json"}');
http_download("https://exemplo.com/arquivo.zip", "download.zip");
```

#### TCP Networking
```dryad
#<tcp>

let conn = tcp_client_connect("servidor.com", 80);
tcp_client_send(conn, "GET / HTTP/1.1\r\n\r\n");
let resposta = tcp_client_receive(conn);
tcp_client_disconnect(conn);
```

### ✅ Lista Completa de Módulos
| Módulo | Status | Descrição |
|--------|--------|-----------|
| `console_io` | ✅ | Entrada/saída console |
| `file_io` | ✅ | Manipulação de arquivos |
| `binary_io` | ✅ | I/O binário |
| `terminal_ansi` | ✅ | Controle de terminal |
| `http_client` | ✅ | Cliente HTTP |
| `http_server` | ✅ | Servidor HTTP |
| `tcp` | ✅ | Protocolo TCP |
| `udp` | ✅ | Protocolo UDP |
| `crypto` | ✅ | Criptografia |
| `time` | ✅ | Data e tempo |
| `system_env` | ✅ | Ambiente sistema |
| `encode_decode` | ✅ | JSON, Base64 |
| `debug` | ✅ | Debug tools |
| `utils` | ✅ | Utilitários |

---

## 💬 Comentários

### ✅ Comentários de Linha
```dryad
// Este é um comentário de linha
let x = 5; // Comentário no final da linha
```

### ✅ Comentários de Bloco
```dryad
/*
Este é um comentário
de múltiplas linhas
*/

let y = /* comentário inline */ 10;
```

---

## 🔒 Palavras Reservadas

### ✅ Palavras-chave Implementadas

#### Declarações
- `let` - Declaração de variável
- `const` - Declaração de constante
- `function` - Declaração de função
- `class` - Declaração de classe
- `constructor` - Construtor de classe

#### Controle de Fluxo
- `if`, `else` - Condicionais
- `while`, `do` - Loops
- `for` - Loop for
- `break`, `continue` - Controle de loop
- `return` - Retorno de função

#### Orientação a Objetos
- `new` - Instanciação
- `this` - Referência ao objeto atual
- `super` - Referência à classe pai
- `extends` - Herança

#### Tratamento de Erros
- `try`, `catch`, `finally` - Tratamento de exceções
- `throw` - Lançamento de exceção

#### Valores Literais
- `true`, `false` - Booleanos
- `null` - Valor nulo

#### Programação Assíncrona
- `async` - Função assíncrona
- `await` - Aguardar resultado
- `thread` - Thread separada

#### Módulos
- `export` - Exportar símbolos

---

## 🎯 Funcionalidades por Status

### ✅ **Implementado e Testado** (v1.0)
- [x] Todos os tipos básicos (number, string, boolean, null)
- [x] Operadores completos (incluindo avançados: `**`, `%%`, `^^`, `##`)
- [x] Estruturas de controle com sintaxe C obrigatória
- [x] Sistema de funções completo (incluindo async/thread)
- [x] Classes com herança
- [x] 14+ módulos nativos funcionais
- [x] Sistema de comentários
- [x] Tratamento de erros (try/catch/throw)

### 🔮 **Planejado para v0.2**
- [ ] Arrays nativos: `[1, 2, 3]`
- [ ] Objects/Maps: `{key: value, key2: value2}`
- [ ] Template strings: `` `Hello ${name}` ``
- [ ] Arrow functions: `(x) => x * 2`
- [ ] Destructuring: `let [a, b] = array`
- [ ] Spread operator: `...array`
- [ ] Optional chaining: `obj?.prop?.method?.()`

### 🔮 **Planejado para v0.3+**
- [ ] Sistema de tipos: `let x: number = 5`
- [ ] Generics: `function<T>(param: T)`
- [ ] Interfaces: `interface User { name: string }`
- [ ] Enums: `enum Color { Red, Green, Blue }`
- [ ] Módulos/Import: `import { func } from "module"`
- [ ] Package manager avançado

---

## 🚀 Exemplos Práticos

Veja exemplos completos em [`/examples`](../examples/README.md):

- **Básico**: [`/examples/basic/`](../examples/basic/) - Operadores, controle, funções, classes
- **Console**: [`/examples/console_io/`](../examples/console_io/) - Entrada/saída interativa
- **Arquivos**: [`/examples/file_io/`](../examples/file_io/) - Manipulação de arquivos
- **HTTP**: [`/examples/http/`](../examples/http/) - Cliente/servidor web
- **Networking**: [`/examples/networking/`](../examples/networking/) - TCP/UDP

---

## 🔧 Executando Código

```bash
# Executar arquivo
cargo run --bin dryad run arquivo.dryad

# Debug (tokens + AST)
cargo run --bin dryad run arquivo.dryad --verbose

# Verificar sintaxe
cargo run --bin dryad check arquivo.dryad

# Modo interativo
cargo run --bin dryad repl
```

---

**✅ Status**: Documentação completa e atualizada  
**📅 Última revisão**: Janeiro 2025  
**🎯 Compatibilidade**: Dryad Runtime v0.1+

> 💡 **Dica**: Para funcionalidades mais avançadas, consulte os [exemplos práticos](../examples/README.md) que demonstram uso real de todas as funcionalidades implementadas.