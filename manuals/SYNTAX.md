# � Sintaxe Completa da Linguagem Dryad

**Target:** Usuários e Desenvolvedores  
**Versão:** 0.1.1  
**Data:** Janeiro 2025  
**Status:** Documentação Completa da Sintaxe

---

## 📋 Índice

1. [Introdução](#introdução)
2. [Tipos de Dados](#tipos-de-dados)
3. [Variáveis e Declarações](#variáveis-e-declarações)
4. [Operadores](#operadores)
5. [Estruturas de Controle](#estruturas-de-controle)
6. [Funções](#funções)
7. [Classes e Objetos](#classes-e-objetos)
8. [Módulos e Imports](#módulos-e-imports)
9. [Funções Nativas](#funções-nativas)
10. [Comentários](#comentários)
11. [Palavras Reservadas](#palavras-reservadas)
12. [Exemplos Práticos](#exemplos-práticos)
13. [Funcionalidades Futuras](#funcionalidades-futuras)

---

## 🚀 Introdução

Dryad é uma linguagem de programação moderna e expressiva, projetada para ser simples de aprender mas poderosa o suficiente para projetos complexos. Esta documentação cobre toda a sintaxe disponível na versão atual.

### Características Principais
- **Tipagem dinâmica**: Tipos são inferidos automaticamente
- **Orientação a objetos**: Suporte completo a classes e herança
- **Modular**: Sistema robusto de módulos e imports
- **Interativa**: REPL integrado para desenvolvimento rápido
- **Extensível**: Funções nativas e integração com outras linguagens

---

## 🏷️ Tipos de Dados

### Tipos Primitivos

#### Number (Número)
Todos os números em Dryad são de ponto flutuante (64-bit).

```dryad
let idade = 25;
let altura = 1.75;
let pi = 3.14159;
let negativo = -42;
```

#### String (Cadeia de caracteres)
Strings são delimitadas por aspas duplas.

```dryad
let nome = "João";
let sobrenome = "Silva";
let frase = "Olá, mundo!";
let vazio = "";
```

#### Boolean (Booleano)
Valores verdadeiro ou falso.

```dryad
let verdadeiro = true;
let falso = false;
let maior = 10 > 5;  // true
let menor = 3 > 8;   // false
```

#### Null (Nulo)
Representa ausência de valor.

```dryad
let indefinido = null;
let nada = null;
```

### Tipos Compostos

#### Arrays
```dryad
// Planejado para versões futuras
let numeros = [1, 2, 3, 4, 5];
let nomes = ["Ana", "Bruno", "Carlos"];
```

#### Objects
```dryad
// Planejado para versões futuras
let pessoa = {
    nome: "Maria",
    idade: 30,
    ativo: true
};
```

---

## 📝 Variáveis e Declarações

### Declaração de Variáveis

Use a palavra-chave `let` para declarar variáveis:

```dryad
let nome = "Dryad";
let versao = 0.1;
let ativo = true;
```

### Reatribuição de Variáveis

```dryad
let contador = 0;
contador = contador + 1;
contador = 10;
```

### Inicialização

Variáveis podem ser declaradas sem valor inicial:

```dryad
let x;  // x é null
x = 42; // agora x é 42
```

---

## ⚡ Operadores

### Operadores Aritméticos

```dryad
let a = 10;
let b = 3;

let soma = a + b;          // 13
let subtracao = a - b;     // 7
let multiplicacao = a * b; // 30
let divisao = a / b;       // 3.333...
```

### Operadores de Comparação

```dryad
let x = 5;
let y = 10;

let igual = x == y;        // false
let diferente = x != y;    // true
let menor = x < y;         // true
let maior = x > y;         // false
let menorIgual = x <= y;   // true
let maiorIgual = x >= y;   // false
```

### Operadores Lógicos

```dryad
let a = true;
let b = false;

let e = a && b;            // false (AND)
let ou = a || b;           // true (OR)
let nao = !a;              // false (NOT)
```

### Operadores de Atribuição

```dryad
let x = 5;
x += 2;  // x = x + 2 (agora x é 7)
x -= 3;  // x = x - 3 (agora x é 4)
x *= 2;  // x = x * 2 (agora x é 8)
x /= 4;  // x = x / 4 (agora x é 2)
```

### Operadores de Incremento/Decremento

```dryad
let contador = 0;
contador++;  // Incrementa 1 (agora contador é 1)
contador--;  // Decrementa 1 (agora contador é 0)
```

### Operadores de calculo avançado
```dryad
let modulo = 10 % 3; // Resto da divisão (1)
let exponenciacao = 2 ** 3; // 2 elevado a 3 (8)
let raizEnésima = 27 ^^ (1/3); // Raiz cúbica de 27 (3)
let moduloSeguro = 10 %% 3; // Sempre positivo (1)
let potenciaBase10 = 10 ## 3; // 1000
```

### Operadores de byte
```dryad
let byte1 = 0b1010; // 10 em binário
let byte2 = 0o12;   // 10 em octal
let byte3 = 0xA;    // 10 em hexadecimal
```

### Operadores byteshift e bitwise
```dryad
let deslocamentoEsquerda = 1 << 2; // 4 (1 * 2^2)
let deslocamentoDireita = 4 >> 2; // 1 (4 / 2^2)
let bitwiseAnd = 0b1100 & 0b1010; // 0b1000 (8)
let bitwiseOr = 0b1100 | 0b1010;  // 0b1110 (14)
let bitwiseXor = 0b1100 !^ 0b1010; // 0b0110 (6)
let deslocamentoSimétricoDireita = 0b1010 >>> 1; // 0b0101 (5)
let deslocamentoSimétricoEsquerda = 0b0101 <<< 1; // 0b1010 (10)
```

### Operadores de Concatenção de Strings

```dryad
let saudacao = "Olá, " + "Dryad!"; // "Olá, Dryad!"
let nomeCompleto = "João" + " " + "Silva"; // "João Silva"
```

### Operadores de strings (scape literals)
```dryad
- ✅ `\n` - Nova linha
- ✅ `\t` - Tabulação
- ✅ `\r` - Retorno de carro
- ✅ `\\` - Barra invertida literal
- ✅ `\"` - Aspas duplas
- ✅ `\'` - Aspas simples (NOVO)
```

### Precedência de Operadores

De maior para menor precedência:

1. `!` (NOT)
2. `*`, `/` (Multiplicação, Divisão)
3. `+`, `-` (Adição, Subtração)
4. `<`, `>`, `<=`, `>=` (Comparação)
5. `==`, `!=` (Igualdade)
6. `&&` (AND)
7. `||` (OR)

```dryad
// Exemplos de precedência
let resultado1 = 2 + 3 * 4;     // 14 (não 20)
let resultado2 = (2 + 3) * 4;   // 20
let resultado3 = !false && true; // true
```

---

## 🔀 Estruturas de Controle

### Condicionais (if/else)

#### If simples
```dryad
let idade = 18;
if idade >= 18 {
    print("Maior de idade");
}
```

#### If-else
```dryad
let nota = 7.5;
if nota >= 7.0 {
    print("Aprovado");
} else {
    print("Reprovado");
}
```

#### If-else encadeado
```dryad
let pontuacao = 85;
if pontuacao >= 90 {
    print("Excelente");
} else if pontuacao >= 80 {
    print("Bom");
} else if pontuacao >= 70 {
    print("Regular");
} else {
    print("Insuficiente");
}
```

### Loops

#### While
```dryad
let i = 0;
while i < 5 {
    print(i);
    i = i + 1;
}
```

#### For
```dryad
for i = 0; i < 5; i = i + 1 {
    print(i);
}
```

#### Do-While (Planejado)
```dryad
// Planejado para versões futuras
let i = 0;
do {
    print(i);
    i = i + 1;
} while i < 5;
```

#### Arrays, Matrizes e Tuplos
```dryad
let numeros = [1, 2, 3, 4, 5];
let matriz = [[1, 2], [3, 4]];
let vazio = []; // Array vazio
let tupla = (1, "dois", 3.0);
let tuplaVazia = (); // Tupla vazia
let valortupla = tupla.1; // Acessa o segundo elemento da tupla
let valorarray = numeros[2]; // Acessa o terceiro elemento do array
let valormatriz = matriz[1][0]; // Acessa o primeiro elemento da segunda linha da matriz
```

#### Try Catch Finally, Exceptions, Throw
```dryad
try {
    // Código que pode gerar erro
    let resultado = operacaoRiscosa();
    throw "Erro customizado"; // Lança uma exceção
} catch (erro) {
    // Tratamento do erro
    print("Erro capturado: " + erro);
} finally {
    // Código que sempre será executado
    print("Limpeza sempre executada");
}

// Try com apenas finally
try {
    let dados = processarDados();
} finally {
    liberarRecursos();
}

// Throw statements
throw "Mensagem de erro";
throw variavelErro;
```

#### Foreach (depende da implementação de arrays/matrizes/tuplos)
```dryad
for item in lista {
    // bloco de código para cada item
}

// Itera sobre arrays
for num in [1, 2, 3, 4, 5] {
    print(num);
}

// Itera sobre tuplas
for element in (1, "text", true) {
    print(element);
}

// Itera sobre strings (caractere por caractere)
for char in "Dryad" {
    print(char);
}
```
---

## 🔧 Funções

### Declaração de Funções

```dryad
function saudacao(nome) {
    return "Olá, " + nome + "!";
}

let mensagem = saudacao("Maria");
print(mensagem); // "Olá, Maria!"
```



### Funções sem Retorno

```dryad
function cumprimentar(nome) {
    print("Oi, " + nome + "!");
}

cumprimentar("João"); // "Oi, João!"
```

### Funções com Múltiplos Parâmetros

```dryad
function somar(a, b) {
    return a + b;
}

function calcular(x, y, z) {
    let resultado = x + y * z;
    return resultado;
}

let soma = somar(5, 3);          // 8
let calculo = calcular(2, 3, 4); // 14
```

### Funções Recursivas

```dryad
function fatorial(n) {
    if n <= 1 {
        return 1;
    }
    return n * fatorial(n - 1);
}

let resultado = fatorial(5); // 120
```

### Funções como Valores

```dryad
function quadrado(x) {
    return x * x;
}

let funcao = quadrado;
let resultado = funcao(4); // 16
```

#### Funções Anônimas (Lambdas)
```dryad
// Planejado para versões futuras   
let quadrado = (x) => x * x;
let resultado = quadrado(5); // 25
```


---

## 🏛️ Classes e Objetos

### Declaração de Classes

```dryad
class Pessoa {
    function init(nome, idade) {
        this.nome = nome;
        this.idade = idade;
    }
    
    function apresentar() {
        return "Meu nome é " + this.nome + " e tenho " + this.idade + " anos.";
    }
    
    function aniversario() {
        this.idade = this.idade + 1;
        return "Agora tenho " + this.idade + " anos!";
    }
}
```

### Criação de Instâncias

```dryad
let pessoa1 = Pessoa("Ana", 25);
let pessoa2 = Pessoa("Bruno", 30);

print(pessoa1.apresentar()); // "Meu nome é Ana e tenho 25 anos."
print(pessoa2.aniversario()); // "Agora tenho 31 anos!"
```




### Métodos Estáticos

```dryad
class Calculadora {
    static function pi() {
        return 3.14159;
    }
    
    static function circunferencia(raio) {
        return 2 * Calculadora.pi() * raio;
    }
}

let circ = Calculadora.circunferencia(5); // 31.4159
```

### Herança

```dryad
// Planejado para versões futuras
class Estudante extends Pessoa {
    function init(nome, idade, curso) {
        super.init(nome, idade);
        this.curso = curso;
    }
    
    function estudar() {
        return this.nome + " está estudando " + this.curso;
    }
}
```

### Visibilidade

```dryad
// Planejado para versões futuras
class ContaBancaria {
    public let numero;
    private let saldo;
    protected let titular;
    
    public function depositar(valor) {
        this.saldo = this.saldo + valor;
    }
    
    private function validarSaque(valor) {
        return valor <= this.saldo;
    }

    public static function criar(numero) {
        return ContaBancaria(numero, 0);
    }

    public function sacar(valor) {
        if this.validarSaque(valor) {
            this.saldo = this.saldo - valor;
            this.titular.notificarSaque(valor);
            return "Saque de " + valor + " realizado.";
        } else {
            return "Saldo insuficiente.";
        }
    }

    protected function notificarSaque(valor) {
        print("Notificando saque de " + valor + " para " + this.titular.nome);
    }
}

class ContaEspecial extends ContaBancaria {
    public function sacar(valor) {
        if this.validarSaque(valor) {
            this.saldo = this.saldo - valor;
            this.titular.notificarSaque(valor);
            return "Saque de " + valor + " realizado.";
        } else {
            return "Saldo insuficiente.";
        }
    }
}
```
print("Mensagem simples");
print("Valor: " + 42);

// Operações de arquivo
// read_file("arquivo.txt")
// write_file("saida.txt", "conteúdo")
```

### Chamadas de Função
```javascript
// Chamada simples
print("Hello World");

// Com expressões
print("Resultado: " + (2 + 3));

// Com variáveis
let nome = "Maria";
print("Olá, " + nome);

// Aninhadas
print("Debug: " + print("valor interno"));
```
---


## 🧮 Threading e asyncronismo

Dryad suporta programação assíncrona e concorrente, permitindo que múltiplas tarefas sejam executadas simultaneamente. Isso é especialmente útil para operações de I/O, como chamadas de rede ou leitura de arquivos.

### Funções Assíncronas

```dryad
async function buscarDados(url) {
    let resposta = await http_get(url);
    return resposta;
}
```

### Uso de `await`

```dryad
async function processarDados() {
    let dados = await buscarDados("https://api.exemplo.com/dados");
    print(dados);
} 
```

### Dentro de classes

Por ser ligada as funções os métodos também podem ser assíncronos.

```dryad
class API {
    async function fetchData(endpoint) {
        let response = await http_get(endpoint);
        return response;
    }
}
```


### Criação de Threads

```dryad
thread function tarefaLonga() {
    // Código que leva tempo para executar
    sleep(5000);
    print("Tarefa longa concluída");
}

let minhaThread = thread(tarefaLonga);
```

### Sincronização

```dryad
let contador = 0;
let lock = mutex();
thread function incrementar() {
    lock.lock();
    contador = contador + 1;
    lock.unlock();
}
let threads = [];
for (let i = 0; i < 10; i = i + 1) {
    threads.push(thread(incrementar));
}
for (let t in threads) {
    t.join();
}
print("Contador final: " + contador);
```

## 📦 Módulos e Imports

### Sistema Oak

Dryad utiliza o sistema de módulos **Oak**, que permite instalar e organizar bibliotecas de forma eficiente. O Oak é um gerenciador de pacotes completo que oferece dois tipos de projeto:

#### Tipos de Projeto

**1. Projeto (Project)**
```json
{
  "name": "meu-projeto",
  "version": "0.1.0", 
  "type": "project",
  "main": "main.dryad",
  "dependencies": {},
  "scripts": {
    "start": "dryad run main.dryad",
    "test": "dryad test",
    "check": "dryad check main.dryad"
  }
}
```

**2. Biblioteca (Library)**
```json
{
  "name": "minha-biblioteca",
  "version": "0.1.0",
  "type": "library", 
  "main": "src/main.dryad",
  "dependencies": {
    "dryad-stdlib": "^0.1.0"
  },
  "scripts": {
    "check": "dryad check src/main.dryad",
    "test": "dryad test"
  }
}
```

#### Estrutura de Projetos

**Projeto:**
```
meu-projeto/
├── main.dryad           # Ponto de entrada
├── oaklibs.json         # Configuração do projeto
├── oaklock.json         # Lock file (gerado automaticamente)
├── README.md
├── .gitignore
└── src/                 # Código adicional (opcional)
```

**Biblioteca:**
```
minha-biblioteca/
├── src/
│   └── main.dryad       # Ponto de entrada da biblioteca
├── lib/
│   ├── matematica.dryad # Módulos exportáveis
│   └── utilidades.dryad
├── oaklibs.json         # Configuração da biblioteca
├── oaklock.json         # Mapeamento de módulos
├── README.md
└── .gitignore
```

#### Comandos Oak

**Inicializar Projeto:**
```bash
# Criar projeto
oak init meu-projeto --type project

# Criar biblioteca  
oak init minha-biblioteca --type library
```

**Gerenciar Dependências:**
```bash
# Instalar dependência
oak install matematica-utils --version "^0.1.0"

# Remover dependência
oak remove matematica-utils

# Listar dependências
oak list

# Atualizar dependências
oak update
```

**Resolução de Módulos:**
```bash
# Gerar/atualizar oaklock.json
oak lock

# Informações do projeto
oak info

# Executar scripts
oak run start
oak run test
oak run check
```

#### Arquivo oaklock.json

O `oaklock.json` mapeia aliases para caminhos de arquivos, permitindo importações eficientes:

```json
{
  "modules": {
    "matematica-utils": {
      "paths": {
        "matematica": "./oak_modules/matematica-utils/lib/matematica.dryad",
        "utilidades": "./oak_modules/matematica-utils/lib/utilidades.dryad", 
        "formas": "./oak_modules/matematica-utils/lib/formas.dryad"
      }
    },
    "dryad-stdlib": {
      "paths": {
        "io": "./oak_modules/dryad-stdlib/io.dryad",
        "math": "./oak_modules/dryad-stdlib/math.dryad",
        "string": "./oak_modules/dryad-stdlib/string.dryad"
      }
    }
  }
}
```

### Exports

#### Export de Variáveis
```dryad
// arquivo: matematica.dryad
export let PI = 3.14159;
export let E = 2.71828;
```

#### Export de Funções
```dryad
// arquivo: utilidades.dryad
export function quadrado(x) {
    return x * x;
}

export function cubo(x) {
    return x * x * x;
}
```

#### Export de Classes
```dryad
// arquivo: formas.dryad
export class Retangulo {
    function init(largura, altura) {
        this.largura = largura;
        this.altura = altura;
    }
    
    function area() {
        return this.largura * this.altura;
    }
}
```

#### Export de Métodos Estáticos
```dryad
// arquivo: calculadora.dryad
export class Calculadora {
    static function pi() {
        return 3.14159;
    }
    
    static function circunferencia(raio) {
        return 2 * Calculadora.pi() * raio;
    }
}
```

### Imports

#### Use (Import direto)
```dryad
// Importando por caminho relativo -> começa do arquivo atual e percorre o caminho provido.
use "../../oak_modules/matematica-utils/lib/matematica.dryad";

// Importando por caminho absoluto -> começa com a raiz do projeto e percorre o caminho provido.
use "@/matematica-utils/lib/utilidades.dryad";

// Import de bibliotecas instaladas (oak_modules) -> usa o ficheiro oaklock.json para resolver o caminho. com base no alias provido.
use "matematica-utils/matematica";
use "matematica-utils/utilidades";

// Uso direto das funções importadas.
let resultado = quadrado(4);
let forma = Retangulo(10, 20);
let area = forma.area();
```

#### Estrutura Modular Completa

**Biblioteca matematica-utils/lib/matematica.dryad:**
```dryad
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
```

**Projeto que usa a biblioteca:**
```dryad
// main.dryad
use "matematica";

let fat5 = fatorial(5);    // 120
let fib7 = fibonacci(7);   // 13
print("5! = " + fat5);
print("fibonacci(7) = " + fib7);
```

#### Configuração Completa

**oaklibs.json (projeto que consome):**
```json
{
  "name": "meu-app",
  "version": "1.0.0",
  "type": "project",
  "main": "main.dryad",
  "dependencies": {
    "matematica-utils": "^0.1.0",
    "dryad-stdlib": "^0.1.0"
  }
}
```

**oaklock.json (gerado automaticamente):**
```json
{
  "modules": {
    "matematica-utils": {
      "paths": {
        "matematica": "./oak_modules/matematica-utils/lib/matematica.dryad",
        "utilidades": "./oak_modules/matematica-utils/lib/utilidades.dryad"
      }
    }
  }
}
```

### Estrutura de Módulos

```
projeto/
├── main.dryad
├── oaklibs.json
├── oaklock.json
├── oak_modules/
│   └── matematica-utils/
│       └── lib/
│           ├── matematica.dryad
│           ├── utilidades.dryad
│           └── formas.dryad
└── README.md
```

### Configuração Oak

O arquivo `oaklibs.json` é o coração do sistema de módulos:

```json
{
  "name": "meu-projeto",
  "version": "1.0.0",
  "type": "project",
  "main": "main.dryad",
  "dependencies": {
    "matematica-utils": "^0.1.0"
  },
  "scripts": {
    "start": "dryad run main.dryad",
    "test": "dryad test",
    "check": "dryad check main.dryad"
use "matematica-utils/matematica";

let resultado = quadrado(4); // Usa quadrado diretamente
```

### Estrutura de Módulos

```
projeto/
├── main.dryad
├── lib/
│   ├── matematica.dryad
│   ├── utilidades.dryad
│   └── formas.dryad
└── oaklibs.json
```

### Configuração Oak

```json
{
  "name": "meu-projeto",
  "version": "1.0.0",
  "lib_paths": ["./lib"], // Caminho para os módulos ./lib é a biblioteca padrão (common library)
  "dependencies": {}
}
```

---

## 🔧 Funções Nativas

### I/O (Entrada/Saída)

#### Print Functions
```dryad
print("Olá");           // Imprime sem quebra de linha
println("Mundo");       // Imprime com quebra de linha
```

#### Input Function
```dryad
let nome = input("Digite seu nome: ");
print("Olá, " + nome);
```

### String Functions

#### len() - Comprimento
```dryad
let texto = "Dryad";
let tamanho = len(texto); // 5
```

#### substr() - Substring
```dryad
let frase = "Linguagem Dryad";
let parte = substr(frase, 0, 9); // "Linguagem"
```

#### concat() - Concatenação
```dryad
let primeiro = "Olá";
let segundo = "mundo";
let completo = concat(primeiro, ", " + segundo); // "Olá, mundo"
```

### Math Functions

#### abs() - Valor Absoluto
```dryad
let negativo = -15;
let positivo = abs(negativo); // 15
```

#### sqrt() - Raiz Quadrada
```dryad
let numero = 16;
let raiz = sqrt(numero); // 4.0
```

#### pow() - Potenciação
```dryad
let base = 2;
let expoente = 3;
let resultado = pow(base, expoente); // 8.0
```

### Type Functions

#### type() - Tipo do Valor
```dryad
let numero = 42;
let texto = "Hello";
let booleano = true;

print(type(numero));   // "number"
print(type(texto));    // "string"
print(type(booleano)); // "bool"
```

#### Conversões de Tipo
```dryad
// to_string() - Converter para string
let num = 42;
let str = to_string(num); // "42"

// to_number() - Converter para número
let texto = "3.14";
let numero = to_number(texto); // 3.14
```

---

## 💬 Comentários

### Comentários de Linha

```dryad
// Este é um comentário de linha
let x = 10; // Comentário no final da linha

// Múltiplas linhas de comentário
// Cada linha precisa começar com //
// Como estas linhas aqui
```

### Comentários de Bloco

```dryad
// Planejado para versões futuras
/*
   Este é um comentário
   de múltiplas linhas
   que será implementado no futuro
*/
```

---

## 🔒 Palavras Reservadas

### Palavras-chave da Linguagem

#### Declarações
- `let` - Declaração de variável
- `function` - Declaração de função
- `class` - Declaração de classe
- `export` - Exportar elemento
- `static` - Método/propriedade estática

#### Controle de Fluxo
- `if` - Condicional
- `else` - Alternativa condicional
- `while` - Loop
- `for` - Loop iterativo
- `in` - Palavra-chave para foreach loops
- `return` - Retorno de função
- `break` - Quebra de loop
- `continue` - Continuar loop
- `try` - Bloco de tentativa
- `catch` - Captura de exceção
- `finally` - Bloco sempre executado
- `throw` - Lançar exceção

#### Módulos
- `using` - Import com namespace
- `use` - Import direto

#### Valores Literais
- `true` - Verdadeiro
- `false` - Falso
- `null` - Nulo/vazio

#### Orientação a Objetos
- `this` - Referência ao objeto atual
- `super` - Referência à classe pai
- `public` - Visibilidade pública
- `private` - Visibilidade privada

### Threading e Assíncronismo "async" | "await" | "thread" | "mutex"
- `async` - Declaração de função assíncrona
- `await` - Espera por uma operação assíncrona
- `thread` - Criação de thread
- `mutex` - Criação de mutex para sincronização

#### Outras
- `var` - (Reservado para uso futuro)
- `const` - (Reservado para uso futuro)


## 📦 Estruturas de dados

### Map
```dryad
// Hashmap (planejado para versões futuras)
Um hashmap (ou mapa de hash) é uma estrutura de dados que armazena informações como pares de chave-valor. Cada chave é única e é usada para aceder ao seu valor correspondente de forma muito rápida, sendo útil para associar dados a identificadores. Uma das principais vantagens do hashmap é a sua eficiência, pois as operações básicas (inserir, remover e obter) levam, idealmente, tempo constante. 

let HashMap = {
    "nome": "Alice",
    "idade": 30,
    "cidade": "Lisboa"
};

let nome = HashMap["nome"]; // "Alice"
HashMap["idade"] = 31; // Atualiza idade
HashMap["profissao"] = "Engenheira"; // Adiciona nova chave-valor
let idade = HashMap["idade"]; // 31
let profissao = HashMap["profissao"]; // "Engenheira"

```
---

## 📚 Exemplos Práticos

### Calculadora Simples

```dryad
// calculadora.dryad
class Calculadora {
    static function somar(a, b) {
        return a + b;
    }
    
    static function subtrair(a, b) {
        return a - b;
    }
    
    static function multiplicar(a, b) {
        return a * b;
    }
    
    static function dividir(a, b) {
        if b == 0 {
            print("Erro: Divisão por zero!");
            return null;
        }
        return a / b;
    }
}

// Usando a calculadora
let resultado1 = Calculadora.somar(10, 5);     // 15
let resultado2 = Calculadora.dividir(20, 4);   // 5.0
let resultado3 = Calculadora.dividir(10, 0);   // null (erro)
```

### Sistema de Usuários

```dryad
// usuario.dryad
class Usuario {
    function init(nome, email, idade) {
        this.nome = nome;
        this.email = email;
        this.idade = idade;
        this.ativo = true;
    }
    
    function perfil() {
        let status = this.ativo ? "Ativo" : "Inativo";
        return this.nome + " (" + this.email + ") - " + status;
    }
    
    function desativar() {
        this.ativo = false;
        return "Usuário " + this.nome + " foi desativado.";
    }
    
    function podeVotar() {
        return this.idade >= 16;
    }
}

// Criando usuários
let user1 = Usuario("Ana Silva", "ana@email.com", 25);
let user2 = Usuario("João Santos", "joao@email.com", 15);

print(user1.perfil());     // "Ana Silva (ana@email.com) - Ativo"
print(user1.podeVotar());  // true
print(user2.podeVotar());  // false
```

### Módulo de Utilidades

```dryad
// math_utils.dryad
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

export function fibonacci(n) {
    if n <= 1 {
        return n;
    }
    return fibonacci(n - 1) + fibonacci(n - 2);
}

// main.dryad
use "math_utils";

print(ehPar(4));        // true
print(ehPrimo(17));     // true
print(fibonacci(7));    // 13
```

### Conversor de Temperatura

```dryad
// temperatura.dryad
export class ConversorTemperatura {
    static function celsiusParaFahrenheit(celsius) {
        return (celsius * 9 / 5) + 32;
    }
    
    static function fahrenheitParaCelsius(fahrenheit) {
        return (fahrenheit - 32) * 5 / 9;
    }
    
    static function celsiusParaKelvin(celsius) {
        return celsius + 273.15;
    }
    
    static function kelvinParaCelsius(kelvin) {
        return kelvin - 273.15;
    }
}

// Exemplo de uso
using "temperatura" as temp;

let celsius = 25;
let fahrenheit = temp.ConversorTemperatura.celsiusParaFahrenheit(celsius);
let kelvin = temp.ConversorTemperatura.celsiusParaKelvin(celsius);

println("Temperatura:");
println(celsius + "°C = " + fahrenheit + "°F");
println(celsius + "°C = " + kelvin + "K");
```

---

## 🚀 Funcionalidades Futuras

### Arrays Indexáveis

```dryad
// Planejado para versões futuras
let numeros = [1, 2, 3, 4, 5];
let primeiro = numeros[0];      // 1
numeros[2] = 10;               // Modifica o terceiro elemento
let tamanho = len(numeros);    // 5

// Métodos de array
numeros.push(6);               // Adiciona elemento
let ultimo = numeros.pop();    // Remove e retorna último
```

### Objects/Maps

```dryad
// Planejado para versões futuras
let pessoa = {
    "nome": "Maria",
    "idade": 30,
    "email": "maria@email.com"
};

pessoa["telefone"] = "123-456-7890";  // Adiciona nova propriedade
let nome = pessoa["nome"];            // Acesso por chave
```

### Loops Avançados

```dryad
// Planejado para versões futuras

// While loop
let contador = 0;
while contador < 10 {
    print(contador);
    contador = contador + 1;
}

// For loop
for i in 0..10 {
    print(i);
}

// For-each loop
let lista = [1, 2, 3, 4, 5];
for item in lista {
    print(item);
}
```

### Tratamento de Exceções

```dryad
// Planejado para versões futuras
try {
    let resultado = dividir(10, 0);
    print(resultado);
} catch (erro) {
    print("Erro capturado: " + erro.message);
} finally {
    print("Sempre executado");
}

// Lançar exceções
function validarIdade(idade) {
    if idade < 0 {
        throw "Idade não pode ser negativa";
    }
    return true;
}
```

### Funções de Usuário Avançadas

```dryad
// Planejado para versões futuras

// Parâmetros padrão
function saudar(nome = "Visitante", saudacao = "Olá") {
    return saudacao + ", " + nome + "!");
}

// Parâmetros variáveis
function somar(...numeros) {
    let total = 0;
    for numero in numeros {
        total = total + numero;
    }
    return total;
}

// Funções lambda/anônimas
let quadrado = (x) => x * x;
let filtrados = lista.filter((x) => x > 5);
```

### Modules Avançados

```dryad
// Planejado para versões futuras

// Re-exports
export { funcao1, Classe1 } from "outro_modulo";

// Exports com renomeação
export { minhaFuncao as funcaoUtil };

// Import específico
import { funcao1, Classe1 } from "modulo";

// Import tudo
import * as utils from "utilidades";
```

### Recursos de Sistema

```dryad
// Planejado para versões futuras

// File System
let conteudo = fs.readFile("arquivo.txt");
fs.writeFile("saida.txt", "conteúdo");
let arquivos = fs.listDir("diretorio/");

// JSON
let objeto = json.parse('{"nome": "João", "idade": 25}');
let texto = json.stringify(objeto);

// HTTP (futuro distante)
let resposta = http.get("https://api.exemplo.com/dados");
```

---

## 📖 Guia de Referência Rápida

### Sintaxe Básica
```dryad
// Variáveis
let nome = "Dryad";
let idade = 1;

// Funções
function cumprimentar(nome) {
    return "Olá, " + nome;
}

// Classes
class Pessoa {
    function init(nome) {
        this.nome = nome;
    }
}

// Condicionais
if idade >= 18 {
    print("Adulto");
} else {
    print("Menor");
}
```

### Operadores Essenciais
```dryad
+  -  *  /     // Aritméticos
== != < > <= >= // Comparação
&& || !         // Lógicos
=               // Atribuição
```

### Funções Nativas Principais
```dryad
print()     println()   input()
len()       substr()    concat()
abs()       sqrt()      pow()
type()      to_string() to_number()
```

---

*Esta documentação reflete a sintaxe atual da linguagem Dryad (v0.1.1). Para funcionalidades futuras e roadmap detalhado, consulte a documentação de desenvolvimento.*
