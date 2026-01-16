# Task 01: Database - Migrations e Enums

## Objetivo
Criar a estrutura de banco de dados (migrations) e enums necessários para o sistema de controle financeiro doméstico.

## Contexto
Esta é a primeira etapa da implementação. Vamos criar todas as tabelas e enums que suportarão as transações financeiras, tags, e configurações de notificação.

## Escopo

### 1. Enums
- [ ] `TransactionStatusEnum` (pending, paid)
- [ ] `TransactionTypeEnum` (debit, credit)

### 2. Migrations
- [ ] `create_transactions_table` - Tabela principal de transações
- [ ] `create_tags_table` - Tabela de tags para categorização
- [ ] `create_transaction_tag_table` - Tabela pivot para relacionamento many-to-many
- [ ] `create_notification_settings_table` - Configurações de notificações

## Detalhamento

### TransactionStatusEnum
**Localização**: `app/Enums/TransactionStatusEnum.php`

**Valores**:
- `Pending` = 'pending'
- `Paid` = 'paid'

### TransactionTypeEnum
**Localização**: `app/Enums/TransactionTypeEnum.php`

**Valores**:
- `Debit` = 'debit' (conta a pagar)
- `Credit` = 'credit' (futuramente contas a receber - não usado em cálculos ainda)

### Migration: transactions
**Campos**:
- `id` - bigIncrements
- `user_id` - foreignId para users (onDelete cascade)
- `title` - string(255)
- `description` - text nullable
- `amount` - decimal(12, 2)
- `type` - string (enum: debit, credit) - default 'debit'
- `status` - string (enum: pending, paid) - default 'pending'
- `due_date` - date
- `paid_at` - datetime nullable
- `timestamps`

**Índices**:
- Index em `user_id`
- Index em `due_date`
- Index em `status`
- Index em `type`
- Composite index: `(user_id, due_date, status)`

### Migration: tags
**Campos**:
- `id` - bigIncrements
- `name` - string(100)
- `color` - string(7) nullable (hex color: #FF5733)
- `created_at` - timestamp

**Índices**:
- Unique em `name`

### Migration: transaction_tag (pivot)
**Campos**:
- `transaction_id` - foreignId (onDelete cascade)
- `tag_id` - foreignId (onDelete cascade)
- Primary key composta: `(transaction_id, tag_id)`

**Índices**:
- Index em `transaction_id`
- Index em `tag_id`

### Migration: notification_settings
**Campos**:
- `id` - bigIncrements
- `emails` - json (array de emails)
- `notify_due_today` - boolean (default true)
- `notify_overdue` - boolean (default true)
- `timestamps`

**Observação**: Teremos apenas 1 registro nesta tabela (configuração global)

## Comandos Artisan a Usar

```bash
# Criar Enums
php artisan make:enum TransactionStatusEnum
php artisan make:enum TransactionTypeEnum

# Criar Migrations
php artisan make:migration create_transactions_table
php artisan make:migration create_tags_table
php artisan make:migration create_transaction_tag_table
php artisan make:migration create_notification_settings_table
```

## Estrutura Esperada dos Enums

### TransactionStatusEnum
```php
<?php

namespace App\Enums;

enum TransactionStatusEnum: string
{
    case Pending = 'pending';
    case Paid = 'paid';
}
```

### TransactionTypeEnum
```php
<?php

namespace App\Enums;

enum TransactionTypeEnum: string
{
    case Debit = 'debit';
    case Credit = 'credit';
}
```

## Convenções

- Todas as foreign keys devem ter `onDelete('cascade')`
- Usar `$table->foreignId()` para chaves estrangeiras
- Timestamps sempre com `$table->timestamps()`
- Campos nullable devem ter `->nullable()`
- Valores default devem ser especificados com `->default()`
- Enums devem estar em `App\Enums` namespace
- Nomes de tabelas em snake_case plural
- Migrations devem ter rollback (`down()` method) funcional

## Acceptance Criteria

- [ ] 2 Enums criados em `app/Enums/`
- [ ] 4 Migrations criadas em `database/migrations/`
- [ ] Todas as migrations têm método `up()` e `down()` implementados
- [ ] Foreign keys configuradas corretamente com cascade
- [ ] Índices criados para otimizar queries
- [ ] Campos com tipos e constraints corretos
- [ ] Migrations seguem padrão Laravel 12

## Próxima Task
Task 02: Models e Relacionamentos

## Observações
- **NÃO executar** `php artisan migrate` nesta task
- Migrations serão executadas manualmente posteriormente
- Focar apenas na estrutura e definição dos schemas
- Seguir convenções do Laravel 12
