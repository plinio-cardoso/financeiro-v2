<div x-data="{
    isOpen: false,
    editingId: @entangle('editingTransactionId'),
    editingRecurringId: @entangle('editingRecurringId'),

    openCreate() {
        this.editingId = null;
        this.editingRecurringId = null;
        this.isOpen = true;
    },

    openEditRecurring(recurringId, transactionId) {
        this.editingId = transactionId || null;
        this.editingRecurringId = recurringId;
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
    @open-edit-modal.window="openEditTransaction($event.detail.transactionId)">

    {{-- Main Content Card --}}
    <div
        class="bg-white dark:bg-gray-800 rounded-[2rem] border border-gray-100 dark:border-gray-700/50 shadow-sm overflow-hidden">
        {{-- List Header with Actions --}}
        <div
            class="px-6 py-3 border-b border-gray-50 dark:border-gray-700/50 bg-gray-50/50 dark:bg-gray-900/10 flex items-center justify-between">
            <div class="h-10 flex items-center">
                {{-- Global Loading Indicator --}}
                <div wire:loading.flex class="items-center gap-3">
                    <div class="w-4 h-4 border-2 border-[#4ECDC4] border-t-transparent rounded-full animate-spin"></div>
                    <span
                        class="text-[10px] font-black uppercase tracking-widest text-gray-500 dark:text-gray-400 whitespace-nowrap">
                        Sincronizando...
                    </span>
                </div>
            </div>

            <x-button @click="openCreate()"
                class="!bg-[#4ECDC4] hover:!bg-[#3dbdb5] !text-gray-900 shadow-sm py-1.5 px-4 rounded-lg active:scale-95 transition-all text-[11px] font-bold uppercase tracking-wider">
                Nova Transação
            </x-button>
        </div>

        {{-- Totals Summary Bar --}}
        <div
            class="flex items-center gap-6 px-6 py-3 bg-white dark:bg-gray-800 rounded-2xl shadow-sm relative overflow-hidden">
            <div class="flex items-center gap-2">
                <span class="text-[10px] font-black uppercase tracking-widest text-gray-600 dark:text-gray-400">
                    Total de Itens
                </span>
                <div class="relative">
                    <span wire:loading.remove wire:target="applyFilters, sortBy, gotoPage"
                        class="inline-flex items-center text-sm font-black text-[#4ECDC4] bg-[#4ECDC420] px-2 py-1 rounded-lg">
                        {{ $this->totalCount }}
                    </span>
                    <div wire:loading wire:target="applyFilters, sortBy, gotoPage"
                        class="h-7 w-8 bg-gray-200 dark:bg-gray-700 animate-pulse rounded-lg"></div>
                </div>
            </div>

            <div class="w-px self-stretch bg-gray-100 dark:bg-gray-700"></div>

            <div class="flex items-center gap-2">
                <span class="text-[10px] font-black uppercase tracking-widest text-gray-600 dark:text-gray-400">
                    Saldo Período
                </span>
                <div class="relative">
                    <div wire:loading.remove wire:target="applyFilters, sortBy, gotoPage">
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
                    <div wire:loading wire:target="applyFilters, sortBy, gotoPage"
                        class="h-7 w-24 bg-gray-200 dark:bg-gray-700 animate-pulse rounded-lg"></div>
                </div>
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
                            <div class="flex-1 relative overflow-hidden">
                                {{-- Loading Overlay for Modal Content --}}
                                <div wire:loading wire:target="editingTransactionId, editingRecurringId"
                                    class="absolute inset-0 z-[60] bg-white/60 dark:bg-gray-900/60 backdrop-blur-sm flex items-center justify-center">
                                    <div class="flex flex-col items-center gap-4">
                                        <div
                                            class="w-10 h-10 border-4 border-[#4ECDC4] border-t-transparent rounded-full animate-spin">
                                        </div>
                                        <span
                                            class="text-[10px] font-black uppercase tracking-widest text-[#4ECDC4]">Carregando...</span>
                                    </div>
                                </div>

                                {{-- Component Content --}}
                                <div class="w-full h-full px-6 py-8 overflow-y-auto custom-scrollbar">
                                    {{-- Transaction Form (now handles both transaction and recurrence) --}}
                                    <livewire:transaction-form :transaction-id="$editingTransactionId"
                                        :key="'transaction-form-' . ($editingTransactionId ?? 'new')" />
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Tabela de Transações --}}
        <div class="overflow-hidden bg-white shadow dark:bg-gray-800 rounded-none relative">
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
                                Categorias
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