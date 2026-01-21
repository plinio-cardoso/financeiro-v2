<div x-data="{
    isOpen: false,
    editingId: @entangle('editingTransactionId'),
    editingRecurringId: @entangle('editingRecurringId'),
    modalCounter: @entangle('modalCounter').live,

    openCreate() {
        this.editingId = null;
        this.editingRecurringId = null;
        this.modalCounter++;
        this.isOpen = true;
    },

    openEditRecurring(recurringId, transactionId) {
        this.editingId = transactionId || null;
        this.editingRecurringId = recurringId;
        this.modalCounter++;
        this.isOpen = true;
    },

    openEditTransaction(transactionId) {
        this.editingId = transactionId;
        this.editingRecurringId = null;
        this.isOpen = true;
    },

    closeModalAndReset() {
        this.isOpen = false;
        $wire.closeModal();
    }
}" @transaction-saved.window="closeModalAndReset()" @recurring-saved.window="closeModalAndReset()"
    @close-modal.window="closeModalAndReset()" @keydown.escape.window="closeModalAndReset()"
    @open-edit-modal.window="openEditTransaction($event.detail.transactionId)"
    @tags-loaded.window="$store.tags.setTags($event.detail.tags)">
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
            <x-custom-select wire:model.live="filterStatus" :options="[]" placeholder="Todos os Status"
                x-init="options = $store.options.statuses; $watch('$store.options.statuses', val => options = val)"
                class="!py-2 !text-xs !font-bold" />
        </div>

        {{-- Type Filter --}}
        <div class="w-40">
            <x-custom-select wire:model.live="filterType" :options="[]" placeholder="Todos os Tipos"
                x-init="options = $store.options.types; $watch('$store.options.types', val => options = val)"
                class="!py-2 !text-xs !font-bold" />
        </div>

        {{-- Recurrence Filter --}}
        <div class="w-44">
            <x-custom-select wire:model.live="filterRecurrence" :options="[
        ['value' => '', 'label' => 'Recorrência (Todos)'],
        ['value' => 'recurring', 'label' => 'Recorrentes'],
        ['value' => 'not_recurring', 'label' => 'Não recorrentes']
    ]" placeholder="Recorrência (Todos)" class="!py-2 !text-xs !font-bold" />
        </div>

        {{-- Tags Filter --}}
        <div class="w-48">
            <x-multi-select wire:model.live="selectedTags" :options="[]" placeholder="Tags"
                x-init="options = $store.tags.list; $watch('$store.tags.list', val => options = val)"
                class="!py-2 !text-xs !font-bold" />
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

            <x-button @click="openCreate()"
                class="!bg-[#4ECDC4] hover:!bg-[#3dbdb5] !text-gray-900 shadow-sm py-1.5 px-4 rounded-lg active:scale-95 transition-all text-[11px] font-bold uppercase tracking-wider">
                Nova Transação
            </x-button>
        </div>

        {{-- Totals Summary Bar --}}
        <div class="flex items-center gap-6 px-6 py-3 bg-white dark:bg-gray-800 rounded-2xl shadow-sm">
            <div class="flex items-center gap-2">
                <span class="text-[10px] font-black uppercase tracking-widest text-gray-600 dark:text-gray-400">
                    Total de Itens
                </span>
                <span
                    class="inline-flex items-center text-sm font-black text-[#4ECDC4] bg-[#4ECDC420] px-2 py-1 rounded-lg">
                    {{ $this->totalCount }}
                </span>
            </div>

            <div class="w-px self-stretch bg-gray-100 dark:bg-gray-700"></div>

            <div class="flex items-center gap-2">
                <span class="text-[10px] font-black uppercase tracking-widest text-gray-600 dark:text-gray-400">
                    Saldo Período
                </span>
                @if($this->totalAmount > 0)
                    <span
                        class="inline-flex items-center text-sm font-black px-2 py-1 rounded-lg !text-emerald-600 dark:!text-emerald-400 !bg-emerald-50 dark:!bg-emerald-500/10">
                        R$ {{ number_format(abs($this->totalAmount), 2, ',', '.') }}
                    </span>
                @elseif($this->totalAmount < 0)
                    <span
                        class="inline-flex items-center text-sm font-black px-2 py-1 rounded-lg !text-rose-600 dark:!text-rose-400 !bg-rose-50 dark:!bg-rose-500/10">
                        R$ {{ number_format(abs($this->totalAmount), 2, ',', '.') }}
                    </span>
                @else
                    <span
                        class="inline-flex items-center text-sm font-black px-2 py-1 rounded-lg !text-gray-600 dark:!text-gray-400 !bg-gray-50 dark:!bg-gray-700/50">
                        R$ {{ number_format(abs($this->totalAmount), 2, ',', '.') }}
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
                                    <span x-text="editingId ? 'Editar Transação' : 'Nova Transação'"></span>
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
                                    {{-- Transaction Form (now handles both transaction and recurrence) --}}
                                    <livewire:transaction-form :transaction-id="$editingTransactionId"
                                        :key="'transaction-' . $modalCounter . '-' . ($editingTransactionId ?? 'new')" />
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Tabela de Transações --}}
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
                            <th scope="col" wire:click="sortBy('status')"
                                class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase cursor-pointer dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-600">
                                <div class="flex items-center gap-1 whitespace-nowrap">
                                    Status
                                    @if ($sortField === 'status')
                                        <span>{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                                    @endif
                                </div>
                            </th>
                            <th scope="col" wire:click="sortBy('due_date')"
                                class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase cursor-pointer dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-600">
                                <div class="flex items-center gap-1 whitespace-nowrap min-w-[120px]">
                                    Vencimento
                                    @if ($sortField === 'due_date')
                                        <span>{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                                    @endif
                                </div>
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
                            <livewire:transaction-row :transaction="$transaction"
                                wire:key="row-{{ $transaction->id }}-{{ $transaction->updated_at?->timestamp ?? time() }}" />
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