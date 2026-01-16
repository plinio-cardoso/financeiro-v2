# Task 06: Livewire Components

## Objetivo
Criar componentes Livewire para interatividade sem JavaScript, substituindo listagens e formulários tradicionais por componentes reativos e dinâmicos.

## Contexto
Livewire permite criar interfaces interativas sem escrever JavaScript. Vamos criar componentes para dashboard stats, listagem de transações com filtros/paginação, formulários, e ações AJAX.

## Escopo

### Componentes Livewire a Criar
- [ ] `DashboardStats` - Cards com estatísticas do mês
- [ ] `TransactionList` - Listagem com filtros, ordenação e paginação
- [ ] `TransactionForm` - Formulário de criar/editar
- [ ] `TransactionActions` - Ações (marcar como pago, excluir)

## Detalhamento

### DashboardStats

**Localização**:
- Class: `app/Livewire/DashboardStats.php`
- View: `resources/views/livewire/dashboard-stats.blade.php`

**Responsabilidade**: Exibir cards com estatísticas financeiras do mês

**Propriedades**:
```php
public int $userId;
public array $stats = [];
```

**Métodos**:
```php
public function mount(DashboardService $dashboardService): void
```
- Carrega estatísticas do usuário autenticado
- Popula `$this->stats` com dados do DashboardService

**View** - Cards superiores:
- Total a pagar no mês atual
- Total já pago no mês
- Total previsto para o próximo mês
- Contagem de transações vencidas

**Uso**:
```blade
<livewire:dashboard-stats />
```

### TransactionList

**Localização**:
- Class: `app/Livewire/TransactionList.php`
- View: `resources/views/livewire/transaction-list.blade.php`

**Responsabilidade**: Listagem de transações com filtros, ordenação e paginação

**Propriedades Públicas**:
```php
// Filtros
public string $search = '';
public ?string $startDate = null;
public ?string $endDate = null;
public array $selectedTags = [];
public ?string $filterStatus = null;
public ?string $filterType = null;

// Ordenação
public string $sortBy = 'due_date';
public string $sortDirection = 'asc';

// Paginação
public int $perPage = 15;
```

**Propriedades Computadas**:
```php
#[Computed]
public function transactions()
```
- Retorna transações filtradas usando TransactionService
- Aplica filtros, ordenação e paginação
- Usa eager loading de `tags` e `user`

```php
#[Computed]
public function tags()
```
- Retorna todas as tags disponíveis para o filtro

**Métodos**:
```php
public function updatedSearch(): void
```
- Reseta para primeira página quando search muda
- `$this->resetPage();`

```php
public function updatedSelectedTags(): void
```
- Reseta para primeira página quando tags mudam

```php
public function updatedFilterStatus(): void
```
- Reseta página quando status muda

```php
public function sortBy(string $field): void
```
- Alterna direção se já está ordenando por esse campo
- Define novo campo de ordenação

```php
public function clearFilters(): void
```
- Limpa todos os filtros
- Reseta search, dates, tags, status, type

**View** - Elementos:
- Formulário de filtros (search, date range, tags multi-select, status, type)
- Botão "Limpar Filtros"
- Tabela de transações
- Headers com ordenação clicável
- Paginação

**Uso**:
```blade
<livewire:transaction-list />
```

### TransactionForm

**Localização**:
- Class: `app/Livewire/TransactionForm.php`
- View: `resources/views/livewire/transaction-form.blade.php`

**Responsabilidade**: Formulário para criar ou editar transação

**Propriedades**:
```php
public ?Transaction $transaction = null;

// Form fields
public string $title = '';
public string $description = '';
public float $amount = 0;
public string $type = 'debit';
public string $status = 'pending';
public string $dueDate = '';
public ?string $paidAt = null;
public array $selectedTags = [];

// Computed
public bool $editing = false;
```

**Propriedades Computadas**:
```php
#[Computed]
public function tags()
```
- Retorna todas as tags disponíveis

**Métodos**:
```php
public function mount(?Transaction $transaction = null): void
```
- Se $transaction fornecida, preenche campos (modo edição)
- Caso contrário, modo criação

```php
public function save(TransactionService $transactionService): void
```
- Valida dados inline com `$this->validate()`
- Se editing: chama `updateTransaction()`
- Se criando: chama `createTransaction()`
- Emite evento `transaction-saved`
- Usa `InteractsWithBanner` trait para mostrar sucesso
- `$this->banner('Transação salva com sucesso!');`

```php
protected function rules(): array
```
- Retorna regras de validação (mesmas do Form Request)

```php
protected function messages(): array
```
- Retorna mensagens customizadas em português

**View** - Campos:
- Input: title
- Textarea: description
- Input number: amount
- Select: type (debit/credit)
- Select: status (pending/paid)
- Input date: due_date
- Input datetime: paid_at (visível apenas se status = paid)
- Multi-select: tags
- Botões: Salvar, Cancelar

**Uso**:
```blade
{{-- Criar --}}
<livewire:transaction-form />

{{-- Editar --}}
<livewire:transaction-form :transaction="$transaction" />
```

### TransactionActions

**Localização**:
- Class: `app/Livewire/TransactionActions.php`
- View: `resources/views/livewire/transaction-actions.blade.php`

**Responsabilidade**: Ações rápidas (marcar como pago, excluir) via AJAX

**Propriedades**:
```php
public Transaction $transaction;
public bool $confirmingDelete = false;
```

**Métodos**:
```php
public function togglePaidStatus(TransactionService $transactionService): void
```
- Se transaction está paid: marca como pending
- Se transaction está pending: marca como paid
- Usa TransactionService para a ação
- Emite evento `transaction-updated`
- Banner de sucesso

```php
public function confirmDelete(): void
```
- Abre modal de confirmação
- `$this->confirmingDelete = true;`

```php
public function delete(TransactionService $transactionService): void
```
- Deleta transação via service
- Emite evento `transaction-deleted`
- Banner de sucesso
- Redireciona para lista

**View** - Elementos:
- Botão toggle paid/pending (ícone check)
- Botão excluir (ícone trash)
- Modal de confirmação de exclusão (usar Jetstream modal)

**Uso**:
```blade
<livewire:transaction-actions :transaction="$transaction" />
```

## Comandos Artisan a Usar

```bash
php artisan make:livewire DashboardStats
php artisan make:livewire TransactionList
php artisan make:livewire TransactionForm
php artisan make:livewire TransactionActions
```

## Exemplo de Estrutura - TransactionList

```php
<?php

namespace App\Livewire;

use App\Models\Tag;
use App\Services\TransactionService;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Computed;

class TransactionList extends Component
{
    use WithPagination;

    // Filtros
    public string $search = '';
    public ?string $startDate = null;
    public ?string $endDate = null;
    public array $selectedTags = [];
    public ?string $filterStatus = null;

    // Ordenação
    public string $sortBy = 'due_date';
    public string $sortDirection = 'asc';

    public function __construct(
        private TransactionService $transactionService
    ) {}

    #[Computed]
    public function transactions()
    {
        return $this->transactionService->getFilteredTransactions(
            auth()->id(),
            [
                'search' => $this->search,
                'start_date' => $this->startDate,
                'end_date' => $this->endDate,
                'tags' => $this->selectedTags,
                'status' => $this->filterStatus,
                'sort_by' => $this->sortBy,
                'sort_direction' => $this->sortDirection,
            ]
        )->paginate($this->perPage);
    }

    #[Computed]
    public function tags()
    {
        return Tag::orderBy('name')->get();
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function sortBy(string $field): void
    {
        if ($this->sortBy === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortBy = $field;
            $this->sortDirection = 'asc';
        }
    }

    public function clearFilters(): void
    {
        $this->reset([
            'search',
            'startDate',
            'endDate',
            'selectedTags',
            'filterStatus',
        ]);
    }

    public function render()
    {
        return view('livewire.transaction-list');
    }
}
```

## Livewire 3 - Convenções Importantes

### Wire Models
- `wire:model.live` - Atualização em tempo real
- `wire:model` - Atualização ao sair do campo (deferred)

### Loading States
```blade
<button wire:click="save" wire:loading.attr="disabled">
    Salvar
</button>

<div wire:loading wire:target="save">
    Salvando...
</div>
```

### Eventos
```php
// Emitir evento
$this->dispatch('transaction-saved');

// Escutar evento
#[On('transaction-saved')]
public function refresh(): void
{
    // Recarregar dados
}
```

### Computed Properties
```php
#[Computed]
public function transactions()
{
    return Transaction::where(...)->get();
}
```

Uso na view:
```blade
@foreach ($this->transactions as $transaction)
    ...
@endforeach
```

### Paginação
```php
use Livewire\WithPagination;

// No component
use WithPagination;

// Na view
{{ $transactions->links() }}
```

## Jetstream Integration

### Banner para Mensagens
```php
use Laravel\Jetstream\InteractsWithBanner;

class TransactionForm extends Component
{
    use InteractsWithBanner;

    public function save()
    {
        // ...
        $this->banner('Transação salva com sucesso!');
    }
}
```

### Modals de Confirmação
```blade
<x-confirmation-modal wire:model.live="confirmingDelete">
    <x-slot name="title">
        Excluir Transação
    </x-slot>

    <x-slot name="content">
        Tem certeza que deseja excluir esta transação?
    </x-slot>

    <x-slot name="footer">
        <x-secondary-button wire:click="$toggle('confirmingDelete')">
            Cancelar
        </x-secondary-button>

        <x-danger-button wire:click="delete">
            Excluir
        </x-danger-button>
    </x-slot>
</x-confirmation-modal>
```

## Acceptance Criteria

- [ ] 4 componentes Livewire criados em `app/Livewire/`
- [ ] 4 views criadas em `resources/views/livewire/`
- [ ] DashboardStats exibe estatísticas do mês
- [ ] TransactionList tem filtros funcionais
- [ ] TransactionList tem ordenação clicável
- [ ] TransactionList tem paginação
- [ ] TransactionForm valida dados inline
- [ ] TransactionForm funciona para criar e editar
- [ ] TransactionActions marca como pago/pendente via AJAX
- [ ] TransactionActions deleta com confirmação
- [ ] Componentes usam InteractsWithBanner trait
- [ ] Loading states implementados
- [ ] Componentes injetam Services via DI

## Dependências
- Task 02 completa (Models)
- Task 03 completa (Services)

## Próxima Task
Task 07: Views e Frontend (Tailwind)

## Observações
- Livewire 3 usa `wire:model.live` para real-time
- Components devem injetar Services no constructor
- Use computed properties para queries dinâmicas
- Emita eventos para comunicação entre components
- Banner do Jetstream para feedback ao usuário
- Modals do Jetstream para confirmações
