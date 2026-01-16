# Task 03: Services - Lógica de Negócio

## Objetivo
Criar os Services que contêm toda a lógica de negócio do sistema, mantendo os Controllers finos e focados apenas em orquestração.

## Contexto
Services são responsáveis por toda a regra de negócio. Eles orquestram operações entre models, repositories e serviços externos. Seguindo o princípio SOLID, cada Service tem uma responsabilidade única.

## Escopo

### Services a Criar
- [ ] `TransactionService` - Lógica de transações
- [ ] `NotificationService` - Lógica de notificações
- [ ] `MailgunService` - Abstração para envio de emails via Mailgun
- [ ] `DashboardService` - Lógica para cálculos do dashboard

## Detalhamento

### TransactionService

**Localização**: `app/Services/TransactionService.php`

**Métodos Principais**:

```php
public function createTransaction(array $data): Transaction
```
- Cria nova transação
- Valida user_id
- Associa tags se fornecidas
- Retorna Transaction criada

```php
public function updateTransaction(Transaction $transaction, array $data): Transaction
```
- Atualiza transação existente
- Pode atualizar tags
- Retorna Transaction atualizada

```php
public function deleteTransaction(Transaction $transaction): bool
```
- Deleta transação
- Remove associações de tags automaticamente (cascade)

```php
public function markAsPaid(Transaction $transaction): Transaction
```
- Marca transação como paga
- Define `paid_at` como now()
- Usa action trait do model
- Retorna Transaction atualizada

```php
public function markAsPending(Transaction $transaction): Transaction
```
- Marca transação como pendente
- Limpa `paid_at`
- Usa action trait do model

```php
public function calculateMonthlyTotals(int $userId, int $year, int $month): array
```
- Calcula totais do mês **apenas para débitos**
- Retorna array com:
  - `total_due` - Total a pagar no mês (pending + paid debits)
  - `total_paid` - Total já pago no mês (paid debits)
  - `total_pending` - Total pendente no mês (pending debits)

```php
public function getFilteredTransactions(int $userId, array $filters): Collection
```
- Retorna transações filtradas
- Filtros possíveis:
  - `start_date` - Data inicial
  - `end_date` - Data final
  - `search` - Busca em title
  - `tags` - Array de tag IDs
  - `status` - Status da transação
  - `type` - Tipo da transação
  - `sort_by` - Campo para ordenação (amount, due_date)
  - `sort_direction` - asc ou desc
- Aplica eager loading de `tags`
- Retorna Collection de Transactions

```php
public function getNextMonthTotal(int $userId, int $year, int $month): float
```
- Calcula total previsto para o próximo mês
- Considera apenas débitos pendentes
- Retorna valor decimal

### DashboardService

**Localização**: `app/Services/DashboardService.php`

**Métodos Principais**:

```php
public function getCurrentMonthStats(int $userId): array
```
- Retorna estatísticas do mês atual
- Array com:
  - `total_due` - Total a pagar no mês
  - `total_paid` - Total já pago
  - `total_pending` - Total ainda pendente
  - `next_month_total` - Total previsto próximo mês
  - `transactions_count` - Contagem de transações
  - `overdue_count` - Contagem de vencidas

```php
public function getMonthlyTransactions(int $userId, ?int $year = null, ?int $month = null): Collection
```
- Retorna transações do mês
- Se year/month não fornecidos, usa mês atual
- Ordenadas por due_date
- Com tags eager loaded

### NotificationService

**Localização**: `app/Services/NotificationService.php`

**Dependências**:
- `MailgunService` (injetado via constructor)
- `NotificationSetting` model

**Métodos Principais**:

```php
public function sendDueTodayNotifications(\DateTime $date): int
```
- Busca transações com due_date = $date e status = pending (apenas débitos)
- Verifica se notify_due_today está ativo
- Envia email via MailgunService
- Retorna quantidade de notificações enviadas

```php
public function sendOverdueNotifications(\DateTime $date): int
```
- Busca transações com due_date < $date e status = pending (apenas débitos)
- Verifica se notify_overdue está ativo
- Envia email via MailgunService
- Retorna quantidade de notificações enviadas

```php
private function shouldSendNotification(string $type): bool
```
- Verifica nas NotificationSettings se deve enviar
- $type: 'due_today' ou 'overdue'

```php
private function getNotificationEmails(): array
```
- Retorna lista de emails cadastrados
- Usa NotificationSetting::getSettings()

### MailgunService

**Localização**: `app/Services/MailgunService.php`

**Responsabilidade**: Abstrai completamente a API do Mailgun

**Métodos Principais**:

```php
public function send(array $to, string $subject, string $view, array $data = []): bool
```
- `$to` - Array de emails destinatários
- `$subject` - Assunto do email
- `$view` - Nome da view Blade para o corpo do email
- `$data` - Dados para passar para a view
- Retorna true se sucesso, false se falha
- Loga erros

```php
private function buildEmailHtml(string $view, array $data): string
```
- Renderiza view Blade como HTML
- Usa `view($view, $data)->render()`

**Configuração**:
- Lê configs de `config('services.mailgun')`
- Domain, secret, endpoint

## Comandos Artisan a Usar

```bash
php artisan make:class Services/TransactionService
php artisan make:class Services/DashboardService
php artisan make:class Services/NotificationService
php artisan make:class Services/MailgunService
```

## Estrutura de Exemplo - TransactionService

```php
<?php

namespace App\Services;

use App\Models\Transaction;
use App\Enums\TransactionTypeEnum;
use Illuminate\Support\Collection;

class TransactionService
{
    public function createTransaction(array $data): Transaction
    {
        $transaction = Transaction::create([
            'user_id' => $data['user_id'],
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
            'amount' => $data['amount'],
            'type' => $data['type'] ?? TransactionTypeEnum::Debit,
            'status' => $data['status'],
            'due_date' => $data['due_date'],
        ]);

        if (isset($data['tags']) && is_array($data['tags'])) {
            $transaction->tags()->sync($data['tags']);
        }

        return $transaction->load('tags');
    }

    public function calculateMonthlyTotals(int $userId, int $year, int $month): array
    {
        $transactions = Transaction::forUser($userId)
            ->forMonth($year, $month)
            ->debits() // Apenas débitos
            ->get();

        return [
            'total_due' => $transactions->sum('amount'),
            'total_paid' => $transactions->where('status', 'paid')->sum('amount'),
            'total_pending' => $transactions->where('status', 'pending')->sum('amount'),
        ];
    }

    // ... outros métodos
}
```

## Dependency Injection

Services devem ser injetados nos Controllers:

```php
class TransactionController extends Controller
{
    public function __construct(
        private TransactionService $transactionService
    ) {}
}
```

## Convenções

- Services ficam em `app/Services/`
- Um service por responsabilidade (SRP - Single Responsibility Principle)
- Services não retornam responses HTTP, apenas dados
- Use type hints em parâmetros e retornos
- Injete dependências via constructor
- Services podem chamar outros services
- Lógica complexa fica nos services, não nos controllers
- Services retornam Models, Collections ou arrays de dados

## Regras de Negócio Importantes

### Apenas Débitos em Cálculos
- **CRÍTICO**: Créditos existem no cadastro mas **NÃO entram em**:
  - Totais do dashboard
  - Cálculos mensais
  - Notificações
- Sempre filtrar por `type = debit` em cálculos

### Filtros de Transações
- Busca por título deve ser case-insensitive (`LIKE %search%`)
- Tags: filtro multi-select (transações que têm qualquer uma das tags)
- Ordenação padrão: `due_date ASC`

### Notificações
- Enviar apenas para transações **pendentes**
- Enviar apenas para **débitos**
- Verificar configurações antes de enviar
- Logar todas as tentativas de envio

## Acceptance Criteria

- [ ] 4 Services criados em `app/Services/`
- [ ] TransactionService implementado com todos os métodos
- [ ] DashboardService implementado
- [ ] NotificationService implementado
- [ ] MailgunService implementado
- [ ] Dependency injection configurada corretamente
- [ ] Lógica de negócio isolada dos controllers
- [ ] Apenas débitos considerados em cálculos
- [ ] Métodos têm type hints corretos
- [ ] Código segue SOLID principles

## Dependências
- Task 01 completa (Migrations e Enums)
- Task 02 completa (Models e Relacionamentos)

## Próxima Task
Task 04: Form Requests (Validação)

## Observações
- Services são a **camada de lógica de negócio**
- Controllers apenas orquestram, Services executam
- Testar services isoladamente será mais fácil
- MailgunService será usado pelos Commands (Task 08)
- Configuração do Mailgun será feita em `config/services.php` posteriormente
