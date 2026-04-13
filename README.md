# Banco Digital - Desafio Técnico

Sistema bancário desenvolvido em **PHP 8**, **Slim Framework 3**, **Doctrine ORM**, **PostgreSQL**, **Twig**, **Bootstrap** e **Docker**.

O projeto implementa operações bancárias básicas com foco em:

* Integridade de saldo
* Concorrência segura
* Arquitetura organizada
* Testes automatizados
* Boa experiência de uso

---

## Funcionalidades

### Administração

* Login administrativo
* Criação de contas bancárias
* Dashboard com métricas do sistema
* Listagem de contas cadastradas

### Usuário de Conta

* Login por conta
* Visualização de saldo
* Crédito em conta
* Débito em conta
* Transferência entre contas
* Extrato com filtros e paginação

---

## Diferenciais Técnicos Implementados

* Controle de concorrência com **Pessimistic Lock**
* Transações atômicas com Doctrine
* Proteção contra **saldo negativo**
* Constraints de negócio no banco de dados
* Proteção **CSRF**
* Sessão segura com regeneração de ID
* Testes de integração automatizados
* Banco separado para testes

---

## Stack Utilizada

* PHP 8.0
* Slim Framework 3
* Doctrine ORM
* PostgreSQL
* Twig
* Bootstrap 5
* Docker / Docker Compose
* PHPUnit
* Phinx

---

## Requisitos

* Docker
* Docker Compose

---

## Como rodar o projeto do zero

### 1. Clonar o repositório

```bash
git clone <url-do-repositorio>
cd nome-do-projeto
```

---

### 2. Subir os containers

```bash
docker compose up -d --build
```

---

### 3. Instalar dependências PHP

```bash
docker compose exec app composer install
```

---

### 4. Criar banco de testes

```bash
docker compose exec db psql -U app_user -d postgres -c "CREATE DATABASE app_db_test;"
```

---

### 5. Rodar migrations do ambiente principal

```bash
docker compose exec app ./vendor/bin/phinx migrate
```

---

### 6. Rodar migrations do ambiente de teste

```bash
docker compose exec app ./vendor/bin/phinx migrate -e testing
```

---

## Acesso ao Sistema

### Aplicação

```text
http://localhost:8080
```

### Credenciais Admin Padrão

```text
Email: admin@bank.com
Senha: 123456
```

---

## Rodando os Testes

O projeto utiliza um **banco exclusivo para testes (`app_db_test`)**.

### Executar toda a suíte

```bash
docker compose exec app ./vendor/bin/phpunit
```

---

## Estrutura dos Testes

Atualmente são cobertos cenários como:

* Criação de conta
* E-mail duplicado
* E-mail inválido
* Senha inválida
* Crédito
* Débito
* Saldo insuficiente
* Transferência
* Transferência inválida
* Extrato de conta

---

## Banco de Dados

### Banco principal

```text
app_db
```

### Banco de testes

```text
app_db_test
```

---

## Estrutura do Projeto

```text
app/
├── Controllers/
├── Entities/
├── Exceptions/
├── Middleware/
├── Routes/
├── Services/

config/
db/
templates/
tests/
```

---

## Segurança Implementada

* CSRF Protection via middleware customizado
* Session Regeneration no login
* Logout seguro
* Validação de dados de entrada
* Hash de senha com password_hash

---

## Concorrência / Integridade

Operações financeiras utilizam:

* Transações de banco de dados
* Pessimistic Lock (`PESSIMISTIC_WRITE`)
* Lock ordenado em transferências

Garantindo consistência mesmo sob múltiplas requisições simultâneas.

---

## Observações

Caso precise recriar completamente o ambiente:

```bash
docker compose down -v
docker compose up -d --build
```

Depois rode novamente as migrations.

---

## Autor

Desenvolvido por Lael Albuquerque
