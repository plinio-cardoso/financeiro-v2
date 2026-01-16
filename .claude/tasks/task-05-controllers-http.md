# Task 05: Controllers - HTTP

## Objetivo
Criar Controllers finos que apenas orquestram requisições HTTP, delegando toda lógica de negócio para Services.

## Contexto
Controllers são **pontos de entrada HTTP**. Eles validam entrada (via Form Requests), delegam para Services, e retornam respostas. Devem ser extremamente finos (10-20 linhas por método).

## Escopo

### Controllers a Criar
- [ ] `DashboardController` - Exibe dashboard principal
- [ ] `TransactionController` - CRUD de transações
- [ ] `NotificationSettingController` - Configurações de notificação

## Detalhamento

### DashboardController

**Localização**: `app/Http/Controllers/DashboardController.php`

**Métodos**:

```php
public function index(DashboardService $dashboardService)
```
- Rota: `GET /dashboard`
- Obtém estatísticas do mês atual via `DashboardService`
- Retorna view `dashboard.index` com dados
- Dados passados para view:
  - `stats` - Array com estatísticas do mês
  - `transactions` - Collection de transações do mês atual

**Responsabilidades**:
- Injetar DashboardService
- Chamar método do service
- Retornar view com dados

### TransactionController

**Localização**: `app/Http/Controllers/TransactionController.php`

**Métodos**:

```php
public function index(Request $request, TransactionService $transactionService)
```
- Rota: `GET /transactions`
- Exibe listagem de transações (será substituído por Livewire depois)
- Obtém transações via `TransactionService::getFilteredTransactions()`
- Pode receber query params para filtros
- Retorna view `transactions.index`

```php
public function create()
```
- Rota: `GET /transactions/create`
- Exibe formulário de criação
- Carrega tags disponíveis: `Tag::orderBy('name')->get()`
- Retorna view `transactions.create` com tags

```php
public function store(StoreTransactionRequest $request, TransactionService $transactionService)
```
- Rota: `POST /transactions`
- Cria nova transação
- Valida via StoreTransactionRequest
- Chama `TransactionService::createTransaction($request->validated())`
- Redireciona para `/transactions` com mensagem de sucesso
- Flash message: "Transação criada com sucesso!"

```php
public function edit(Transaction $transaction)
```
- Rota: `GET /transactions/{transaction}/edit`
- Exibe formulário de edição
- Route model binding automático
- Autorização via Policy (futuramente)
- Carrega tags disponíveis
- Retorna view `transactions.edit` com transaction e tags

```php
public function update(UpdateTransactionRequest $request, Transaction $transaction, TransactionService $transactionService)
```
- Rota: `PUT/PATCH /transactions/{transaction}`
- Atualiza transação existente
- Valida via UpdateTransactionRequest (já verifica ownership)
- Chama `TransactionService::updateTransaction($transaction, $request->validated())`
- Redireciona para `/transactions` com mensagem de sucesso
- Flash message: "Transação atualizada com sucesso!"

```php
public function destroy(Transaction $transaction, TransactionService $transactionService)
```
- Rota: `DELETE /transactions/{transaction}`
- Deleta transação
- Verifica ownership (via Policy futuramente)
- Chama `TransactionService::deleteTransaction($transaction)`
- Redireciona para `/transactions` com mensagem de sucesso
- Flash message: "Transação excluída com sucesso!"

**Middleware**:
- `auth` - Todas as rotas requerem autenticação

### NotificationSettingController

**Localização**: `app/Http/Controllers/NotificationSettingController.php`

**Métodos**:

```php
public function edit()
```
- Rota: `GET /settings/notifications`
- Exibe formulário de configurações
- Obtém configurações via `NotificationSetting::getSettings()`
- Retorna view `settings.notifications` com settings

```php
public function update(UpdateNotificationSettingRequest $request)
```
- Rota: `PUT /settings/notifications`
- Atualiza configurações de notificação
- Valida via UpdateNotificationSettingRequest
- Atualiza ou cria registro único
- Redireciona para `/settings/notifications` com mensagem de sucesso
- Flash message: "Configurações atualizadas com sucesso!"

## Rotas a Adicionar

**Arquivo**: `routes/web.php`

```php
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\NotificationSettingController;

Route::middleware(['auth', 'verified'])->group(function () {
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])
        ->name('dashboard');

    // Transactions
    Route::resource('transactions', TransactionController::class);

    // Notification Settings
    Route::get('/settings/notifications', [NotificationSettingController::class, 'edit'])
        ->name('settings.notifications.edit');
    Route::put('/settings/notifications', [NotificationSettingController::class, 'update'])
        ->name('settings.notifications.update');
});
```

## Comandos Artisan a Usar

```bash
php artisan make:controller DashboardController
php artisan make:controller TransactionController --resource
php artisan make:controller NotificationSettingController
```

## Exemplo de Controller Fino

```php
<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTransactionRequest;
use App\Http\Requests\UpdateTransactionRequest;
use App\Models\Transaction;
use App\Models\Tag;
use App\Services\TransactionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class TransactionController extends Controller
{
    public function __construct(
        private TransactionService $transactionService
    ) {}

    public function index(): View
    {
        $transactions = $this->transactionService->getFilteredTransactions(
            auth()->id(),
            request()->all()
        );

        return view('transactions.index', compact('transactions'));
    }

    public function create(): View
    {
        $tags = Tag::orderBy('name')->get();

        return view('transactions.create', compact('tags'));
    }

    public function store(StoreTransactionRequest $request): RedirectResponse
    {
        $this->transactionService->createTransaction($request->validated());

        return redirect()->route('transactions.index')
            ->with('success', 'Transação criada com sucesso!');
    }

    public function edit(Transaction $transaction): View
    {
        $tags = Tag::orderBy('name')->get();

        return view('transactions.edit', compact('transaction', 'tags'));
    }

    public function update(UpdateTransactionRequest $request, Transaction $transaction): RedirectResponse
    {
        $this->transactionService->updateTransaction($transaction, $request->validated());

        return redirect()->route('transactions.index')
            ->with('success', 'Transação atualizada com sucesso!');
    }

    public function destroy(Transaction $transaction): RedirectResponse
    {
        $this->transactionService->deleteTransaction($transaction);

        return redirect()->route('transactions.index')
            ->with('success', 'Transação excluída com sucesso!');
    }
}
```

## Convenções

- Controllers em `app/Http/Controllers/`
- Métodos devem ter **10-20 linhas no máximo**
- Sempre usar dependency injection no constructor
- Type hints em parâmetros e retornos
- Route model binding para buscar models por ID
- Flash messages em português
- Named routes para facilitar redirecionamentos
- Usar `compact()` para passar dados para views
- Middleware `auth` para todas as rotas
- Resource controllers para CRUD padrão

## Princípios Importantes

### Controller Fino
❌ **Errado** - Controller com lógica de negócio:
```php
public function store(Request $request)
{
    $validated = $request->validate([...]);
    $transaction = Transaction::create($validated);
    $transaction->tags()->sync($request->tags);

    if ($transaction->status === 'paid') {
        $transaction->paid_at = now();
        $transaction->save();
    }

    return redirect()->back();
}
```

✅ **Correto** - Controller fino:
```php
public function store(StoreTransactionRequest $request, TransactionService $transactionService)
{
    $transactionService->createTransaction($request->validated());

    return redirect()->route('transactions.index')
        ->with('success', 'Transação criada com sucesso!');
}
```

### Dependency Injection
- Injete Services no constructor
- Injete Form Requests nos métodos
- Use Route Model Binding para models

### Responses
- Views: retorne `view('nome.view', compact('dados'))`
- Redirects: use named routes `redirect()->route('nome.rota')`
- Flash messages: use `with('success', 'Mensagem')`

## Acceptance Criteria

- [ ] 3 Controllers criados em `app/Http/Controllers/`
- [ ] Todos os métodos têm máximo 20 linhas
- [ ] Controllers usam dependency injection
- [ ] Form Requests usados para validação
- [ ] Services usados para lógica de negócio
- [ ] Type hints em parâmetros e retornos
- [ ] Named routes definidos em `routes/web.php`
- [ ] Middleware `auth` aplicado
- [ ] Flash messages em português
- [ ] Route model binding para Transaction

## Dependências
- Task 01 completa (Models para route binding)
- Task 03 completa (Services para lógica)
- Task 04 completa (Form Requests para validação)

## Próxima Task
Task 06: Livewire Components

## Observações
- Controllers são **apenas orquestração**
- Lógica de negócio **sempre nos Services**
- Validação **sempre nos Form Requests**
- As views serão criadas na Task 07
- Livewire substituirá parte da funcionalidade na Task 06
