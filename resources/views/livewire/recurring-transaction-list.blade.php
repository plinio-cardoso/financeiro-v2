<div x-data="{
    isOpen: false,
    editingId: @entangle('editingRecurringId'),
    modalCounter: @entangle('modalCounter').live,

    openEdit(recurringId) {
        this.editingId = recurringId;
        this.modalCounter++;
        this.isOpen = true;
    },

    closeModalAndReset() {
        this.isOpen = false;
        $wire.closeModal();
    }
}" @recurring-saved.window="closeModalAndReset()" @close-modal.window="closeModalAndReset()"
    @keydown.escape.window="closeModalAndReset()" @tags-loaded.window="$store.tags.setTags($event.detail.tags)">
    {{-- Compact Filters Row --}}
    <div class="flex flex-wrap items-center gap-4 mb-8">
        {{-- Type Filter --}}
        <div class="w-40">
            <x-custom-select wire:model.live="filterType" :options="[]"
                x-init="options = $store.options.types"
                placeholder="Todos os Tipos" class="!py-2 !text-xs !font-bold" />
        </div>

        {{-- Status Filter --}}
        <div class="w-40">
            <x-custom-select wire:model.live="filterStatus" :options="[]"
                x-init="options = $store.options.recurringStatuses"
                placeholder="Todos os Status" class="!py-2 !text-xs !font-bold" />
        </div>

        {{-- Frequency Filter --}}
        <div class="w-44">
            <x-custom-select wire:model.live="filterFrequency" :options="[]"
                x-init="options = $store.options.frequencies"
                placeholder="Todas Frequências" class="!py-2 !text-xs !font-bold" />
        </div>

        <button wire:click="clearFilters" @disabled(!$this->hasActiveFilters) @class([
            'text-xs font-bold uppercase tracking-widest ml-2 transition-colors',
            'text-gray-400 hover:text-[#4ECDC4] cursor-pointer' => $this->hasActiveFilters,
            'text-gray-300 dark:text-gray-600 cursor-not-allowed opacity-50' => !$this->hasActiveFilters,
        ])>
            Limpar filtros
        </button>
    </div>

    {{-- Main Content Card --}}
    <div
        class="bg-white dark:bg-gray-800 rounded-[2rem] border border-gray-100 dark:border-gray-700/50 shadow-sm overflow-hidden">
        {{-- List Header with Search & Actions --}}
        <div
            class="px-6 py-3 border-b border-gray-50 dark:border-gray-700/50 bg-gray-50/50 dark:bg-gray-900/10 flex items-center justify-between">
            <div class="relative w-64" x-data="{
                searchValue: @entangle('search').live,
                localSearch: '',
                timeout: null,
                handleInput() {
                    clearTimeout(this.timeout);
                    this.timeout = setTimeout(() => {
                        if (this.localSearch.length >= 3 || this.localSearch === '') {
                            this.searchValue = this.localSearch;
                        }
                    }, 500);
                }
            }" x-init="localSearch = searchValue">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <svg class="h-3.5 w-3.5 text-gray-400 dark:text-gray-500" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3"
                            d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </div>
                <input type="text" x-model="localSearch" @input="handleInput()" placeholder="Buscar (mín. 3 letras)..."
                    class="block w-full pl-9 pr-4 py-1.5 bg-white dark:bg-gray-900 border border-gray-100 dark:border-gray-700 rounded-lg text-[11px] font-bold text-gray-900 dark:text-gray-100 placeholder-gray-400 dark:placeholder-gray-600 focus:ring-2 focus:ring-[#4ECDC4]/10 focus:border-[#4ECDC4]/50 transition-all shadow-sm">
            </div>
        </div>

        {{-- Totals Summary Bar --}}
        <div class="flex items-center gap-6 px-6 py-3 bg-white dark:bg-gray-800 rounded-2xl shadow-sm">
            <div class="flex items-center gap-2">
                <span class="text-[10px] font-black uppercase tracking-widest text-gray-600 dark:text-gray-400">
                    Total de Recorrências
                </span>
                <span
                    class="inline-flex items-center text-sm font-black text-[#4ECDC4] bg-[#4ECDC420] px-2 py-1 rounded-lg">
                    {{ $this->totalCount }}
                </span>
            </div>

            <div class="w-px self-stretch bg-gray-100 dark:bg-gray-700"></div>

            <div class="flex items-center gap-2">
                <span class="text-[10px] font-black uppercase tracking-widest text-gray-600 dark:text-gray-400">
                    Impacto Mensal Estimado
                </span>
                @if($this->totalMonthlyAmount > 0)
                    <span
                        class="inline-flex items-center text-sm font-black px-2 py-1 rounded-lg !text-emerald-600 dark:!text-emerald-400 !bg-emerald-50 dark:!bg-emerald-500/10">
                        R$ {{ number_format(abs($this->totalMonthlyAmount), 2, ',', '.') }}
                    </span>
                @elseif($this->totalMonthlyAmount < 0)
                    <span
                        class="inline-flex items-center text-sm font-black px-2 py-1 rounded-lg !text-rose-600 dark:!text-rose-400 !bg-rose-50 dark:!bg-rose-500/10">
                        R$ {{ number_format(abs($this->totalMonthlyAmount), 2, ',', '.') }}
                    </span>
                @else
                    <span
                        class="inline-flex items-center text-sm font-black px-2 py-1 rounded-lg !text-gray-600 dark:!text-gray-400 !bg-gray-50 dark:!bg-gray-700/50">
                        R$ {{ number_format(abs($this->totalMonthlyAmount), 2, ',', '.') }}
                    </span>
                @endif
            </div>
        </div>

        {{-- Slide-over Modal (Pure Alpine for speed) --}}
        <div x-show="isOpen" wire:ignore.self class="fixed inset-0 z-50 overflow-hidden" style="display: none;"
            x-transition:enter="transition ease-in-out duration-500" x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in-out duration-500"
            x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">

            <div class="absolute inset-0 overflow-hidden">
                {{-- Backdrop --}}
                <div class="absolute inset-0 bg-gray-500/75 dark:bg-gray-900/80 transition-opacity"
                    @click="closeModalAndReset()"></div>

                <div class="fixed inset-y-0 right-0 flex max-w-full pl-10 pointer-events-none">
                    <div x-show="isOpen" class="w-screen max-w-md pointer-events-auto shadow-2xl"
                        x-transition:enter="transform transition ease-in-out duration-500"
                        x-transition:enter-start="translate-x-full" x-transition:enter-end="translate-x-0"
                        x-transition:leave="transform transition ease-in-out duration-500"
                        x-transition:leave-start="translate-x-0" x-transition:leave-end="translate-x-full">

                        <div class="flex flex-col h-full bg-white dark:bg-gray-900">
                            {{-- Header --}}
                            <div
                                class="px-6 py-6 border-b border-gray-100 dark:border-gray-800 flex items-center justify-between rounded-none">
                                <h2 class="text-xl font-black text-gray-900 dark:text-white uppercase tracking-tighter">
                                    Editar Recorrência
                                </h2>
                                <button type="button" @click="closeModalAndReset()"
                                    class="p-2 text-gray-400 hover:text-gray-500 dark:hover:text-gray-300">
                                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </button>
                            </div>

                            {{-- Content Area --}}
                            <div class="flex-1 flex items-center justify-center relative overflow-hidden">
                                {{-- Component Content - No loader needed as modal opens instantly --}}
                                <div class="w-full h-full px-6 py-8 overflow-y-auto custom-scrollbar">
                                    {{-- Recurring Transaction Form --}}
                                    <livewire:recurring-transaction-form :recurring-id="$editingRecurringId"
                                        :key="'recurring-' . $modalCounter . '-' . ($editingRecurringId ?? 'new')" />
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Tabela de Transações Recorrentes --}}
        <div class="overflow-hidden bg-white shadow dark:bg-gray-800 rounded-none">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-700">
                        <tr>
                            <th scope="col" wire:click="sortBy('title')"
                                class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase cursor-pointer dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-600">
                                <div class="flex items-center gap-1 whitespace-nowrap">
                                    Título
                                    @if ($sortField === 'title')
                                        <span>{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                                    @endif
                                </div>
                            </th>
                            <th scope="col" wire:click="sortBy('amount')"
                                class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase cursor-pointer dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-600">
                                <div class="flex items-center gap-1 whitespace-nowrap">
                                    Valor
                                    @if ($sortField === 'amount')
                                        <span>{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                                    @endif
                                </div>
                            </th>
                            <th scope="col" wire:click="sortBy('type')"
                                class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase cursor-pointer dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-600">
                                <div class="flex items-center gap-1 whitespace-nowrap">
                                    Tipo
                                    @if ($sortField === 'type')
                                        <span>{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                                    @endif
                                </div>
                            </th>
                            <th scope="col" wire:click="sortBy('frequency')"
                                class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase cursor-pointer dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-600">
                                <div class="flex items-center gap-1 whitespace-nowrap">
                                    Frequência
                                    @if ($sortField === 'frequency')
                                        <span>{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                                    @endif
                                </div>
                            </th>
                            <th scope="col" wire:click="sortBy('next_due_date')"
                                class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase cursor-pointer dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-600">
                                <div class="flex items-center gap-1 whitespace-nowrap min-w-[120px]">
                                    Próximo Vencimento
                                    @if ($sortField === 'next_due_date')
                                        <span>{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                                    @endif
                                </div>
                            </th>
                            <th scope="col"
                                class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase dark:text-gray-300">
                                Status
                            </th>
                            <th scope="col" class="relative px-6 py-3">
                                <span class="sr-only">Ações</span>
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200 dark:bg-gray-800 dark:divide-gray-700">
                        @forelse ($this->recurringTransactions as $recurring)
                            <tr class="hover:bg-gray-100 dark:hover:bg-gray-700/30 transition-colors">
                                {{-- Title & Description --}}
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center gap-2">
                                        <svg class="w-4 h-4 text-[#4ECDC4] flex-shrink-0" fill="none"
                                            viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                        </svg>
                                        <div class="min-w-0 flex-1">
                                            <span class="text-sm font-bold text-gray-900 dark:text-gray-100">
                                                {{ $recurring->title }}
                                            </span>
                                            @if ($recurring->description)
                                                <div class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                                    {{ Str::limit($recurring->description, 50) }}
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </td>

                                {{-- Amount --}}
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="text-sm font-bold text-gray-900 dark:text-gray-100">
                                        R$ {{ number_format($recurring->amount, 2, ',', '.') }}
                                    </span>
                                </td>

                                {{-- Type --}}
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span @class([
                                        'inline-flex px-2 text-xs font-semibold leading-5 rounded-full',
                                        'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200' =>
                                            $recurring->type->value === 'debit',
                                        'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' =>
                                            $recurring->type->value === 'credit',
                                    ])>
                                        {{ $recurring->type->value === 'debit' ? 'Débito' : 'Crédito' }}
                                    </span>
                                </td>

                                {{-- Frequency --}}
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="text-sm text-gray-900 dark:text-gray-100">
                                        @switch($recurring->frequency->value)
                                            @case('weekly')
                                                Semanal
                                            @break

                                            @case('monthly')
                                                Mensal
                                            @break

                                            @case('custom')
                                                Personalizada
                                            @break
                                        @endswitch
                                    </span>
                                </td>

                                {{-- Next Due Date --}}
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                        {{ $recurring->next_due_date ? $recurring->next_due_date->format('d/m/Y') : '-' }}
                                    </span>
                                </td>

                                {{-- Status --}}
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span @class([
                                        'inline-flex px-2 text-xs font-semibold leading-5 rounded-full',
                                        'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' =>
                                            $recurring->active,
                                        'bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-200' =>
                                            !$recurring->active,
                                    ])>
                                        {{ $recurring->active ? 'Ativa' : 'Inativa' }}
                                    </span>
                                </td>

                                {{-- Actions --}}
                                <td class="px-2 py-4 text-sm font-medium text-right whitespace-nowrap">
                                    <div class="flex justify-end gap-2 pr-8">
                                        <button @click="openEdit({{ $recurring->id }})"
                                            class="text-gray-400 hover:text-[#4ECDC4] dark:text-gray-500 dark:hover:text-[#4ECDC4] transition-colors p-1 rounded-full hover:bg-[#4ECDC410] dark:hover:bg-[#4ECDC420]"
                                            title="Editar recorrência">
                                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24"
                                                stroke="currentColor">
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
                                    Nenhuma transação recorrente encontrada.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Paginação --}}
            <div class="px-6 py-4 bg-gray-50 dark:bg-gray-700">
                {{ $this->recurringTransactions->links() }}
            </div>
        </div>
    </div>
</div>
