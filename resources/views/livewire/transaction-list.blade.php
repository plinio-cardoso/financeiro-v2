<div>


    {{-- Filtros --}}
    <div class="mb-6 bg-white shadow sm:rounded-lg dark:bg-gray-800">
        <div class="px-4 py-5 sm:p-6">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Filtros</h3>
                <x-button wire:click="createTransaction" class="bg-indigo-600 hover:bg-indigo-700 shadow-md shadow-indigo-500/10">
                    {{ __('Nova Transação') }}
                </x-button>
            </div>

            <x-slide-over wire:model.live="showCreateModal" maxWidth="md">
                <x-slot name="title">
                    {{ $editingTransactionId ? __('Editar Transação') : __('Nova Transação') }}
                </x-slot>

                <livewire:transaction-form :transaction-id="$editingTransactionId" :key="'transaction-form-' . ($editingTransactionId ?? 'new')" />
            </x-slide-over>

            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
                {{-- Search --}}
                <div>
                    <label for="search" class="block text-sm font-bold text-gray-900 dark:text-gray-300">
                        Buscar
                    </label>
                    <input type="text" id="search" wire:model.live="search"
                        class="block w-full mt-1 border-gray-300 text-gray-900 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300"
                        placeholder="Título ou descrição">
                </div>

                {{-- Data Início --}}
                <div>
                    <label for="startDate" class="block text-sm font-bold text-gray-900 dark:text-gray-300">
                        Data Início
                    </label>
                    <input type="date" id="startDate" wire:model.live="startDate"
                        class="block w-full mt-1 border-gray-300 text-gray-900 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300">
                </div>

                {{-- Data Fim --}}
                <div>
                    <label for="endDate" class="block text-sm font-bold text-gray-900 dark:text-gray-300">
                        Data Fim
                    </label>
                    <input type="date" id="endDate" wire:model.live="endDate"
                        class="block w-full mt-1 border-gray-300 text-gray-900 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300">
                </div>

                {{-- Status --}}
                <div>
                    <label for="filterStatus" class="block text-sm font-bold text-gray-900 dark:text-gray-300">
                        Status
                    </label>
                    <x-custom-select wire:model.live="filterStatus" :options="[
        ['value' => '', 'label' => 'Todos'],
        ['value' => 'pending', 'label' => 'Pendente'],
        ['value' => 'paid', 'label' => 'Pago']
    ]"     placeholder="Todos" />
                </div>

                {{-- Tipo --}}
                <div>
                    <label for="filterType" class="block text-sm font-bold text-gray-900 dark:text-gray-300">
                        Tipo
                    </label>
                    <x-custom-select wire:model.live="filterType" :options="[
        ['value' => '', 'label' => 'Todos'],
        ['value' => 'debit', 'label' => 'Débito'],
        ['value' => 'credit', 'label' => 'Crédito']
    ]" placeholder="Todos" />
                </div>

                {{-- Tags --}}
                <div>
                    <label for="selectedTags" class="block text-sm font-bold text-gray-900 dark:text-gray-300">
                        Tags
                    </label>
                    <x-multi-select wire:model.live="selectedTags" :options="$this->tags"
                        placeholder="Selecione as Tags" />
                </div>
            </div>

            <div class="mt-4">
                <button type="button" wire:click="clearFilters"
                    class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 dark:bg-gray-700 dark:text-gray-300 dark:border-gray-600 dark:hover:bg-gray-600">
                    Limpar Filtros
                </button>
            </div>
        </div>
    </div>

    {{-- Tabela de Transações --}}
    <div class="overflow-hidden bg-white shadow sm:rounded-lg dark:bg-gray-800">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-700">
                    <tr>
                        <th scope="col" wire:click="sortBy('title')"
                            class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase cursor-pointer dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-600">
                            Título
                            @if ($sortField === 'title')
                                <span>{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                            @endif
                        </th>
                        <th scope="col" wire:click="sortBy('amount')"
                            class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase cursor-pointer dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-600">
                            Valor
                            @if ($sortField === 'amount')
                                <span>{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                            @endif
                        </th>
                        <th scope="col" wire:click="sortBy('type')"
                            class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase cursor-pointer dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-600">
                            Tipo
                            @if ($sortField === 'type')
                                <span>{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                            @endif
                        </th>
                        <th scope="col" wire:click="sortBy('status')"
                            class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase cursor-pointer dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-600">
                            Status
                            @if ($sortField === 'status')
                                <span>{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                            @endif
                        </th>
                        <th scope="col" wire:click="sortBy('due_date')"
                            class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase cursor-pointer dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-600">
                            Vencimento
                            @if ($sortField === 'due_date')
                                <span>{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                            @endif
                        </th>
                        <th scope="col"
                            class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase dark:text-gray-300">
                            Tags
                        </th>
                        <th scope="col" class="relative px-6 py-3">
                            <span class="sr-only">Ações</span>
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200 dark:bg-gray-800 dark:divide-gray-700">
                    @forelse ($this->transactions as $transaction)
                        <tr wire:key="transaction-{{ $transaction->id }}">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                    {{ $transaction->title }}
                                </div>
                                @if ($transaction->description)
                                    <div class="text-sm text-gray-500 dark:text-gray-400">
                                        {{ Str::limit($transaction->description, 50) }}
                                    </div>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900 dark:text-gray-100">
                                    R$ {{ number_format($transaction->amount, 2, ',', '.') }}
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span
                                    class="inline-flex px-2 text-xs font-semibold leading-5 rounded-full
                                                                    {{ $transaction->type->value === 'debit' ? 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200' : 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' }}">
                                    {{ $transaction->type->value === 'debit' ? 'Débito' : 'Crédito' }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span
                                    class="inline-flex px-2 text-xs font-semibold leading-5 rounded-full
                                                                    {{ $transaction->status->value === 'paid' ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' : 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200' }}">
                                    {{ $transaction->status->value === 'paid' ? 'Pago' : 'Pendente' }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-900 whitespace-nowrap dark:text-gray-100">
                                {{ $transaction->due_date->format('d/m/Y') }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex flex-wrap gap-1">
                                    @foreach ($transaction->tags as $tag)
                                        <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full"
                                            style="background-color: {{ $tag->color }}20; color: {{ $tag->color }}">
                                            {{ $tag->name }}
                                        </span>
                                    @endforeach
                                </div>
                            </td>
                            <td class="px-6 py-4 text-sm font-medium text-right whitespace-nowrap">
                                <div class="flex justify-end gap-2">
                                    @if ($transaction->type->value === 'debit' && $transaction->status->value === 'pending')
                                        <button wire:click="markAsPaid({{ $transaction->id }})"
                                            wire:loading.attr="disabled"
                                            wire:loading.class="opacity-50 cursor-not-allowed"
                                            wire:target="markAsPaid({{ $transaction->id }})"
                                            class="text-green-600 hover:text-green-900 dark:text-green-400 dark:hover:text-green-300 transition-colors p-1 rounded-full hover:bg-green-50 dark:hover:bg-green-900/20"
                                            title="Marcar como Pago">
                                            <svg wire:loading.remove wire:target="markAsPaid({{ $transaction->id }})" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                            </svg>
                                            <svg wire:loading wire:target="markAsPaid({{ $transaction->id }})" class="w-5 h-5 animate-spin" fill="none" viewBox="0 0 24 24">
                                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                            </svg>
                                        </button>
                                    @endif
                                    <button wire:click="editTransaction({{ $transaction->id }})"
                                        class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300 transition-colors p-1 rounded-full hover:bg-indigo-50 dark:hover:bg-indigo-900/20"
                                        title="Editar">
                                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                                        </svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-4 text-sm text-center text-gray-500 dark:text-gray-400">
                                Nenhuma transação encontrada.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Paginação --}}
        <div class="px-6 py-4 bg-gray-50 dark:bg-gray-700">
            {{ $this->transactions->links() }}
        </div>
    </div>


</div>