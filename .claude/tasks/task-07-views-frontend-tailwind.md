# Task 07: Views e Frontend (Tailwind)

## Objetivo
Criar todas as views Blade com Tailwind CSS, integrando componentes Livewire e customizando o menu lateral do Jetstream para o sistema financeiro.

## Contexto
Vamos criar layouts, views principais e views dos componentes Livewire, seguindo o design system do Jetstream com Tailwind CSS. O projeto usa Livewire stack do Jetstream, então já temos componentes prontos.

## Escopo

### Layouts
- [ ] Customizar menu lateral do Jetstream

### Views de Páginas
- [ ] `dashboard.index` - Dashboard principal
- [ ] `transactions.index` - Lista de transações
- [ ] `transactions.create` - Criar transação
- [ ] `transactions.edit` - Editar transação
- [ ] `settings.notifications` - Configurações de notificação

### Views Livewire
- [ ] `livewire.dashboard-stats` - Cards de estatísticas
- [ ] `livewire.transaction-list` - Tabela com filtros
- [ ] `livewire.transaction-form` - Formulário
- [ ] `livewire.transaction-actions` - Botões de ação

## Detalhamento

### 1. Customizar Menu Lateral (Navigation Menu)

**Arquivo**: `resources/views/navigation-menu.blade.php`

**Alterações Necessárias**:

Substituir os links padrão do Jetstream pelos links do sistema financeiro:

```blade
<!-- Navigation Links -->
<div class="hidden space-x-8 sm:-my-px sm:ms-10 sm:flex">
    <x-nav-link href="{{ route('dashboard') }}" :active="request()->routeIs('dashboard')">
        {{ __('Dashboard') }}
    </x-nav-link>

    <x-nav-link href="{{ route('transactions.index') }}" :active="request()->routeIs('transactions.*')">
        {{ __('Transações') }}
    </x-nav-link>

    <x-nav-link href="{{ route('transactions.create') }}" :active="request()->routeIs('transactions.create')">
        {{ __('Nova Transação') }}
    </x-nav-link>

    <x-nav-link href="{{ route('settings.notifications.edit') }}" :active="request()->routeIs('settings.*')">
        {{ __('Configurações') }}
    </x-nav-link>
</div>
```

**Menu Responsivo (Mobile)** - Fazer a mesma alteração na seção responsive:

```blade
<!-- Responsive Navigation Menu -->
<div :class="{'block': open, 'hidden': ! open}" class="hidden sm:hidden">
    <div class="pt-2 pb-3 space-y-1">
        <x-responsive-nav-link href="{{ route('dashboard') }}" :active="request()->routeIs('dashboard')">
            {{ __('Dashboard') }}
        </x-responsive-nav-link>

        <x-responsive-nav-link href="{{ route('transactions.index') }}" :active="request()->routeIs('transactions.*')">
            {{ __('Transações') }}
        </x-responsive-nav-link>

        <!-- ... outros links -->
    </div>
</div>
```

### 2. Dashboard View

**Arquivo**: `resources/views/dashboard.blade.php`

**Estrutura**:
```blade
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Stats Cards (Livewire Component) -->
            <livewire:dashboard-stats />

            <!-- Divisor -->
            <x-section-border />

            <!-- Transações do Mês -->
            <div class="mt-8">
                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">
                    Transações do Mês Atual
                </h3>

                <livewire:transaction-list />
            </div>
        </div>
    </div>
</x-app-layout>
```

### 3. Transactions Index View

**Arquivo**: `resources/views/transactions/index.blade.php`

```blade
<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ __('Transações') }}
            </h2>

            <a href="{{ route('transactions.create') }}" class="inline-flex items-center px-4 py-2 bg-gray-800 dark:bg-gray-200 border border-transparent rounded-md font-semibold text-xs text-white dark:text-gray-800 uppercase tracking-widest hover:bg-gray-700 dark:hover:bg-white focus:bg-gray-700 dark:focus:bg-white active:bg-gray-900 dark:active:bg-gray-300">
                {{ __('Nova Transação') }}
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <livewire:transaction-list />
        </div>
    </div>
</x-app-layout>
```

### 4. Transaction Create View

**Arquivo**: `resources/views/transactions/create.blade.php`

```blade
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Nova Transação') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg p-6">
                <livewire:transaction-form />
            </div>
        </div>
    </div>
</x-app-layout>
```

### 5. Transaction Edit View

**Arquivo**: `resources/views/transactions/edit.blade.php`

```blade
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Editar Transação') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg p-6">
                <livewire:transaction-form :transaction="$transaction" />
            </div>
        </div>
    </div>
</x-app-layout>
```

### 6. Notification Settings View

**Arquivo**: `resources/views/settings/notifications.blade.php`

```blade
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Configurações de Notificação') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <x-form-section submit="updateSettings">
                <x-slot name="title">
                    {{ __('Notificações por E-mail') }}
                </x-slot>

                <x-slot name="description">
                    {{ __('Configure os e-mails que receberão notificações sobre contas a vencer e vencidas.') }}
                </x-slot>

                <x-slot name="form">
                    <!-- Emails -->
                    <div class="col-span-6">
                        <x-label for="emails" value="{{ __('E-mails para Notificação') }}" />

                        <div id="emails-container" class="mt-2 space-y-2">
                            @foreach(old('emails', $settings->emails ?? []) as $index => $email)
                                <div class="flex gap-2">
                                    <x-input type="email" name="emails[]" value="{{ $email }}" class="flex-1" />
                                    <button type="button" onclick="removeEmail(this)" class="px-3 py-2 bg-red-500 text-white rounded">
                                        Remover
                                    </button>
                                </div>
                            @endforeach
                        </div>

                        <button type="button" onclick="addEmail()" class="mt-2 text-sm text-blue-600 hover:text-blue-800">
                            + Adicionar E-mail
                        </button>

                        <x-input-error for="emails" class="mt-2" />
                    </div>

                    <!-- Notify Due Today -->
                    <div class="col-span-6">
                        <label class="flex items-center">
                            <input type="checkbox" name="notify_due_today" value="1" {{ old('notify_due_today', $settings->notify_due_today ?? true) ? 'checked' : '' }} class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500" />
                            <span class="ms-2 text-sm text-gray-600 dark:text-gray-400">
                                {{ __('Notificar sobre contas que vencem hoje') }}
                            </span>
                        </label>
                    </div>

                    <!-- Notify Overdue -->
                    <div class="col-span-6">
                        <label class="flex items-center">
                            <input type="checkbox" name="notify_overdue" value="1" {{ old('notify_overdue', $settings->notify_overdue ?? true) ? 'checked' : '' }} class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500" />
                            <span class="ms-2 text-sm text-gray-600 dark:text-gray-400">
                                {{ __('Notificar sobre contas vencidas') }}
                            </span>
                        </label>
                    </div>
                </x-slot>

                <x-slot name="actions">
                    <x-action-message class="me-3" on="saved">
                        {{ __('Salvo.') }}
                    </x-action-message>

                    <x-button>
                        {{ __('Salvar') }}
                    </x-button>
                </x-slot>
            </x-form-section>
        </div>
    </div>

    @push('scripts')
    <script>
        function addEmail() {
            const container = document.getElementById('emails-container');
            const div = document.createElement('div');
            div.className = 'flex gap-2';
            div.innerHTML = `
                <input type="email" name="emails[]" class="flex-1 border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm" />
                <button type="button" onclick="removeEmail(this)" class="px-3 py-2 bg-red-500 text-white rounded">
                    Remover
                </button>
            `;
            container.appendChild(div);
        }

        function removeEmail(button) {
            button.parentElement.remove();
        }
    </script>
    @endpush
</x-app-layout>
```

### 7. Livewire: Dashboard Stats View

**Arquivo**: `resources/views/livewire/dashboard-stats.blade.php`

```blade
<div class="grid grid-cols-1 md:grid-cols-3 gap-6">
    <!-- Total a Pagar -->
    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
        <div class="text-sm font-medium text-gray-500 dark:text-gray-400">
            Total a Pagar (Mês Atual)
        </div>
        <div class="mt-2 text-3xl font-semibold text-gray-900 dark:text-gray-100">
            R$ {{ number_format($stats['total_due'] ?? 0, 2, ',', '.') }}
        </div>
    </div>

    <!-- Total Pago -->
    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
        <div class="text-sm font-medium text-gray-500 dark:text-gray-400">
            Total Pago (Mês Atual)
        </div>
        <div class="mt-2 text-3xl font-semibold text-green-600 dark:text-green-400">
            R$ {{ number_format($stats['total_paid'] ?? 0, 2, ',', '.') }}
        </div>
    </div>

    <!-- Próximo Mês -->
    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
        <div class="text-sm font-medium text-gray-500 dark:text-gray-400">
            Previsto Próximo Mês
        </div>
        <div class="mt-2 text-3xl font-semibold text-gray-900 dark:text-gray-100">
            R$ {{ number_format($stats['next_month_total'] ?? 0, 2, ',', '.') }}
        </div>
    </div>
</div>
```

### 8. Livewire: Transaction List View

**Arquivo**: `resources/views/livewire/transaction-list.blade.php`

```blade
<div>
    <!-- Filtros -->
    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6 mb-6">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <!-- Busca -->
            <div>
                <x-label for="search" value="Buscar" />
                <x-input type="text" wire:model.live="search" placeholder="Título da transação..." class="mt-1 block w-full" />
            </div>

            <!-- Data Inicial -->
            <div>
                <x-label for="startDate" value="Data Inicial" />
                <x-input type="date" wire:model.live="startDate" class="mt-1 block w-full" />
            </div>

            <!-- Data Final -->
            <div>
                <x-label for="endDate" value="Data Final" />
                <x-input type="date" wire:model.live="endDate" class="mt-1 block w-full" />
            </div>

            <!-- Status -->
            <div>
                <x-label for="filterStatus" value="Status" />
                <select wire:model.live="filterStatus" class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm">
                    <option value="">Todos</option>
                    <option value="pending">Pendente</option>
                    <option value="paid">Pago</option>
                </select>
            </div>

            <!-- Tipo -->
            <div>
                <x-label for="filterType" value="Tipo" />
                <select wire:model.live="filterType" class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm">
                    <option value="">Todos</option>
                    <option value="debit">Débito</option>
                    <option value="credit">Crédito</option>
                </select>
            </div>

            <!-- Tags -->
            <div>
                <x-label for="selectedTags" value="Tags" />
                <select wire:model.live="selectedTags" multiple class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm">
                    @foreach($this->tags as $tag)
                        <option value="{{ $tag->id }}">{{ $tag->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="mt-4">
            <x-secondary-button wire:click="clearFilters">
                Limpar Filtros
            </x-secondary-button>
        </div>
    </div>

    <!-- Tabela -->
    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
            <thead class="bg-gray-50 dark:bg-gray-900">
                <tr>
                    <th wire:click="sortBy('title')" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-800">
                        Título
                        @if($sortBy === 'title')
                            <span>{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                        @endif
                    </th>
                    <th wire:click="sortBy('amount')" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-800">
                        Valor
                        @if($sortBy === 'amount')
                            <span>{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                        @endif
                    </th>
                    <th wire:click="sortBy('due_date')" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-800">
                        Vencimento
                        @if($sortBy === 'due_date')
                            <span>{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                        @endif
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                        Status
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                        Tags
                    </th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                        Ações
                    </th>
                </tr>
            </thead>
            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                @forelse($this->transactions as $transaction)
                    <tr wire:key="transaction-{{ $transaction->id }}">
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                            {{ $transaction->title }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                            R$ {{ number_format($transaction->amount, 2, ',', '.') }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                            {{ $transaction->due_date->format('d/m/Y') }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $transaction->status->value === 'paid' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }}">
                                {{ $transaction->status->value === 'paid' ? 'Pago' : 'Pendente' }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-sm">
                            <div class="flex gap-1 flex-wrap">
                                @foreach($transaction->tags as $tag)
                                    <span class="px-2 py-1 text-xs rounded" style="background-color: {{ $tag->color ?? '#6B7280' }}20; color: {{ $tag->color ?? '#6B7280' }}">
                                        {{ $tag->name }}
                                    </span>
                                @endforeach
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                            <livewire:transaction-actions :transaction="$transaction" :key="'actions-'.$transaction->id" />
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-6 py-4 text-center text-sm text-gray-500 dark:text-gray-400">
                            Nenhuma transação encontrada.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <!-- Paginação -->
        <div class="px-6 py-4">
            {{ $this->transactions->links() }}
        </div>
    </div>

    <!-- Loading overlay -->
    <div wire:loading class="fixed inset-0 bg-gray-900 bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-white dark:bg-gray-800 rounded-lg p-6">
            <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-indigo-600 mx-auto"></div>
            <p class="mt-4 text-gray-900 dark:text-gray-100">Carregando...</p>
        </div>
    </div>
</div>
```

### 9. Livewire: Transaction Form View

**Arquivo**: `resources/views/livewire/transaction-form.blade.php`

```blade
<form wire:submit="save">
    <div class="space-y-6">
        <!-- Título -->
        <div>
            <x-label for="title" value="Título *" />
            <x-input type="text" wire:model="title" class="mt-1 block w-full" required />
            <x-input-error for="title" class="mt-2" />
        </div>

        <!-- Descrição -->
        <div>
            <x-label for="description" value="Descrição" />
            <textarea wire:model="description" rows="3" class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm"></textarea>
            <x-input-error for="description" class="mt-2" />
        </div>

        <div class="grid grid-cols-2 gap-4">
            <!-- Valor -->
            <div>
                <x-label for="amount" value="Valor *" />
                <x-input type="number" step="0.01" wire:model="amount" class="mt-1 block w-full" required />
                <x-input-error for="amount" class="mt-2" />
            </div>

            <!-- Tipo -->
            <div>
                <x-label for="type" value="Tipo *" />
                <select wire:model="type" class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm">
                    <option value="debit">Débito</option>
                    <option value="credit">Crédito</option>
                </select>
                <x-input-error for="type" class="mt-2" />
            </div>
        </div>

        <div class="grid grid-cols-2 gap-4">
            <!-- Status -->
            <div>
                <x-label for="status" value="Status *" />
                <select wire:model.live="status" class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm">
                    <option value="pending">Pendente</option>
                    <option value="paid">Pago</option>
                </select>
                <x-input-error for="status" class="mt-2" />
            </div>

            <!-- Data de Vencimento -->
            <div>
                <x-label for="dueDate" value="Data de Vencimento *" />
                <x-input type="date" wire:model="dueDate" class="mt-1 block w-full" required />
                <x-input-error for="dueDate" class="mt-2" />
            </div>
        </div>

        <!-- Data de Pagamento (condicional) -->
        @if($status === 'paid')
            <div>
                <x-label for="paidAt" value="Data de Pagamento *" />
                <x-input type="datetime-local" wire:model="paidAt" class="mt-1 block w-full" required />
                <x-input-error for="paidAt" class="mt-2" />
            </div>
        @endif

        <!-- Tags -->
        <div>
            <x-label for="selectedTags" value="Tags" />
            <select wire:model="selectedTags" multiple class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm" size="5">
                @foreach($this->tags as $tag)
                    <option value="{{ $tag->id }}">{{ $tag->name }}</option>
                @endforeach
            </select>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Segure Ctrl/Cmd para selecionar múltiplas tags</p>
            <x-input-error for="selectedTags" class="mt-2" />
        </div>

        <!-- Botões -->
        <div class="flex justify-end gap-4">
            <x-secondary-button type="button" onclick="window.history.back()">
                Cancelar
            </x-secondary-button>

            <x-button wire:loading.attr="disabled">
                <span wire:loading.remove>{{ $editing ? 'Atualizar' : 'Criar' }}</span>
                <span wire:loading>Salvando...</span>
            </x-button>
        </div>
    </div>
</form>
```

### 10. Livewire: Transaction Actions View

**Arquivo**: `resources/views/livewire/transaction-actions.blade.php`

```blade
<div class="flex gap-2 justify-end">
    <!-- Toggle Paid/Pending -->
    <button
        wire:click="togglePaidStatus"
        wire:loading.attr="disabled"
        class="text-sm px-3 py-1 rounded {{ $transaction->isPaid() ? 'bg-yellow-500 hover:bg-yellow-600' : 'bg-green-500 hover:bg-green-600' }} text-white"
        title="{{ $transaction->isPaid() ? 'Marcar como Pendente' : 'Marcar como Pago' }}"
    >
        {{ $transaction->isPaid() ? '↶' : '✓' }}
    </button>

    <!-- Edit -->
    <a href="{{ route('transactions.edit', $transaction) }}" class="text-sm px-3 py-1 rounded bg-blue-500 hover:bg-blue-600 text-white">
        ✎
    </a>

    <!-- Delete -->
    <button
        wire:click="confirmDelete"
        class="text-sm px-3 py-1 rounded bg-red-500 hover:bg-red-600 text-white"
        title="Excluir"
    >
        🗑
    </button>

    <!-- Confirmation Modal -->
    <x-confirmation-modal wire:model.live="confirmingDelete">
        <x-slot name="title">
            Excluir Transação
        </x-slot>

        <x-slot name="content">
            Tem certeza que deseja excluir esta transação? Esta ação não pode ser desfeita.
        </x-slot>

        <x-slot name="footer">
            <x-secondary-button wire:click="$toggle('confirmingDelete')" wire:loading.attr="disabled">
                Cancelar
            </x-secondary-button>

            <x-danger-button class="ms-3" wire:click="delete" wire:loading.attr="disabled">
                Excluir
            </x-danger-button>
        </x-slot>
    </x-confirmation-modal>
</div>
```

## Convenções Tailwind & Jetstream

### Dark Mode
- Sempre adicionar classes dark: para suporte a tema escuro
- Exemplo: `bg-white dark:bg-gray-800`

### Componentes Jetstream
- Usar componentes prontos: `<x-button>`, `<x-input>`, `<x-label>`
- Section components: `<x-form-section>`, `<x-action-section>`
- Modals: `<x-dialog-modal>`, `<x-confirmation-modal>`

### Grid & Layout
- Responsive: `grid-cols-1 md:grid-cols-3`
- Spacing: `space-y-6`, `gap-4`
- Container: `max-w-7xl mx-auto`

### Colors
- Primary: indigo
- Success: green
- Danger: red
- Warning: yellow
- Neutral: gray

## Acceptance Criteria

- [ ] Menu lateral customizado com links corretos
- [ ] Dashboard view criada e funcional
- [ ] Transactions index view criada
- [ ] Transactions create view criada
- [ ] Transactions edit view criada
- [ ] Settings notifications view criada
- [ ] 4 views Livewire criadas
- [ ] Dark mode suportado em todas as views
- [ ] Layout responsivo (mobile-first)
- [ ] Componentes Jetstream usados corretamente
- [ ] Filtros e ordenação funcionais
- [ ] Loading states implementados
- [ ] Modals de confirmação implementados

## Dependências
- Task 06 completa (Componentes Livewire)

## Próxima Task
Task 08: Commands e Integração Mailgun

## Observações
- Views Livewire não precisam de controller render
- Usar `wire:loading` para feedback visual
- Jetstream já fornece componentes prontos
- Tailwind classes devem ser usadas diretamente (não custom CSS)
- Menu responsivo é automático com Alpine.js (vem com Livewire 3)
