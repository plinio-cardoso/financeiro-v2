<div>
    {{-- Compact Filters Row --}}
    <div class="flex flex-wrap items-center gap-4 mb-8">
        {{-- Data Range Group --}}
        <div
            class="flex items-center bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 px-1">
            <input type="date" wire:model.live="startDate"
                class="bg-transparent border-none focus:ring-0 text-xs font-bold text-gray-600 dark:text-gray-400 py-2 px-3">
            <div class="w-px h-4 bg-gray-100 dark:bg-gray-700"></div>
            <input type="date" wire:model.live="endDate"
                class="bg-transparent border-none focus:ring-0 text-xs font-bold text-gray-600 dark:text-gray-400 py-2 px-3">
        </div>

        {{-- Status Filter --}}
        <div class="w-40">
            <x-custom-select wire:model.live="filterStatus" :options="[
        ['value' => '', 'label' => 'Todos os Status'],
        ['value' => 'pending', 'label' => 'Pendentes'],
        ['value' => 'paid', 'label' => 'Pagos']
    ]"            placeholder="Todos os Status" class="!py-2 !text-xs !font-bold" />
        </div>

        {{-- Type Filter --}}
        <div class="w-40">
            <x-custom-select wire:model.live="filterType" :options="[
        ['value' => '', 'label' => 'Todos os Tipos'],
        ['value' => 'debit', 'label' => 'Débitos'],
        ['value' => 'credit', 'label' => 'Créditos']
    ]"  placeholder="Todos os Tipos" class="!py-2 !text-xs !font-bold" />
        </div>

        {{-- Tags Filter --}}
        <div class="w-48">
            <x-multi-select wire:model.live="selectedTags" :options="$this->tags" placeholder="Categorias"
                class="!py-2 !text-xs !font-bold" />
        </div>

        <button wire:click="clearFilters"
            class="text-xs font-bold text-gray-400 hover:text-[#4ECDC4] transition-colors uppercase tracking-widest ml-2">
            Limpar filtros
        </button>


    </div>



    {{-- Main Content Card --}}
    <div
        class="bg-white dark:bg-gray-800 rounded-[2rem] border border-gray-100 dark:border-gray-700/50 shadow-sm overflow-hidden">
        {{-- List Header with Search & Actions --}}
        <div
            class="px-6 py-3 border-b border-gray-50 dark:border-gray-700/50 bg-gray-50/50 dark:bg-gray-900/10 flex items-center justify-between">
            <div class="relative w-64">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <svg class="h-3.5 w-3.5 text-gray-400 dark:text-gray-500" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3"
                            d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </div>
                <input type="text" wire:model.live="search" placeholder="Buscar..."
                    class="block w-full pl-9 pr-4 py-1.5 bg-white dark:bg-gray-900 border border-gray-100 dark:border-gray-700 rounded-lg text-[11px] font-bold text-gray-900 dark:text-gray-100 placeholder-gray-400 dark:placeholder-gray-600 focus:ring-2 focus:ring-[#4ECDC4]/10 focus:border-[#4ECDC4]/50 transition-all shadow-sm">
            </div>

            <x-button wire:click="createTransaction"
                class="!bg-[#4ECDC4] hover:!bg-[#3dbdb5] !text-gray-900 shadow-sm py-1.5 px-4 rounded-lg active:scale-95 transition-all text-[11px] font-bold uppercase tracking-wider">
                Nova Transação
            </x-button>
        </div>

        {{-- Totals Summary Bar --}}
        <div class="flex items-center gap-6 px-6 py-3 mb-6 bg-white dark:bg-gray-800 rounded-2xl shadow-sm">
            <div class="flex items-center gap-2">
                <span class="text-[10px] font-black uppercase tracking-widest text-gray-600 dark:text-gray-400">
                    Total de Itens
                </span>
                <span class="text-sm font-black text-[#4ECDC4] bg-[#4ECDC420] px-2 py-0.5 rounded-lg">
                    {{ $this->totalCount }}
                </span>
            </div>

            <div class="w-px h-4 bg-gray-100 dark:bg-gray-700"></div>

            <div class="flex items-center gap-2">
                <span class="text-[10px] font-black uppercase tracking-widest text-gray-600 dark:text-gray-400">
                    Saldo Período
                </span>
                @if($this->totalAmount > 0)
                    <span class="text-sm font-black px-2 py-0.5 rounded-lg !text-emerald-600 dark:!text-emerald-400 !bg-emerald-50 dark:!bg-emerald-500/10">
                        R$ {{ number_format(abs($this->totalAmount), 2, ',', '.') }}
                    </span>
                @elseif($this->totalAmount < 0)
                    <span class="text-sm font-black px-2 py-0.5 rounded-lg !text-rose-600 dark:!text-rose-400 !bg-rose-50 dark:!bg-rose-500/10">
                        R$ {{ number_format(abs($this->totalAmount), 2, ',', '.') }}
                    </span>
                @else
                    <span class="text-sm font-black px-2 py-0.5 rounded-lg !text-gray-600 dark:!text-gray-400 !bg-gray-50 dark:!bg-gray-700/50">
                        R$ {{ number_format(abs($this->totalAmount), 2, ',', '.') }}
                    </span>
                @endif
            </div>
        </div>

        <x-slide-over wire:model.live="showCreateModal" maxWidth="md">
            <x-slot name="title">
                {{ $editingTransactionId ? __('Editar Transação') : __('Nova Transação') }}
            </x-slot>

            <livewire:transaction-form :transaction-id="$editingTransactionId" :key="'transaction-form-' . ($editingTransactionId ?? 'new')" />
        </x-slide-over>

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
                                            <button wire:click="markAsPaid({{ $transaction->id }})" wire:loading.attr="disabled"
                                                wire:loading.class="opacity-50 cursor-not-allowed"
                                                wire:target="markAsPaid({{ $transaction->id }})"
                                                class="text-green-600 hover:text-green-900 dark:text-green-400 dark:hover:text-green-300 transition-colors p-1 rounded-full hover:bg-green-50 dark:hover:bg-green-900/20"
                                                title="Marcar como Pago">
                                                <svg wire:loading.remove wire:target="markAsPaid({{ $transaction->id }})"
                                                    class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                </svg>
                                                <svg wire:loading wire:target="markAsPaid({{ $transaction->id }})"
                                                    class="w-5 h-5 animate-spin" fill="none" viewBox="0 0 24 24">
                                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                                        stroke-width="4"></circle>
                                                    <path class="opacity-75" fill="currentColor"
                                                        d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                                    </path>
                                                </svg>
                                            </button>
                                        @endif
                                        <button wire:click="editTransaction({{ $transaction->id }})"
                                            class="text-[#4ECDC4] hover:text-[#3dbdb5] dark:text-[#4ECDC4] dark:hover:text-[#3dbdb5] transition-colors p-1 rounded-full hover:bg-[#4ECDC410] dark:hover:bg-[#4ECDC420]"
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
</div>