# Banco Digital - Desafio Técnico

Sistema bancário desenvolvido como solução para desafio técnico, utilizando PHP 8, Slim Framework 3, Doctrine ORM, PostgreSQL, Docker, Twig/Bootstrap, PHPUnit e Phinx para migrations.

## Funcionalidades Implementadas

### Admin Bancário
- Criar contas bancárias
- Visualizar contas cadastradas
- Visualizar saldos

### Usuário de Conta
- Login por conta
- Visualizar saldo
- Creditar valores
- Debitar valores
- Transferir entre contas
- Emitir extrato

## Regras de Negócio

- Não permite saldo negativo
- Não permite transferência para a mesma conta
- Não permite valores inválidos
- Toda movimentação gera histórico de extrato

## Controle de Concorrência

Para garantir consistência em operações concorrentes:
- Uso de transações com Doctrine
- Lock pessimista (`PESSIMISTIC_WRITE`)
- Lock ordenado em transferências para minimizar deadlocks

## Stack Utilizada

- PHP 8
- Slim Framework 3
- Doctrine ORM
- PostgreSQL
- Twig
- Bootstrap 5
- PHPUnit
- Phinx
- Docker / Docker Compose

## Como Rodar o Projeto

### 1. Clonar o repositório

```bash
git clone <repo-url>
cd banco-digital
```

### 2. Subir os containers

```bash
docker compose up -d --build
```

### 3. Instalar dependências

```bash
docker compose exec app composer install
```

### 4. Rodar migrations

```bash
docker compose exec app ./vendor/bin/phinx migrate
```

Esse comando irá:
- criar as tabelas do banco
- registrar o histórico de migrations
- criar automaticamente a conta admin inicial

### 5. Acessar a aplicação

Abra no navegador:

```text
http://localhost:8080
```

## Credenciais Iniciais

### Admin Bancário

```text
Email: admin@bank.com
Senha: 123456
```

## Executando os Testes

```bash
docker compose exec app ./vendor/bin/phpunit
```

## Cobertura de Testes

Testes de integração cobrindo:
- criação de conta
- crédito
- débito
- bloqueio por saldo insuficiente
- transferência entre contas
- bloqueio de transferência inválida

## Estrutura do Projeto

```text
app/
├── Controllers/
├── Entities/
├── Services/

config/
├── doctrine.php

db/
├── migrations/

templates/
├── admin/
├── account/
├── auth/

tests/
├── Integration/
```

## Decisões Técnicas

### Histórico separado em `transactions`
Mantém auditoria completa das movimentações.

### Duas transações por transferência
Cada transferência gera:
- `transfer_out`
- `transfer_in`

Isso permite extrato correto para ambas as contas.

### Saldo persistido em `accounts`
Mantido para leitura rápida e performance.